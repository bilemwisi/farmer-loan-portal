<?php
require_once 'db.php';
$message = '';
$action = $_POST['action'] ?? '';
$loanId = isset($_POST['loan_id']) ? (int)$_POST['loan_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $loanId > 0 && in_array($action, ['approve', 'reject'], true)) {
    $status = $action === 'approve' ? 'approved' : 'rejected';
    $db = getDb();
    $stmt = $db->prepare("UPDATE loans SET status = ?, updated_at = NOW() WHERE id = ? AND status = 'pending'");
    $stmt->bind_param('si', $status, $loanId);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $message = 'Loan request updated successfully.';
    } else {
        $message = 'Loan request could not be updated, or it was already processed.';
    }
    $stmt->close();
}

$db = getDb();
$result = $db->query(
        "SELECT l.id AS loan_id, l.amount, l.purpose, l.status, l.created_at, l.updated_at,
          f.id AS farmer_id, f.name, f.nid, f.phone, f.address, f.farm_size, f.crops, f.nid_file
      FROM loans l
      JOIN farmers f ON l.farmer_id = f.id
      ORDER BY l.created_at DESC"
);
$loans = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <style>main{margin:40px auto;max-width:1100px;padding:24px;background:#fff;border-radius:12px;} table{width:100%;border-collapse:collapse} th,td{padding:12px;border-bottom:1px solid #eee;text-align:left} .actions button{margin-right:8px;} .status-pending{color:#d32f2f;font-weight:700;} .status-approved{color:#2e7d32;font-weight:700;} .status-rejected{color:#757575;font-weight:700;}</style>
</head>
<body>
  <main>
    <h1>Admin — Loan Requests</h1>
    <p>Approve or reject farmer loan requests.</p>

    <?php if ($message !== ''): ?>
      <div class="booking-summary" style="border-color:#2e7d32;background:#e8f5e9;margin-bottom:18px;">
        <?php echo escape($message); ?>
      </div>
    <?php endif; ?>

    <?php if (empty($loans)): ?>
      <p>No loan requests available yet.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Farmer</th>
            <th>Amount</th>
            <th>Purpose</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($loans as $loan): ?>
            <tr>
              <td><?php echo escape($loan['loan_id']); ?></td>
              <td>
                <?php echo escape($loan['name']); ?> <br>
                ID: <?php echo escape($loan['nid']); ?> <br>
                Phone: <?php echo escape($loan['phone']); ?>
                <?php if (!empty($loan['nid_file'])): ?>
                  <br>
                  <a href="<?php echo escape($loan['nid_file']); ?>" target="_blank">View NID</a>
                <?php endif; ?>
              </td>
              <td><?php echo number_format($loan['amount']); ?> TZS</td>
              <td><?php echo escape($loan['purpose']); ?></td>
              <td class="status-<?php echo escape($loan['status']); ?>"><?php echo escape($loan['status']); ?></td>
              <td><?php echo escape($loan['created_at']); ?></td>
              <td class="actions">
                <?php if ($loan['status'] === 'pending'): ?>
                  <form method="post" style="display:inline;">
                    <input type="hidden" name="loan_id" value="<?php echo escape($loan['loan_id']); ?>">
                    <input type="hidden" name="action" value="approve">
                    <button class="button primary" type="submit">Approve</button>
                  </form>
                  <form method="post" style="display:inline;">
                    <input type="hidden" name="loan_id" value="<?php echo escape($loan['loan_id']); ?>">
                    <input type="hidden" name="action" value="reject">
                    <button class="button secondary" type="submit">Reject</button>
                  </form>
                <?php else: ?>
                  <span>Processed</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <p style="margin-top:18px;">Back to farmer form: <a href="index.php">Farmer page</a></p>
  </main>
</body>
</html>
