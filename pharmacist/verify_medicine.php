<?php
require_once "../config/pharmacist_auth.php";
require_once "../classes/verifier.php"; 
$result = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verification_code'])) {
    $verifier = new SystemVerifier();
    $verificationCode = trim($_POST['verification_code']);
    $verificationType = $_POST['verification_type'] ?? 'Batch';
    $pharmacistId = isset($_SESSION['customerID']) ? $_SESSION['customerID'] : 2; 

    if ($verificationType === 'Pack') {
        $result = $verifier->verifyPackCode($verificationCode, $pharmacistId, 'Pharmacist', false);
    } else {
        $result = $verifier->verifyBatchCode($verificationCode, $pharmacistId, 'Pharmacist');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scan Incoming Shipment | Pharmacy Portal</title>
    <link rel="stylesheet" href="../assets/style.css"> 
</head>
<body>

<?php include("../includes/pharmacist_sidebar.php"); ?>

<div class="main-content">
    <div class="container container-wide">
        
        <div style="margin-bottom: 20px;">
            <h1 style="color: #003366; margin: 0; margin-bottom: 5px;">Verify Incoming Shipment</h1>
            <p style="color: #666; font-size: 16px;">Scan medicine batches from your suppliers before adding them to pharmacy shelves.</p>
        </div>

        <div class="card" style="max-width: 600px; padding: 40px; text-align: center; border-top: 5px solid #004080;">
            <form method="POST">
                <label style="display: block; text-align: left; font-weight: bold; margin-bottom: 10px; color: #333;">Verification Type</label>
                <select name="verification_type" style="margin-bottom: 15px;">
                    <option value="Batch">Batch</option>
                    <option value="Pack">Pack</option>
                </select>
                <label style="display: block; text-align: left; font-weight: bold; margin-bottom: 10px; color: #333;">Batch or Pack Code</label>
                <input type="text" name="verification_code" required placeholder="Scan or type code..." style="width: 100%; padding: 15px; font-size: 18px; margin-bottom: 20px; border: 2px solid #ddd; border-radius: 5px; box-sizing: border-box; text-transform: uppercase;">
                <button type="submit" style="width: 100%; background-color: #004080; color: white; padding: 15px; font-size: 18px; font-weight: bold; border: none; border-radius: 5px; cursor: pointer;">Run System Check</button>
            </form>

            <?php if ($result): ?>
                <div style="margin-top: 30px; padding: 20px; border-radius: 5px; font-size: 18px; font-weight: bold; 
                    <?php echo ($result['status'] == 'Genuine') ? 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'; ?>">
                    <?php echo htmlspecialchars($result['message']); ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>