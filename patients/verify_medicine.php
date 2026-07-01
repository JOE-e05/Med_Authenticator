<?php
require_once "../config/patient_auth.php";
require_once "../config/database.php";
require_once "../classes/verifier.php";

$message = "";
$medicineDetails = "";
$database = new Database();
$conn = $database->getConnection();

if(isset($_POST['verify'])){
    $batchNumber = trim($_POST['batch_number']);
    $userId = $_SESSION['customerID']; // Pulled from your updated auth

    $verifier = new SystemVerifier();
    $resultData = $verifier->checkBatchNumber($batchNumber);
    $status = $resultData['status'];

    if($status === 'Genuine'){
        $medicine = $resultData['details'];
        $message = "Medicine Verified Successfully";
        // Mapped to your exact columns: medName, manufacture, expiryDate
        $medicineDetails = "
            <strong>Medicine Name:</strong> {$medicine['medName']}<br><br>
            <strong>Manufacturer:</strong> {$medicine['manufacture']}<br><br>
            <strong>Batch Number:</strong> {$medicine['batchNumber']}<br><br>
            <strong>Expiry Date:</strong> {$medicine['expiryDate']}<br><br>
            <strong>Status:</strong> <span style='color: green;'>Genuine</span>
        ";
    } else {
        $message = "Medicine Not Found. Suspected Counterfeit.";
        $medicineDetails = "
            <strong>Entered Batch Number:</strong> " . htmlspecialchars($batchNumber) . "<br><br>
            <strong>Status:</strong> <span style='color: red;'>Suspected Counterfeit</span>
        ";
    }

    // Mapped to your exact 'verification_log' table
    $logQuery = "INSERT INTO verification_log (userID, batchNumber, result) VALUES (:uid, :batch, :result)";
    $logStmt = $conn->prepare($logQuery);
    $logStmt->bindParam(':uid', $userId);
    $logStmt->bindParam(':batch', $batchNumber);
    $logStmt->bindParam(':result', $status);
    $logStmt->execute();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Medicine</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container">
    <div class="card">
        <h2>Verify Medicine</h2><br>
        <form method="POST">
            <label>Batch Number</label>
            <input type="text" name="batch_number" placeholder="Enter Batch Number" required>
            <button type="submit" name="verify">Verify</button>
        </form>
    </div>
    <?php if(!empty($message)){ ?>
        <div class="card">
            <h3>Verification Result</h3><br>
            <p><?php echo $message; ?></p><br>
            <?php echo $medicineDetails; ?>
        </div>
    <?php } ?>
</div>
</body>
</html>