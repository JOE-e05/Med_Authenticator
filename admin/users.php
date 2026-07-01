<?php
require_once "../config/admin_auth.php";
require_once "../classes/AdminManager.php";

$adminManager = new AdminManager();
$message = "";


if (isset($_GET['status']) && isset($_GET['id'])) {
    $newStatus = (int)$_GET['status']; 
    if ($adminManager->updateUserStatus($_GET['id'], $newStatus)) {
        header("Location: users.php");
        exit();
    }
}

if (isset($_GET['delete'])) {
    try {
        if ($adminManager->deleteUser($_GET['delete'])) {
            $message = "<div style='color: #856404; background-color: #fff3cd; padding: 10px; border: 1px solid #ffeeba; border-radius: 5px; margin-bottom: 15px;'> User account permanently deleted.</div>";
        }
    } catch (Exception $e) {
        $message = "<div style='color: red; font-weight: bold; margin-bottom: 15px;'> Error: " . $e->getMessage() . "</div>";
    }
}

$users = $adminManager->getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css"> 
</head>
<body>

<?php include("../includes/admin_sidebar.php"); ?>

<div class="main-content">
    <div class="container container-wide">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 style="color: #003366; margin: 0;">User Management</h1>
        </div>

        <?php echo $message; ?>

        <div class="card">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <tr style="border-bottom: 2px solid #003366;">
                    <th style="padding-bottom: 10px;">ID</th>
                    <th style="padding-bottom: 10px;">Name</th>
                    <th style="padding-bottom: 10px;">Email</th>
                    <th style="padding-bottom: 10px;">Role</th>
                    <th style="padding-bottom: 10px;">Status</th>
                    <th style="padding-bottom: 10px;">Actions</th>
                </tr>
                
                <?php foreach($users as $user): ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 15px 0; color: #666;">#<?php echo htmlspecialchars($user['customerID']); ?></td>
                    <td><strong><?php echo htmlspecialchars($user['CustomerName']); ?></strong></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <span style="background-color: #e9ecef; padding: 5px 10px; border-radius: 15px; font-size: 14px;">
                            <?php echo htmlspecialchars($user['role']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if($user['status'] == 1): ?>
                            <span style="color: green; font-weight: bold;">Active</span>
                        <?php else: ?>
                            <span style="color: red; font-weight: bold;">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $user['customerID']; ?>" style="color: #0056b3; font-weight: bold; text-decoration: none; margin-right: 10px;">Edit</a>
                        
                        <?php if($user['status'] == 1): ?>
                            <a href="users.php?status=0&id=<?php echo $user['customerID']; ?>" style="color: #fd7e14; text-decoration: none; margin-right: 10px;">Deactivate</a>
                        <?php else: ?>
                            <a href="users.php?status=1&id=<?php echo $user['customerID']; ?>" style="color: #28a745; text-decoration: none; margin-right: 10px;">Activate</a>
                        <?php endif; ?>

                        <a href="users.php?delete=<?php echo $user['customerID']; ?>" onclick="return confirm('Delete this user account permanently?');" style="color: #dc3545; font-weight: bold; text-decoration: none;">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                
            </table>
        </div>

    </div>
</div>

</body>
</html>