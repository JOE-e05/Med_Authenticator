<?php
require_once __DIR__ . '/../config/database.php';

class PharmacistManager {
    private $pdo;

    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
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

    public function getVerificationCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM verification_log");
        return (int) $stmt->fetchColumn();
    }


   public function getInventory($searchTerm = "") {
        try {
            if (!empty($searchTerm)) {
                $stmt = $this->pdo->prepare(
                    "SELECT b.med_name AS medName, b.batch_code AS batchNumber,
                            mp.company_name AS manufacturer
                     FROM medicine_batches b
                     LEFT JOIN manufacturer_profiles mp ON mp.user_id = b.manufacturer_user_id
                     WHERE b.med_name LIKE :search OR b.batch_code LIKE :search
                     ORDER BY b.med_name ASC"
                );
                $stmt->execute([':search' => "%$searchTerm%"]);
                return $stmt->fetchAll();
            }

            $stmt = $this->pdo->query(
                "SELECT b.med_name AS medName, b.batch_code AS batchNumber,
                        mp.company_name AS manufacturer
                 FROM medicine_batches b
                 LEFT JOIN manufacturer_profiles mp ON mp.user_id = b.manufacturer_user_id
                 ORDER BY b.med_name ASC"
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            if (!empty($searchTerm)) {
                $stmt = $this->pdo->prepare("SELECT * FROM medicine WHERE medName LIKE :search OR batchNumber LIKE :search ORDER BY medName ASC");
                $stmt->execute([':search' => "%$searchTerm%"]);
                return $stmt->fetchAll();
            }

            $stmt = $this->pdo->query("SELECT * FROM medicine ORDER BY medName ASC");
            return $stmt->fetchAll();
        }
    }

    public function getVerificationLogs($userId = null, $search = '') {
        try {
            $sql = "SELECT v.loginID, v.userID, v.batchNumber, v.verification_type, v.verified_at,
                           v.result, u.CustomerName
                    FROM verification_log v
                    LEFT JOIN users u ON v.userID = u.customerID
                    WHERE 1=1";

            $params = [];

            if ($userId !== null) {
                $sql .= " AND v.userID = :userId";
                $params[':userId'] = $userId;
            }

            if ($search !== '') {
                $sql .= " AND (v.batchNumber LIKE :search OR v.result LIKE :search)";
                $params[':search'] = "%$search%";
            }

            $sql .= " ORDER BY v.verified_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $sql = "SELECT v.loginID, v.userID, v.batchNumber, v.verified_at, v.result, u.CustomerName
                    FROM verification_log v
                    LEFT JOIN users u ON v.userID = u.customerID
                    WHERE 1=1";

            $params = [];
            if ($userId !== null) {
                $sql .= " AND v.userID = :userId";
                $params[':userId'] = $userId;
            }
            if ($search !== '') {
                $sql .= " AND (v.batchNumber LIKE :search OR v.result LIKE :search)";
                $params[':search'] = "%$search%";
            }

            $sql .= " ORDER BY v.verified_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }
    }

    public function getPharmacistVerificationLogs($pharmacistId, $search = '') {
        $pharmacistId = (int) $pharmacistId;
        if ($pharmacistId < 1) {
            return [];
        }

        try {
            $sql = "SELECT v.loginID, v.userID, v.batchNumber, v.verification_type, v.verified_at,
                           v.result, u.CustomerName
                    FROM verification_log v
                    LEFT JOIN users u ON v.userID = u.customerID
                    WHERE v.userID = :userId
                      AND (v.actor_role = 'Pharmacist' OR v.actor_role IS NULL)";

            $params = [':userId' => $pharmacistId];

            if ($search !== '') {
                $sql .= " AND (v.batchNumber LIKE :search OR v.result LIKE :search OR v.verification_type LIKE :search)";
                $params[':search'] = "%$search%";
            }

            $sql .= " ORDER BY v.verified_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $sql = "SELECT v.loginID, v.userID, v.batchNumber, 'Batch' AS verification_type,
                           v.verified_at, v.result, u.CustomerName
                    FROM verification_log v
                    LEFT JOIN users u ON v.userID = u.customerID
                    WHERE v.userID = :userId";

            $params = [':userId' => $pharmacistId];
            if ($search !== '') {
                $sql .= " AND (v.batchNumber LIKE :search OR v.result LIKE :search)";
                $params[':search'] = "%$search%";
            }

            $sql .= " ORDER BY v.verified_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }
    }
}
?>