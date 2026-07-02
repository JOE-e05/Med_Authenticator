<?php
session_start();
require_once "../classes/manufacturerManager.php";

if (!isset($_SESSION['manufacturer_logged_in']) || $_SESSION['manufacturer_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$manager = new ManufacturerManager();
$profile = $manager->getProfileByUserId((int) $_SESSION['manufacturer_user_id']);
$approvalHistory = $manager->getApprovalHistoryByUserId((int) $_SESSION['manufacturer_user_id']);

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

                <?php if (!empty($profile['reviewed_at'])): ?>
                    <p style="margin-bottom: 10px;"><strong>Last Reviewed At:</strong> <?php echo htmlspecialchars($profile['reviewed_at']); ?></p>
                <?php endif; ?>

                <?php if (!empty($profile['review_notes'])): ?>
                    <div style="margin: 12px 0 16px; padding: 12px; border-radius: 6px; background: #f8f9fa; border-left: 4px solid #003366;">
                        <strong>Latest Reviewer Note:</strong><br>
                        <?php echo nl2br(htmlspecialchars($profile['review_notes'])); ?>
                    </div>
                <?php endif; ?>

                <p style="color:#555;">An administrator must approve your account before code generation features become available.</p>
                <a href="pending.php" style="display:inline-block; margin-top:14px; color:#003366; font-weight:bold;">Refresh status</a>

                <?php if (!empty($approvalHistory)): ?>
                    <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 16px;">
                        <h3 style="color:#003366; margin:0 0 10px;">Approval Timeline</h3>
                        <div style="max-height: 230px; overflow:auto; background:#f8f9fa; border:1px solid #e2e2e2; border-radius:6px; padding:10px;">
                            <?php foreach ($approvalHistory as $event): ?>
                                <div style="padding: 8px 0; border-bottom: 1px solid #ececec;">
                                    <div style="font-size: 14px; color: #333;">
                                        <strong><?php echo htmlspecialchars($event['new_status']); ?></strong>
                                        <?php if (!empty($event['old_status'])): ?>
                                            (from <?php echo htmlspecialchars($event['old_status']); ?>)
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 13px; color:#666; margin-top: 3px;">
                                        <?php echo htmlspecialchars($event['acted_at']); ?>
                                        <?php if (!empty($event['reviewer_name'])): ?>
                                            by <?php echo htmlspecialchars($event['reviewer_name']); ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($event['action_notes'])): ?>
                                        <div style="font-size: 13px; color:#444; margin-top: 4px;">
                                            <?php echo nl2br(htmlspecialchars($event['action_notes'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
