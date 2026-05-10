<?php
require_once __DIR__ . '/../includes/auth.php';
$pageTitle = 'Create Account';
if (isLoggedIn()) { header('Location: ' . SITE_URL . '/index.php'); exit; }

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name     = clean($_POST['name']     ?? '');
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone    = clean($_POST['phone']    ?? '');
    $password = $_POST['password']       ?? '';
    $confirm  = $_POST['confirm']        ?? '';

    if (!$name)                      $errors[] = 'Full name is required.';
    if (!$email)                     $errors[] = 'A valid email is required.';
    if (strlen($password) < 8)       $errors[] = 'Password must be at least 8 characters.';
    if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Password must contain at least one uppercase letter.';
    if (!preg_match('/[0-9]/', $password)) $errors[] = 'Password must contain at least one number.';
    if ($password !== $confirm)      $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $db = getDB();
        $exists = $db->prepare("SELECT id FROM users WHERE email = ?");
        $exists->execute([$email]);
        if ($exists->fetch()) {
            $errors[] = 'This email is already registered. <a href="login.php">Login instead?</a>';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $ins  = $db->prepare("INSERT INTO users (name, email, phone, password) VALUES (?,?,?,?)");
            $ins->execute([$name, $email, $phone, $hash]);
            $success = true;
        }
    }
}
include __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
  <div class="form-card" style="max-width:520px">
    <div class="text-center mb-4">
      <span style="font-size:2.5rem">🌱</span>
      <h2 class="mt-2">Create Your Account</h2>
      <p class="text-muted small">Join Goviya.lk for fresh farm produce</p>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success text-center">
      <i class="bi bi-check-circle-fill me-2"></i>
      Account created! <a href="login.php" class="fw-600">Sign in now</a>
    </div>
    <?php endif; ?>

    <?php foreach ($errors as $e): ?>
    <div class="alert alert-danger"><?= $e ?></div>
    <?php endforeach; ?>

    <?php if (!$success): ?>
    <form method="POST">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Full Name <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="text" name="name" class="form-control" placeholder="Kasun Perera"
                 value="<?= clean($_POST['name'] ?? '') ?>" required>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Email Address <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" class="form-control" placeholder="kasun@example.com"
                 value="<?= clean($_POST['email'] ?? '') ?>" required>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Phone Number</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-phone"></i></span>
          <input type="tel" name="phone" class="form-control" placeholder="071-234-5678"
                 value="<?= clean($_POST['phone'] ?? '') ?>">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Password <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" id="pw" class="form-control" placeholder="Min 8 chars, 1 uppercase, 1 number" required>
          <button type="button" class="input-group-text border-start-0"
                  onclick="const p=document.getElementById('pw');p.type=p.type==='password'?'text':'password'">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
          <input type="password" name="confirm" class="form-control" placeholder="Re-enter password" required>
        </div>
      </div>
      <button type="submit" class="btn btn-green w-100 py-2 mt-1">Create Account</button>
    </form>
    <?php endif; ?>

    <hr class="my-4">
    <p class="text-center small text-muted">
      Already have an account? <a href="login.php" class="text-green fw-600">Sign in</a>
    </p>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
