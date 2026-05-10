<?php
$pageTitle = 'Categories';
require_once __DIR__ . '/admin_header.php';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = clean($_POST['name'] ?? '');
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
        $desc = clean($_POST['description'] ?? '');
        if ($name) {
            $db->prepare("INSERT INTO categories (name, slug, description) VALUES (?,?,?)")
               ->execute([$name, $slug, $desc]);
            setFlash('success', 'Category added!');
        }
    }
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $db->prepare("UPDATE categories SET is_active=0 WHERE id=?")->execute([$id]);
        setFlash('success', 'Category removed.');
    }
    header('Location: categories.php'); exit;
}

$categories = $db->query(
    "SELECT c.*, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id AND p.is_active=1
     WHERE c.is_active=1
     GROUP BY c.id ORDER BY c.id"
)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-700 mb-0">🏷️ Categories</h4>
  <button class="btn btn-green" data-bs-toggle="modal" data-bs-target="#addCatModal">
    <i class="bi bi-plus-lg me-1"></i> Add Category
  </button>
</div>

<div class="bg-white rounded-4 shadow-sm overflow-hidden">
  <table class="table table-hover align-middle mb-0">
    <thead class="table-light">
      <tr>
        <th class="ps-4">Name</th>
        <th>Slug</th>
        <th>Description</th>
        <th>Products</th>
        <th class="pe-4">Action</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($categories as $c): ?>
    <tr>
      <td class="ps-4 fw-600"><?= clean($c['name']) ?></td>
      <td><code><?= clean($c['slug']) ?></code></td>
      <td class="text-muted small"><?= clean($c['description']) ?></td>
      <td><span class="badge bg-light text-dark"><?= $c['product_count'] ?></span></td>
      <td class="pe-4">
        <form method="POST" class="d-inline">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $c['id'] ?>">
          <button type="submit" class="btn btn-sm btn-outline-danger"
                  data-confirm="Delete category '<?= clean($c['name']) ?>'? Products will be unlinked.">
            <i class="bi bi-trash3"></i>
          </button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ADD MODAL -->
<div class="modal fade" id="addCatModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-700">Add Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Category Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Dairy & Eggs" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-green">Add Category</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>
