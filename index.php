<?php
require_once 'db.php';
$message = '';
$errors = [];
$submitted = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $submitted = array_map('trim', $_POST);
    $name = $submitted['name'] ?? '';
    $nid = $submitted['nid'] ?? '';
    $phone = $submitted['phone'] ?? '';
    $address = $submitted['address'] ?? '';
    $farmSize = $submitted['farmSize'] ?? '';
    $crops = $submitted['crops'] ?? '';
    $amount = $submitted['amount'] ?? '';
    $purpose = $submitted['purpose'] ?? '';

    if ($name === '') {
        $errors[] = 'Full name is required.';
    }
    if ($nid === '') {
        $errors[] = 'National ID is required.';
    }
    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    }
    if ($address === '') {
        $errors[] = 'Address is required.';
    }
    if (!is_numeric($amount) || $amount <= 0) {
        $errors[] = 'Loan amount must be a positive number.';
    }

    if (empty($errors)) {
        $db = getDb();
        $db->begin_transaction();

      // Handle uploaded NID image (optional)
      $nidFileName = '';
      if (!empty($_FILES['nid_file']['name']) && isset($_FILES['nid_file']) && $_FILES['nid_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $origName = $_FILES['nid_file']['name'];
        $ext = pathinfo($origName, PATHINFO_EXTENSION);
        $safe = bin2hex(random_bytes(8));
        $newName = time() . '_' . $safe . ($ext ? '.' . $ext : '');
        $target = $uploadDir . '/' . $newName;
        if (move_uploaded_file($_FILES['nid_file']['tmp_name'], $target)) {
          $nidFileName = 'uploads/' . $newName;
        }
      }

        $farmerStmt = $db->prepare("INSERT INTO farmers (name, nid, phone, address, farm_size, crops, nid_file, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $farmSizeValue = is_numeric($farmSize) ? (float)$farmSize : 0;
        $farmerStmt->bind_param('ssssdss', $name, $nid, $phone, $address, $farmSizeValue, $crops, $nidFileName);
        $farmerStmt->execute();
        $farmerId = $farmerStmt->insert_id;
        $farmerStmt->close();

        $loanStmt = $db->prepare("INSERT INTO loans (farmer_id, amount, purpose, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
        $amountValue = (float)$amount;
        $loanStmt->bind_param('ids', $farmerId, $amountValue, $purpose);
        $loanStmt->execute();
        $loanId = $loanStmt->insert_id;
        $loanStmt->close();

        $db->commit();
        $message = "Farmer registered successfully. Farmer ID: $farmerId, Loan request ID: $loanId. An admin will review it soon.";
        $submitted = [];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Farmer Loan Registration</title>
  <link rel="stylesheet" href="style.css">
  <style>main{margin:40px auto;max-width:900px;padding:24px;background:#fff;border-radius:12px;}</style>
</head>
<body>
  <main>
    <h1>Farmer Registration & Loan Request</h1>
    <p>Use this form to register and request a loan. An admin can approve or reject requests on the admin page.</p>

    <?php if (!empty($errors)): ?>
      <div class="booking-summary" style="border-color:#d32f2f;background:#fff1f1;margin-bottom:18px;">
        <strong>Please fix the following:</strong>
        <ul>
          <?php foreach ($errors as $error): ?>
            <li><?php echo escape($error); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($message !== ''): ?>
      <div class="booking-summary" style="border-color:#2e7d32;background:#e8f5e9;margin-bottom:18px;">
        <?php echo escape($message); ?>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" id="farmerForm">
      <input type="hidden" name="action" value="register">
      <div class="form-grid">
        <label>Full name
          <input name="name" required value="<?php echo escape($submitted['name'] ?? ''); ?>">
        </label>
        <label>National ID
          <input name="nid" required value="<?php echo escape($submitted['nid'] ?? ''); ?>">
        </label>
        <label>Phone
          <input name="phone" required value="<?php echo escape($submitted['phone'] ?? ''); ?>">
        </label>
        <label>Address
          <input name="address" required value="<?php echo escape($submitted['address'] ?? ''); ?>">
        </label>
        <label>Farm size (acres)
          <input name="farmSize" type="number" min="0" step="0.1" value="<?php echo escape($submitted['farmSize'] ?? ''); ?>">
        </label>
        <label>Main crops
          <input name="crops" placeholder="e.g. maize, beans" value="<?php echo escape($submitted['crops'] ?? ''); ?>">
        </label>
        <label>National ID image (optional)
          <input name="nid_file" type="file" accept="image/*">
        </label>
        <label>Requested loan amount (TZS)
          <input name="amount" type="number" min="1000" required value="<?php echo escape($submitted['amount'] ?? ''); ?>">
        </label>
        <label>Loan purpose
          <input name="purpose" value="<?php echo escape($submitted['purpose'] ?? ''); ?>">
        </label>
      </div>
      <button class="button primary" type="submit">Submit Loan Request</button>
    </form>

    <p style="margin-top:18px;">Open the admin page to review requests: <a href="admin.php">Admin dashboard</a></p>
  <script src="upload.js"></script>
  </main>
</body>
</html>
