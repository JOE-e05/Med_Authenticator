<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/authorization.php';

class AdminManager {
    private $pdo;

    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
    }

    public function addGenuineMedicine($name, $manufacturer, $batch, $mfg, $exp, $actorRole = 'Admin') {
        authz_require_role($actorRole);

        $sql = "INSERT INTO medicine (medName, manufacture, batchNumber, manufactureDate, expiryDate)
                VALUES (:name, :manufacturer, :batch, :mfg, :exp)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':manufacturer' => $manufacturer,
            ':batch' => $batch,
            ':mfg' => $mfg,
            ':exp' => $exp
        ]);
    }

    public function getAllMedicines() {
        $stmt = $this->pdo->query("SELECT * FROM medicine ORDER BY medName ASC");
        return $stmt->fetchAll();
    }

    public function getMedicineById($medID) {
        $stmt = $this->pdo->prepare("SELECT * FROM medicine WHERE medID = :id");
        $stmt->execute([':id' => $medID]);
        return $stmt->fetch();
    }

    public function updateMedicine($medID, $name, $manufacturer, $batch, $mfg, $exp, $actorRole = 'Admin') {
        authz_require_role($actorRole);

        $sql = "UPDATE medicine
                SET medName = :name, manufacture = :manufacturer, batchNumber = :batch,
                    manufactureDate = :mfg, expiryDate = :exp
                WHERE medID = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':manufacturer' => $manufacturer,
            ':batch' => $batch,
            ':mfg' => $mfg,
            ':exp' => $exp,
            ':id' => $medID
        ]);
    }

    public function deleteMedicine($medID, $actorRole = 'Admin') {
        authz_require_role($actorRole);

        $stmt = $this->pdo->prepare("DELETE FROM medicine WHERE medID = :id");
        return $stmt->execute([':id' => $medID]);
    }

    public function getMedicineCount() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM medicine_batches");
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM medicine");
            return (int) $stmt->fetchColumn();
        }
    }

    public function getUserCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
        return (int) $stmt->fetchColumn();
    }

    public function getVerificationCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM verification_log");
        return (int) $stmt->fetchColumn();
    }

    public function getPendingManufacturerCount() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM manufacturer_profiles WHERE approval_status = 'Pending'");
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getAllUsers() {
        try {
            $sql = "SELECT u.*, mp.company_name, mp.license_number, mp.approval_status
                    FROM users u
                    LEFT JOIN manufacturer_profiles mp ON mp.user_id = u.customerID
                    ORDER BY u.role ASC, u.CustomerName ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $stmt = $this->pdo->query("SELECT * FROM users ORDER BY role ASC, CustomerName ASC");
            return $stmt->fetchAll();
        }
    }

    public function getUserById($customerId) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE customerID = :id");
        $stmt->execute([':id' => $customerId]);
        return $stmt->fetch();
    }

    public function updateUser($customerId, $name, $email, $role, $actorRole = 'Admin') {
        authz_require_role($actorRole);

        $sql = "UPDATE users
                SET CustomerName = :name, email = :email, role = :role
                WHERE customerID = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':role' => $role,
            ':id' => $customerId
        ]);
    }

    public function updateUserStatus($customerId, $status, $actorRole = 'Admin') {
        authz_require_role($actorRole);

        $sql = "UPDATE users SET status = :status WHERE customerID = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $customerId
        ]);
    }

    public function deleteUser($customerId, $actorRole = 'Admin') {
        authz_require_role($actorRole);

        $stmt = $this->pdo->prepare("DELETE FROM users WHERE customerID = :id");
        return $stmt->execute([':id' => $customerId]);
    }

    public function getManufacturerQueue($statusFilter = '') {
        try {
            $sql = "SELECT mp.*, u.CustomerName, u.email, u.status AS user_status
                    FROM manufacturer_profiles mp
                    INNER JOIN users u ON u.customerID = mp.user_id";

            $params = [];
            if ($statusFilter !== '') {
                $sql .= " WHERE mp.approval_status = :status";
                $params[':status'] = $statusFilter;
            }

            $sql .= " ORDER BY mp.submitted_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            $this->attachDuplicateRiskSignals($rows);
            return $rows;
        } catch (PDOException $e) {
            return [];
        }
    }

    private function attachDuplicateRiskSignals(&$queue) {
        if (empty($queue) || !is_array($queue)) {
            return;
        }

        foreach ($queue as &$item) {
            $item['duplicate_risk_score'] = 0;
            $item['duplicate_risk_level'] = 'None';
            $item['duplicate_signals'] = [];
        }
        unset($item);

        try {
            $userRows = $this->pdo->query("SELECT customerID, email, phoneNumber FROM users")->fetchAll();
            $profileRows = $this->pdo->query("SELECT profile_id, company_name, license_number, contact_phone FROM manufacturer_profiles")->fetchAll();
        } catch (PDOException $e) {
            return;
        }

        $emailCounts = [];
        $phoneCounts = [];
        $licenseCounts = [];
        $companyCounts = [];

        foreach ($userRows as $user) {
            $emailKey = $this->normalizeTextKey($user['email'] ?? '');
            if ($emailKey !== '') {
                $emailCounts[$emailKey] = ($emailCounts[$emailKey] ?? 0) + 1;
            }

            $phoneKey = $this->normalizePhoneKey($user['phoneNumber'] ?? '');
            if ($phoneKey !== '') {
                $phoneCounts[$phoneKey] = ($phoneCounts[$phoneKey] ?? 0) + 1;
            }
        }

        foreach ($profileRows as $profile) {
            $licenseKey = $this->normalizeTextKey($profile['license_number'] ?? '');
            if ($licenseKey !== '') {
                $licenseCounts[$licenseKey] = ($licenseCounts[$licenseKey] ?? 0) + 1;
            }

            $companyKey = $this->normalizeTextKey($profile['company_name'] ?? '');
            if ($companyKey !== '') {
                $companyCounts[$companyKey] = ($companyCounts[$companyKey] ?? 0) + 1;
            }

            $contactPhoneKey = $this->normalizePhoneKey($profile['contact_phone'] ?? '');
            if ($contactPhoneKey !== '') {
                $phoneCounts[$contactPhoneKey] = ($phoneCounts[$contactPhoneKey] ?? 0) + 1;
            }
        }

        foreach ($queue as &$item) {
            $riskScore = 0;
            $signals = [];

            $licenseKey = $this->normalizeTextKey($item['license_number'] ?? '');
            if ($licenseKey !== '' && ($licenseCounts[$licenseKey] ?? 0) > 1) {
                $extra = ((int) $licenseCounts[$licenseKey]) - 1;
                $signals[] = 'License number matches ' . $extra . ' other manufacturer profile(s).';
                $riskScore += 4;
            }

            $companyKey = $this->normalizeTextKey($item['company_name'] ?? '');
            if ($companyKey !== '' && ($companyCounts[$companyKey] ?? 0) > 1) {
                $extra = ((int) $companyCounts[$companyKey]) - 1;
                $signals[] = 'Company name matches ' . $extra . ' other manufacturer profile(s).';
                $riskScore += 2;
            }

            $emailKey = $this->normalizeTextKey($item['email'] ?? '');
            if ($emailKey !== '' && ($emailCounts[$emailKey] ?? 0) > 1) {
                $extra = ((int) $emailCounts[$emailKey]) - 1;
                $signals[] = 'Email matches ' . $extra . ' other user account(s).';
                $riskScore += 2;
            }

            $phoneKey = $this->normalizePhoneKey($item['contact_phone'] ?? '');
            if ($phoneKey !== '' && ($phoneCounts[$phoneKey] ?? 0) > 1) {
                $extra = ((int) $phoneCounts[$phoneKey]) - 1;
                $signals[] = 'Contact phone matches ' . $extra . ' other account/profile record(s).';
                $riskScore += 1;
            }

            $item['duplicate_risk_score'] = $riskScore;
            $item['duplicate_risk_level'] = $this->mapDuplicateRiskLevel($riskScore);
            $item['duplicate_signals'] = $signals;
        }
        unset($item);
    }

    private function normalizeTextKey($value) {
        $value = strtolower(trim((string) $value));
        if ($value === '') {
            return '';
        }

        return preg_replace('/\s+/', ' ', $value);
    }

    private function normalizePhoneKey($phone) {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === null || strlen($digits) < 7) {
            return '';
        }

        return $digits;
    }

    private function mapDuplicateRiskLevel($score) {
        $score = (int) $score;
        if ($score >= 5) {
            return 'High';
        }
        if ($score >= 3) {
            return 'Medium';
        }
        if ($score >= 1) {
            return 'Low';
        }

        return 'None';
    }

    public function updateManufacturerApproval($profileId, $newStatus, $reviewNotes, $adminId, $actorRole = 'Admin') {
        authz_require_role($actorRole);

        $allowed = ['Pending', 'Approved', 'Rejected', 'Suspended'];
        if (!in_array($newStatus, $allowed, true)) {
            throw new InvalidArgumentException('Invalid manufacturer status update.');
        }

        $this->pdo->beginTransaction();
        try {
            $currentStmt = $this->pdo->prepare("SELECT user_id, approval_status FROM manufacturer_profiles WHERE profile_id = :id");
            $currentStmt->execute([':id' => $profileId]);
            $current = $currentStmt->fetch();

            if (!$current) {
                throw new RuntimeException('Manufacturer profile not found.');
            }

            $updateStmt = $this->pdo->prepare(
                "UPDATE manufacturer_profiles
                 SET approval_status = :status,
                     reviewed_at = NOW(),
                     reviewed_by = :reviewedBy,
                     review_notes = :notes
                 WHERE profile_id = :id"
            );
            $updateStmt->execute([
                ':status' => $newStatus,
                ':reviewedBy' => $adminId,
                ':notes' => $reviewNotes,
                ':id' => $profileId
            ]);

            $newUserStatus = ($newStatus === 'Approved') ? 1 : 0;
            $userStmt = $this->pdo->prepare("UPDATE users SET status = :status WHERE customerID = :id");
            $userStmt->execute([
                ':status' => $newUserStatus,
                ':id' => $current['user_id']
            ]);

            $logStmt = $this->pdo->prepare(
                "INSERT INTO manufacturer_approval_log
                    (profile_id, old_status, new_status, action_notes, acted_by, acted_at)
                 VALUES
                    (:profileId, :oldStatus, :newStatus, :notes, :actedBy, NOW())"
            );
            $logStmt->execute([
                ':profileId' => $profileId,
                ':oldStatus' => $current['approval_status'],
                ':newStatus' => $newStatus,
                ':notes' => $reviewNotes,
                ':actedBy' => $adminId
            ]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getVerificationLogs() {
        try {
            $sql = "SELECT v.loginID, v.userID, v.batchNumber, v.verification_type, v.actor_role,
                           v.verified_at, v.result, u.CustomerName
                    FROM verification_log v
                    LEFT JOIN users u ON v.userID = u.customerID
                    ORDER BY v.verified_at DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $sql = "SELECT v.loginID, v.userID, v.batchNumber, v.verified_at, v.result, u.CustomerName
                    FROM verification_log v
                    LEFT JOIN users u ON v.userID = u.customerID
                    ORDER BY v.verified_at DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        }
    }

    public function getScanResultStats() {
        try {
            $stmt = $this->pdo->query("SELECT result, COUNT(*) AS total FROM verification_log GROUP BY result");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getTopScannedBatches() {
        try {
            $stmt = $this->pdo->query(
                "SELECT batchNumber, COUNT(*) AS scan_count
                 FROM verification_log
                 GROUP BY batchNumber
                 ORDER BY scan_count DESC
                 LIMIT 5"
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>