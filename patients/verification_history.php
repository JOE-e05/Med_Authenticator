<?php
require_once "../config/patient_auth.php";
require_once "../config/database.php";

$database = new Database();
$conn = $database->getConnection();
$userId = $_SESSION['customerID'];

// Joining your verification_log with your medicine table exactly
$query = "SELECT 
            v.batchNumber, 
            v.result, 
            v.verified_at, 
            m.medName 
          FROM verification_log v
          LEFT JOIN medicine m ON v.batchNumber = m.batchNumber 
          WHERE v.userID = :uid 
          ORDER BY v.verified_at DESC";
          
$stmt = $conn->prepare($query);
$stmt->bindParam(':uid', $userId);
$stmt->execute();
$historyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verification History</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container">
    <div class="card">
        <h2>Verification History</h2><br>
        <table border="1" width="100%" cellpadding="8">
            <tr>
                <th>Batch Number</th>
                <th>Medicine Name</th>
                <th>Result</th>
                <th>Time Checked</th>
            </tr>
            <?php foreach($historyData as $row){ ?>
            <tr>
                <td><?php echo htmlspecialchars($row['batchNumber']); ?></td>
                <td><?php echo htmlspecialchars($row['medName'] ?? 'Unknown Counterfeit'); ?></td>
                <td><?php echo htmlspecialchars($row['result']); ?></td>
                <td><?php echo htmlspecialchars($row['verified_at']); ?></td>
            </tr>
            <?php } ?>
        </table>
        <br>
        <a href="dashboard.php"><button>Back to Dashboard</button></a>
    </div>
</div>
</body>
</html>