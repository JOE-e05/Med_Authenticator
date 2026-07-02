<?php
require_once "../config/pharmacist_auth.php";
require_once "../classes/pharmacistManager.php";

$pharmacistManager = new PharmacistManager();

$search = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : "";
$medicines = $pharmacistManager->getInventory($search);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medicine Inventory | Pharmacy Portal</title>
    <link rel="stylesheet" href="../assets/style.css"> 
</head>
<body>

<?php include("../includes/pharmacist_sidebar.php"); ?>

<div class="main-content">
    <div class="container container-wide">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 style="color: #003366; margin: 0;">Medicine Inventory</h1>
            
            <form method="GET" style="display: flex; gap: 10px;">
                <input type="text" name="search" placeholder="Search drug or batch..." value="<?php echo $search; ?>" style="padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 250px;">
                <button type="submit" style="background-color: #003366; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Search</button>
                <?php if($search): ?>
                    <a href="inventory.php" style="background-color: #dc3545; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none;">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <tr style="border-bottom: 2px solid #003366;">
                    <th style="padding-bottom: 10px;">Drug Name</th>
                    <th style="padding-bottom: 10px;">Batch Number</th>
                    <th style="padding-bottom: 10px;">Manufacturer</th>
                    <th style="padding-bottom: 10px;">Status</th>
                </tr>
                
               <?php if(empty($medicines)): ?>
                    <tr><td colspan="4" style="padding: 20px; text-align: center; color: #666;">No verified medicines found in the Whitelist.</td></tr>
                <?php else: ?>
                    <?php foreach($medicines as $med): ?>
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 15px 0;"><strong><?php echo htmlspecialchars($med['medName'] ?? 'Unknown'); ?></strong></td>
                        <td style="color: #003366; font-weight: bold;"><?php echo htmlspecialchars($med['batchNumber']); ?></td>
                        <td><?php echo htmlspecialchars($med['manufacturer'] ?? ($med['manufacture'] ?? 'N/A')); ?></td>
                        <td><span style="color: green; font-weight: bold;"> Whitelisted</span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                
            </table>
        </div>

    </div>
</div>

</body>
</html>