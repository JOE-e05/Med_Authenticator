<?php
require_once "../config/patient_auth.php";
require_once "../classes/verifier.php";

$message = "";
$medicineDetails = "";

if(isset($_POST['verify'])){
    $packCode = trim($_POST['pack_code']);
    $userId = $_SESSION['customerID'];

    $verifier = new SystemVerifier();
    $resultData = $verifier->verifyPackCode($packCode, $userId, 'Patient', true);
    $status = $resultData['status'];

    if($status === 'Genuine'){
        $medicine = $resultData['details'] ?? [];
        $message = "Medicine Verified Successfully";
       
        $medicineDetails = "
            <strong>Medicine Name:</strong> " . htmlspecialchars($medicine['med_name'] ?? 'N/A') . "<br><br>
            <strong>Manufacturer:</strong> " . htmlspecialchars($medicine['manufacturer_name'] ?? 'N/A') . "<br><br>
            <strong>Batch Code:</strong> " . htmlspecialchars($medicine['batch_code'] ?? 'N/A') . "<br><br>
            <strong>Pack Code:</strong> " . htmlspecialchars($medicine['pack_code'] ?? $packCode) . "<br><br>
            <strong>Expiry Date:</strong> " . htmlspecialchars($medicine['expiry_date'] ?? 'N/A') . "<br><br>
            <strong>Status:</strong> <span style='color: green;'>Genuine</span>
        ";
    } else {
        $message = "Medicine Not Found. Suspected Counterfeit.";
        $medicineDetails = "
            <strong>Entered Pack Code:</strong> " . htmlspecialchars($packCode) . "<br><br>
            <strong>Status:</strong> <span style='color: red;'>Suspected Counterfeit</span>
            <br><br><strong>Action:</strong> An alert has been automatically sent to administrators and regulators.
        ";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Medicine</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include("../includes/patient_sidebar.php"); ?>

<div class="main-content">
    <div class="container container-wide">
        <div class="card" style="max-width: 760px;">
            <h2>Verify Medicine Pack Code</h2><br>
            <form method="POST">
                <label>Pack Code</label>
                <input type="text" name="pack_code" placeholder="Enter Pack Code" required>
                <button type="submit" name="verify">Verify</button>
            </form>
        </div>

        <?php if(!empty($message)){ ?>
            <div class="card" style="max-width: 760px; margin-top: 15px;">
                <h3>Verification Result</h3><br>
                <p><?php echo htmlspecialchars($message); ?></p><br>
                <?php echo $medicineDetails; ?>
            </div>
        <?php } ?>
    </div>
</div>
</body>
</html>