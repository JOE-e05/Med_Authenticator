<?php
 require_once "../config/regulator_auth.php";

$regulatorName = isset($_SESSION['CustomerName']) ? $_SESSION['CustomerName'] : "Chief Regulator";
$regulatorEmail = isset($_SESSION['email']) ? $_SESSION['email'] : "ppb@kenya.go.ke";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | PPB Portal</title>
    <link rel="stylesheet" href="../assets/style.css"> 
</head>
<body>

<?php include("../includes/regulator_sidebar.php"); ?>
<div class="main-content">
    <div class="container container-wide">
        
        <div style="margin-bottom: 20px;">
            <h1 style="color: #6f42c1; margin-bottom: 5px;">Regulator Profile</h1>
            <p style="color: #666; font-size: 16px;">Pharmacy and Poisons Board Administration.</p>
        </div>

        <div class="card" style="max-width: 600px; border-top: 5px solid #6f42c1;">
            
            <div style="display: flex; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
                <div style="width: 80px; height: 80px; background-color: #6f42c1; color: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 30px; font-weight: bold; margin-right: 20px;">
                    <?php echo substr($regulatorName, 0, 1); ?>
                </div>
                <div>
                    <h2 style="color: #333; margin: 0;"><?php echo htmlspecialchars($regulatorName); ?></h2>
                    <span style="background-color: #e9ecef; padding: 3px 8px; border-radius: 10px; font-size: 13px; font-weight: bold; color: #555; display: inline-block; margin-top: 5px;">National Regulator</span>
                </div>
            </div>

            <div style="line-height: 1.8; font-size: 16px;">
                <p style="margin: 10px 0; border-bottom: 1px dashed #f1f1f1; padding-bottom: 5px;">
                    <strong>Official Email:</strong> <span style="color: #555; float: right;"><?php echo htmlspecialchars($regulatorEmail); ?></span>
                </p>
                <p style="margin: 10px 0; border-bottom: 1px dashed #f1f1f1; padding-bottom: 5px;">
                    <strong>Clearance Level:</strong> <span style="color: #dc3545; font-weight: bold; float: right;">Top Tier Oversight</span>
                </p>
            </div>
            
        </div>
    </div>
</div>

</body>
</html>