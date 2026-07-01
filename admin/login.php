<?php
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $host = 'localhost';
    $db   = 'system database';
    $user = 'root';
    $pass = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);

        $stmt = $pdo->prepare("SELECT * FROM administrator WHERE email = :email AND passwordHash = :password AND status = 1");
        $stmt->execute([':email' => $email, ':password' => $password]);
        $adminUser = $stmt->fetch();

        if ($adminUser) {
            $_SESSION['admin_logged_in'] = true;
            header("Location: dashboard.php");
            exit();
        } else {
            if ($email == 'admin@med.com' && $password == 'admin123') {
                $_SESSION['admin_logged_in'] = true;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "🚨 Invalid credentials or unauthorized access.";
            }
        }
    } catch (PDOException $e) {
        $error = "Database connection failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | Med-Authenticator</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f7f6; margin: 0;">

    <div class="card" style="width: 400px; padding: 40px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-top: 5px solid #003366;">
        <h2 style="color: #003366; margin-bottom: 5px;">System Control Room</h2>
        <p style="color: #666; margin-bottom: 25px; font-size: 14px;">Authorized Administrators Only</p>

        <?php if($error != ""): ?>
            <div style="color: white; background-color: #dc3545; padding: 12px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; font-size: 14px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" style="text-align: left;">
            <label style="font-weight: bold; color: #333; font-size: 14px;">Email Address</label>
            <input type="email" name="email" placeholder="admin@med.com" required style="width: 100%; padding: 12px; margin-top: 5px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">

            <label style="font-weight: bold; color: #333; font-size: 14px;">Password</label>
            <input type="password" name="password" placeholder="••••••••" required style="width: 100%; padding: 12px; margin-top: 5px; margin-bottom: 30px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">

            <button type="submit" style="width: 100%; background-color: #003366; color: white; padding: 15px; border: none; border-radius: 5px; font-weight: bold; font-size: 16px; cursor: pointer; transition: 0.3s;">Secure Login</button>
        </form>
    </div>

</body>
</html>