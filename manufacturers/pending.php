<?php
session_start();
require_once "../classes/manufacturerManager.php";

if (!isset($_SESSION['manufacturer_logged_in']) || $_SESSION['manufacturer_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$manager = new ManufacturerManager();
$profile = $manager->getProfileByUserId((int) $_SESSION['manufacturer_user_id']);

if ($profile && $profile['approval_status'] === 'Approved') {
    $_SESSION['manufacturer_approval_status'] = 'Approved';
    header('Location: dashboard.php');
    exit();
}

$status = $profile['approval_status'] ?? 'Pending';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approval Pending | Manufacturer Portal</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php include("../includes/manufacturer_sidebar.php"); ?>

    <div class="main-content">
        <div class="container container-wide">
            <div class="card" style="max-width: 760px; border-top: 5px solid #fd7e14;">
                <h2 style="color: #003366; margin-bottom: 10px;">Account Review In Progress</h2>
                <p style="color: #666; margin-bottom: 20px;">Your registration is currently in <strong><?php echo htmlspecialchars($status); ?></strong> state.</p>

                <p style="margin-bottom: 10px;"><strong>Company:</strong> <?php echo htmlspecialchars($profile['company_name'] ?? ($_SESSION['manufacturer_company_name'] ?? 'N/A')); ?></p>
                <p style="margin-bottom: 10px;"><strong>License Number:</strong> <?php echo htmlspecialchars($profile['license_number'] ?? ($_SESSION['manufacturer_license_number'] ?? 'N/A')); ?></p>
                <p style="margin-bottom: 20px;"><strong>Submitted At:</strong> <?php echo htmlspecialchars($profile['submitted_at'] ?? 'N/A'); ?></p>

                <p style="color:#555;">An administrator must approve your account before code generation features become available.</p>
                <a href="pending.php" style="display:inline-block; margin-top:14px; color:#003366; font-weight:bold;">Refresh status</a>
            </div>
        </div>
    </div>
</body>
</html>
