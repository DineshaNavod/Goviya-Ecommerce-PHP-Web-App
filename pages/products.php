<?php
require_once __DIR__ . '/../includes/auth.php';
$pageTitle = 'Shop Fresh Produce';
$showFlash = true;
$db = getDB();

// Filters
$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$minPrice = (float)($_GET['min_price'] ?? 0);
$maxPrice = (float)($_GET['max_price'] ?? 9999);
$sort     = $_GET['sort'] ?? 'featured';

// Build query
$params = [];
$where  = ["p.is_active = 1"];

if ($search !== '') {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category !== '') {
    $where[] = "c.slug = ?";
    $params[] = $category;
}
if ($minPrice > 0) { $where[] = "COALESCE(p.sale_price, p.price) >= ?"; $params[] = $minPrice; }
if ($maxPrice < 9999) { $where[] = "COALESCE(p.sale_price, p.price) <= ?"; $params[] = $maxPrice; }

$orderBy = match($sort) {
    'price_asc'  => 'COALESCE(p.sale_price, p.price) ASC',
    'price_desc' => 'COALESCE(p.sale_price, p.price) DESC',
    'newest'     => 'p.created_at DESC',
    default      => 'p.is_featured DESC, p.created_at DESC',
};

$sql = "SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY $orderBy";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$allCats = $db->query("SELECT * FROM categories WHERE is_active=1")->fetchAll();
$catIcons = ['vegetables'=>'🥦','fruits'=>'🍎','rice-grains'=>'🌾','dairy-eggs'=>'🥚','herbs-spices'=>'🌿'];

$activeCategory = $category ? $db->prepare("SELECT name FROM categories WHERE slug=?")->execute([$category]) : null;
$activeCatName = '';
if ($category) {
    $s = $db->prepare("SELECT name FROM categories WHERE slug=?");
    $s->execute([$category]);
    $activeCatName = $s->fetchColumn() ?: '';
}

include __DIR__ . '/../includes/header.php';
?>

<!-- PAGE HERO -->
<div class="page-hero">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-2">
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>">Home</a></li>
        <li class="breadcrumb-item active"><?= $activeCatName ?: 'All Products' ?></li>
      </ol>
    </nav>
    <h1 class="h2"><?= $search ? 'Search: "' . clean($search) . '"' : ($activeCatName ?: 'All Fresh Products') ?></h1>
    <p class="text-white-50 mt-1"><?= count($products) ?> products found</p>
  </div>
</div>

<div class="container py-5">
  <div class="row g-4">
    <!-- SIDEBAR FILTERS -->
    <div class="col-lg-3">
      <div class="bg-white rounded-4 p-4 shadow-sm sticky-top" style="top:90px">
        <h6 class="fw-700 mb-3">🔍 Filter Products</h6>
        <form method="GET">
          <?php if ($search): ?>
          <input type="hidden" name="search" value="<?= clean($search) ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label fw-600 small">Category</label>
            <select name="category" class="form-select form-select-sm">
              <option value="">All Categories</option>
              <?php foreach ($allCats as $c): ?>
              <option value="<?= $c['slug'] ?>" <?= $category === $c['slug'] ? 'selected' : '' ?>>
                <?= $catIcons[$c['slug']] ?? '' ?> <?= clean($c['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-600 small">Price Range (Rs.)</label>
            <div class="d-flex gap-2">
              <input type="number" name="min_price" class="form-control form-control-sm"
                     placeholder="Min" value="<?= $minPrice ?: '' ?>" min="0">
              <input type="number" name="max_price" class="form-control form-control-sm"
                     placeholder="Max" value="<?= $maxPrice < 9999 ? $maxPrice : '' ?>" min="0">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-600 small">Sort By</label>
            <select name="sort" class="form-select form-select-sm">
              <option value="featured" <?= $sort==='featured' ? 'selected' : '' ?>>Featured First</option>
              <option value="newest"   <?= $sort==='newest'   ? 'selected' : '' ?>>Newest</option>
              <option value="price_asc" <?= $sort==='price_asc' ? 'selected' : '' ?>>Price: Low → High</option>
              <option value="price_desc" <?= $sort==='price_desc' ? 'selected' : '' ?>>Price: High → Low</option>
            </select>
          </div>

          <button type="submit" class="btn btn-green w-100 btn-sm">Apply Filters</button>
          <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-outline-secondary w-100 btn-sm mt-2">Clear All</a>
        </form>

        <hr>
        <h6 class="fw-700 mb-2">Categories</h6>
        <div class="d-flex flex-column gap-1">
          <a href="<?= SITE_URL ?>/pages/products.php" class="text-decoration-none small text-dark <?= !$category ? 'fw-600 text-success' : '' ?>">All Products</a>
          <?php foreach ($allCats as $c): ?>
          <a href="?category=<?= $c['slug'] ?>" class="text-decoration-none small text-dark <?= $category===$c['slug'] ? 'fw-600 text-success' : '' ?>">
            <?= $catIcons[$c['slug']] ?? '' ?> <?= clean($c['name']) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- PRODUCTS GRID -->
    <div class="col-lg-9">
      <?php if (empty($products)): ?>
      <div class="text-center py-5">
        <div style="font-size:5rem">🔍</div>
        <h4 class="mt-3">No products found</h4>
        <p class="text-muted">Try different filters or search terms</p>
        <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-green mt-2">Browse All Products</a>
      </div>
      <?php else: ?>
      <div class="row g-3">
        <?php foreach ($products as $p):
          $price = $p['sale_price'] ?: $p['price'];
          $emoji = $catIcons[$p['cat_slug']] ?? '🛒';
        ?>
        <div class="col-6 col-md-4">
          <div class="product-card h-100 fade-in-up">
            <?php if ($p['sale_price']): ?>
            <span class="product-badge badge-sale">SALE</span>
            <?php elseif ($p['is_featured']): ?>
            <span class="product-badge">Fresh</span>
            <?php endif; ?>

            <div class="product-img-wrap">
              <?php if ($p['image']): ?>
              <img src="<?= SITE_URL ?>/assets/images/products/<?= $p['image'] ?>"
                   alt="<?= clean($p['name']) ?>" data-emoji="<?= $emoji ?>">
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
              <button class="btn-add-cart" disabled style="background:#bbb">Out of Stock</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
