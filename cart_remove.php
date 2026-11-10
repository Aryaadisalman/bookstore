<?php
/**
 * cart_remove.php - Handler menghapus buku dari keranjang
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

$bookId = (int)($_POST['book_id'] ?? 0);

if (isset($_SESSION['cart'][$bookId])) {
    unset($_SESSION['cart'][$bookId]);

    // Hitung ulang total
    $total = 0;
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
        $count += $item['quantity'];
    }

    echo json_encode([
        'success'   => true,
        'message'   => 'Buku dihapus dari keranjang.',
        'total'     => number_format($total, 0, ',', '.'),
        'cartCount' => $count
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Buku tidak ditemukan di keranjang.'
    ]);
}

exit;
?>
