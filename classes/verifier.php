<?php
require_once __DIR__ . '/../config/database.php';

class SystemVerifier {
    private $pdo;

    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
    }

    public function checkBatchNumber($batchNumber, $userId, $actorRole = 'Unknown') {
        return $this->verifyBatchCode($batchNumber, $userId, $actorRole);
    }

    public function verifyBatchCode($batchCode, $userId, $actorRole = 'Unknown') {
        $batchCode = $this->sanitizeCode($batchCode);
        if ($batchCode === '') {
            return [
                'status' => 'Error',
                'message' => 'Batch code cannot be empty.'
            ];
        }

        try {
            $batchData = $this->findBatch($batchCode);

            if ($batchData) {
                $resultStatus = 'Genuine';
                $message = 'SAFE: This batch is authentic and registered by an approved manufacturer.';
            } else {
                $resultStatus = 'Counterfeit';
                $message = 'NOT SAFE: This batch code does not exist in the verified registry.';
            }

            $logId = $this->insertVerificationLog($userId, $batchCode, 'Batch', $resultStatus, $actorRole);

            return [
                'status' => $resultStatus,
                'message' => $message,
                'verification_log_id' => $logId,
                'details' => $batchData
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'Error',
                'message' => 'Verification engine error: ' . $e->getMessage()
            ];
        }
    }

    public function verifyPackCode($packCode, $userId, $actorRole = 'Unknown', $autoAlert = false) {
        $packCode = $this->sanitizeCode($packCode);
        if ($packCode === '') {
            return [
                'status' => 'Error',
                'message' => 'Pack code cannot be empty.'
            ];
        }

        try {
            $packData = $this->findPack($packCode);

            if ($packData) {
                $resultStatus = 'Genuine';
                $message = 'SAFE: This medicine pack code is authentic and valid.';
            } else {
                $resultStatus = 'Counterfeit';
                $message = 'NOT SAFE: This medicine pack code is not found in the verified registry.';
            }

            $logId = $this->insertVerificationLog($userId, $packCode, 'Pack', $resultStatus, $actorRole);

            if ($autoAlert && $resultStatus === 'Counterfeit') {
                $this->createAutoCounterfeitAlert($userId, $packCode, $logId);
            }

            return [
                'status' => $resultStatus,
                'message' => $message,
                'verification_log_id' => $logId,
                'details' => $packData
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'Error',
                'message' => 'Verification engine error: ' . $e->getMessage()
            ];
        }
    }

    private function sanitizeCode($rawCode) {
        return htmlspecialchars(strip_tags(trim((string) $rawCode)));
    }

    private function findBatch($batchCode) {
        try {
            $query = "SELECT b.batch_id, b.med_name, b.batch_code, b.expiry_date,
                             mp.company_name AS manufacturer_name
                      FROM medicine_batches b
                      LEFT JOIN manufacturer_profiles mp ON mp.user_id = b.manufacturer_user_id
                      WHERE b.batch_code = :batchCode
                      LIMIT 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':batchCode' => $batchCode]);
            $row = $stmt->fetch();
            if ($row) {
                return $row;
            }
        } catch (PDOException $e) {
            // Fall back to legacy table for backward compatibility.
        }

        $legacyQuery = "SELECT medID AS batch_id, medName AS med_name, batchNumber AS batch_code,
                               expiryDate AS expiry_date, manufacture AS manufacturer_name
                        FROM medicine
                        WHERE batchNumber = :batchCode
                        LIMIT 1";
        $legacyStmt = $this->pdo->prepare($legacyQuery);
        $legacyStmt->execute([':batchCode' => $batchCode]);
        return $legacyStmt->fetch() ?: null;
    }

    private function findPack($packCode) {
        try {
            $query = "SELECT p.pack_id, p.pack_code, b.batch_code, b.med_name, b.expiry_date,
                             mp.company_name AS manufacturer_name
                      FROM medicine_pack_codes p
                      INNER JOIN medicine_batches b ON b.batch_id = p.batch_id
                      LEFT JOIN manufacturer_profiles mp ON mp.user_id = b.manufacturer_user_id
                      WHERE p.pack_code = :packCode AND p.is_active = 1
                      LIMIT 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':packCode' => $packCode]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    private function insertVerificationLog($userId, $code, $verificationType, $result, $actorRole) {
        try {
            $query = "INSERT INTO verification_log
                        (userID, actor_role, batchNumber, verification_type, result, verified_at)
                      VALUES
                        (:userId, :actorRole, :code, :verificationType, :result, NOW())";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':userId' => $userId,
                ':actorRole' => $actorRole,
                ':code' => $code,
                ':verificationType' => $verificationType,
                ':result' => $result
            ]);
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            $legacyQuery = "INSERT INTO verification_log (userID, batchNumber, result, verified_at)
                            VALUES (:userId, :code, :result, NOW())";
            $legacyStmt = $this->pdo->prepare($legacyQuery);
            $legacyStmt->execute([
                ':userId' => $userId,
                ':code' => $code,
                ':result' => $result
            ]);
            return (int) $this->pdo->lastInsertId();
        }
    }

    private function createAutoCounterfeitAlert($userId, $packCode, $verificationLogId) {
        try {
            $query = "INSERT INTO report
                        (userID, verification_log_id, batchNumber, description, source_type, status, reported_at)
                      VALUES
                        (:userId, :verificationLogId, :batchNumber, :description, 'AutoAlert', 'Pending', NOW())";

            $description = 'Automatically generated alert after counterfeit patient verification.';

            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':userId' => $userId,
                ':verificationLogId' => $verificationLogId,
                ':batchNumber' => $packCode,
                ':description' => $description
            ]);
        } catch (PDOException $e) {
            $query = "INSERT INTO report (userID, batchNumber, description, status)
                      VALUES (:userId, :batchNumber, :description, 'Pending')";
            $description = 'Counterfeit verification detected. Please investigate.';
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':userId' => $userId,
                ':batchNumber' => $packCode,
                ':description' => $description
            ]);
        }
    }
}
?>