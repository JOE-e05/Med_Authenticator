<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/authorization.php';

class ManufacturerManager {
    private $pdo;

    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
    }

    public function registerManufacturer($fullName, $email, $password, $companyName, $licenseNumber, $country, $phone, $address) {
        $this->pdo->beginTransaction();

        try {
            $checkStmt = $this->pdo->prepare("SELECT customerID FROM users WHERE email = :email LIMIT 1");
            $checkStmt->execute([':email' => $email]);
            if ($checkStmt->fetch()) {
                throw new RuntimeException('Email already exists in the system.');
            }

            $licenseStmt = $this->pdo->prepare("SELECT profile_id FROM manufacturer_profiles WHERE license_number = :license LIMIT 1");
            $licenseStmt->execute([':license' => $licenseNumber]);
            if ($licenseStmt->fetch()) {
                throw new RuntimeException('License number is already registered.');
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $userStmt = $this->pdo->prepare(
                "INSERT INTO users (CustomerName, email, passwordHash, role, status, phoneNumber, firstRegistration)
                 VALUES (:name, :email, :passwordHash, 'Manufacturer', 0, :phone, CURDATE())"
            );
            $userStmt->execute([
                ':name' => $fullName,
                ':email' => $email,
                ':passwordHash' => $hashedPassword,
                ':phone' => $phone
            ]);

            $userId = (int) $this->pdo->lastInsertId();

            $profileStmt = $this->pdo->prepare(
                "INSERT INTO manufacturer_profiles
                    (user_id, company_name, license_number, country, contact_phone, address, approval_status, submitted_at)
                 VALUES
                    (:userId, :companyName, :licenseNumber, :country, :phone, :address, 'Pending', NOW())"
            );
            $profileStmt->execute([
                ':userId' => $userId,
                ':companyName' => $companyName,
                ':licenseNumber' => $licenseNumber,
                ':country' => $country,
                ':phone' => $phone,
                ':address' => $address
            ]);

            $this->pdo->commit();
            return true;
        } catch (RuntimeException $e) {
            $this->pdo->rollBack();
            throw $e;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function loginManufacturer($email, $password) {
        $query = "SELECT u.customerID, u.CustomerName, u.email, u.passwordHash, u.status,
                         mp.profile_id, mp.company_name, mp.license_number, mp.approval_status
                  FROM users u
                  LEFT JOIN manufacturer_profiles mp ON mp.user_id = u.customerID
                  WHERE u.email = :email AND u.role = 'Manufacturer'
                  LIMIT 1";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            return null;
        }

        $stored = (string) $user['passwordHash'];
        $passwordValid = false;

        if (password_verify($password, $stored)) {
            $passwordValid = true;
        } elseif ($password === $stored) {
            $passwordValid = true;
        }

        if (!$passwordValid) {
            return null;
        }

        return $user;
    }

    public function getProfileByUserId($userId) {
        $stmt = $this->pdo->prepare(
            "SELECT mp.*, u.CustomerName, u.email
             FROM manufacturer_profiles mp
             INNER JOIN users u ON u.customerID = mp.user_id
             WHERE mp.user_id = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch();
    }

    public function getManufacturerSummary($userId) {
        $summary = [
            'total_batches' => 0,
            'total_pack_codes' => 0
        ];

        try {
            $batchStmt = $this->pdo->prepare("SELECT COUNT(*) FROM medicine_batches WHERE manufacturer_user_id = :id");
            $batchStmt->execute([':id' => $userId]);
            $summary['total_batches'] = (int) $batchStmt->fetchColumn();

            $packStmt = $this->pdo->prepare(
                "SELECT COUNT(*)
                 FROM medicine_pack_codes p
                 INNER JOIN medicine_batches b ON b.batch_id = p.batch_id
                 WHERE b.manufacturer_user_id = :id"
            );
            $packStmt->execute([':id' => $userId]);
            $summary['total_pack_codes'] = (int) $packStmt->fetchColumn();
        } catch (PDOException $e) {
            // Return default zero values if migration not applied.
        }

        return $summary;
    }

    public function createBatchWithPackCodes($userId, $medicineName, $manufactureDate, $expiryDate, $packCount, $actorRole = 'Manufacturer') {
        authz_require_role($actorRole);

        $packCount = (int) $packCount;
        if ($packCount < 1) {
            throw new RuntimeException('Pack quantity must be at least 1.');
        }
        if ($packCount > 5000) {
            throw new RuntimeException('Pack quantity is too large for one request.');
        }

        $profile = $this->getProfileByUserId($userId);
        if (!$profile || $profile['approval_status'] !== 'Approved') {
            throw new RuntimeException('Only approved manufacturers can generate production codes.');
        }

        $this->pdo->beginTransaction();
        try {
            $batchCode = $this->generateUniqueCode('medicine_batches', 'batch_code', 'BTH', 12);

            $batchStmt = $this->pdo->prepare(
                "INSERT INTO medicine_batches
                    (manufacturer_user_id, med_name, batch_code, manufacture_date, expiry_date, planned_pack_count, created_at)
                 VALUES
                    (:userId, :medName, :batchCode, :mfgDate, :expDate, :packCount, NOW())"
            );
            $batchStmt->execute([
                ':userId' => $userId,
                ':medName' => $medicineName,
                ':batchCode' => $batchCode,
                ':mfgDate' => $manufactureDate,
                ':expDate' => $expiryDate,
                ':packCount' => $packCount
            ]);

            $batchId = (int) $this->pdo->lastInsertId();

            $packInsertStmt = $this->pdo->prepare(
                "INSERT INTO medicine_pack_codes (batch_id, pack_code, is_active, created_at)
                 VALUES (:batchId, :packCode, 1, NOW())"
            );

            $packCodes = [];
            for ($i = 0; $i < $packCount; $i++) {
                $packCode = $this->generateUniqueCode('medicine_pack_codes', 'pack_code', 'PK', 16);
                $packInsertStmt->execute([
                    ':batchId' => $batchId,
                    ':packCode' => $packCode
                ]);
                $packCodes[] = $packCode;
            }

            // Preserve legacy compatibility for modules still reading from medicine table.
            $legacyStmt = $this->pdo->prepare(
                "INSERT INTO medicine (medName, manufacture, batchNumber, manufactureDate, expiryDate)
                 VALUES (:name, :manufacturer, :batch, :mfg, :exp)"
            );
            $legacyStmt->execute([
                ':name' => $medicineName,
                ':manufacturer' => $profile['company_name'],
                ':batch' => $batchCode,
                ':mfg' => $manufactureDate,
                ':exp' => $expiryDate
            ]);

            $this->pdo->commit();

            return [
                'batch_code' => $batchCode,
                'pack_codes' => $packCodes,
                'pack_count' => $packCount
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getBatchHistory($userId, $search = '') {
        $sql = "SELECT b.batch_id, b.med_name, b.batch_code, b.manufacture_date, b.expiry_date,
                       b.planned_pack_count, b.created_at, COUNT(p.pack_id) AS generated_packs
                FROM medicine_batches b
                LEFT JOIN medicine_pack_codes p ON p.batch_id = b.batch_id
                WHERE b.manufacturer_user_id = :userId";

        $params = [':userId' => $userId];

        if ($search !== '') {
            $sql .= " AND (b.med_name LIKE :search OR b.batch_code LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " GROUP BY b.batch_id ORDER BY b.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function generateUniqueCode($table, $column, $prefix, $randomLength) {
        $attempt = 0;
        while ($attempt < 20) {
            $attempt++;
            $candidate = $prefix . strtoupper(bin2hex(random_bytes((int) ceil($randomLength / 2))));
            $candidate = substr($candidate, 0, strlen($prefix) + $randomLength);

            $checkStmt = $this->pdo->prepare("SELECT 1 FROM {$table} WHERE {$column} = :code LIMIT 1");
            $checkStmt->execute([':code' => $candidate]);
            if (!$checkStmt->fetch()) {
                return $candidate;
            }
        }

        throw new RuntimeException('Unable to generate a unique code after multiple attempts.');
    }
}
?>
