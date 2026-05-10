<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pageTitle = 'Order Confirmed';
$db = getDB();
$orderId = (int)($_GET['id'] ?? 0);

$order = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$order->execute([$orderId, $_SESSION['user_id']]);
$order = $order->fetch();

if (!$order) { header('Location: ' . SITE_URL . '/index.php'); exit; }

$items = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items->execute([$orderId]);
$items = $items->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="container py-5" style="max-width:680px">
  <div class="text-center mb-5">
    <div style="font-size:5rem">✅</div>
    <h1 class="mt-3" style="font-family:'Playfair Display',serif;color:var(--clr-green-dark)">Order Placed!</h1>
    <p class="text-muted">Thank you for shopping with Goviya.lk</p>
    <div class="badge bg-success fs-6 px-3 py-2"><?= clean($order['order_number']) ?></div>
  </div>

  <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
    <h6 class="fw-700 mb-3 text-muted text-uppercase small">Order Details</h6>
    <div class="row g-2 text-sm">
      <div class="col-6"><span class="text-muted">Order #</span><br><strong><?= clean($order['order_number']) ?></strong></div>
      <div class="col-6"><span class="text-muted">Date</span><br><strong><?= date('d M Y', strtotime($order['created_at'])) ?></strong></div>
      <div class="col-6 mt-2"><span class="text-muted">Payment</span><br>
        <strong><?= match($order['payment_method']) { 'card' => '💳 Card', 'cod' => '💵 Cash on Delivery', 'bank_transfer' => '🏦 Bank Transfer', default => $order['payment_method'] } ?></strong>
      </div>
      <div class="col-6 mt-2"><span class="text-muted">Status</span><br>
        <span class="badge badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
    <h6 class="fw-700 mb-3 text-muted text-uppercase small">Items Ordered</h6>
    <?php foreach ($items as $item): ?>
    <div class="d-flex justify-content-between py-2 border-bottom">
      <span><?= clean($item['name']) ?> <span class="text-muted">× <?= $item['quantity'] ?></span></span>
      <span class="fw-600"><?= CURRENCY ?> <?= number_format($item['subtotal'], 2) ?></span>
    </div>
    <?php endforeach; ?>
    <div class="d-flex justify-content-between pt-3 fw-700 text-success">
      <span>Total Paid</span>
      <span><?= CURRENCY ?> <?= number_format($order['total_amount'], 2) ?></span>
    </div>
  </div>

  <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
    <h6 class="fw-700 mb-3 text-muted text-uppercase small">Delivery To</h6>
    <p class="mb-1"><strong><?= clean($order['shipping_name']) ?></strong></p>
    <p class="mb-1 text-muted"><?= clean($order['shipping_address']) ?></p>
    <p class="mb-0 text-muted"><?= clean($order['shipping_city']) ?> · <?= clean($order['shipping_phone']) ?></p>
  </div>

  <?php if ($order['payment_method'] === 'bank_transfer'): ?>
  <div class="alert alert-info">
    <h6 class="fw-700">Bank Transfer Details</h6>
    <p class="mb-1">Bank: <strong>Commercial Bank of Ceylon</strong></p>
    <p class="mb-1">Account Name: <strong>Goviya (Pvt) Ltd</strong></p>
    <p class="mb-1">Account No: <strong>1234567890</strong></p>
    <p class="mb-0">Reference: <strong><?= clean($order['order_number']) ?></strong></p>
  </div>
  <?php endif; ?>

  <div class="d-flex gap-3 justify-content-center mt-4">
    <a href="<?= SITE_URL ?>/pages/orders.php" class="btn btn-outline-green px-4">
      <i class="bi bi-bag-check me-2"></i>My Orders
    </a>
    <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-green px-4">
      Continue Shopping
    </a>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
