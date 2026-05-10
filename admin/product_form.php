<div class="row g-3">
  <div class="col-sm-8">
    <label class="form-label">Product Name <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" placeholder="e.g. Fresh Tomatoes" required>
  </div>
  <div class="col-sm-4">
    <label class="form-label">Category <span class="text-danger">*</span></label>
    <select name="category_id" class="form-select" required>
      <option value="">Select…</option>
      <?php foreach ($categories as $c): ?>
      <option value="<?= $c['id'] ?>"><?= clean($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-sm-4">
    <label class="form-label">Price (Rs.) <span class="text-danger">*</span></label>
    <input type="number" name="price" class="form-control" step="0.01" min="0" placeholder="0.00" required>
  </div>
  <div class="col-sm-4">
    <label class="form-label">Sale Price <span class="text-muted small">(optional)</span></label>
    <input type="number" name="sale_price" class="form-control" step="0.01" min="0" placeholder="Leave blank if no sale">
  </div>
  <div class="col-sm-4">
    <label class="form-label">Unit <span class="text-danger">*</span></label>
    <select name="unit" class="form-select">
      <option value="kg">kg</option>
      <option value="g">g</option>
      <option value="piece">piece</option>
      <option value="bunch">bunch</option>
      <option value="litre">litre</option>
      <option value="tray">tray</option>
      <option value="5kg">5kg bag</option>
    </select>
  </div>
  <div class="col-sm-4">
    <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
    <input type="number" name="stock" class="form-control" min="0" value="0" required>
  </div>
  <div class="col-12">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="2" placeholder="Short product description…"></textarea>
  </div>
  <div class="col-12">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="is_featured" id="addFeatured">
      <label class="form-check-label" for="addFeatured">⭐ Mark as Featured Product</label>
    </div>
  </div>
</div>
