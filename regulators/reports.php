<?php
require_once "../config/regulator_auth.php";
require_once "../config/csrf.php";
require_once "../classes/regulatorManager.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$regulatorManager = new RegulatorManager();
$successMessage = "";

$search = trim($_GET['search'] ?? '');
$sourceFilter = trim($_GET['source_filter'] ?? '');
$statusFilter = trim($_GET['status_filter'] ?? '');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_report'])) {
    csrf_require_valid_post();

    $reportId = $_POST['reportID'];
    $status = $_POST['status'];
    $adminReview = trim($_POST['admin_review']);
    
    $regulatorId = isset($_SESSION['customerID']) ? $_SESSION['customerID'] : 3; 
    
    if ($regulatorManager->updateReport($reportId, $status, $adminReview, $regulatorId, 'Regulator')) {
        $successMessage = "Report #$reportId successfully updated!";
    }
}

$reports = $regulatorManager->getInvestigationFeed($search, $sourceFilter, $statusFilter);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Investigate Reports | PPB</title>
    <link rel="stylesheet" href="../assets/style.css"> 
</head>
<body>

<?php include("../includes/regulator_sidebar.php"); ?>

<div class="main-content">
    <div class="container container-wide">
        
        <h1 style="color: #6f42c1; margin-bottom: 20px;">Active Investigations</h1>

        <form method="GET" class="card" style="margin-bottom: 20px; display:flex; gap:10px; align-items:end; flex-wrap:wrap;">
            <div style="min-width: 220px;">
                <label style="font-weight:bold; display:block; margin-bottom:6px;">Search Feed</label>
                <input type="text" name="search" placeholder="Code, reporter, or keyword" value="<?php echo htmlspecialchars($search); ?>" style="margin:0;">
            </div>

            <div style="min-width: 190px;">
                <label style="font-weight:bold; display:block; margin-bottom:6px;">Source</label>
                <select name="source_filter" style="margin:0;">
                    <option value="">All sources</option>
                    <option value="Manual" <?php if ($sourceFilter === 'Manual') echo 'selected'; ?>>Manual Reports</option>
                    <option value="AutoAlert" <?php if ($sourceFilter === 'AutoAlert') echo 'selected'; ?>>Auto Alerts</option>
                </select>
            </div>

            <div style="min-width: 220px;">
                <label style="font-weight:bold; display:block; margin-bottom:6px;">Status</label>
                <select name="status_filter" style="margin:0;">
                    <option value="">All statuses</option>
                    <option value="Pending" <?php if ($statusFilter === 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Under Investigation" <?php if ($statusFilter === 'Under Investigation') echo 'selected'; ?>>Under Investigation</option>
                    <option value="Resolved" <?php if ($statusFilter === 'Resolved') echo 'selected'; ?>>Resolved</option>
                </select>
            </div>

            <button type="submit" style="width:auto;">Apply Filters</button>
            <?php if ($search !== '' || $sourceFilter !== '' || $statusFilter !== ''): ?>
                <a href="reports.php" style="color:#dc3545; font-weight:bold; margin-bottom:8px;">Clear</a>
            <?php endif; ?>
        </form>
        
        <?php if($successMessage): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold;">
                 <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <?php if(empty($reports)): ?>
            <div class="card"><p style="text-align: center; color: #666;">No reports have been submitted yet.</p></div>
        <?php else: ?>
            <?php foreach($reports as $report): ?>
                <div class="card" style="margin-bottom: 20px; border-left: 5px solid <?php echo (isset($report['status']) && $report['status'] == 'Resolved') ? '#28a745' : ((isset($report['status']) && $report['status'] == 'Under Investigation') ? '#fd7e14' : '#dc3545'); ?>;">
                    
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">
                        <h3 style="margin: 0; color: #333;">Report ID: #<?php echo htmlspecialchars($report['reportID']); ?></h3>
                        <span style="color: #666; font-size: 14px;">Reported on: <?php echo htmlspecialchars($report['reported_at']); ?></span>
                    </div>

                    <p><strong>Reporter:</strong> <?php echo htmlspecialchars($report['CustomerName'] ?? 'Anonymous'); ?></p>
                    <p><strong>Source:</strong> <?php echo htmlspecialchars($report['source_type'] ?? 'Manual'); ?></p>
                    <p><strong>Suspect Batch/Pack Code:</strong> <span style="color: #dc3545; font-weight: bold;"><?php echo htmlspecialchars($report['batchNumber'] ?? 'N/A'); ?></span></p>
                    <p><strong>Incident Description:</strong> <?php echo htmlspecialchars($report['description']); ?></p>
                    <?php if (!empty($report['verification_log_id'])): ?>
                        <p><strong>Linked Verification ID:</strong> #<?php echo (int) $report['verification_log_id']; ?></p>
                        <p><strong>Verification Type:</strong> <?php echo htmlspecialchars($report['verification_type'] ?? 'N/A'); ?></p>
                        <p><strong>Verification Result:</strong> <?php echo htmlspecialchars($report['verification_result'] ?? 'N/A'); ?></p>
                        <p><strong>Verified At:</strong> <?php echo htmlspecialchars($report['verification_time'] ?? 'N/A'); ?></p>
                    <?php endif; ?>
                    
                    <hr style="border: 0; height: 1px; background: #eee; margin: 20px 0;">

                    <form method="POST" style="background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
                        <?php echo csrf_input_field(); ?>
                        <input type="hidden" name="reportID" value="<?php echo htmlspecialchars($report['reportID']); ?>">
                        
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Investigation Status</label>
                        <select name="status" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="Pending" <?php if(isset($report['status']) && $report['status']=="Pending") echo "selected"; ?>>Pending</option>
                            <option value="Under Investigation" <?php if(isset($report['status']) && $report['status']=="Under Investigation") echo "selected"; ?>>Under Investigation</option>
                            <option value="Resolved" <?php if(isset($report['status']) && $report['status']=="Resolved") echo "selected"; ?>>Resolved</option>
                        </select>

                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Admin Review & Actions Taken</label>
                        <textarea name="admin_review" rows="3" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"><?php echo htmlspecialchars($report['admin_review'] ?? ''); ?></textarea>

                        <button type="submit" name="update_report" style="background-color: #6f42c1; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold;">Save Updates</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</div>

</body>
</html>