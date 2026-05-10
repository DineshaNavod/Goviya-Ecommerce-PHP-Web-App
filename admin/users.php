<?php
$pageTitle = 'Users';
require_once __DIR__ . '/admin_header.php';
$db = getDB();

// Toggle role or deactivate (soft delete by changing role is not implemented; we just display)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $userId = (int)$_POST['user_id'];

    if ($action === 'toggle_role') {
        $current = $db->prepare("SELECT role FROM users WHERE id=?");
        $current->execute([$userId]);
        $role = $current->fetchColumn();
        $newRole = $role === 'admin' ? 'customer' : 'admin';
        $db->prepare("UPDATE users SET role=? WHERE id=?")->execute([$newRole, $userId]);
        setFlash('success', 'User role updated.');
    }
    header('Location: users.php'); exit;
}

$search = clean($_GET['search'] ?? '');
$params = [];
$where  = '';
if ($search) {
    $where = "WHERE name LIKE ? OR email LIKE ?";
    $params = ["%$search%", "%$search%"];
}
$stmt = $db->prepare("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id=u.id) AS order_count FROM users u $where ORDER BY created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-700 mb-0">👥 Users</h4>
  <form method="GET" class="d-flex gap-2">
    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name or email…" value="<?= clean($search) ?>" style="width:220px">
    <button class="btn btn-sm btn-outline-success">Search</button>
    <?php if ($search): ?><a href="users.php" class="btn btn-sm btn-outline-secondary">Clear</a><?php endif; ?>
  </form>
</div>

<div class="bg-white rounded-4 shadow-sm overflow-hidden">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-4">#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Role</th>
          <th>Orders</th>
          <th>Joined</th>
          <th class="pe-4">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td class="ps-4 text-muted small"><?= $u['id'] ?></td>
        <td class="fw-600"><?= clean($u['name']) ?></td>
        <td class="text-muted small"><?= clean($u['email']) ?></td>
        <td class="text-muted small"><?= clean($u['phone'] ?? '—') ?></td>
        <td>
          <span class="badge <?= $u['role'] === 'admin' ? 'bg-success' : 'bg-secondary' ?>">
            <?= ucfirst($u['role']) ?>
          </span>
        </td>
        <td>
          <span class="badge bg-light text-dark"><?= $u['order_count'] ?></span>
        </td>
        <td class="text-muted small"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
        <td class="pe-4">
          <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
          <form method="POST" class="d-inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle_role">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <button type="submit" class="btn btn-sm <?= $u['role'] === 'admin' ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                    data-confirm="<?= $u['role'] === 'admin' ? 'Remove admin role?' : 'Grant admin role?' ?>">
              <?= $u['role'] === 'admin' ? 'Revoke Admin' : 'Make Admin' ?>
            </button>
          </form>
          <?php else: ?>
          <span class="text-muted small">You</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if (empty($users)): ?>
  <div class="text-center py-5 text-muted">No users found.</div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>
