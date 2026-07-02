<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function authz_require_role($role) {
    $role = trim((string) $role);

    $ok = false;
    if ($role === 'Admin') {
        $ok = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    } elseif ($role === 'Pharmacist') {
        $ok = isset($_SESSION['pharmacist_logged_in']) && $_SESSION['pharmacist_logged_in'] === true;
    } elseif ($role === 'Regulator') {
        $ok = isset($_SESSION['regulator_logged_in']) && $_SESSION['regulator_logged_in'] === true;
    } elseif ($role === 'Patient') {
        $ok = isset($_SESSION['patient_logged_in']) && $_SESSION['patient_logged_in'] === true;
    } elseif ($role === 'Manufacturer') {
        $ok = isset($_SESSION['manufacturer_logged_in']) && $_SESSION['manufacturer_logged_in'] === true;
    }

    if (!$ok) {
        throw new RuntimeException('Unauthorized action for role: ' . $role);
    }
}

function authz_require_any_role(array $roles) {
    foreach ($roles as $role) {
        try {
            authz_require_role($role);
            return;
        } catch (RuntimeException $e) {
            // Keep checking other roles.
        }
    }

    throw new RuntimeException('Unauthorized action.');
}
?>
