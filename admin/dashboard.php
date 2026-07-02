<?php
require_once "../config/admin_auth.php"; 
require_once "../classes/adminManager.php"; 

$adminManager = new AdminManager();

$totalMedicines = $adminManager->getMedicineCount();
$totalUsers = $adminManager->getUserCount();
$totalScans = $adminManager->getVerificationCount();
$pendingManufacturers = $adminManager->getPendingManufacturerCount();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Command Center | Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css"> 
</head>
<body>

<?php include("../includes/admin_sidebar.php"); ?>

<div class="main-content">
    <div class="container container-wide">
        
        <div style="margin-bottom: 20px;">
            <h1 style="color: #003366; margin-bottom: 5px;">System Command Center</h1>
            <p style="color: #666; font-size: 16px;">Welcome back, Administrator. Here is the live status of the Med-Authenticator network.</p>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 30px;">
            
            <div class="card" style="flex: 1; text-align: center; border-top: 5px solid #28a745;">
                <h3 style="color: #666; margin-bottom: 10px;">Generated Batch Records</h3>
                <h1 style="color: #003366; font-size: 40px; margin: 0;"><?php echo $totalMedicines; ?></h1>
            </div>

            <div class="card" style="flex: 1; text-align: center; border-top: 5px solid #0056b3;">
                <h3 style="color: #666; margin-bottom: 10px;">Registered Users</h3>
                <h1 style="color: #003366; font-size: 40px; margin: 0;"><?php echo $totalUsers; ?></h1>
            </div>

            <div class="card" style="flex: 1; text-align: center; border-top: 5px solid #17a2b8;">
                <h3 style="color: #666; margin-bottom: 10px;">Pending Manufacturer Approvals</h3>
                <h1 style="color: #003366; font-size: 40px; margin: 0;"><?php echo $pendingManufacturers; ?></h1>
            </div>

            <div class="card" style="flex: 1; text-align: center; border-top: 5px solid #6f42c1;">
                <h3 style="color: #666; margin-bottom: 10px;">Total Verifications</h3>
                <h1 style="color: #003366; font-size: 40px; margin: 0;"><?php echo $totalScans; ?></h1>
            </div>

        </div>

        <h2 style="color: #003366; margin-bottom: 15px;">Quick Actions</h2>
        <div style="display: flex; gap: 20px;">

            <a href="users.php" style="flex: 1; text-decoration: none;">
                <div class="card" style="transition: 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <h3 style="color: #003366;"> Manage Users</h3>
                    <p style="color: #666; margin-top: 10px;">Oversee system access for Patients, Pharmacists, and Regulators.</p>
                </div>
            </a>

            <a href="manufacturers.php" style="flex: 1; text-decoration: none;">
                <div class="card" style="transition: 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <h3 style="color: #003366;">Review Manufacturers</h3>
                    <p style="color: #666; margin-top: 10px;">Approve or reject pending manufacturer registration requests.</p>
                </div>
            </a>

            <a href="reports.php" style="flex: 1; text-decoration: none;">
                <div class="card" style="transition: 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <h3 style="color: #003366;">View Scan Logs</h3>
                    <p style="color: #666; margin-top: 10px;">Monitor live verification activity and counterfeit alerts.</p>
                </div>
            </a>

        </div>

    </div>
</div>

</body>
</html>