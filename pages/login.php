<?php
require_once __DIR__ . '/../includes/auth.php';
$pageTitle = 'Login';

if (isLoggedIn()) { header('Location: ' . SITE_URL . '/index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            $redirect = $_GET['redirect'] ?? '';
            $safeRedirect = (str_starts_with($redirect, SITE_URL) || str_starts_with($redirect, '/'))
                ? $redirect : SITE_URL . '/index.php';

            setFlash('success', 'Welcome back, ' . $user['name'] . '!');
            header('Location: ' . ($redirect ? $safeRedirect : SITE_URL . '/index.php'));
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
include __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
  <div class="form-card">
    <div class="text-center mb-4">
      <span style="font-size:2.5rem">🌿</span>
      <h2 class="mt-2">Welcome Back</h2>
      <p class="text-muted small">Sign in to your Goviya.lk account</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?= clean($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" class="form-control" placeholder="you@example.com"
                 value="<?= isset($_POST['email']) ? clean($_POST['email']) : '' ?>" required>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label d-flex justify-content-between">
          Password
          <a href="<?= SITE_URL ?>/pages/forgot_password.php" class="text-green small">Forgot password?</a>
        </label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" id="passwordInput" class="form-control" placeholder="••••••••" required>
          <button type="button" class="input-group-text border-start-0"
                  onclick="const p=document.getElementById('passwordInput');p.type=p.type==='password'?'text':'password'">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>
      <button type="submit" class="btn btn-green w-100 py-2 mt-2">Sign In</button>
    </form>

    <hr class="my-4">
    <p class="text-center small text-muted">
      Don't have an account?
      <a href="<?= SITE_URL ?>/pages/register.php" class="text-green fw-600">Create one</a>
    </p>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
