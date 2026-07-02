<?php
require_once "../config/admin_auth.php";
require_once "../config/csrf.php";
require_once "../classes/adminManager.php";

$adminManager = new AdminManager();
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_action'])) {
    csrf_require_valid_post();

    $action = trim($_POST['user_action']);
    $userId = (int) ($_POST['user_id'] ?? 0);

    if ($userId > 0) {
        try {
            if ($action === 'set_status') {
                $newStatus = (int) ($_POST['new_status'] ?? 0);
                if ($adminManager->updateUserStatus($userId, $newStatus, 'Admin')) {
                    $message = "<div style='color: #155724; background-color: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 15px;'> User status updated.</div>";
                }
            } elseif ($action === 'delete') {
                if ($adminManager->deleteUser($userId, 'Admin')) {
                    $message = "<div style='color: #856404; background-color: #fff3cd; padding: 10px; border: 1px solid #ffeeba; border-radius: 5px; margin-bottom: 15px;'> User account permanently deleted.</div>";
                }
            }
        } catch (Exception $e) {
            $message = "<div style='color: red; font-weight: bold; margin-bottom: 15px;'> Error: " . $e->getMessage() . "</div>";
        }
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
                            <form method="POST" style="display:inline-block; margin-right: 10px;">
                                <?php echo csrf_input_field(); ?>
                                <input type="hidden" name="user_action" value="set_status">
                                <input type="hidden" name="user_id" value="<?php echo (int) $user['customerID']; ?>">
                                <input type="hidden" name="new_status" value="0">
                                <button type="submit" style="background:none; border:none; color:#fd7e14; padding:0; margin:0; width:auto; cursor:pointer;">Deactivate</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" style="display:inline-block; margin-right: 10px;">
                                <?php echo csrf_input_field(); ?>
                                <input type="hidden" name="user_action" value="set_status">
                                <input type="hidden" name="user_id" value="<?php echo (int) $user['customerID']; ?>">
                                <input type="hidden" name="new_status" value="1">
                                <button type="submit" style="background:none; border:none; color:#28a745; padding:0; margin:0; width:auto; cursor:pointer;">Activate</button>
                            </form>
                        <?php endif; ?>

                        <form method="POST" style="display:inline-block;">
                            <?php echo csrf_input_field(); ?>
                            <input type="hidden" name="user_action" value="delete">
                            <input type="hidden" name="user_id" value="<?php echo (int) $user['customerID']; ?>">
                            <button type="submit" onclick="return confirm('Delete this user account permanently?');" style="background:none; border:none; color:#dc3545; font-weight:bold; padding:0; margin:0; width:auto; cursor:pointer;">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                
            </table>
        </div>

    </div>
</div>

</body>
</html>