<?php
require_once "../config/patient_auth.php";
require_once "../config/database.php";

$message = "";
$database = new Database();
$conn = $database->getConnection();
$userId = $_SESSION['customerID'];

if(isset($_POST['submit_report'])){
    $batchNumber = trim($_POST['batch_number']);
    $description = trim($_POST['description']);
    $defaultStatus = "Pending"; // Default status for a new report

    // Much simpler! Just inserting directly into your 'report' table
    $insertStmt = $conn->prepare("INSERT INTO report (userID, batchNumber, description, status) VALUES (:uid, :batch, :desc, :status)");
    $insertStmt->bindParam(':uid', $userId);
    $insertStmt->bindParam(':batch', $batchNumber);
    $insertStmt->bindParam(':desc', $description);
    $insertStmt->bindParam(':status', $defaultStatus);
    
    if($insertStmt->execute()){
        $message = "Report submitted successfully to the PPB.";
    } else {
        $message = "Failed to submit report.";
    }
}

// Mapped to your 'report' table
$reportsStmt = $conn->prepare("SELECT * FROM report WHERE userID = :uid ORDER BY reported_at DESC");
$reportsStmt->bindParam(':uid', $userId);
$reportsStmt->execute();
$reports = $reportsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Medicine</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container">
    <div class="card">
        <h2>Report Suspicious Medicine</h2><br>
        <?php if(!empty($message)){ echo "<p style='color:green;'>$message</p><br>"; } ?>
        
        <form method="POST">
            <label>Batch Number</label>
            <input type="text" name="batch_number" required>
            <label>Description</label>
            <textarea name="description" rows="5" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;" required></textarea>
            <br><br>
            <button type="submit" name="submit_report">Submit Report</button>
        </form>

        <div class="card" style="margin-top: 20px;">
            <h2>My Reports</h2>
            <table border="1" width="100%" cellpadding="8">
                <tr>
                    <th>Report ID</th>
                    <th>Batch Number</th>
                    <th>Description</th>
                    <th>Status</th>
                </tr>
                <?php foreach($reports as $report){ ?>
                <tr>
                    <td><?php echo htmlspecialchars($report['reportID']); ?></td>
                    <td><?php echo htmlspecialchars($report['batchNumber']); ?></td>
                    <td><?php echo htmlspecialchars($report['description']); ?></td>
                    <td><?php echo htmlspecialchars($report['status'] ?? 'Pending'); ?></td>
                </tr>
                <?php } ?>
            </table>
        </div>
        <br>
        <a href="dashboard.php"><button>Back to Dashboard</button></a>
    </div>
</div>
</body>
</html>