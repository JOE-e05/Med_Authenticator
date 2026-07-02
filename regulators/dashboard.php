<?php
require_once "../config/regulator_auth.php"; 
require_once "../classes/regulatorManager.php";

$regulatorManager = new RegulatorManager();

$pending = $regulatorManager->getStatusCount('Pending');
$investigating = $regulatorManager->getStatusCount('Under Investigation');
$resolved = $regulatorManager->getStatusCount('Resolved');
$verificationSummary = $regulatorManager->getVerificationSummary();

$regulatorName = isset($_SESSION['CustomerName']) ? $_SESSION['CustomerName'] : "Chief Regulator";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Regulator Dashboard | PPB</title>
    <link rel="stylesheet" href="../assets/style.css"> 
</head>
<body>

<?php include("../includes/regulator_sidebar.php"); ?>

<div class="main-content">
    <div class="container container-wide">
        
        <div style="margin-bottom: 20px;">
            <h1 style="color: #6f42c1; margin-bottom: 5px;">Pharmacy & Poisons Board Oversight</h1>
            <p style="color: #666; font-size: 16px;">Welcome, <strong><?php echo htmlspecialchars($regulatorName); ?></strong>. Here is the national report overview.</p>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 30px;">
            <div class="card" style="flex: 1; text-align: center; border-top: 5px solid #dc3545;">
                <h3 style="color: #666; margin-bottom: 10px;">Pending Reports</h3>
                <h1 style="color: #dc3545; font-size: 40px; margin: 0;"><?php echo $pending; ?></h1>
            </div>
            <div class="card" style="flex: 1; text-align: center; border-top: 5px solid #fd7e14;">
                <h3 style="color: #666; margin-bottom: 10px;">Under Investigation</h3>
                <h1 style="color: #fd7e14; font-size: 40px; margin: 0;"><?php echo $investigating; ?></h1>
            </div>
            <div class="card" style="flex: 1; text-align: center; border-top: 5px solid #28a745;">
                <h3 style="color: #666; margin-bottom: 10px;">Resolved</h3>
                <h1 style="color: #28a745; font-size: 40px; margin: 0;"><?php echo $resolved; ?></h1>
            </div>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 30px;">
            <div class="card" style="flex: 1; text-align: center; border-top: 5px solid #17a2b8;">
                <h3 style="color: #666; margin-bottom: 10px;">Total Verifications</h3>
                <h1 style="color: #003366; font-size: 40px; margin: 0;"><?php echo (int) $verificationSummary['total']; ?></h1>
            </div>
            <div class="card" style="flex: 1; text-align: center; border-top: 5px solid #28a745;">
                <h3 style="color: #666; margin-bottom: 10px;">Genuine Scans</h3>
                <h1 style="color: #003366; font-size: 40px; margin: 0;"><?php echo (int) $verificationSummary['genuine']; ?></h1>
            </div>
            <div class="card" style="flex: 1; text-align: center; border-top: 5px solid #dc3545;">
                <h3 style="color: #666; margin-bottom: 10px;">Counterfeit Scans</h3>
                <h1 style="color: #003366; font-size: 40px; margin: 0;"><?php echo (int) $verificationSummary['counterfeit']; ?></h1>
            </div>
        </div>

        <h2 style="color: #333; margin-bottom: 15px;">Oversight Actions</h2>
        <div style="display: flex; gap: 20px;">
            <a href="reports.php" style="flex: 1; text-decoration: none;">
                <div class="card" style="transition: 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <h3 style="color: #6f42c1;"> Investigate Reports</h3>
                    <p style="color: #666; margin-top: 10px;">Review and update the status of public counterfeit reports.</p>
                </div>
            </a>
        </div>

    </div>
</div>

</body>
</html>