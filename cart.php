<?php
/**
 * cart.php - Halaman keranjang belanja
 * Menampilkan buku yang dipilih, mengubah jumlah, menghapus,
 * menampilkan total harga, dan tombol checkout.
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

$cart = $_SESSION['cart'] ?? [];

// Hitung total harga
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

require_once 'includes/header.php';
?>

<h1 class="mb-4"><i class="bi bi-cart3"></i> Keranjang Belanja</h1>

<?php if (empty($cart)): ?>
    <div class="text-center py-5">
        <i class="bi bi-cart display-1 text-muted"></i>
        <p class="text-muted mt-3">Keranjang belanja Anda kosong.</p>
        <a href="index.php" class="btn btn-primary">
            <i class="bi bi-arrow-left"></i> Lanjutkan Belanja
        </a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle cart-table">
            <thead class="table-light">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Buku</th>
                    <th scope="col">Harga</th>
                    <th scope="col">Jumlah</th>
                    <th scope="col">Subtotal</th>
                    <th scope="col" class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($cart as $bookId => $item): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td>
                            <strong><?= htmlspecialchars($item['title']) ?></strong>
                        </td>
                        <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                        <td>
                            <input type="number" class="form-control qty-input"
                                   data-book-id="<?= $bookId ?>"
                                   value="<?= $item['quantity'] ?>" min="1" style="max-width: 80px;">
                        </td>
                        <td>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-danger remove-from-cart"
                                    data-book-id="<?= $bookId ?>">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="table-active">
                    <td colspan="4" class="fw-bold text-end">Total:</td>
                    <td colspan="2" class="fw-bold fs-5 book-price">
                        Rp <?= number_format($total, 0, ',', '.') ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Lanjutkan Belanja
        </a>
        <a href="checkout.php" class="btn btn-primary btn-lg">
            <i class="bi bi-credit-card"></i> Checkout
        </a>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
