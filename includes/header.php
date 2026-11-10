<?php
/**
 * includes/header.php
 * Header HTML yang dipakai di semua halaman utama (root level).
 * Pastikan file ini di-include SETELAH config/database.php dan includes/auth.php
 */

// Load categories untuk dropdown navbar
$categories = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    // ignore
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookstore</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Bookstore</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button"
                            data-bs-toggle="dropdown">Kategori</a>
                        <ul class="dropdown-menu">
                            <?php foreach ($categories as $cat): ?>
                                <li><a class="dropdown-item"
                                        href="index.php?category=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#contactAdminModal">
                            <i class="bi bi-headset"></i> Hubungi Admin
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="bi bi-cart3"></i> Keranjang
                            <?php
                            $cartCount = 0;
                            if (isset($_SESSION['cart'])) {
                                foreach ($_SESSION['cart'] as $item) {
                                    $cartCount += $item['quantity'];
                                }
                            }
                            if ($cartCount > 0): ?>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $cartCount ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/index.php">
                                <i class="bi bi-speedometer2"></i> Dashboard Admin
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <?= htmlspecialchars(getUserName()) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="admin/index.php"><i class="bi bi-speedometer2"></i>
                                            Dashboard Admin</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="logout.php">Keluar</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">Daftar</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Modal Hubungi Admin -->
    <div class="modal fade" id="contactAdminModal" tabindex="-1" aria-labelledby="contactAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="contactAdminModalLabel">
                        <i class="bi bi-headset"></i> Hubungi Admin
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Ada pertanyaan seputar pesanan atau pembayaran? Silakan hubungi admin kami
                        melalui salah satu kontak berikut:</p>
                    <div class="list-group">
                        <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Bookstore%2C%20saya%20ingin%20bertanya"
                            target="_blank" rel="noopener" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                            <i class="bi bi-whatsapp text-success fs-4"></i>
                            <div>
                                <div class="fw-bold">WhatsApp</div>
                                <div class="text-muted small">+62 812-3456-7890</div>
                            </div>
                        </a>
                        <a href="mailto:admin@bookstore.com"
                            class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                            <i class="bi bi-envelope-fill text-primary fs-4"></i>
                            <div>
                                <div class="fw-bold">Email</div>
                                <div class="text-muted small">admin@bookstore.com</div>
                            </div>
                        </a>
                        <div class="list-group-item d-flex align-items-center gap-3">
                            <i class="bi bi-telephone-fill text-secondary fs-4"></i>
                            <div>
                                <div class="fw-bold">Telepon</div>
                                <div class="text-muted small">(021) 123-4567</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Floating WhatsApp -->
    <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Bookstore%2C%20saya%20ingin%20bertanya" target="_blank"
        rel="noopener"
        class="position-fixed d-flex align-items-center justify-content-center text-white bg-success rounded-circle shadow"
        style="bottom: 24px; right: 24px; width: 56px; height: 56px; z-index: 1050; font-size: 28px;"
        title="Hubungi Admin via WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>

    <main class="container my-4">