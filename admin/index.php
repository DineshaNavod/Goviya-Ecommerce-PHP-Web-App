<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/admin_header.php';

$db = getDB();
$totalOrders   = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue  = $db->query("SELECT SUM(total_amount) FROM orders WHERE payment_status='paid'")->fetchColumn();
$totalProducts = $db->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn();
$totalUsers    = $db->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();

$recentOrders = $db->query(
    "SELECT o.*, u.name AS customer FROM orders o
     JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 8"
)->fetchAll();
$lowStock = $db->query("SELECT * FROM products WHERE stock < 10 AND is_active=1 ORDER BY stock ASC LIMIT 5")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-700 mb-0">📊 Dashboard</h4>
  <span class="text-muted small"><?= date('l, d F Y') ?></span>
</div>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card stat-green">
      <span class="stat-icon">📦</span>
      <div><div class="stat-num"><?= $totalOrders ?></div><div class="stat-lbl">Total Orders</div></div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card stat-orange">
      <span class="stat-icon">💰</span>
      <div><div class="stat-num">Rs.<?= number_format((float)$totalRevenue) ?></div><div class="stat-lbl">Revenue Earned</div></div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card stat-teal">
      <span class="stat-icon">🥦</span>
      <div><div class="stat-num"><?= $totalProducts ?></div><div class="stat-lbl">Active Products</div></div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card stat-earth">
      <span class="stat-icon">👥</span>
      <div><div class="stat-num"><?= $totalUsers ?></div><div class="stat-lbl">Customers</div></div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- RECENT ORDERS -->
  <div class="col-lg-8">
    <div class="bg-white rounded-4 shadow-sm p-4">
      <div class="d-flex justify-content-between mb-3">
        <h6 class="fw-700">Recent Orders</h6>
        <a href="orders.php" class="btn btn-sm btn-outline-success">View All</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle small">
          <thead class="table-light">
            <tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Payment</th><th>Status</th></tr>
          </thead>
          <tbody>
          <?php foreach ($recentOrders as $o):
            $badgeCls = ['pending'=>'warning','confirmed'=>'info','delivered'=>'success','cancelled'=>'danger','processing'=>'primary','shipped'=>'secondary'][$o['status']] ?? 'secondary';
          ?>
          <tr>
            <td><a href="orders.php?id=<?= $o['id'] ?>" class="fw-600 text-decoration-none"><?= clean($o['order_number']) ?></a></td>
            <td><?= clean($o['customer']) ?></td>
            <td class="fw-600">Rs. <?= number_format($o['total_amount'], 2) ?></td>
            <td><span class="badge bg-light text-dark"><?= ucfirst($o['payment_method']) ?></span></td>
            <td><span class="badge bg-<?= $badgeCls ?>"><?= ucfirst($o['status']) ?></span></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- LOW STOCK ALERT -->
  <div class="col-lg-4">
    <div class="bg-white rounded-4 shadow-sm p-4">
      <h6 class="fw-700 mb-3">⚠️ Low Stock Alert</h6>
      <?php if (empty($lowStock)): ?>
      <p class="text-muted small">All products are well stocked.</p>
      <?php else: ?>
      <?php foreach ($lowStock as $p): ?>
      <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
        <span class="small fw-600"><?= clean($p['name']) ?></span>
        <span class="badge <?= $p['stock'] == 0 ? 'bg-danger' : 'bg-warning text-dark' ?>">
          <?= $p['stock'] ?> left
        </span>
      </div>
      <?php endforeach; ?>
      <a href="products.php" class="btn btn-sm btn-outline-warning mt-3 w-100">Manage Stock</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>
