<?php
require_once "../config/admin_auth.php";
require_once "../classes/AdminManager.php";

$adminManager = new AdminManager();

$logs = $adminManager->getVerificationLogs();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Reports | Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css"> 
</head>
<body>

<?php include("../includes/admin_sidebar.php"); ?>

<div class="main-content">
    <div class="container container-wide">
        
        <div style="margin-bottom: 20px;">
            <h1 style="color: #003366; margin: 0; margin-bottom: 5px;">System Reports & Scan Logs</h1>
            <p style="color: #666; font-size: 16px;">Live monitoring of all batch verifications performed on the system.</p>
        </div>

        <div class="card">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <tr style="border-bottom: 2px solid #003366;">
                    <th style="padding-bottom: 10px;">Scan ID</th>
                    <th style="padding-bottom: 10px;">User (Patient)</th>
                    <th style="padding-bottom: 10px;">Batch Scanned</th>
                    <th style="padding-bottom: 10px;">Date & Time</th>
                    <th style="padding-bottom: 10px;">Result</th>
                </tr>
                
                <?php foreach($logs as $log): ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 15px 0; color: #666;">#<?php echo htmlspecialchars($log['loginID']); ?></td>
                    
                    <td><strong><?php echo htmlspecialchars($log['CustomerName'] ?? 'Unknown User'); ?></strong></td>
                    
                    <td><strong style="color: #003366;"><?php echo htmlspecialchars($log['batchNumber']); ?></strong></td>
                    
                    <td><?php echo htmlspecialchars($log['verified_at']); ?></td>
                    
                    <td>
                        <?php 
                    
                        if (strtolower($log['result']) == 'genuine' || strtolower($log['result']) == 'authentic') {
                            echo '<span style="color: green; font-weight: bold;"> Safe ' . htmlspecialchars($log['result']) . '</span>';
                        } else {
                            echo '<span style="color: red; font-weight: bold;"> Not safe ' . htmlspecialchars($log['result']) . '</span>';
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                
            </table>
            
            <?php if(empty($logs)): ?>
                <p style="text-align: center; color: #666; margin-top: 20px;">No verification logs found in the system yet.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>