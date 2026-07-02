<?php
session_start();

if (!isset($_SESSION['manufacturer_logged_in']) || $_SESSION['manufacturer_logged_in'] !== true) {
    header("Location: ../manufacturers/login.php");
    exit();
}

if (!isset($_SESSION['manufacturer_approval_status']) || $_SESSION['manufacturer_approval_status'] !== 'Approved') {
    header("Location: ../manufacturers/pending.php");
    exit();
}
?>
