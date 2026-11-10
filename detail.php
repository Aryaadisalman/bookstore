<?php
/**
 * detail.php - Halaman detail buku
 * Menampilkan cover, judul, penulis, kategori, deskripsi, harga,
 * dan tombol "Tambah ke Keranjang".
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

// Ambil ID buku dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect('index.php');
}

// Query buku berdasarkan ID (prepared statement)
$stmt = $pdo->prepare("SELECT b.*, c.name AS category_name
                       FROM books b
                       LEFT JOIN categories c ON b.category_id = c.id
                       WHERE b.id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    setFlash('message', 'Buku tidak ditemukan.');
    redirect('index.php');
}

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <img src="assets/images/<?= $book['image'] ?: 'no-image.jpg' ?>"
             class="img-fluid rounded shadow-sm"
             alt="<?= htmlspecialchars($book['title']) ?>">
    </div>
    <div class="col-md-8">
        <h1 class="mb-3"><?= htmlspecialchars($book['title']) ?></h1>

        <p class="text-muted">
            <i class="bi bi-person"></i> Penulis: <strong><?= htmlspecialchars($book['author']) ?></strong>
        </p>
        <p class="text-muted">
            <i class="bi bi-folder"></i> Kategori: <strong><?= htmlspecialchars($book['category_name']) ?></strong>
        </p>

        <h3 class="book-price my-4">Rp <?= number_format($book['price'], 0, ',', '.') ?></h3>

        <div class="my-4">
            <h5>Deskripsi</h5>
            <p><?= nl2br(htmlspecialchars($book['description'])) ?></p>
        </div>

        <!-- Tambah ke Keranjang -->
        <form action="cart_add.php" method="POST" class="d-flex align-items-center gap-3">
            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
            <input type="number" name="quantity" value="1" min="1" class="form-control" style="max-width: 80px;">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
            </button>
        </form>

        <?php if (!isLoggedIn()): ?>
            <div class="alert alert-info mt-3 mb-0">
                <a href="login.php" class="alert-link fw-bold">Login</a> untuk proses checkout yang lebih cepat.
            </div>
        <?php endif; ?>

        <a href="index.php" class="btn btn-outline-secondary mt-3">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
