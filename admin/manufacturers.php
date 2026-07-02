<?php
require_once "../config/admin_auth.php";
require_once "../config/csrf.php";
require_once "../classes/adminManager.php";

$adminManager = new AdminManager();
$message = '';
$error = '';

$statusFilter = trim($_GET['status_filter'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_manufacturer'])) {
    csrf_require_valid_post();

    $profileId = (int) ($_POST['profile_id'] ?? 0);
    $newStatus = trim($_POST['approval_status'] ?? '');
    $reviewNotes = trim($_POST['review_notes'] ?? '');
    $adminId = (int) ($_SESSION['admin_id'] ?? 0);

    try {
        $adminManager->updateManufacturerApproval($profileId, $newStatus, $reviewNotes, $adminId, 'Admin');
        $message = 'Manufacturer approval status updated successfully.';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$queue = $adminManager->getManufacturerQueue($statusFilter);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manufacturer Approvals | Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include("../includes/admin_sidebar.php"); ?>

<div class="main-content">
    <div class="container container-wide">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:20px;">
            <div>
                <h1 style="color:#003366; margin:0;">Manufacturer Approval Queue</h1>
                <p style="color:#666; margin-top:5px;">Review and validate manufacturer onboarding requests.</p>
            </div>
            <form method="GET" style="display:flex; gap:8px; align-items:center;">
                <select name="status_filter" style="width: 220px; margin:0;">
                    <option value="">All statuses</option>
                    <option value="Pending" <?php if ($statusFilter === 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Approved" <?php if ($statusFilter === 'Approved') echo 'selected'; ?>>Approved</option>
                    <option value="Rejected" <?php if ($statusFilter === 'Rejected') echo 'selected'; ?>>Rejected</option>
                    <option value="Suspended" <?php if ($statusFilter === 'Suspended') echo 'selected'; ?>>Suspended</option>
                </select>
                <button type="submit" style="width:auto;">Filter</button>
                <?php if ($statusFilter !== ''): ?>
                    <a href="manufacturers.php" style="color:#dc3545; font-weight:bold;">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($message !== ''): ?>
            <div style="color:#155724; background:#d4edda; padding:12px; border-radius:5px; margin-bottom:14px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div style="color:#721c24; background:#f8d7da; padding:12px; border-radius:5px; margin-bottom:14px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($queue)): ?>
            <div class="card"><p style="color:#666;">No manufacturer records found for the selected filter.</p></div>
        <?php else: ?>
            <?php foreach ($queue as $item): ?>
                <div class="card" style="margin-bottom:16px; border-left:4px solid #003366;">
                    <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; margin-bottom:10px;">
                        <div>
                            <h3 style="margin:0; color:#003366;"><?php echo htmlspecialchars($item['company_name']); ?></h3>
                            <p style="margin-top:6px; color:#666;"><strong>License:</strong> <?php echo htmlspecialchars($item['license_number']); ?></p>
                            <p style="margin-top:6px; color:#666;"><strong>Contact:</strong> <?php echo htmlspecialchars($item['CustomerName']); ?> (<?php echo htmlspecialchars($item['email']); ?>)</p>
                        </div>
                        <div style="text-align:right; color:#666;">
                            <div><strong>Status:</strong> <?php echo htmlspecialchars($item['approval_status']); ?></div>
                            <div><strong>Submitted:</strong> <?php echo htmlspecialchars($item['submitted_at']); ?></div>
                        </div>
                    </div>

                    <form method="POST" style="background:#f8f9fa; padding:14px; border-radius:6px;">
                        <?php echo csrf_input_field(); ?>
                        <input type="hidden" name="profile_id" value="<?php echo (int) $item['profile_id']; ?>">

                        <label>Set Approval Status</label>
                        <select name="approval_status" required>
                            <option value="Pending" <?php if ($item['approval_status'] === 'Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Approved" <?php if ($item['approval_status'] === 'Approved') echo 'selected'; ?>>Approved</option>
                            <option value="Rejected" <?php if ($item['approval_status'] === 'Rejected') echo 'selected'; ?>>Rejected</option>
                            <option value="Suspended" <?php if ($item['approval_status'] === 'Suspended') echo 'selected'; ?>>Suspended</option>
                        </select>

                        <label>Review Notes</label>
                        <textarea name="review_notes" rows="3" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;"><?php echo htmlspecialchars($item['review_notes'] ?? ''); ?></textarea>

                        <button type="submit" name="update_manufacturer" style="margin-top:10px;">Save Decision</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
