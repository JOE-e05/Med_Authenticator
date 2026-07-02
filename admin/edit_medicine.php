<?php
require_once "../config/admin_auth.php";
require_once "../classes/adminManager.php";

$adminManager = new AdminManager();
$message = "";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid request. No user ID provided.");
}
$id = $_GET['id'];


$currentUser = $adminManager->getUserById($id);
if (!$currentUser) {
    die("User record not found.");
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role']; 

    try {
        if ($adminManager->updateUser($id, $name, $email, $role)) {
            header("Location: users.php");
            exit();
        }
    } catch (Exception $e) {
        $message = "<div style='color: red; font-weight: bold; margin-bottom: 15px;'> Error: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User | Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css"> 
</head>
<body>

<?php include("../includes/admin_sidebar.php"); ?>

<div class="main-content">
    <div class="container container-wide">
        
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="color: #003366; margin: 0;">Edit User Profile</h2>
                <a href="users.php" style="text-decoration: none; color: #666; font-weight: bold;">← Back to Directory</a>
            </div>
            
            <p>Updating records for: <strong><?php echo htmlspecialchars($currentUser['CustomerName']); ?></strong></p>
            <br>

            <?php echo $message; ?>

            <form method="POST">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($currentUser['CustomerName']); ?>" required>
                
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                
                <label>System Role</label>
                <select name="role" style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px;">
                    <option value="Customer" <?php if($currentUser['role'] == 'Customer') echo 'selected'; ?>>Customer</option>
                    <option value="Pharmacist" <?php if($currentUser['role'] == 'Pharmacist') echo 'selected'; ?>>Pharmacist</option>
                    <option value="Regulator" <?php if($currentUser['role'] == 'Regulator') echo 'selected'; ?>>Regulator</option>
                    <option value="Manufacturer" <?php if($currentUser['role'] == 'Manufacturer') echo 'selected'; ?>>Manufacturer</option>
                    <option value="Admin" <?php if($currentUser['role'] == 'Admin') echo 'selected'; ?>>Admin</option>
                </select>
                
                <button type="submit" name="update_user" style="background-color: #003366;">Save User Updates</button>
            </form>
        </div>

    </div>
</div>

</body>
</html>