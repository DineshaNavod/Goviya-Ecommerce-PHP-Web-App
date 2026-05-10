<?php
$pageTitle = 'Orders';
require_once __DIR__ . '/admin_header.php';
$db = getDB();

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $orderId = (int)$_POST['order_id'];
    $status  = $_POST['status'] ?? '';
    $allowed = ['pending','confirmed','processing','shipped','delivered','cancelled'];
    if (in_array($status, $allowed)) {
        $db->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status, $orderId]);
        setFlash('success', 'Order status updated.');
    }
    header('Location: orders.php'); exit;
}

// Filter
$filterStatus = $_GET['status'] ?? '';
$where = $filterStatus ? "WHERE o.status = " . $db->quote($filterStatus) : '';

$orders = $db->query(
    "SELECT o.*, u.name AS customer, u.email AS customer_email
     FROM orders o JOIN users u ON o.user_id = u.id
     $where ORDER BY o.created_at DESC"
)->fetchAll();

$statuses = ['pending','confirmed','processing','shipped','delivered','cancelled'];
$counts = [];
foreach ($statuses as $s) {
    $c = $db->prepare("SELECT COUNT(*) FROM orders WHERE status=?");
    $c->execute([$s]);
    $counts[$s] = $c->fetchColumn();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-700 mb-0">📦 Orders</h4>
  <span class="text-muted small"><?= count($orders) ?> order<?= count($orders) != 1 ? 's' : '' ?></span>
</div>

<!-- STATUS FILTER TABS -->
<div class="d-flex flex-wrap gap-2 mb-4">
  <a href="orders.php" class="btn btn-sm <?= !$filterStatus ? 'btn-green' : 'btn-outline-secondary' ?>">All</a>
  <?php foreach ($statuses as $s): ?>
  <a href="?status=<?= $s ?>"
     class="btn btn-sm <?= $filterStatus === $s ? 'btn-green' : 'btn-outline-secondary' ?>">
    <?= ucfirst($s) ?>
    <span class="badge bg-white text-dark ms-1"><?= $counts[$s] ?></span>
  </a>
  <?php endforeach; ?>
</div>

<div class="bg-white rounded-4 shadow-sm overflow-hidden">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-4">Order #</th>
          <th>Customer</th>
          <th>Total</th>
          <th>Payment</th>
          <th>Pay Status</th>
          <th>Date</th>
          <th>Status</th>
          <th class="pe-4">Update</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($orders as $o):
        $badgeCls = [
          'pending'=>'warning','confirmed'=>'info','processing'=>'primary',
          'shipped'=>'secondary','delivered'=>'success','cancelled'=>'danger'
        ][$o['status']] ?? 'secondary';
        $payBadge = $o['payment_status'] === 'paid' ? 'success' : ($o['payment_status'] === 'failed' ? 'danger' : 'warning');
      ?>
      <tr>
        <td class="ps-4">
          <strong><?= clean($o['order_number']) ?></strong>
        </td>
        <td>
          <div class="fw-600 small"><?= clean($o['customer']) ?></div>
          <div class="text-muted" style="font-size:.75rem"><?= clean($o['customer_email']) ?></div>
        </td>
        <td class="fw-700 text-success">Rs. <?= number_format($o['total_amount'], 2) ?></td>
        <td><span class="badge bg-light text-dark"><?= ucfirst(str_replace('_',' ',$o['payment_method'])) ?></span></td>
        <td><span class="badge bg-<?= $payBadge ?>"><?= ucfirst($o['payment_status']) ?></span></td>
        <td class="text-muted small"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
        <td><span class="badge bg-<?= $badgeCls ?>"><?= ucfirst($o['status']) ?></span></td>
        <td class="pe-4">
          <form method="POST" class="d-flex gap-1 align-items-center">
            <?= csrf_field() ?>
            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
            <select name="status" class="form-select form-select-sm" style="width:140px">
              <?php foreach ($statuses as $s): ?>
              <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-green px-2">
              <i class="bi bi-check-lg"></i>
            </button>
          </form>
        </td>
      </tr>
      <!-- Order Items Row (collapsible) -->
      <tr class="table-light">
        <td colspan="8" class="px-4 py-2">
          <?php
          $items = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
          $items->execute([$o['id']]);
          $items = $items->fetchAll();
          $parts = array_map(fn($i) => clean($i['name']) . " ×{$i['quantity']}", $items);
          echo '<span class="text-muted small"><i class="bi bi-bag me-1"></i>' . implode(' &nbsp;|&nbsp; ', $parts) . '</span>';
          echo ' &nbsp;— &nbsp;<span class="text-muted small"><i class="bi bi-geo-alt me-1"></i>' . clean($o['shipping_address'] . ', ' . $o['shipping_city']) . '</span>';
          ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if (empty($orders)): ?>
  <div class="text-center py-5 text-muted">No orders found.</div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>
