<?php

class AdminManager {
    private $pdo;

    public function __construct() {
        $host = 'localhost';
        $db   = 'system database'; 
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        
        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (\PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function addGenuineMedicine($name, $manufacturer, $batch, $mfg, $exp) {
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

    public function updateMedicine($medID, $name, $manufacturer, $batch, $mfg, $exp) {
        $sql = "UPDATE medicine 
                SET medName = :name, manufacture = :manufacturer, batchNumber = :batch, manufactureDate = :mfg, expiryDate = :exp 
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

    public function deleteMedicine($medID) {
        $stmt = $this->pdo->prepare("DELETE FROM medicine WHERE medID = :id");
        return $stmt->execute([':id' => $medID]);
    }
    
    public function getMedicineCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM medicine");
        return $stmt->fetchColumn();
    }

    public function getUserCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
        return $stmt->fetchColumn();
    }

    public function getVerificationCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM verification_log");
        return $stmt->fetchColumn();
    }

    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY role ASC, CustomerName ASC");
        return $stmt->fetchAll();
    }

    public function getUserById($customerId) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE customerID = :id");
        $stmt->execute([':id' => $customerId]);
        return $stmt->fetch();
    }

    public function updateUser($customerId, $name, $email, $role) {
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

    public function updateUserStatus($customerId, $status) {

        $sql = "UPDATE users SET status = :status WHERE customerID = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $customerId
        ]);
    }

    public function deleteUser($customerId) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE customerID = :id");
        return $stmt->execute([':id' => $customerId]);
    }

    public function getVerificationLogs() {
        $sql = "SELECT v.loginID, v.userID, v.batchNumber, v.verified_at, v.result, u.CustomerName 
                FROM verification_log v 
                LEFT JOIN users u ON v.userID = u.customerID 
                ORDER BY v.verified_at DESC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function getScanResultStats() {
        $stmt = $this->pdo->query("SELECT result, COUNT(*) as total FROM verification_log GROUP BY result");
        return $stmt->fetchAll();
    }

    public function getTopScannedBatches() {
        $stmt = $this->pdo->query
        ("SELECT batchNumber, COUNT(*) as scan_count 
                                   FROM verification_log 
                                   GROUP BY batchNumber 
                                   ORDER BY scan_count DESC LIMIT 5");
        return $stmt->fetchAll();
    }
}
?>