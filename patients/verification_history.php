<?php
require_once "../config/patient_auth.php";
require_once "../config/database.php";

$database = new Database();
$conn = $database->getConnection();
$userId = $_SESSION['customerID'];
$search = trim($_GET['search'] ?? '');

try {
    $query = "SELECT 
                v.batchNumber, 
                v.result, 
                v.verification_type,
                v.verified_at, 
                m.medName
              FROM verification_log v
              LEFT JOIN medicine m ON v.batchNumber = m.batchNumber 
              WHERE v.userID = :uid";

    if ($search !== '') {
        $query .= " AND (v.batchNumber LIKE :search OR v.result LIKE :search)";
    }

    $query .= "
              ORDER BY v.verified_at DESC";

    $stmt = $conn->prepare($query);
    $params = [':uid' => $userId];
    if ($search !== '') {
        $params[':search'] = "%$search%";
    }
    $stmt->execute($params);
    $historyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $legacyQuery = "SELECT
                        v.batchNumber,
                        v.result,
                        v.verified_at,
                        m.medName
                    FROM verification_log v
                    LEFT JOIN medicine m ON v.batchNumber = m.batchNumber
                    WHERE v.userID = :uid";

    if ($search !== '') {
        $legacyQuery .= " AND (v.batchNumber LIKE :search OR v.result LIKE :search)";
    }

    $legacyQuery .= " ORDER BY v.verified_at DESC";

    $legacyStmt = $conn->prepare($legacyQuery);
    $legacyParams = [':uid' => $userId];
    if ($search !== '') {
        $legacyParams[':search'] = "%$search%";
    }
    $legacyStmt->execute($legacyParams);
    $historyData = $legacyStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verification History</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include("../includes/patient_sidebar.php"); ?>

<div class="main-content">
    <div class="container container-wide">
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
                <h2 style="margin:0;">Verification History</h2>
                <form method="GET" style="display:flex; gap:8px; align-items:center;">
                    <input type="text" name="search" placeholder="Search code or result" value="<?php echo htmlspecialchars($search); ?>" style="width:240px; margin:0;">
                    <button type="submit" style="width:auto;">Search</button>
                    <?php if ($search !== ''): ?>
                        <a href="verification_history.php" style="color:#dc3545; font-weight:bold;">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <table style="margin-top: 14px;">
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Medicine Name</th>
                    <th>Result</th>
                    <th>Time Checked</th>
                </tr>
                <?php if (empty($historyData)): ?>
                    <tr><td colspan="5" style="text-align:center; color:#666;">No verification records found.</td></tr>
                <?php else: ?>
                    <?php foreach($historyData as $row){ ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['batchNumber']); ?></td>
                        <td><?php echo htmlspecialchars($row['verification_type'] ?? 'Batch'); ?></td>
                        <td><?php echo htmlspecialchars($row['medName'] ?? 'Unknown'); ?></td>
                        <td><?php echo htmlspecialchars($row['result']); ?></td>
                        <td><?php echo htmlspecialchars($row['verified_at']); ?></td>
                    </tr>
                    <?php } ?>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>