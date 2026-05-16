<?php
$pageTitle = 'Products';
require_once __DIR__ . '/admin_header.php';
$db = getDB();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        
        $row = $db->prepare("SELECT image FROM products WHERE id=?");
        $row->execute([$id]);
        $old = $row->fetchColumn();
        if ($old && file_exists(__DIR__ . '/../assets/images/products/' . $old)) {
            unlink(__DIR__ . '/../assets/images/products/' . $old);
        }
        $db->prepare("UPDATE products SET is_active=0 WHERE id=?")->execute([$id]);
        setFlash('success', 'Product removed.');
        header('Location: products.php'); exit;
    }

    if (in_array($action, ['add', 'edit'])) {
        $name      = clean($_POST['name']        ?? '');
        $catId     = (int)($_POST['category_id'] ?? 0);
        $price     = (float)($_POST['price']     ?? 0);
        $salePrice = (($_POST['sale_price'] ?? '') !== '') ? (float)$_POST['sale_price'] : null;
        $unit      = clean($_POST['unit']        ?? 'kg');
        $stock     = (int)($_POST['stock']       ?? 0);
        $desc      = clean($_POST['description'] ?? '');
        $featured  = isset($_POST['is_featured']) ? 1 : 0;

        
        $imageName = $_POST['existing_image'] ?? null; 

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file     = $_FILES['image'];
            $allowed  = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            $maxSize  = 2 * 1024 * 1024; // 2MB

            
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowed)) {
                setFlash('error', 'Image must be JPG, PNG or WebP.');
                header('Location: products.php'); exit;
            }
            if ($file['size'] > $maxSize) {
                setFlash('error', 'Image must be under 2MB.');
                header('Location: products.php'); exit;
            }

            
            if ($action === 'edit' && $imageName && file_exists(__DIR__ . '/../assets/images/products/' . $imageName)) {
                unlink(__DIR__ . '/../assets/images/products/' . $imageName);
            }

          
            $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
            $imageName = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
            $dest      = __DIR__ . '/../assets/images/products/' . $imageName;

            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                setFlash('error', 'Failed to save image. Check folder permissions.');
                header('Location: products.php'); exit;
            }
        }

        if ($action === 'add') {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name)) . '-' . time();
            $db->prepare(
                "INSERT INTO products (category_id, name, slug, description, price, sale_price, unit, stock, image, is_featured)
                 VALUES (?,?,?,?,?,?,?,?,?,?)"
            )->execute([$catId, $name, $slug, $desc, $price, $salePrice, $unit, $stock, $imageName, $featured]);
            setFlash('success', 'Product added!');
        } else {
            $id = (int)$_POST['id'];
            $db->prepare(
                "UPDATE products SET category_id=?, name=?, description=?, price=?, sale_price=?,
                 unit=?, stock=?, image=?, is_featured=? WHERE id=?"
            )->execute([$catId, $name, $desc, $price, $salePrice, $unit, $stock, $imageName, $featured, $id]);
            setFlash('success', 'Product updated!');
        }
        header('Location: products.php'); exit;
    }
}

$products   = $db->query(
    "SELECT p.*, c.name AS cat_name FROM products p
     JOIN categories c ON p.category_id = c.id
     WHERE p.is_active=1 ORDER BY p.created_at DESC"
)->fetchAll();
$categories = $db->query("SELECT * FROM categories WHERE is_active=1")->fetchAll();
$catIcons   = ['vegetables'=>'🥦','fruits'=>'🍎','rice-grains'=>'🌾','dairy-eggs'=>'🥚','herbs-spices'=>'🌿'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-700 mb-0">🥦 Products</h4>
  <button class="btn btn-green" data-bs-toggle="modal" data-bs-target="#addModal">
    <i class="bi bi-plus-lg me-1"></i> Add Product
  </button>
</div>

<div class="bg-white rounded-4 shadow-sm overflow-hidden">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-4" style="width:60px">Image</th>
          <th>Product</th>
          <th>Category</th>
          <th>Price</th>
          <th>Sale</th>
          <th>Stock</th>
          <th>⭐</th>
          <th class="pe-4">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($products as $p):
        $emoji = $catIcons[$p['cat_name']] ?? '🛒';
      ?>
      <tr>
        <td class="ps-4">
          <?php if ($p['image'] && file_exists(__DIR__ . '/../assets/images/products/' . $p['image'])): ?>
          <img src="<?= SITE_URL ?>/assets/images/products/<?= htmlspecialchars($p['image']) ?>"
               style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid #eee"
               alt="<?= clean($p['name']) ?>">
          <?php else: ?>
          <div style="width:48px;height:48px;background:#f0f7f0;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.5rem">
            <?= $emoji ?>
          </div>
          <?php endif; ?>
        </td>
        <td>
          <div class="fw-600"><?= clean($p['name']) ?></div>
          <div class="text-muted small">per <?= clean($p['unit']) ?></div>
        </td>
        <td><span class="badge bg-light text-dark"><?= clean($p['cat_name']) ?></span></td>
        <td>Rs. <?= number_format($p['price'], 2) ?></td>
        <td><?= $p['sale_price'] ? '<span class="text-danger">Rs. ' . number_format($p['sale_price'], 2) . '</span>' : '<span class="text-muted">—</span>' ?></td>
        <td>
          <span class="badge <?= $p['stock'] == 0 ? 'bg-danger' : ($p['stock'] < 10 ? 'bg-warning text-dark' : 'bg-success') ?>">
            <?= $p['stock'] ?>
          </span>
        </td>
        <td><?= $p['is_featured'] ? '⭐' : '—' ?></td>
        <td class="pe-4">
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-primary"
                    onclick="editProduct(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)"
                    title="Edit">
              <i class="bi bi-pencil"></i>
            </button>
            <form method="POST" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger"
                      data-confirm="Delete '<?= clean($p['name']) ?>'?" title="Delete">
                <i class="bi bi-trash3"></i>
              </button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if (empty($products)): ?>
  <div class="text-center py-5 text-muted">No products yet. Add your first product!</div>
  <?php endif; ?>
</div>


<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-700">➕ Add New Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <div class="modal-body">

          <div class="row g-3">
            <div class="col-sm-8">
              <label class="form-label fw-600">Product Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" placeholder="e.g. Fresh Tomatoes" required>
            </div>
            <div class="col-sm-4">
              <label class="form-label fw-600">Category <span class="text-danger">*</span></label>
              <select name="category_id" class="form-select" required>
                <option value="">Select…</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= clean($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-sm-4">
              <label class="form-label fw-600">Price (Rs.) <span class="text-danger">*</span></label>
              <input type="number" name="price" class="form-control" step="0.01" min="0" placeholder="0.00" required>
            </div>
            <div class="col-sm-4">
              <label class="form-label fw-600">Sale Price <span class="text-muted fw-400">(optional)</span></label>
              <input type="number" name="sale_price" class="form-control" step="0.01" min="0" placeholder="Leave blank if none">
            </div>
            <div class="col-sm-2">
              <label class="form-label fw-600">Unit</label>
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
            <div class="col-sm-2">
              <label class="form-label fw-600">Stock</label>
              <input type="number" name="stock" class="form-control" min="0" value="0" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-600">Description</label>
              <textarea name="description" class="form-control" rows="2" placeholder="Short product description…"></textarea>
            </div>

            
            <div class="col-12">
              <label class="form-label fw-600">Product Image</label>
              <div class="upload-area" id="addUploadArea" onclick="document.getElementById('addImageInput').click()">
                <div class="upload-placeholder" id="addUploadPlaceholder">
                  <div style="font-size:2.5rem">📷</div>
                  <div class="fw-600 mt-2">Click to upload image</div>
                  <div class="text-muted small">JPG, PNG or WebP — max 2MB</div>
                </div>
                <img id="addImagePreview" src="" alt="" style="display:none;max-height:160px;border-radius:8px;object-fit:cover">
              </div>
              <input type="file" id="addImageInput" name="image" accept="image/jpeg,image/png,image/webp"
                     style="display:none" onchange="previewImage(this,'addImagePreview','addUploadPlaceholder')">
            </div>

            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_featured" id="addFeatured">
                <label class="form-check-label" for="addFeatured">⭐ Mark as Featured Product (shown on homepage)</label>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-green px-4">Add Product</button>
        </div>
      </form>
    </div>
  </div>
</div>


<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-700">✏️ Edit Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data" id="editForm">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="editId">
        <input type="hidden" name="existing_image" id="editExistingImage">
        <div class="modal-body" id="editModalBody"></div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-green px-4">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>


<style>
.upload-area {
  border: 2px dashed #74c69d;
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  cursor: pointer;
  transition: all .2s;
  background: #f8fdf8;
  min-height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.upload-area:hover { border-color: #2d6a4f; background: #f0f7f0; }
.upload-area img { max-width: 100%; }
</style>

<script>
const categories = <?= json_encode($categories) ?>;
const siteUrl    = '<?= SITE_URL ?>';


function previewImage(input, previewId, placeholderId) {
    const file = input.files[0];
    if (!file) return;

    
    if (file.size > 2 * 1024 * 1024) {
        alert('Image must be under 2MB');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);
        if (preview) { preview.src = e.target.result; preview.style.display = 'block'; }
        if (placeholder) placeholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
}


function editProduct(p) {
    document.getElementById('editId').value = p.id;
    document.getElementById('editExistingImage').value = p.image || '';

    const catOptions = categories.map(c =>
        `<option value="${c.id}" ${c.id == p.category_id ? 'selected' : ''}>${c.name}</option>`
    ).join('');

    const units = ['kg','g','piece','bunch','litre','tray','5kg'];
    const unitOptions = units.map(u => `<option value="${u}" ${p.unit===u?'selected':''}>${u}</option>`).join('');

    const currentImage = p.image
        ? `<img src="${siteUrl}/assets/images/products/${p.image}"
               style="max-height:100px;border-radius:8px;object-fit:cover;margin-bottom:8px;display:block"
               id="editImagePreview" alt="Current image">
           <div class="text-muted small mb-2">Current image — upload a new one to replace it</div>`
        : `<img id="editImagePreview" src="" style="display:none;max-height:100px;border-radius:8px">
           <div class="upload-placeholder" id="editUploadPlaceholder">
             <div style="font-size:2rem">📷</div>
             <div class="fw-600 mt-1">Click to upload image</div>
             <div class="text-muted small">JPG, PNG or WebP — max 2MB</div>
           </div>`;

    document.getElementById('editModalBody').innerHTML = `
      <div class="row g-3">
        <div class="col-sm-8">
          <label class="form-label fw-600">Product Name</label>
          <input type="text" name="name" class="form-control" value="${p.name.replace(/"/g,'&quot;')}" required>
        </div>
        <div class="col-sm-4">
          <label class="form-label fw-600">Category</label>
          <select name="category_id" class="form-select" required>${catOptions}</select>
        </div>
        <div class="col-sm-4">
          <label class="form-label fw-600">Price (Rs.)</label>
          <input type="number" name="price" class="form-control" value="${p.price}" step="0.01" min="0" required>
        </div>
        <div class="col-sm-4">
          <label class="form-label fw-600">Sale Price</label>
          <input type="number" name="sale_price" class="form-control" value="${p.sale_price||''}" step="0.01" min="0" placeholder="Leave blank if none">
        </div>
        <div class="col-sm-2">
          <label class="form-label fw-600">Unit</label>
          <select name="unit" class="form-select">${unitOptions}</select>
        </div>
        <div class="col-sm-2">
          <label class="form-label fw-600">Stock</label>
          <input type="number" name="stock" class="form-control" value="${p.stock}" min="0" required>
        </div>
        <div class="col-12">
          <label class="form-label fw-600">Description</label>
          <textarea name="description" class="form-control" rows="2">${p.description||''}</textarea>
        </div>
        <div class="col-12">
          <label class="form-label fw-600">Product Image</label>
          <div class="upload-area" onclick="document.getElementById('editImageInput').click()">
            ${currentImage}
          </div>
          <input type="file" id="editImageInput" name="image" accept="image/jpeg,image/png,image/webp"
                 style="display:none"
                 onchange="previewImage(this,'editImagePreview','editUploadPlaceholder')">
          <div class="form-text">Leave empty to keep current image</div>
        </div>
        <div class="col-12">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_featured" id="editFeatured" ${p.is_featured==1?'checked':''}>
            <label class="form-check-label" for="editFeatured">⭐ Mark as Featured Product</label>
          </div>
        </div>
      </div>
    `;

    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>

<?php include __DIR__ . '/admin_footer.php'; ?>
