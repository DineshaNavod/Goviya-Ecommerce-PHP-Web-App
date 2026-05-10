<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pageTitle = 'My Orders';
$db = getDB();
$orders = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders->execute([$_SESSION['user_id']]);
$orders = $orders->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="page-hero">
  <div class="container"><h1 class="h2">📦 My Orders</h1></div>
</div>
<div class="container py-5">
  <?php if (empty($orders)): ?>
  <div class="text-center py-5">
    <div style="font-size:5rem">📭</div>
    <h4 class="mt-3">No orders yet</h4>
    <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-green mt-3">Start Shopping</a>
  </div>
  <?php else: ?>
  <div class="row g-4">
    <?php foreach ($orders as $order):
      $items = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
      $items->execute([$order['id']]);
      $items = $items->fetchAll();
      $badgeCls = [
        'pending'=>'warning','confirmed'=>'info','processing'=>'primary',
        'shipped'=>'secondary','delivered'=>'success','cancelled'=>'danger'
      ][$order['status']] ?? 'secondary';
    ?>
    <div class="col-12">
      <div class="bg-white rounded-4 shadow-sm p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start mb-3 gap-2">
          <div>
            <span class="fw-700 fs-6"><?= clean($order['order_number']) ?></span>
            <span class="text-muted small ms-2"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
          </div>
          <div class="d-flex gap-2 align-items-center">
            <span class="badge bg-<?= $badgeCls ?> px-3 py-2"><?= ucfirst($order['status']) ?></span>
            <span class="fw-700 text-success"><?= CURRENCY ?> <?= number_format($order['total_amount'], 2) ?></span>
          </div>
        </div>
        <div class="border-top pt-3">
          <?php foreach ($items as $item): ?>
          <div class="d-flex justify-content-between small py-1">
            <span><?= clean($item['name']) ?> × <?= $item['quantity'] ?></span>
            <span class="text-muted"><?= CURRENCY ?> <?= number_format($item['subtotal'], 2) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="mt-3 text-muted small">
          <i class="bi bi-geo-alt me-1"></i><?= clean($order['shipping_address']) ?>, <?= clean($order['shipping_city']) ?>
          &nbsp;|&nbsp;
          <i class="bi bi-credit-card me-1"></i>
          <?= match($order['payment_method']) { 'card'=>'Card', 'cod'=>'Cash on Delivery', 'bank_transfer'=>'Bank Transfer', default=>$order['payment_method'] } ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
