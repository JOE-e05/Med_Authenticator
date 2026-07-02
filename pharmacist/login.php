<?php
session_start();
require_once "../config/csrf.php";
require_once "../config/database.php";

if (isset($_SESSION['pharmacist_logged_in']) && $_SESSION['pharmacist_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    csrf_require_valid_post();

    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    try {
        $database = new Database();
        $pdo = $database->getConnection();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND role = 'Pharmacist' AND status = 1 LIMIT 1");
        $stmt->execute([':email' => $email]);
        $pharmacistUser = $stmt->fetch();

        $passwordValid = false;
        if ($pharmacistUser) {
            $stored = (string) ($pharmacistUser['passwordHash'] ?? '');
            if (password_verify($password, $stored) || $password === $stored) {
                $passwordValid = true;
            }
        }

        if ($pharmacistUser && $passwordValid) {
            session_regenerate_id(true);
            $_SESSION['pharmacist_logged_in'] = true;
            $_SESSION['customerID'] = $pharmacistUser['customerID'];
            $_SESSION['CustomerName'] = $pharmacistUser['CustomerName'];
            $_SESSION['email'] = $pharmacistUser['email'];
            $_SESSION['phoneNumber'] = $pharmacistUser['phoneNumber'];
            $_SESSION['firstRegistration'] = $pharmacistUser['firstRegistration'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid credentials or unauthorized access.";
        }
    } catch (PDOException $e) {
        $error = "Database connection failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pharmacist Login | Med-Authenticator</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f7f6; margin: 0;">

    <div class="card" style="width: 400px; padding: 40px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-top: 5px solid #17a2b8;">
        <h2 style="color: #003366; margin-bottom: 5px;">Pharmacy Portal</h2>
        <p style="color: #666; margin-bottom: 25px; font-size: 14px;">Authorized Pharmacists Only</p>

        <?php if($error != ""): ?>
            <div style="color: white; background-color: #dc3545; padding: 12px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; font-size: 14px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" style="text-align: left;">
            <?php echo csrf_input_field(); ?>
            <label style="font-weight: bold; color: #333; font-size: 14px;">Email Address</label>
            <input type="email" name="email" placeholder="pharmacy@med.com" required style="width: 100%; padding: 12px; margin-top: 5px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">

            <label style="font-weight: bold; color: #333; font-size: 14px;">Password</label>
            <input type="password" name="password" placeholder="••••••••" required style="width: 100%; padding: 12px; margin-top: 5px; margin-bottom: 30px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">

            <button type="submit" style="width: 100%; background-color: #17a2b8; color: white; padding: 15px; border: none; border-radius: 5px; font-weight: bold; font-size: 16px; cursor: pointer; transition: 0.3s;">Secure Login</button>
        </form>
    </div>

</body>
</html>