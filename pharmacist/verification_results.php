<?php
require_once "../config/pharmacist_auth.php";
require_once "../classes/pharmacistManager.php";

$pharmacistManager = new PharmacistManager();
$search = trim($_GET['search'] ?? '');
$pharmacistId = isset($_SESSION['customerID']) ? (int) $_SESSION['customerID'] : null;
$logs = $pharmacistManager->getVerificationLogs(null, $search);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verification Results | Pharmacy Portal</title>
    <link rel="stylesheet" href="../assets/style.css"> 
</head>
<body>

<?php include("../includes/pharmacist_sidebar.php"); ?>

<div class="main-content">
    <div class="container container-wide">
        
        <div style="margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center; gap:10px;">
            <div>
            <h1 style="color: #003366; margin: 0; margin-bottom: 5px;">Verification Logs</h1>
            <p style="color: #666; font-size: 16px;">Monitor all medication scans happening across the system.</p>
            </div>

            <form method="GET" style="display:flex; gap:10px; align-items:center;">
                <input type="text" name="search" placeholder="Search code or result" value="<?php echo htmlspecialchars($search); ?>" style="width:240px; margin:0;">
                <button type="submit" style="width:auto;">Search</button>
                <?php if ($search !== ''): ?>
                    <a href="verification_results.php" style="color:#dc3545; font-weight:bold;">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <tr style="border-bottom: 2px solid #003366;">
                    <th style="padding-bottom: 10px;">Patient Name</th>
                    <th style="padding-bottom: 10px;">Code Scanned</th>
                    <th style="padding-bottom: 10px;">Type</th>
                    <th style="padding-bottom: 10px;">Date & Time</th>
                    <th style="padding-bottom: 10px;">Result</th>
                </tr>
                
                <?php if(empty($logs)): ?>
                    <tr><td colspan="5" style="padding: 20px; text-align: center; color: #666;">No scans recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach($logs as $log): ?>
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 15px 0;"><strong><?php echo htmlspecialchars($log['CustomerName'] ?? 'Unknown'); ?></strong></td>
                        <td style="color: #003366; font-weight: bold;"><?php echo htmlspecialchars($log['batchNumber']); ?></td>
                        <td><?php echo htmlspecialchars($log['verification_type'] ?? 'Batch'); ?></td>
                        <td><?php echo htmlspecialchars($log['verified_at']); ?></td>
                        <td>
                            <?php 
                            if (strtolower($log['result']) == 'genuine' || strtolower($log['result']) == 'authentic') {
                                echo '<span style="color: green; font-weight: bold;"> ' . htmlspecialchars($log['result']) . '</span>';
                            } else {
                                echo '<span style="color: red; font-weight: bold;"> ' . htmlspecialchars($log['result']) . '</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                
            </table>
        </div>

    </div>
</div>

</body>
</html>