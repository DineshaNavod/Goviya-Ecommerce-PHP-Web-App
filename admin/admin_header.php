<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
$currentAdmin = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? clean($pageTitle) . ' | Admin' : 'Admin Panel' ?> — Goviya.lk</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>


<aside class="admin-sidebar d-none d-lg-flex flex-column">
  <div class="brand">
    <div class="brand-text text-white fs-5">🌿 Goviya.lk</div>
    <div class="text-white-50 small mt-1">Admin Panel</div>
  </div>
  <nav class="mt-2 flex-grow-1">
    <a href="index.php"    class="admin-nav-item <?= $currentAdmin==='index.php'    ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="products.php" class="admin-nav-item <?= $currentAdmin==='products.php' ? 'active' : '' ?>"><i class="bi bi-box-seam"></i> Products</a>
    <a href="categories.php" class="admin-nav-item <?= $currentAdmin==='categories.php' ? 'active' : '' ?>"><i class="bi bi-tags"></i> Categories</a>
    <a href="orders.php"   class="admin-nav-item <?= $currentAdmin==='orders.php'   ? 'active' : '' ?>"><i class="bi bi-bag-check"></i> Orders</a>
    <a href="users.php"    class="admin-nav-item <?= $currentAdmin==='users.php'    ? 'active' : '' ?>"><i class="bi bi-people"></i> Users</a>
    <div class="mt-auto border-top border-white border-opacity-10 pt-2 mb-3">
      <a href="<?= SITE_URL ?>/index.php" class="admin-nav-item"><i class="bi bi-house"></i> View Site</a>
      <a href="<?= SITE_URL ?>/pages/logout.php" class="admin-nav-item text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </nav>
</aside>


<nav class="navbar goviya-nav d-lg-none">
  <div class="container-fluid">
    <span class="brand-text text-white">🌿 Goviya Admin</span>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminMobileNav">
      <i class="bi bi-list text-white"></i>
    </button>
    <div class="collapse navbar-collapse" id="adminMobileNav">
      <ul class="navbar-nav mt-2">
        <li class="nav-item"><a class="nav-link text-white" href="index.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="categories.php">Categories</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="orders.php">Orders</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="users.php">Users</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="<?= SITE_URL ?>/pages/logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="admin-content">
  <?php $f = getFlash(); if ($f):
    $cls = $f['type']==='success' ? 'alert-success' : 'alert-danger'; ?>
  <div class="alert <?= $cls ?> alert-dismissible fade show">
    <?= htmlspecialchars($f['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>
