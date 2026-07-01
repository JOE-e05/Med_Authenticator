<?php

require_once __DIR__ . '/../config/database.php';

class SystemVerifier {
    private $dbConnection;

    public function __construct() {
        $database = new Database();
        $this->dbConnection = $database->getConnection();
    }

    public function checkBatchNumber($batchNumber) {
        $batchNumber = htmlspecialchars(strip_tags(trim($batchNumber)));

        if (empty($batchNumber)) {
            return [
                'status' => 'Error',
                'message' => 'Batch number cannot be empty.'
            ];
        }

        try {
            $query = "SELECT * FROM Medicine WHERE batchNumber = :batchNumber LIMIT 1";
            $stmt = $this->dbConnection->prepare($query);
            $stmt->bindParam(':batchNumber', $batchNumber);
            $stmt->execute();

           if ($stmt->rowCount() > 0) {
                return [
                    'status' => 'Genuine',
                    'message' => 'This medicine is authentic.',
                    'details' => $stmt->fetch(PDO::FETCH_ASSOC)
                ];
            } else {
                return [
                    'status' => 'Counterfeit',
                    'message' => 'Warning: This batch number cannot be found.'
                ];
            }

        } catch (PDOException $e) {
            return [
                'status' => 'Error',
                'message' => 'Verification engine error: ' . $e->getMessage()
            ];
        }
    }
}
?>