<?php
/**
 * checkout.php - Halaman checkout
 * Form: nama, email, alamat, nomor telepon.
 * Menampilkan ringkasan pesanan.
 * Tombol "Buat Pesanan" yang menyimpan data pesanan ke MySQL.
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

$cart = $_SESSION['cart'] ?? [];

// Jika keranjang kosong, kembalikan ke cart
if (empty($cart)) {
    setFlash('message', 'Keranjang belanja Anda kosong.');
    redirect('cart.php');
}

// Hitung total
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

$errors = [];

// Jika user sudah login, ambil data otomatis
$defaultName    = getUserName() ?? '';
$defaultEmail   = '';
$defaultAddress = '';
$defaultPhone   = '';

if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmt->execute([getUserId()]);
    $user = $stmt->fetch();
    if ($user) {
        $defaultName  = $user['name'];
        $defaultEmail = $user['email'];
    }
}

// Proses checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');

    // Validasi
    if (empty($name)) {
        $errors[] = 'Nama lengkap wajib diisi.';
    }
    if (empty($email)) {
        $errors[] = 'Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (empty($address)) {
        $errors[] = 'Alamat wajib diisi.';
    }
    if (empty($phone)) {
        $errors[] = 'Nomor telepon wajib diisi.';
    }

    // Jika tidak ada error, simpan pesanan ke database
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Simpan order ke tabel orders
            $stmt = $pdo->prepare("INSERT INTO orders
                (user_id, name, email, address, phone, total_amount)
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                isLoggedIn() ? getUserId() : null,
                $name,
                $email,
                $address,
                $phone,
                $total
            ]);
            $orderId = $pdo->lastInsertId();

            // Simpan order_details
            $stmt = $pdo->prepare("INSERT INTO order_details
                (order_id, book_id, quantity, price)
                VALUES (?, ?, ?, ?)");

            foreach ($cart as $bookId => $item) {
                $stmt->execute([
                    $orderId,
                    $bookId,
                    $item['quantity'],
                    $item['price']
                ]);
            }

            $pdo->commit();

            // Kosongkan keranjang
            unset($_SESSION['cart']);

            setFlash('message', 'Pesanan Anda berhasil dibuat! Nomor pesanan: ' . $orderId);
            redirect('index.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Terjadi kesalahan saat menyimpan pesanan. Silakan coba lagi.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-credit-card"></i> Form Checkout</h4>
            </div>
            <div class="card-body">

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="checkout.php">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= htmlspecialchars($defaultName) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($defaultEmail) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea name="address" class="form-control" rows="3"
                                  placeholder="Masukkan alamat lengkap..." required><?= htmlspecialchars($defaultAddress) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="tel" name="phone" class="form-control"
                               placeholder="08xx-xxxx-xxxx" value="<?= htmlspecialchars($defaultPhone) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-check-circle"></i> Buat Pesanan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm order-summary">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-receipt"></i> Ringkasan Pesanan</h4>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php foreach ($cart as $item): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= htmlspecialchars($item['title']) ?> (x<?= $item['quantity'] ?>)</span>
                            <span>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></span>
                        </li>
                    <?php endforeach; ?>
                    <li class="list-group-item d-flex justify-content-between fw-bold mt-2">
                        <span>Total:</span>
                        <span class="book-price fs-5">Rp <?= number_format($total, 0, ',', '.') ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
