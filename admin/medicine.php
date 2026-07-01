<?php
require_once "../config/admin_auth.php";
require_once "../classes/AdminManager.php"; 

$adminManager = new AdminManager();
$message = "";


if (isset($_GET['delete'])) {
    try {
        if ($adminManager->deleteMedicine($_GET['delete'])) {
            $message = "<div style='color: #856404; background-color: #fff3cd; padding: 10px; border: 1px solid #ffeeba; border-radius: 5px; margin-bottom: 15px;'> Batch successfully removed from records.</div>";
        }
    } catch (Exception $e) {
        $message = "<div style='color: red; font-weight: bold; margin-bottom: 15px;'> Error: " . $e->getMessage() . "</div>";
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_medicine'])) {
    $name = $_POST['medicine_name'];
    $manufacturer = $_POST['manufacturer'];
    $batch = $_POST['batch_number'];
    $mfg = $_POST['manufacture_date'];
    $exp = $_POST['expiry_date'];

    try {
        if ($adminManager->addGenuineMedicine($name, $manufacturer, $batch, $mfg, $exp)) {
            $message = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 15px;'> Genuine Medicine successfully authorized.</div>";
        }
    } catch (Exception $e) {
        $message = "<div style='color: red; font-weight: bold; margin-bottom: 15px;'> Error: " . $e->getMessage() . "</div>";
    }
}

$medicines = $adminManager->getAllMedicines();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage batch numbers | Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css"> 
</head>
<body>
<?php include("../includes/admin_sidebar.php"); ?>
<div class="main-content">
        <div class="container">
        
        <div class="card">
            <h2 style="color: #003366;">Add Genuine Batch</h2>
            <p>Register a verified medication batch directly from the manufacturer.</p>
            <br>
            
            <?php echo $message; ?>

            <form method="POST">
                <label>Medicine Name</label>
                <input type="text" name="medicine_name" placeholder="e.g. Amoxicillin" required>
                
                <label>Manufacturer</label>
                <input type="text" name="manufacturer" placeholder="e.g. GlaxoSmithKline" required>
                
                <label>Batch Number</label>
                <input type="text" name="batch_number" placeholder="e.g. BATCH-8899" required>
                
                <label>Manufacture Date</label>
                <input type="date" name="manufacture_date" required>
                
                <label>Expiry Date</label>
                <input type="date" name="expiry_date" required>
                
                <button type="submit" name="add_medicine">Authorize Batch</button>
            </form>
        </div>

        <br>

        <div class="card">
            <h2 style="color: #003366;">Verified batch Inventory</h2>
            <br>
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <tr style="border-bottom: 2px solid #003366;">
                    <th style="padding-bottom: 10px;">Name</th>
                    <th style="padding-bottom: 10px;">Manufacturer</th>
                    <th style="padding-bottom: 10px;">Batch Number</th>
                    <th style="padding-bottom: 10px;">Expiry Date</th>
                    <th style="padding-bottom: 10px;">Actions</th>
                </tr>
                
                <?php foreach($medicines as $med): ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 15px 0;"><?php echo htmlspecialchars($med['medName']); ?></td>
                    <td><?php echo htmlspecialchars($med['manufacture']); ?></td>
                    <td><strong><?php echo htmlspecialchars($med['batchNumber']); ?></strong></td>
                    <td><?php echo htmlspecialchars($med['expiryDate']); ?></td>
                    <td>
                        <a href="edit_medicine.php?id=<?php echo $med['medID']; ?>" style="color: #0056b3; font-weight: bold; text-decoration: none; margin-right: 10px;">Edit</a>
                        <a href="medicine.php?delete=<?php echo $med['medID']; ?>" onclick="return confirm('WARNING: Are you sure you want to remove this genuine batch from the records?');" style="color: #dc3545; font-weight: bold; text-decoration: none;">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                
            </table>
        </div>

    </div>
</div>

</body>
</html>