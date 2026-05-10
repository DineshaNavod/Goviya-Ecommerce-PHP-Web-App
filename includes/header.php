<?php
require_once __DIR__ . '/../includes/auth.php';
$cartCount   = cartCount();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? clean($pageTitle) . ' | ' . SITE_NAME : SITE_NAME ?></title>
  <!-- CSRF token + SITE_URL for JS — must be in <head> before main.js loads -->
  <meta name="csrf" content="<?= csrf_token() ?>">
  <meta name="site-url" content="<?= SITE_URL ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">
  <div class="container d-flex justify-content-between align-items-center">
    <span><i class="bi bi-geo-alt-fill me-1"></i> Free delivery within Colombo for orders over Rs.2000</span>
    <span><i class="bi bi-telephone-fill me-1"></i> 011-234-5678</span>
  </div>
</div>

<!-- MAIN NAVBAR -->
<nav class="navbar navbar-expand-lg sticky-top goviya-nav">
  <div class="container">
    <a class="navbar-brand" href="<?= SITE_URL ?>/index.php">
      <span class="brand-leaf">🌿</span>
      <span class="brand-text">Goviya<span class="brand-dot">.lk</span></span>
    </a>

    <form class="search-form d-none d-lg-flex" action="<?= SITE_URL ?>/pages/products.php" method="GET">
      <input type="text" name="search" class="form-control search-input"
             placeholder="Search vegetables, fruits, rice…"
             value="<?= isset($_GET['search']) ? clean($_GET['search']) : '' ?>">
      <button class="search-btn" type="submit"><i class="bi bi-search"></i></button>
    </form>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <i class="bi bi-list text-white fs-4"></i>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-center gap-1">
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/pages/products.php">Shop</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Categories</a>
          <ul class="dropdown-menu">
            <?php
            $cats = getDB()->query("SELECT name, slug FROM categories WHERE is_active=1")->fetchAll();
            foreach ($cats as $c):
            ?>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/pages/products.php?category=<?= $c['slug'] ?>">
              <?= clean($c['name']) ?>
            </a></li>
            <?php endforeach; ?>
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link cart-icon-wrap" href="<?= SITE_URL ?>/pages/cart.php">
            <i class="bi bi-basket2"></i>
            <?php if ($cartCount > 0): ?>
            <span class="cart-badge"><?= $cartCount ?></span>
            <?php endif; ?>
          </a>
        </li>

        <?php if (isLoggedIn()): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle me-1"></i><?= clean($_SESSION['user_name']) ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/pages/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/pages/orders.php"><i class="bi bi-bag-check me-2"></i>My Orders</a></li>
            <?php if (isAdmin()): ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-success" href="<?= SITE_URL ?>/admin/index.php"><i class="bi bi-speedometer2 me-2"></i>Admin Panel</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/pages/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </li>
        <?php else: ?>
        <li class="nav-item">
          <a class="btn btn-outline-light btn-sm px-3" href="<?= SITE_URL ?>/pages/login.php">Login</a>
        </li>
        <li class="nav-item">
          <a class="btn btn-accent btn-sm px-3" href="<?= SITE_URL ?>/pages/register.php">Sign Up</a>
        </li>
        <?php endif; ?>
      </ul>

      <form class="d-lg-none mt-3" action="<?= SITE_URL ?>/pages/products.php" method="GET">
        <div class="input-group">
          <input type="text" name="search" class="form-control" placeholder="Search…">
          <button class="btn btn-accent" type="submit"><i class="bi bi-search"></i></button>
        </div>
      </form>
    </div>
  </div>
</nav>

<main class="main-content">
<?php if (isset($showFlash) && $showFlash): showFlash(); endif; ?>
