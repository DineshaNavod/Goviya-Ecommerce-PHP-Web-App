</main>


<footer class="site-footer mt-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="footer-brand mb-3">
          <span class="brand-leaf">🌿</span>
          <span class="brand-text text-white">Goviya<span class="brand-dot">.lk</span></span>
        </div>
        <p class="text-muted small" style="color:white;">Sri Lanka's freshest farm-to-table marketplace. We connect local farmers with urban households for a healthier, greener tomorrow.</p>
        <div class="social-links mt-3">
          <a href="#"><i class="bi bi-facebook"></i></a>
          <a href="#"><i class="bi bi-instagram"></i></a>
          <a href="#"><i class="bi bi-whatsapp"></i></a>
          <a href="#"><i class="bi bi-youtube"></i></a>
        </div>
      </div>
      <div class="col-sm-6 col-lg-2">
        <h6 class="footer-heading">Shop</h6>
        <ul class="footer-links">
          <li><a href="<?= SITE_URL ?>/pages/products.php?category=vegetables">Vegetables</a></li>
          <li><a href="<?= SITE_URL ?>/pages/products.php?category=fruits">Fruits</a></li>
          <li><a href="<?= SITE_URL ?>/pages/products.php?category=rice-grains">Rice & Grains</a></li>
          <li><a href="<?= SITE_URL ?>/pages/products.php?category=dairy-eggs">Dairy & Eggs</a></li>
          <li><a href="<?= SITE_URL ?>/pages/products.php?category=herbs-spices">Herbs & Spices</a></li>
        </ul>
      </div>
      <div class="col-sm-6 col-lg-2">
        <h6 class="footer-heading">Account</h6>
        <ul class="footer-links">
          <li><a href="<?= SITE_URL ?>/pages/register.php">Register</a></li>
          <li><a href="<?= SITE_URL ?>/pages/login.php">Login</a></li>
          <li><a href="<?= SITE_URL ?>/pages/profile.php">My Profile</a></li>
          <li><a href="<?= SITE_URL ?>/pages/orders.php">My Orders</a></li>
        </ul>
      </div>
      <div class="col-lg-4">
        <h6 class="footer-heading">Contact Us</h6>
        <ul class="footer-links">
          <li><i class="bi bi-geo-alt me-2 text-accent"></i>No 12, Galle Road, Colombo 03</li>
          <li><i class="bi bi-telephone me-2 text-accent"></i>011-234-5678</li>
          <li><i class="bi bi-envelope me-2 text-accent"></i>hello@goviya.lk</li>
          <li><i class="bi bi-clock me-2 text-accent"></i>Mon–Sat: 6AM – 9PM</li>
        </ul>
      </div>
    </div>

    <hr class="footer-divider">
    <div class="d-flex flex-wrap justify-content-between align-items-center small text-muted py-2">
      <span>&copy; <?= date('Y') ?> Goviya.lk. All rights reserved.</span>
      <span class="d-flex gap-3">
        <a href="#" class="text-muted text-decoration-none">Privacy Policy</a>
        <a href="#" class="text-muted text-decoration-none">Terms of Service</a>
      </span>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/main.js?v=5"></script>
</body>
</html>
