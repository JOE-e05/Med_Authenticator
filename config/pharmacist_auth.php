<?php
session_start();

if (!isset($_SESSION['pharmacist_logged_in']) || $_SESSION['pharmacist_logged_in'] !== true) {
    header("Location: ../pharmacist/login.php");
    exit();
}
?>