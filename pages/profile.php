<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pageTitle = 'My Profile';
$db = getDB();
$userId = $_SESSION['user_id'];

$user = $db->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$userId]);
$user = $user->fetch();

$errors = []; $success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? 'profile';

    if ($action === 'profile') {
        $name    = clean($_POST['name']    ?? '');
        $phone   = clean($_POST['phone']   ?? '');
        $address = clean($_POST['address'] ?? '');
        if (!$name) $errors[] = 'Name is required.';
        if (empty($errors)) {
            $db->prepare("UPDATE users SET name=?, phone=?, address=? WHERE id=?")
               ->execute([$name, $phone, $address, $userId]);
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully!';
            $user['name'] = $name; $user['phone'] = $phone; $user['address'] = $address;
        }
    }

    if ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!password_verify($current, $user['password'])) $errors[] = 'Current password is incorrect.';
        if (strlen($new) < 8) $errors[] = 'New password must be at least 8 characters.';
        if ($new !== $confirm) $errors[] = 'Passwords do not match.';
        if (empty($errors)) {
            $hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $userId]);
            $success = 'Password changed successfully!';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="page-hero"><div class="container"><h1 class="h2">👤 My Profile</h1></div></div>
<div class="container py-5" style="max-width:680px">
  <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
  <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= $e ?></div><?php endforeach; ?>

  
  <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
    <h5 class="fw-700 mb-4">Personal Information</h5>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="profile">
      <div class="row g-3">
        <div class="col-sm-6">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" value="<?= clean($user['name']) ?>" required>
        </div>
        <div class="col-sm-6">
          <label class="form-label">Email (read-only)</label>
          <input type="email" class="form-control bg-light" value="<?= clean($user['email']) ?>" readonly>
        </div>
        <div class="col-sm-6">
          <label class="form-label">Phone</label>
          <input type="tel" name="phone" class="form-control" value="<?= clean($user['phone'] ?? '') ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Delivery Address</label>
          <textarea name="address" class="form-control" rows="2"><?= clean($user['address'] ?? '') ?></textarea>
        </div>
      </div>
      <button type="submit" class="btn btn-green mt-3 px-4">Save Changes</button>
    </form>
  </div>

  
  <div class="bg-white rounded-4 shadow-sm p-4">
    <h5 class="fw-700 mb-4">Change Password</h5>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="password">
      <div class="mb-3">
        <label class="form-label">Current Password</label>
        <input type="password" name="current_password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">New Password</label>
        <input type="password" name="new_password" class="form-control" placeholder="Min 8 characters" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm New Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-outline-green px-4">Update Password</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
