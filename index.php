<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Fresh Vegetables & Fruits Delivered to Your Door';
$showFlash = true;

$db = getDB();


$featured = $db->query(
    "SELECT p.*, c.name AS cat_name FROM products p
     JOIN categories c ON p.category_id = c.id
     WHERE p.is_featured = 1 AND p.is_active = 1
     LIMIT 8"
)->fetchAll();


$categories = $db->query("SELECT * FROM categories WHERE is_active = 1")->fetchAll();

$catIcons = [
    'vegetables'   => '🥦',
    'fruits'       => '🍎',
    'rice-grains'  => '🌾',
    'dairy-eggs'   => '🥚',
    'herbs-spices' => '🌿',
];

include __DIR__ . '/includes/header.php';
?>


<section class="hero-section">
  <div class="container position-relative z-1">
    <div class="row align-items-center">
      <div class="col-lg-6">
        <p class="text-accent fw-600 mb-2 fade-in-up">🌿 Farm-to-Table Delivery</p>
        <h1 class="hero-title fade-in-up delay-1">
          Fresh from the<br><span>Farm.</span><br>Right to Your Door.
        </h1>
        <p class="hero-subtitle fade-in-up delay-2">
          Order fresh vegetables, fruits, rice, dairy and more — sourced directly from local Sri Lankan farmers. No preservatives, no middlemen.
        </p>
        <div class="d-flex gap-3 flex-wrap fade-in-up delay-3">
          <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-accent btn-lg px-4">
            <i class="bi bi-basket2 me-2"></i>Shop Now
          </a>
          <a href="#categories" class="btn btn-outline-light btn-lg px-4">Browse Categories</a>
        </div>
      </div>
    </div>
  </div>
  <div class="hero-emoji-float" aria-hidden="true">🥬🍅🌽🥕</div>
</section>


<div class="features-bar">
  <div class="container">
    <div class="row g-3 text-center text-md-start">
      <div class="col-6 col-md-3">
        <div class="feature-item justify-content-center justify-content-md-start">
          <span class="feature-icon">🚚</span>
          <div>
            <div class="feature-title">Free Delivery</div>
            <div class="feature-sub">Orders over Rs.2000</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="feature-item justify-content-center justify-content-md-start">
          <span class="feature-icon">🌱</span>
          <div>
            <div class="feature-title">100% Fresh</div>
            <div class="feature-sub">Harvested daily</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="feature-item justify-content-center justify-content-md-start">
          <span class="feature-icon">🔒</span>
          <div>
            <div class="feature-title">Secure Payments</div>
            <div class="feature-sub">Card & Cash on Delivery</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="feature-item justify-content-center justify-content-md-start">
          <span class="feature-icon">🤝</span>
          <div>
            <div class="feature-title">Support Local</div>
            <div class="feature-sub">Sri Lankan farmers</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<section class="py-5" id="categories">
  <div class="container">
    <h2 class="section-title text-center">Shop by Category</h2>
    <p class="section-lead text-center">Everything fresh, organised for you</p>
    <div class="row g-3">
      <?php foreach ($categories as $cat): ?>
      <div class="col-6 col-sm-4 col-md-2 col-lg-2">
        <a href="<?= SITE_URL ?>/pages/products.php?category=<?= $cat['slug'] ?>" class="category-card fade-in-up">
          <span class="category-icon"><?= $catIcons[$cat['slug']] ?? '🛒' ?></span>
          <h6><?= clean($cat['name']) ?></h6>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<section class="py-5 bg-light">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <h2 class="section-title">Fresh Picks Today</h2>
        <p class="section-lead mb-0">Hand-picked freshest items</p>
      </div>
      <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-outline-green">View All →</a>
    </div>
    <div class="row g-3">
      <?php foreach ($featured as $p):
        $price = $p['sale_price'] ?: $p['price'];
        $emoji = $catIcons[$p['cat_name']] ?? '🥦';
      ?>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="product-card fade-in-up">
          <?php if ($p['sale_price']): ?>
          <span class="product-badge badge-sale">SALE</span>
          <?php elseif ($p['is_featured']): ?>
          <span class="product-badge">Fresh</span>
          <?php endif; ?>

          <div class="product-img-wrap">
            <?php if ($p['image']): ?>
            <img src="<?= SITE_URL ?>/assets/images/products/<?= $p['image'] ?>" alt="<?= clean($p['name']) ?>" data-emoji="<?= $emoji ?>">
            <?php else: ?>
            <div class="product-img-placeholder"><?= $emoji ?></div>
            <?php endif; ?>
          </div>

          <div class="product-body">
            <div class="product-category"><?= clean($p['cat_name']) ?></div>
            <div class="product-name">
              <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark">
                <?= clean($p['name']) ?>
              </a>
            </div>
            <div class="product-unit">per <?= clean($p['unit']) ?></div>
            <div class="product-price">
              <span class="price-current"><?= CURRENCY ?> <?= number_format($price, 2) ?></span>
              <?php if ($p['sale_price']): ?>
              <span class="price-old"><?= CURRENCY ?> <?= number_format($p['price'], 2) ?></span>
              <?php endif; ?>
            </div>
            <?php if ($p['stock'] > 0): ?>
            <button class="btn-add-cart" data-product-id="<?= $p['id'] ?>">
              <i class="bi bi-basket2-fill"></i> Add to Cart
            </button>
            <?php else: ?>
            <button class="btn-add-cart" disabled style="background:#999;">Out of Stock</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<section class="py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-6">
        <div class="rounded-4 p-4 d-flex align-items-center gap-3"
             style="background:linear-gradient(135deg,#2d6a4f,#40916c);color:white;min-height:160px;">
          <span style="font-size:4rem">🥕</span>
          <div>
            <h4 style="font-family:'Playfair Display',serif">Fresh Vegetables</h4>
            <p class="mb-3 opacity-75">Direct from farms, no cold storage</p>
            <a href="<?= SITE_URL ?>/pages/products.php?category=vegetables" class="btn btn-accent btn-sm px-3">Shop Now</a>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="rounded-4 p-4 d-flex align-items-center gap-3"
             style="background:linear-gradient(135deg,#a0522d,#cd853f);color:white;min-height:160px;">
          <span style="font-size:4rem">🍎</span>
          <div>
            <h4 style="font-family:'Playfair Display',serif">Seasonal Fruits</h4>
            <p class="mb-3 opacity-75">Ripe, sweet & naturally grown</p>
            <a href="<?= SITE_URL ?>/pages/products.php?category=fruits" class="btn btn-light btn-sm px-3 text-dark">Shop Now</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
