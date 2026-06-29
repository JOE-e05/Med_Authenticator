<?php
require_once __DIR__ . '/../classes/SystemVerifier.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['batch_number'])) {
    
    $verifier = new SystemVerifier();
    $result = $verifier->checkBatchNumber($_POST['batch_number']);
}
?>