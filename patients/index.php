<?php
session_start();

if (isset($_SESSION['patient_logged_in']) && $_SESSION['patient_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

header("Location: login.php");
exit();
?>