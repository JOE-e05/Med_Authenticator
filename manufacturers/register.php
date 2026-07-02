<?php
session_start();
require_once "../config/csrf.php";
require_once "../classes/manufacturerManager.php";

$manager = new ManufacturerManager();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require_valid_post();

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $companyName = trim($_POST['company_name'] ?? '');
    $licenseNumber = strtoupper(trim($_POST['license_number'] ?? ''));
    $country = trim($_POST['country'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($fullName === '' || $email === '' || $password === '' || $companyName === '' || $licenseNumber === '') {
        $error = 'All required fields must be completed.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        try {
            $manager->registerManufacturer($fullName, $email, $password, $companyName, $licenseNumber, $country, $phone, $address);
            $message = 'Registration submitted successfully. Your account is pending admin approval.';
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        } catch (Exception $e) {
            $error = 'Unable to complete registration: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manufacturer Registration | Med-Authenticator</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body style="display: flex; justify-content: center; padding: 40px 20px; background-color: #f4f7f6;">
    <div class="card" style="width: 700px; max-width: 100%; border-top: 5px solid #003366;">
        <h2 style="color: #003366; margin-bottom: 8px;">Manufacturer Onboarding</h2>
        <p style="color: #666; margin-bottom: 18px;">Register your company to generate verified medicine batch and pack codes.</p>

        <?php if ($message !== ''): ?>
            <div style="color: #155724; background-color: #d4edda; padding: 12px; border-radius: 5px; margin-bottom: 16px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div style="color: #721c24; background-color: #f8d7da; padding: 12px; border-radius: 5px; margin-bottom: 16px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?php echo csrf_input_field(); ?>
            <label>Contact Person Name</label>
            <input type="text" name="full_name" required>

            <label>Email Address</label>
            <input type="email" name="email" required>

            <label>Password (min 8 characters)</label>
            <input type="password" name="password" required minlength="8">

            <label>Company Name</label>
            <input type="text" name="company_name" required>

            <label>License Number</label>
            <input type="text" name="license_number" required>

            <label>Country</label>
            <input type="text" name="country">

            <label>Contact Phone</label>
            <input type="text" name="phone">

            <label>Company Address</label>
            <textarea name="address" rows="4" style="width:100%; padding:12px; border:1px solid #d1d5db; border-radius:6px;"></textarea>

            <button type="submit">Submit Registration</button>
        </form>

        <p style="margin-top: 16px; color: #555;">
            Already registered? <a href="login.php" style="color:#003366; font-weight: bold;">Sign in</a>
        </p>
    </div>
</body>
</html>
