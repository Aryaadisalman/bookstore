<?php
/**
 * admin/index.php - Dashboard admin
 */

require_once '../config/database.php';
require_once '../includes/auth.php';

// Cek apakah sudah login sebagai admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// Statistik sederhana
$totalBooks    = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

require_once 'includes/header.php';
?>
<?php if (isAdmin()): ?>
    <li class="nav-item">
        <a class="nav-link" href="admin/index.php"><i class="bi bi-speedometer2"></i> Dashboard Admin</a>
    </li>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="mb-3"><i class="bi bi-speedometer2"></i> Dashboard</h1>
        <p class="text-muted">Selamat datang, <strong><?= htmlspecialchars(getUserName()) ?></strong></p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center shadow-sm">
            <div class="card-body">
                <i class="bi bi-book display-4 text-primary mb-2"></i>
                <h2 class="fw-bold"><?= $totalBooks ?></h2>
                <p class="text-muted mb-0">Buku</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center shadow-sm">
            <div class="card-body">
                <i class="bi bi-folder display-4 text-info mb-2"></i>
                <h2 class="fw-bold"><?= $totalCategories ?></h2>
                <p class="text-muted mb-0">Kategori</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center shadow-sm">
            <div class="card-body">
                <i class="bi bi-person-circle display-4 text-success mb-2"></i>
                <h2 class="fw-bold"><?= $totalUsers ?></h2>
                <p class="text-muted mb-0">Pengguna</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center shadow-sm">
            <div class="card-body">
                <i class="bi bi-receipt display-4 text-warning mb-2"></i>
                <h2 class="fw-bold"><?= $totalOrders ?></h2>
                <p class="text-muted mb-0">Pesanan</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightbulb"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="books.php" class="btn btn-outline-primary">Kelola Buku</a>
                <a href="categories.php" class="btn btn-outline-info">Kelola Kategori</a>
                <a href="orders.php" class="btn btn-outline-warning">Lihat Pesanan</a>
                <a href="../logout.php" class="btn btn-outline-secondary">Keluar</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
