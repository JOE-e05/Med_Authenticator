<?php
require_once "../config/admin_auth.php";
require_once "../classes/adminManager.php";

$adminManager = new AdminManager();

$resultStats = $adminManager->getScanResultStats();
$topBatches = $adminManager->getTopScannedBatches();
$genuineCount = 0;
$counterfeitCount = 0;


foreach ($resultStats as $stat) {
    if (strtolower($stat['result']) == 'genuine' || strtolower($stat['result']) == 'authentic') {
        $genuineCount += $stat['total'];
    } else {
        $counterfeitCount += $stat['total'];
    }
}

$totalScans = $genuineCount + $counterfeitCount;
$genuinePercent = $totalScans > 0 ? round(($genuineCount / $totalScans) * 100) : 0;
$counterfeitPercent = $totalScans > 0 ? round(($counterfeitCount / $totalScans) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Statistics | Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css"> 
</head>
<body>

<?php include("../includes/admin_sidebar.php"); ?>

<div class="main-content">
    <div class="container container-wide">
        
        <div style="margin-bottom: 20px;">
            <h1 style="color: #003366; margin: 0; margin-bottom: 5px;">Data Analytics & Statistics</h1>
            <p style="color: #666; font-size: 16px;">Deep system insights based on patient scanning behavior.</p>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 30px;">
            
            <div class="card" style="flex: 2;">
                <h3 style="color: #003366; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 20px;">Overall Security Health</h3>
                
                <div style="display: flex; justify-content: space-around; text-align: center; margin-bottom: 20px;">
                    <div>
                        <h1 style="color: #28a745; font-size: 45px; margin: 0;"><?php echo $genuineCount; ?></h1>
                        <p style="color: #666; font-weight: bold;">Authentic Scans</p>
                    </div>
                    <div>
                        <h1 style="color: #dc3545; font-size: 45px; margin: 0;"><?php echo $counterfeitCount; ?></h1>
                        <p style="color: #666; font-weight: bold;">Counterfeit Alerts</p>
                    </div>
                </div>

                <div style="width: 100%; background-color: #f1f1f1; border-radius: 20px; overflow: hidden; display: flex; height: 25px;">
                    <div style="width: <?php echo $genuinePercent; ?>%; background-color: #28a745; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">
                        <?php echo $genuinePercent > 0 ? $genuinePercent . '%' : ''; ?>
                    </div>
                    <div style="width: <?php echo $counterfeitPercent; ?>%; background-color: #dc3545; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">
                        <?php echo $counterfeitPercent > 0 ? $counterfeitPercent . '%' : ''; ?>
                    </div>
                </div>
                <p style="text-align: center; color: #999; font-size: 13px; margin-top: 10px;">Ratio of Authentic vs. Counterfeit medications detected in the field.</p>
            </div>

            <div class="card" style="flex: 1;">
                <h3 style="color: #003366; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 15px;">Most Scanned Batches</h3>
                
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php if(empty($topBatches)): ?>
                        <li style="color: #666; padding: 10px 0;">No scan data available yet.</li>
                    <?php else: ?>
                        <?php foreach($topBatches as $index => $batch): ?>
                            <li style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px dashed #eee;">
                                <strong><span style="color: #0056b3;">#<?php echo $index + 1; ?></span> <?php echo htmlspecialchars($batch['batchNumber']); ?></strong>
                                <span style="background-color: #e9ecef; padding: 2px 8px; border-radius: 10px; font-size: 14px; font-weight: bold; color: #555;">
                                    <?php echo $batch['scan_count']; ?> scans
                                </span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

        </div>

    </div>
</div>

</body>
</html>