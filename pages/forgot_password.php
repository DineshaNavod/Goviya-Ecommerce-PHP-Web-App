<?php
require_once __DIR__ . '/../includes/auth.php';
$pageTitle = 'Forgot Password';

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $error = 'Please enter a valid email address.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $db->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE id=?")
               ->execute([$token, $expires, $user['id']]);
            // In production: send email with reset link
            // mail($email, 'Reset your Goviya.lk password', SITE_URL . '/pages/reset_password.php?token=' . $token);
        }
        // Always show success to prevent email enumeration
        $message = 'If that email exists, a reset link has been sent.';
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
  <div class="form-card">
    <div class="text-center mb-4">
      <span style="font-size:2.5rem">🔑</span>
      <h2 class="mt-2">Forgot Password</h2>
      <p class="text-muted small">Enter your email and we'll send a reset link</p>
    </div>
    <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <?php if (!$message): ?>
    <form method="POST">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>
      </div>
      <button type="submit" class="btn btn-green w-100">Send Reset Link</button>
    </form>
    <?php endif; ?>
    <p class="text-center mt-4 small">
      <a href="login.php" class="text-green">&larr; Back to Login</a>
    </p>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
