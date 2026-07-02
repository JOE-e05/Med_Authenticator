<?php
session_start();
require_once "../classes/manufacturerManager.php";

if (isset($_SESSION['manufacturer_logged_in']) && $_SESSION['manufacturer_logged_in'] === true) {
    if (isset($_SESSION['manufacturer_approval_status']) && $_SESSION['manufacturer_approval_status'] === 'Approved') {
        header('Location: dashboard.php');
    } else {
        header('Location: pending.php');
    }
    exit();
}

$manager = new ManufacturerManager();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    $user = $manager->loginManufacturer($email, $password);
    if (!$user) {
        $error = 'Invalid credentials.';
    } else {
        $_SESSION['manufacturer_logged_in'] = true;
        $_SESSION['manufacturer_user_id'] = (int) $user['customerID'];
        $_SESSION['manufacturer_name'] = $user['CustomerName'];
        $_SESSION['manufacturer_email'] = $user['email'];
        $_SESSION['manufacturer_company_name'] = $user['company_name'] ?? '';
        $_SESSION['manufacturer_license_number'] = $user['license_number'] ?? '';
        $_SESSION['manufacturer_profile_id'] = isset($user['profile_id']) ? (int) $user['profile_id'] : 0;
        $_SESSION['manufacturer_approval_status'] = $user['approval_status'] ?? 'Pending';

        if ($_SESSION['manufacturer_approval_status'] === 'Approved') {
            header('Location: dashboard.php');
        } else {
            header('Location: pending.php');
        }
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manufacturer Login | Med-Authenticator</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f4f7f6; margin: 0;">
    <div class="card" style="width: 420px; border-top: 5px solid #003366;">
        <h2 style="color: #003366; margin-bottom: 8px;">Manufacturer Portal</h2>
        <p style="color: #666; margin-bottom: 18px;">Sign in to manage production codes.</p>

        <?php if ($error !== ''): ?>
            <div style="color: #721c24; background-color: #f8d7da; padding: 12px; border-radius: 5px; margin-bottom: 16px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>

        <p style="margin-top: 16px; color: #555;">
            No account yet? <a href="register.php" style="color:#003366; font-weight: bold;">Register here</a>
        </p>
    </div>
</body>
</html>
