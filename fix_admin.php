<?php
require_once 'includes/config.php';
$hash = password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost' => 12]);
$db   = getDB();

// Check if admin exists
$check = $db->prepare("SELECT id FROM users WHERE email = 'admin@goviya.lk'");
$check->execute();
$exists = $check->fetch();

if ($exists) {
    $db->prepare("UPDATE users SET password = ? WHERE email = 'admin@goviya.lk'")->execute([$hash]);
    echo "<h2 style='color:green;font-family:sans-serif'>✅ Password reset to: Admin@1234</h2>";
} else {
    $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)")
       ->execute(['Admin Goviya', 'admin@goviya.lk', $hash, 'admin']);
    echo "<h2 style='color:green;font-family:sans-serif'>✅ Admin account created! Password: Admin@1234</h2>";
}
echo "<p style='font-family:sans-serif'><a href='pages/login.php'>→ Click here to Login</a></p>";
echo "<p style='color:red;font-family:sans-serif'>⚠️ DELETE this file now: C:\\xampp\\htdocs\\goviya\\fix_admin.php</p>";
?>
