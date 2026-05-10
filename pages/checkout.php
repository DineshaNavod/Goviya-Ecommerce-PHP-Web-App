<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pageTitle = 'Checkout';
$db = getDB();
$userId = $_SESSION['user_id'];

// Fetch cart
$stmt = $db->prepare(
    "SELECT c.quantity, p.id, p.name, p.price, p.sale_price, p.unit, p.stock
     FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?"
);
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    header('Location: ' . SITE_URL . '/pages/cart.php');
    exit;
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += ($item['sale_price'] ?: $item['price']) * $item['quantity'];
}
$delivery   = $subtotal >= 2000 ? 0 : 250;
$grandTotal = $subtotal + $delivery;

// Fetch user info
$user = $db->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$userId]);
$user = $user->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $shipName    = clean($_POST['ship_name']    ?? '');
    $shipPhone   = clean($_POST['ship_phone']   ?? '');
    $shipAddress = clean($_POST['ship_address'] ?? '');
    $shipCity    = clean($_POST['ship_city']    ?? '');
    $payMethod   = $_POST['payment_method']     ?? '';
    $notes       = clean($_POST['notes']        ?? '');

    if (!$shipName)    $errors[] = 'Delivery name is required.';
    if (!$shipPhone)   $errors[] = 'Phone number is required.';
    if (!$shipAddress) $errors[] = 'Delivery address is required.';
    if (!$shipCity)    $errors[] = 'City is required.';
    if (!in_array($payMethod, ['card', 'cod', 'bank_transfer'])) $errors[] = 'Select a payment method.';

    // Card validation
    if ($payMethod === 'card') {
        $cardNum = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
        $cardExp = clean($_POST['card_exp'] ?? '');
        $cardCvv = clean($_POST['card_cvv'] ?? '');
        if (!preg_match('/^\d{16}$/', $cardNum)) $errors[] = 'Enter a valid 16-digit card number.';
        if (!preg_match('/^\d{2}\/\d{2}$/', $cardExp)) $errors[] = 'Enter card expiry as MM/YY.';
        if (!preg_match('/^\d{3,4}$/', $cardCvv)) $errors[] = 'Enter a valid CVV.';
    }

    if (empty($errors)) {
        $db->beginTransaction();
        try {
            $orderNo = generateOrderNumber();
            $payStatus = $payMethod === 'cod' ? 'pending' : 'paid'; // Simulated

            $ins = $db->prepare(
                "INSERT INTO orders (user_id, order_number, total_amount, payment_method, payment_status,
                 shipping_name, shipping_phone, shipping_address, shipping_city, notes)
                 VALUES (?,?,?,?,?,?,?,?,?,?)"
            );
            $ins->execute([
                $userId, $orderNo, $grandTotal, $payMethod, $payStatus,
                $shipName, $shipPhone, $shipAddress, $shipCity, $notes
            ]);
            $orderId = $db->lastInsertId();

            // Insert order items
            $insItem = $db->prepare(
                "INSERT INTO order_items (order_id, product_id, name, price, quantity, subtotal)
                 VALUES (?,?,?,?,?,?)"
            );
            foreach ($cartItems as $item) {
                $price = $item['sale_price'] ?: $item['price'];
                $insItem->execute([$orderId, $item['id'], $item['name'], $price, $item['quantity'], $price * $item['quantity']]);
                // Reduce stock
                $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")->execute([$item['quantity'], $item['id']]);
            }

            // Clear cart
            $db->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$userId]);

            $db->commit();
            header('Location: ' . SITE_URL . '/pages/order_confirm.php?id=' . $orderId);
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Order failed. Please try again.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1 class="h2">🔒 Checkout</h1>
  </div>
</div>

<div class="container py-5">
  <?php foreach ($errors as $e): ?>
  <div class="alert alert-danger"><?= $e ?></div>
  <?php endforeach; ?>

  <form method="POST" id="checkoutForm">
    <?= csrf_field() ?>
    <div class="row g-4">
      <!-- DELIVERY DETAILS -->
      <div class="col-lg-7">
        <div class="checkout-section mb-4">
          <h5 class="fw-700 mb-4"><i class="bi bi-geo-alt-fill text-success me-2"></i>Delivery Details</h5>
          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="ship_name" class="form-control"
                     value="<?= clean($_POST['ship_name'] ?? $user['name']) ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label">Phone Number <span class="text-danger">*</span></label>
              <input type="tel" name="ship_phone" class="form-control"
                     value="<?= clean($_POST['ship_phone'] ?? $user['phone'] ?? '') ?>" required>
            </div>
            <div class="col-12">
              <label class="form-label">Street Address <span class="text-danger">*</span></label>
              <textarea name="ship_address" class="form-control" rows="2" required><?= clean($_POST['ship_address'] ?? $user['address'] ?? '') ?></textarea>
            </div>
            <div class="col-sm-6">
              <label class="form-label">City <span class="text-danger">*</span></label>
              <input type="text" name="ship_city" class="form-control"
                     value="<?= clean($_POST['ship_city'] ?? '') ?>" required>
            </div>
            <div class="col-12">
              <label class="form-label">Order Notes (optional)</label>
              <textarea name="notes" class="form-control" rows="2" placeholder="Any delivery instructions…"><?= clean($_POST['notes'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <!-- PAYMENT -->
        <div class="checkout-section">
          <h5 class="fw-700 mb-4"><i class="bi bi-credit-card-fill text-success me-2"></i>Payment Method</h5>

          <!-- Card -->
          <div class="payment-option <?= ($_POST['payment_method'] ?? '') === 'card' ? 'selected' : '' ?>"
               data-method="card">
            <div class="d-flex align-items-center gap-3">
              <input type="radio" name="payment_method" value="card" class="form-check-input mt-0"
                     <?= ($_POST['payment_method'] ?? '') === 'card' ? 'checked' : '' ?>>
              <div>
                <div class="fw-600">💳 Credit / Debit Card</div>
                <div class="text-muted small">Visa, Mastercard, Amex accepted</div>
              </div>
            </div>
          </div>

          <!-- Card Fields (shown when card selected) -->
          <div id="card-fields" class="mt-3 p-3 rounded-3"
               style="background:var(--clr-cream);display:<?= ($_POST['payment_method'] ?? '') === 'card' ? 'block' : 'none' ?>">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label small fw-600">Card Number</label>
                <input type="text" name="card_number" class="form-control"
                       placeholder="1234 5678 9012 3456" maxlength="19"
                       oninput="this.value=this.value.replace(/\D/g,'').replace(/(.{4})/g,'$1 ').trim()">
              </div>
              <div class="col-6">
                <label class="form-label small fw-600">Expiry (MM/YY)</label>
                <input type="text" name="card_exp" class="form-control" placeholder="08/27" maxlength="5">
              </div>
              <div class="col-6">
                <label class="form-label small fw-600">CVV</label>
                <input type="text" name="card_cvv" class="form-control" placeholder="123" maxlength="4">
              </div>
            </div>
            <div class="alert alert-info mt-3 py-2 small mb-0">
              <i class="bi bi-info-circle me-1"></i>
              <strong>Demo mode:</strong> No real payment is processed. Use any test card details.
            </div>
          </div>

          <!-- COD -->
          <div class="payment-option <?= ($_POST['payment_method'] ?? 'cod') === 'cod' ? 'selected' : '' ?>"
               data-method="cod">
            <div class="d-flex align-items-center gap-3">
              <input type="radio" name="payment_method" value="cod" class="form-check-input mt-0"
                     <?= ($_POST['payment_method'] ?? 'cod') === 'cod' ? 'checked' : '' ?>>
              <div>
                <div class="fw-600">💵 Cash on Delivery</div>
                <div class="text-muted small">Pay when your order arrives</div>
              </div>
            </div>
          </div>

          <!-- Bank Transfer -->
          <div class="payment-option <?= ($_POST['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>"
               data-method="bank_transfer">
            <div class="d-flex align-items-center gap-3">
              <input type="radio" name="payment_method" value="bank_transfer" class="form-check-input mt-0"
                     <?= ($_POST['payment_method'] ?? '') === 'bank_transfer' ? 'checked' : '' ?>>
              <div>
                <div class="fw-600">🏦 Bank Transfer</div>
                <div class="text-muted small">Transfer via internet banking</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ORDER SUMMARY -->
      <div class="col-lg-5">
        <div class="cart-summary-card">
          <h5 class="fw-700 mb-4">Order Summary</h5>
          <?php foreach ($cartItems as $item):
            $price = $item['sale_price'] ?: $item['price'];
          ?>
          <div class="d-flex justify-content-between mb-2 small">
            <span><?= clean($item['name']) ?> × <?= $item['quantity'] ?></span>
            <span><?= CURRENCY ?> <?= number_format($price * $item['quantity'], 2) ?></span>
          </div>
          <?php endforeach; ?>
          <hr>
          <div class="d-flex justify-content-between mb-1 text-muted">
            <span>Subtotal</span><span><?= CURRENCY ?> <?= number_format($subtotal, 2) ?></span>
          </div>
          <div class="d-flex justify-content-between mb-3 text-muted">
            <span>Delivery</span>
            <span class="<?= $delivery == 0 ? 'text-success fw-600' : '' ?>">
              <?= $delivery == 0 ? 'FREE' : CURRENCY . ' ' . number_format($delivery, 2) ?>
            </span>
          </div>
          <div class="d-flex justify-content-between fw-700 fs-5 mb-4 border-top pt-3">
            <span>Total</span>
            <span class="text-success"><?= CURRENCY ?> <?= number_format($grandTotal, 2) ?></span>
          </div>
          <button type="submit" class="btn btn-green w-100 py-3 fw-600 fs-6">
            <i class="bi bi-lock-fill me-2"></i>Place Order
          </button>
          <div class="text-center mt-3 small text-muted">
            <i class="bi bi-shield-check me-1 text-success"></i>Your data is encrypted & secure
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
