<?php
session_start();

require_once __DIR__ . '/../config/db.php';

// Basket quantity changes can come from JS or a normal form post, so keep the request check in one helper.
function is_ajax_request(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function json_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit();
}

// This redirect helper handles both normal page posts and the background JS updates used on the basket page.
function basket_redirect(string $type, string $message): void
{
    if (is_ajax_request()) {
        json_response([
            'ok' => $type === 'success',
            'message' => $message,
        ], $type === 'success' ? 200 : 400);
    }

    $_SESSION[$type] = $message;
    header('Location: /Team23_PixelPals_Term2_Final/public/basket.php');
    exit();
}

if (!isset($_SESSION['user_id'])) {
    basket_redirect('error', 'Please log in to update your basket.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Team23_PixelPals_Term2_Final/public/basket.php');
    exit();
}

$basketItemId = (int) ($_POST['basket_item_id'] ?? 0);
$quantity = (int) ($_POST['quantity'] ?? 1);
$action = $_POST['action'] ?? 'update';

// Refuse obviously invalid requests before touching the database.
if ($basketItemId <= 0) {
    basket_redirect('error', 'That basket item could not be updated.');
}

try {
    // Reload the basket item through the user's basket so one user cannot change another user's row.
    $stmt = $db->prepare(
        'SELECT bi.BasketItemID, bi.Quantity, p.Stock
         FROM basketitem bi
         JOIN basket b ON b.BasketID = bi.BasketID
         JOIN product p ON p.ProductID = bi.ProductID
         WHERE bi.BasketItemID = ? AND b.UserID = ?'
    );
    $stmt->execute([$basketItemId, $_SESSION['user_id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        basket_redirect('error', 'That basket item could not be found.');
    }

    // A remove click and a quantity set to zero both end up deleting the basket row.
    if ($action === 'remove' || $quantity <= 0) {
        $deleteStmt = $db->prepare('DELETE FROM basketitem WHERE BasketItemID = ?');
        $deleteStmt->execute([$basketItemId]);
        basket_redirect('success', 'Item removed from your basket.');
    }

    // Clamp the new quantity between 1 and the current live stock level.
    $newQuantity = min(max(1, $quantity), (int) $item['Stock']);
    $updateStmt = $db->prepare('UPDATE basketitem SET Quantity = ? WHERE BasketItemID = ?');
    $updateStmt->execute([$newQuantity, $basketItemId]);

    // Recalculate totals after the update so the basket page can refresh its summary immediately.
    $totalsStmt = $db->prepare(
        'SELECT
            COALESCE(SUM(bi.Quantity * p.Price), 0) AS Subtotal
         FROM basket b
         JOIN basketitem bi ON bi.BasketID = b.BasketID
         JOIN product p ON p.ProductID = bi.ProductID
         WHERE b.UserID = ?'
    );
    $totalsStmt->execute([$_SESSION['user_id']]);
    $subtotal = (float) $totalsStmt->fetchColumn();
    $delivery = $subtotal >= 100 || $subtotal === 0.0 ? 0.0 : 4.99;

    if (is_ajax_request()) {
        json_response([
            'ok' => true,
            'message' => 'Basket updated.',
            'quantity' => $newQuantity,
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'delivery' => number_format($delivery, 2, '.', ''),
            'total' => number_format($subtotal + $delivery, 2, '.', ''),
        ]);
    }

    basket_redirect('success', 'Basket updated.');
} catch (PDOException $e) {
    basket_redirect('error', 'Could not update your basket. Please try again.');
}
