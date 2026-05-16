<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pageTitle = 'My Cart';
$showFlash = true;
$db = getDB();
$userId = $_SESSION['user_id'];

$items = $db->prepare(
    "SELECT c.quantity, p.id, p.name, p.price, p.sale_price, p.unit, p.image, p.stock,
            cat.name AS cat_name, cat.slug AS cat_slug
     FROM cart c
     JOIN products p   ON c.product_id = p.id
     JOIN categories cat ON p.category_id = cat.id
     WHERE c.user_id = ?"
);
$items->execute([$userId]);
$cartItems = $items->fetchAll();

$total = 0;
foreach ($cartItems as $item) {
    $price = $item['sale_price'] ?: $item['price'];
    $total += $price * $item['quantity'];
}
$delivery = $total >= 2000 ? 0 : 250;
$grandTotal = $total + $delivery;

$catIcons = ['vegetables'=>'🥦','fruits'=>'🍎','rice-grains'=>'🌾','dairy-eggs'=>'🥚','herbs-spices'=>'🌿'];

include __DIR__ . '/../includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1 class="h2">🛒 My Cart</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>">Home</a></li>
        <li class="breadcrumb-item active">Cart</li>
      </ol>
    </nav>
  </div>
</div>

<div class="container py-5">
  <?php if (empty($cartItems)): ?>
  <div class="text-center py-5">
    <div style="font-size:6rem">🛒</div>
    <h3 class="mt-3">Your cart is empty</h3>
    <p class="text-muted">Add some fresh produce to get started</p>
    <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-green mt-3 px-4">Browse Products</a>
  </div>
  <?php else: ?>
  <div class="row g-4">
    
    <div class="col-lg-8">
      <div class="cart-table">
        <table class="table mb-0 align-middle">
          <thead style="background:var(--clr-cream)">
            <tr>
              <th class="ps-4">Product</th>
              <th class="text-center">Price</th>
              <th class="text-center">Qty</th>
              <th class="text-center">Subtotal</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($cartItems as $item):
            $price = $item['sale_price'] ?: $item['price'];
            $sub   = $price * $item['quantity'];
            $emoji = $catIcons[$item['cat_slug']] ?? '🛒';
          ?>
          <tr>
            <td class="ps-4">
              <div class="d-flex align-items-center gap-3">
                <div style="width:64px;height:64px;border-radius:10px;overflow:hidden;background:var(--clr-cream);display:flex;align-items:center;justify-content:center;font-size:2rem;flex-shrink:0">
                  <?php if ($item['image']): ?>
                  <img src="<?= SITE_URL ?>/assets/images/products/<?= $item['image'] ?>"
                       style="width:100%;height:100%;object-fit:cover" alt="">
                  <?php else: echo $emoji; endif; ?>
                </div>
                <div>
                  <div class="fw-600"><?= clean($item['name']) ?></div>
                  <div class="text-muted small">per <?= clean($item['unit']) ?></div>
                </div>
              </div>
            </td>
            <td class="text-center fw-600"><?= CURRENCY ?> <?= number_format($price, 2) ?></td>
            <td class="text-center">
              <input type="number" class="form-control form-control-sm text-center qty-input mx-auto"
                     style="width:72px"
                     value="<?= $item['quantity'] ?>"
                     min="1" max="<?= $item['stock'] ?>"
                     data-product-id="<?= $item['id'] ?>">
            </td>
            <td class="text-center fw-700 text-success"><?= CURRENCY ?> <?= number_format($sub, 2) ?></td>
            <td class="pe-3">
              <button class="btn btn-sm btn-outline-danger btn-remove-cart"
                      data-product-id="<?= $item['id'] ?>" title="Remove">
                <i class="bi bi-trash3"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-between mt-4">
        <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-outline-green">
          <i class="bi bi-arrow-left me-2"></i>Continue Shopping
        </a>
      </div>
    </div>

    
    <div class="col-lg-4">
      <div class="cart-summary-card">
        <h5 class="fw-700 mb-4">Order Summary</h5>
        <div class="d-flex justify-content-between mb-2 text-muted">
          <span>Subtotal</span>
          <span><?= CURRENCY ?> <?= number_format($total, 2) ?></span>
        </div>
        <div class="d-flex justify-content-between mb-2 text-muted">
          <span>Delivery Fee</span>
          <span class="<?= $delivery == 0 ? 'text-success fw-600' : '' ?>">
            <?= $delivery == 0 ? 'FREE' : CURRENCY . ' ' . number_format($delivery, 2) ?>
          </span>
        </div>
        <?php if ($delivery > 0): ?>
        <div class="alert alert-warning py-2 small mb-3">
          Add <?= CURRENCY ?> <?= number_format(2000 - $total, 2) ?> more for free delivery!
        </div>
        <?php else: ?>
        <div class="alert alert-success py-2 small mb-3">🎉 You qualify for free delivery!</div>
        <?php endif; ?>
        <hr>
        <div class="d-flex justify-content-between fw-700 fs-5 mb-4">
          <span>Total</span>
          <span class="text-success"><?= CURRENCY ?> <?= number_format($grandTotal, 2) ?></span>
        </div>
        <a href="<?= SITE_URL ?>/pages/checkout.php" class="btn btn-green w-100 py-3 fw-600 fs-6">
          <i class="bi bi-lock-fill me-2"></i>Proceed to Checkout
        </a>
        <div class="text-center mt-3 small text-muted">
          <i class="bi bi-shield-check me-1 text-success"></i>Secure & encrypted checkout
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
