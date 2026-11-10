<?php
/**
 * cart_add.php - Handler menambahkan buku ke keranjang (session-based)
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$bookId   = (int)($_POST['book_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 1);

if ($quantity < 1) {
    $quantity = 1;
}

// Validasi: pastikan buku ada di database
$stmt = $pdo->prepare("SELECT id, title, price FROM books WHERE id = ?");
$stmt->execute([$bookId]);
$book = $stmt->fetch();

if (!$book) {
    setFlash('message', 'Buku tidak ditemukan.');
    redirect('index.php');
}

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Jika buku sudah di keranjang, tambah quantity
if (isset($_SESSION['cart'][$bookId])) {
    $_SESSION['cart'][$bookId]['quantity'] += $quantity;
} else {
    $_SESSION['cart'][$bookId] = [
        'book_id'  => $book['id'],
        'title'    => $book['title'],
        'price'    => $book['price'],
        'quantity' => $quantity
    ];
}

setFlash('message', 'Buku berhasil ditambahkan ke keranjang!');
redirect('cart.php');
?>
