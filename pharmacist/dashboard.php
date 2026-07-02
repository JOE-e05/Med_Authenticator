<?php
require_once "../config/pharmacist_auth.php"; 
require_once "../classes/pharmacistManager.php";

$pharmacistManager = new PharmacistManager();

$totalMedicines = $pharmacistManager->getMedicineCount();
$totalVerifications = $pharmacistManager->getVerificationCount();

$pharmacistName = isset($_SESSION['CustomerName']) ? $_SESSION['CustomerName'] : "Pharmacist";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pharmacy Portal | Med-Authenticator</title>
    <link rel="stylesheet" href="../assets/style.css"> 
</head>
<body>

<?php include("../includes/pharmacist_sidebar.php"); ?>

<div class="main-content">
    <div class="container container-wide">
        
        <div style="margin-bottom: 20px;">
            <h1 style="color: #003366; margin-bottom: 5px;">Pharmacy Control Portal</h1>
            <p style="color: #666; font-size: 16px;">Welcome to your shift, <strong><?php echo htmlspecialchars($pharmacistName); ?></strong>. Here is your inventory overview.</p>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 30px;">
            <div class="card" style="flex: 1; text-align: center; border-top: 5px solid #17a2b8;">
                <h3 style="color: #666; margin-bottom: 10px;">Total Verified Medicines</h3>
                <h1 style="color: #003366; font-size: 40px; margin: 0;"><?php echo $totalMedicines; ?></h1>
            </div>

            <div class="card" style="flex: 1; text-align: center; border-top: 5px solid #28a745;">
                <h3 style="color: #666; margin-bottom: 10px;">System Verification Scans</h3>
                <h1 style="color: #003366; font-size: 40px; margin: 0;"><?php echo $totalVerifications; ?></h1>
            </div>
        </div>

        <h2 style="color: #003366; margin-bottom: 15px;">Pharmacy Tasks</h2>
        <div style="display: flex; gap: 20px;">
            
            <a href="inventory.php" style="flex: 1; text-decoration: none;">
                <div class="card" style="transition: 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <h3 style="color: #003366;"> Medicine Inventory</h3>
                    <p style="color: #666; margin-top: 10px;">View the authorized Whitelist of genuine medications.</p>
                </div>
            </a>

            <a href="verification_results.php" style="flex: 1; text-decoration: none;">
                <div class="card" style="transition: 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <h3 style="color: #003366;"> Verification Logs</h3>
                        <p style="color: #666; margin-top: 10px;">Review public scan history to monitor for counterfeit spikes.</p>
                </div>
            </a>

        </div>

    </div>
</div>

</body>
</html>