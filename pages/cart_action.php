<?php

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!hash_equals(csrf_token(), $token)) {
    echo json_encode(['success' => false, 'message' => 'Security check failed. Refresh the page and try again.']);
    exit;
}

$action    = $_POST['action']     ?? '';
$productId = (int)($_POST['product_id'] ?? 0);
$quantity  = max(1, (int)($_POST['quantity'] ?? 1));
$userId    = (int)$_SESSION['user_id'];
$db        = getDB();

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

function getCartCount(PDO $db, int $userId): int {
    $stmt = $db->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

switch ($action) {

    case 'add':
        $stmt = $db->prepare("SELECT id, stock FROM products WHERE id = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit;
        }
        if ((int)$product['stock'] < 1) {
            echo json_encode(['success' => false, 'message' => 'Sorry, this product is out of stock.']);
            exit;
        }

        $existing = $db->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $existing->execute([$userId, $productId]);
        $row = $existing->fetch();

        if ($row) {
            $newQty = (int)$row['quantity'] + 1;
            if ($newQty > (int)$product['stock']) {
                echo json_encode(['success' => false, 'message' => 'Cannot exceed available stock (' . $product['stock'] . ').']);
                exit;
            }
            $db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?")
               ->execute([$newQty, $userId, $productId]);
        } else {
            $db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)")
               ->execute([$userId, $productId]);
        }

        echo json_encode([
            'success'   => true,
            'message'   => 'Added to cart!',
            'cartCount' => getCartCount($db, $userId)
        ]);
        break;

    case 'update':
        $stockStmt = $db->prepare("SELECT stock FROM products WHERE id = ? AND is_active = 1");
        $stockStmt->execute([$productId]);
        $maxStock = (int)$stockStmt->fetchColumn();
        $quantity = min(max(1, $quantity), $maxStock);

        $db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?")
           ->execute([$quantity, $userId, $productId]);

        echo json_encode(['success' => true, 'cartCount' => getCartCount($db, $userId)]);
        break;

    case 'remove':
        $db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?")
           ->execute([$userId, $productId]);

        echo json_encode(['success' => true, 'cartCount' => getCartCount($db, $userId)]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
