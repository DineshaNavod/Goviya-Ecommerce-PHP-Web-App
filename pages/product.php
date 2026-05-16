<?php
require_once __DIR__ . '/../includes/auth.php';
$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare(
    "SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
     FROM products p JOIN categories c ON p.category_id = c.id
     WHERE p.id = ? AND p.is_active = 1"
);
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { header('Location: ' . SITE_URL . '/pages/products.php'); exit; }

$pageTitle = $product['name'];


$related = $db->prepare(
    "SELECT p.*, c.name AS cat_name FROM products p JOIN categories c ON p.category_id=c.id
     WHERE p.category_id=? AND p.id != ? AND p.is_active=1 LIMIT 4"
);
$related->execute([$product['category_id'], $id]);
$related = $related->fetchAll();

$catIcons = ['vegetables'=>'🥦','fruits'=>'🍎','rice-grains'=>'🌾','dairy-eggs'=>'🥚','herbs-spices'=>'🌿'];
$emoji = $catIcons[$product['cat_slug']] ?? '🛒';
$price = $product['sale_price'] ?: $product['price'];

include __DIR__ . '/../includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/pages/products.php?category=<?= $product['cat_slug'] ?>"><?= clean($product['cat_name']) ?></a></li>
        <li class="breadcrumb-item active"><?= clean($product['name']) ?></li>
      </ol>
    </nav>
  </div>
</div>

<div class="container py-5">
  <div class="row g-5">
    
    <div class="col-md-5">
      <div class="bg-white rounded-4 shadow-sm overflow-hidden d-flex align-items-center justify-content-center"
           style="min-height:360px;font-size:10rem;background:var(--clr-cream)!important">
        <?php if ($product['image']): ?>
        <img src="<?= SITE_URL ?>/assets/images/products/<?= $product['image'] ?>"
             class="img-fluid w-100" style="object-fit:cover;max-height:360px" alt="<?= clean($product['name']) ?>">
        <?php else: ?>
        <div class="text-center py-4"><?= $emoji ?></div>
        <?php endif; ?>
      </div>
    </div>

    
    <div class="col-md-7">
      <div class="text-muted small mb-2"><?= clean($product['cat_name']) ?></div>
      <h1 class="h2 fw-700 mb-3"><?= clean($product['name']) ?></h1>

      <div class="d-flex align-items-center gap-3 mb-4">
        <span class="fw-900 text-success" style="font-size:2rem">
          <?= CURRENCY ?> <?= number_format($price, 2) ?>
        </span>
        <?php if ($product['sale_price']): ?>
        <span class="text-muted text-decoration-line-through fs-5">
          <?= CURRENCY ?> <?= number_format($product['price'], 2) ?>
        </span>
        <span class="badge" style="background:var(--clr-earth)">SALE</span>
        <?php endif; ?>
        <span class="text-muted small">/ <?= clean($product['unit']) ?></span>
      </div>

      <?php if ($product['description']): ?>
      <p class="text-muted mb-4"><?= clean($product['description']) ?></p>
      <?php endif; ?>

      <div class="d-flex gap-3 mb-4 flex-wrap">
        <div class="text-center p-3 rounded-3 bg-light">
          <div class="fw-700"><?= $product['stock'] > 0 ? $product['stock'] : 'Out of' ?></div>
          <div class="text-muted small">In Stock</div>
        </div>
        <div class="text-center p-3 rounded-3 bg-light">
          <div class="fw-700"><?= clean($product['unit']) ?></div>
          <div class="text-muted small">Per Unit</div>
        </div>
        <div class="text-center p-3 rounded-3 bg-light">
          <div class="fw-700">🚚</div>
          <div class="text-muted small">Daily Delivery</div>
        </div>
      </div>

      <?php if ($product['stock'] > 0): ?>
      <button class="btn btn-green btn-lg px-5 btn-add-cart" data-product-id="<?= $product['id'] ?>">
        <i class="bi bi-basket2-fill me-2"></i>Add to Cart
      </button>
      <?php else: ?>
      <button class="btn btn-secondary btn-lg px-5" disabled>Out of Stock</button>
      <?php endif; ?>

      <div class="mt-4 small text-muted">
        <i class="bi bi-shield-check text-success me-2"></i>Fresh quality guaranteed or full refund
      </div>
    </div>
  </div>

  
  <?php if (!empty($related)): ?>
  <div class="mt-5">
    <h3 class="section-title mb-4">More from <?= clean($product['cat_name']) ?></h3>
    <div class="row g-3">
      <?php foreach ($related as $r):
        $rPrice = $r['sale_price'] ?: $r['price'];
        $rEmoji = $catIcons[$r['cat_slug'] ?? ''] ?? '🛒';
      ?>
      <div class="col-6 col-md-3">
        <div class="product-card h-100">
          <div class="product-img-wrap">
            <?php if ($r['image']): ?>
            <img src="<?= SITE_URL ?>/assets/images/products/<?= $r['image'] ?>" alt="<?= clean($r['name']) ?>">
            <?php else: ?>
            <div class="product-img-placeholder"><?= $rEmoji ?></div>
            <?php endif; ?>
          </div>
          <div class="product-body">
            <div class="product-name">
              <a href="product.php?id=<?= $r['id'] ?>" class="text-decoration-none text-dark"><?= clean($r['name']) ?></a>
            </div>
            <div class="product-price">
              <span class="price-current"><?= CURRENCY ?> <?= number_format($rPrice, 2) ?></span>
            </div>
            <?php if ($r['stock'] > 0): ?>
            <button class="btn-add-cart" data-product-id="<?= $r['id'] ?>">
              <i class="bi bi-basket2-fill"></i> Add to Cart
            </button>
            <?php else: ?>
            <button class="btn-add-cart" disabled style="background:#bbb">Out of Stock</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
