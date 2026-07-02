<?php
session_start();

if (!isset($_SESSION['regulator_logged_in']) || $_SESSION['regulator_logged_in'] !== true) {
    header("Location: ../regulators/login.php");
    exit();
}
?>