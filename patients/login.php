<?php
session_start();
require_once "../config/csrf.php";
require_once "../config/database.php";

if (isset($_SESSION['patient_logged_in']) && $_SESSION['patient_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require_valid_post();

    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    try {
        $database = new Database();
        $pdo = $database->getConnection();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND role = 'Customer' AND status = 1 LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            $altStmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND role = 'Patient' AND status = 1 LIMIT 1");
            $altStmt->execute([':email' => $email]);
            $user = $altStmt->fetch();
        }

        $passwordValid = false;
        if ($user) {
            $stored = (string) ($user['passwordHash'] ?? '');
            if (password_verify($password, $stored) || $password === $stored) {
                $passwordValid = true;
            }
        }

        if ($user && $passwordValid) {
            session_regenerate_id(true);
            $_SESSION['patient_logged_in'] = true;
            $_SESSION['customerID'] = (int) $user['customerID'];
            $_SESSION['CustomerName'] = $user['CustomerName'];
            $_SESSION['email'] = $user['email'];

            header('Location: dashboard.php');
            exit();
        }

        $error = 'Invalid credentials.';
    } catch (PDOException $e) {
        $error = 'Database connection failed.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Login | Med-Authenticator</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body style="display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; background:#f4f7f6;">
    <div class="card" style="width:420px; border-top:5px solid #003366;">
        <h2 style="color:#003366; margin-bottom: 8px;">Patient Portal Login</h2>
        <p style="color:#666; margin-bottom: 16px;">Sign in to verify medicine and access your history.</p>

        <?php if ($error !== ''): ?>
            <div style="color:#721c24; background:#f8d7da; padding:12px; border-radius:5px; margin-bottom:12px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?php echo csrf_input_field(); ?>
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
