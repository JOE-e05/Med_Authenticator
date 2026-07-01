<?php
require_once "../config/patient_auth.php";
require_once "../config/database.php";

$database = new Database();
$conn = $database->getConnection();
$userId = $_SESSION['customerID'];


$stmt1 = $conn->prepare("SELECT COUNT(*) as total FROM verification_log WHERE userID = :uid");
$stmt1->bindParam(':uid', $userId);
$stmt1->execute();
$totalVerifications = $stmt1->fetch(PDO::FETCH_ASSOC)['total'];


$stmt2 = $conn->prepare("SELECT COUNT(*) as total FROM report WHERE userID = :uid");
$stmt2->bindParam(':uid', $userId);
$stmt2->execute();
$totalReports = $stmt2->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include("../includes/patient_sidebar.php"); ?>
    
    <div class="main-content">
        <h1 class="page-title">Patient Dashboard</h1>
        <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['CustomerName']); ?></strong></p>
        
        <div class="dashboard-cards">
            <div class="card">
                <h3>Total Verifications</h3>
                <h1><?php echo $totalVerifications; ?></h1>
            </div>
            <div class="card">
                <h3>Reports Submitted</h3>
                <h1><?php echo $totalReports; ?></h1>
            </div>
        </div>
        <br>
        <div class="dashboard-cards">
            <a href="verify_medicine.php" class="card-link">
                <div class="card">
                    <h3>Verify Medicine</h3>
                    <p>Check authenticity of medicines.</p>
                </div>
            </a>
            <a href="verification_history.php" class="card-link">
                <div class="card">
                    <h3>Verification History</h3>
                    <p>View all previous checks.</p>
                </div>
            </a>
            <a href="report_medicine.php" class="card-link">
                <div class="card">
                    <h3>Report Medicine</h3>
                    <p>Report suspicious medicines.</p>
                </div>
            </a>
        </div>
    </div>
</body>
</html>