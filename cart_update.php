<?php
/**
 * cart_update.php - Handler mengubah jumlah buku di keranjang
 * Menerima request AJAX atau redirect biasa.
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

$bookId   = (int)($_POST['book_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 1);

if ($quantity < 1) {
    $quantity = 1;
}

if (isset($_SESSION['cart'][$bookId])) {
    $_SESSION['cart'][$bookId]['quantity'] = $quantity;

    // Hitung ulang total
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Keranjang diperbarui.',
        'total'   => number_format($total, 0, ',', '.')
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Buku tidak ditemukan di keranjang.'
    ]);
}

exit;
?>
