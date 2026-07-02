<?php
require_once "../config/pharmacist_auth.php";

$pharmacistName = isset($_SESSION['CustomerName']) ? $_SESSION['CustomerName'] : "Test Pharmacist";
$pharmacistEmail = isset($_SESSION['email']) ? $_SESSION['email'] : "pharmacist@med.com";
$pharmacistPhone = isset($_SESSION['phoneNumber']) ? $_SESSION['phoneNumber'] : "0700000000";
$joinDate = isset($_SESSION['firstRegistration']) ? $_SESSION['firstRegistration'] : date("Y-m-d");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | Pharmacy Portal</title>
    <link rel="stylesheet" href="../assets/style.css"> 
</head>
<body>

<?php include("../includes/pharmacist_sidebar.php"); ?>

<div class="main-content"> 
    <div class="container container-wide">
        
        <div style="margin-bottom: 20px;">
            <h1 style="color: #003366; margin-bottom: 5px;">My Profile</h1>
            <p style="color: #666; font-size: 16px;">Manage your authorized pharmacist account details.</p>
        </div>

        <div class="card" style="max-width: 600px; border-top: 5px solid #003366;">
            
            <div style="display: flex; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
                <div style="width: 80px; height: 80px; background-color: #003366; color: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 30px; font-weight: bold; margin-right: 20px;">
                    <?php echo substr($pharmacistName, 0, 1); ?>
                </div>
                <div>
                    <h2 style="color: #003366; margin: 0;"><?php echo htmlspecialchars($pharmacistName); ?></h2>
                    <span style="background-color: #e9ecef; padding: 3px 8px; border-radius: 10px; font-size: 13px; font-weight: bold; color: #555; display: inline-block; margin-top: 5px;">Licensed Pharmacist</span>
                </div>
            </div>

            <div style="line-height: 1.8; font-size: 16px;">
                <p style="margin: 10px 0; border-bottom: 1px dashed #f1f1f1; padding-bottom: 5px;">
                    <strong>Email Address:</strong> <span style="color: #555; float: right;"><?php echo htmlspecialchars($pharmacistEmail); ?></span>
                </p>
                <p style="margin: 10px 0; border-bottom: 1px dashed #f1f1f1; padding-bottom: 5px;">
                    <strong>Phone Number:</strong> <span style="color: #555; float: right;"><?php echo htmlspecialchars($pharmacistPhone); ?></span>
                </p>
                <p style="margin: 10px 0; border-bottom: 1px dashed #f1f1f1; padding-bottom: 5px;">
                    <strong>Account Status:</strong> <span style="color: green; font-weight: bold; float: right;"> Active</span>
                </p>
                <p style="margin: 10px 0;">
                    <strong>System Join Date:</strong> <span style="color: #555; float: right;"><?php echo htmlspecialchars($joinDate); ?></span>
                </p>
            </div>
            
        </div>

    </div>
</div>

</body>
</html>