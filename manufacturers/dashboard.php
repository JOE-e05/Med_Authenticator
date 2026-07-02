<?php
require_once "../config/manufacturer_auth.php";
require_once "../config/csrf.php";
require_once "../classes/manufacturerManager.php";

$manager = new ManufacturerManager();
$message = '';
$error = '';
$generatedBatch = null;

$userId = (int) $_SESSION['manufacturer_user_id'];
$search = trim($_GET['search'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_codes'])) {
    csrf_require_valid_post();

    $medicineName = trim($_POST['medicine_name'] ?? '');
    $manufactureDate = trim($_POST['manufacture_date'] ?? '');
    $expiryDate = trim($_POST['expiry_date'] ?? '');
    $packCount = (int) ($_POST['pack_count'] ?? 0);

    if ($medicineName === '' || $manufactureDate === '' || $expiryDate === '' || $packCount < 1) {
        $error = 'All generation fields are required.';
    } elseif ($expiryDate <= $manufactureDate) {
        $error = 'Expiry date must be after manufacture date.';
    } else {
        try {
            $generatedBatch = $manager->createBatchWithPackCodes($userId, $medicineName, $manufactureDate, $expiryDate, $packCount, 'Manufacturer');
            $message = 'Batch and pack codes generated successfully.';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$summary = $manager->getManufacturerSummary($userId);
$history = $manager->getBatchHistory($userId, $search);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manufacturer Dashboard | Med-Authenticator</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include("../includes/manufacturer_sidebar.php"); ?>

<div class="main-content">
    <div class="container container-wide">
        <div style="margin-bottom: 20px;">
            <h1 style="color: #003366; margin-bottom: 5px;">Manufacturer Control Panel</h1>
            <p style="color: #666;">Welcome, <strong><?php echo htmlspecialchars($_SESSION['manufacturer_name'] ?? 'Manufacturer'); ?></strong>. Generate unique production codes below.</p>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 30px;">
            <div class="card" style="flex: 1; text-align: center; border-top: 5px solid #17a2b8;">
                <h3 style="color: #666; margin-bottom: 10px;">Batches Generated</h3>
                <h1 style="color: #003366; font-size: 40px; margin: 0;"><?php echo (int) $summary['total_batches']; ?></h1>
            </div>
            <div class="card" style="flex: 1; text-align: center; border-top: 5px solid #28a745;">
                <h3 style="color: #666; margin-bottom: 10px;">Pack Codes Generated</h3>
                <h1 style="color: #003366; font-size: 40px; margin: 0;"><?php echo (int) $summary['total_pack_codes']; ?></h1>
            </div>
        </div>

        <div class="card" style="margin-bottom: 25px;">
            <h2 style="color:#003366;">Generate New Batch + Pack Codes</h2>

            <?php if ($message !== ''): ?>
                <div style="margin-top: 12px; color: #155724; background-color:#d4edda; padding:12px; border-radius:5px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div style="margin-top: 12px; color: #721c24; background-color:#f8d7da; padding:12px; border-radius:5px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" style="margin-top: 15px;">
                <?php echo csrf_input_field(); ?>
                <label>Medicine Name</label>
                <input type="text" name="medicine_name" required>

                <label>Manufacture Date</label>
                <input type="date" name="manufacture_date" required>

                <label>Expiry Date</label>
                <input type="date" name="expiry_date" required>

                <label>Number of Unique Pack Codes</label>
                <input type="number" min="1" max="5000" name="pack_count" required>

                <button type="submit" name="generate_codes">Generate Codes</button>
            </form>

            <?php if (is_array($generatedBatch)): ?>
                <div class="card" style="margin-top: 18px; border-left: 4px solid #28a745;">
                    <p><strong>Batch Code:</strong> <?php echo htmlspecialchars($generatedBatch['batch_code']); ?></p>
                    <p><strong>Total Pack Codes:</strong> <?php echo (int) $generatedBatch['pack_count']; ?></p>
                    <p style="margin-bottom:8px;"><strong>First 10 Pack Codes:</strong></p>
                    <div style="max-height: 220px; overflow: auto; background:#f8f9fa; padding:10px; border-radius:6px;">
                        <?php foreach (array_slice($generatedBatch['pack_codes'], 0, 10) as $code): ?>
                            <div style="padding: 4px 0; font-family: monospace;"><?php echo htmlspecialchars($code); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
                <h2 style="color:#003366; margin:0;">Generated Batch History</h2>
                <form method="GET" style="display:flex; gap:10px; align-items:center;">
                    <input type="text" name="search" placeholder="Search medicine or batch" value="<?php echo htmlspecialchars($search); ?>" style="width:260px; margin:0;">
                    <button type="submit" style="width:auto;">Search</button>
                    <?php if ($search !== ''): ?>
                        <a href="dashboard.php" style="color:#dc3545; font-weight:bold;">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <table style="margin-top: 16px;">
                <tr>
                    <th>Medicine</th>
                    <th>Batch Code</th>
                    <th>Pack Count</th>
                    <th>Mfg Date</th>
                    <th>Expiry</th>
                    <th>Created At</th>
                </tr>
                <?php if (empty($history)): ?>
                    <tr><td colspan="6" style="text-align:center; color:#666;">No generated batches found.</td></tr>
                <?php else: ?>
                    <?php foreach ($history as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['med_name']); ?></td>
                            <td style="font-family: monospace;"><?php echo htmlspecialchars($item['batch_code']); ?></td>
                            <td><?php echo (int) $item['generated_packs']; ?></td>
                            <td><?php echo htmlspecialchars($item['manufacture_date']); ?></td>
                            <td><?php echo htmlspecialchars($item['expiry_date']); ?></td>
                            <td><?php echo htmlspecialchars($item['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>
