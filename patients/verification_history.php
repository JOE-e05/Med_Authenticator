<?php
require_once "../config/patient_auth.php";
require_once "../config/database.php";

$database = new Database();
$conn = $database->getConnection();
$userId = $_SESSION['customerID'];
$search = trim($_GET['search'] ?? '');

try {
    $query = "SELECT 
                v.batchNumber AS scanned_code,
                v.result,
                COALESCE(v.verification_type, 'Batch') AS verification_type,
                v.verified_at,
                COALESCE(
                    CASE WHEN COALESCE(v.verification_type, 'Batch') = 'Pack' THEN pack_batch.med_name END,
                    CASE WHEN COALESCE(v.verification_type, 'Batch') = 'Batch' THEN batch_lookup.med_name END,
                    m.medName,
                    'Unknown'
                ) AS medName,
                COALESCE(pack_manufacturer.company_name, batch_manufacturer.company_name, m.manufacture, 'Unknown') AS manufacturer_name,
                CASE
                    WHEN COALESCE(v.verification_type, 'Batch') = 'Pack' THEN COALESCE(pack_batch.batch_code, 'N/A')
                    ELSE COALESCE(batch_lookup.batch_code, v.batchNumber)
                END AS linked_batch_code
              FROM verification_log v
              LEFT JOIN medicine_pack_codes pack_codes ON pack_codes.pack_code = v.batchNumber
              LEFT JOIN medicine_batches pack_batch ON pack_batch.batch_id = pack_codes.batch_id
              LEFT JOIN manufacturer_profiles pack_manufacturer ON pack_manufacturer.user_id = pack_batch.manufacturer_user_id
              LEFT JOIN medicine_batches batch_lookup ON batch_lookup.batch_code = v.batchNumber
              LEFT JOIN manufacturer_profiles batch_manufacturer ON batch_manufacturer.user_id = batch_lookup.manufacturer_user_id
              LEFT JOIN medicine m ON v.batchNumber = m.batchNumber 
              WHERE v.userID = :uid";

    if ($search !== '') {
        $query .= " AND (
                        v.batchNumber LIKE :search
                        OR v.result LIKE :search
                        OR COALESCE(pack_batch.med_name, batch_lookup.med_name, m.medName, '') LIKE :search
                        OR COALESCE(pack_manufacturer.company_name, batch_manufacturer.company_name, m.manufacture, '') LIKE :search
                    )";
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
                        v.batchNumber AS scanned_code,
                        v.result,
                        'Batch' AS verification_type,
                        v.verified_at,
                        COALESCE(m.medName, 'Unknown') AS medName,
                        COALESCE(m.manufacture, 'Unknown') AS manufacturer_name,
                        v.batchNumber AS linked_batch_code
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
                    <th>Manufacturer</th>
                    <th>Linked Batch</th>
                    <th>Result</th>
                    <th>Time Checked</th>
                </tr>
                <?php if (empty($historyData)): ?>
                    <tr><td colspan="7" style="text-align:center; color:#666;">No verification records found.</td></tr>
                <?php else: ?>
                    <?php foreach($historyData as $row){ ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['scanned_code']); ?></td>
                        <td><?php echo htmlspecialchars($row['verification_type'] ?? 'Batch'); ?></td>
                        <td><?php echo htmlspecialchars($row['medName'] ?? 'Unknown'); ?></td>
                        <td><?php echo htmlspecialchars($row['manufacturer_name'] ?? 'Unknown'); ?></td>
                        <td><?php echo htmlspecialchars($row['linked_batch_code'] ?? 'N/A'); ?></td>
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