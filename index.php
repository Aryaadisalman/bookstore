<?php
/**
 * index.php - Halaman utama (Home)
 * Menampilkan daftar buku, fitur search berdasarkan judul,
 * dan filter berdasarkan kategori.
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

// --- Ambil parameter pencarian & filter ---
$search   = isset($_GET['search'])   ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// --- Build query dengan prepared statements ---
$sql = "SELECT b.id, b.title, b.author, b.price, b.image, b.description, c.name AS category_name
        FROM books b
        LEFT JOIN categories c ON b.category_id = c.id
        WHERE 1=1";

$params = [];

if ($search !== '') {
    $sql .= " AND b.title LIKE :search";
    $params[':search'] = "%$search%";
}

if ($category > 0) {
    $sql .= " AND b.category_id = :category";
    $params[':category'] = $category;
}

$sql .= " ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<!-- Search & Category Filter -->
<div class="row mb-4">
    <div class="col-12">
            <form method="GET" action="index.php" class="d-flex gap-2">
                <input type="text" name="search" class="form-control"
                       placeholder="Cari buku..." value="<?= htmlspecialchars($search) ?>">
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="0">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($category == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-outline-light">
                    <i class="bi bi-search"></i> Cari
                </button>
            </form>
    </div>
</div>

<!-- Flash Message -->
<?php
$flash = getFlash('message');
if ($flash): ?>
    <div class="alert alert-success alert-dismissible fade show flash-message" role="alert">
        <?= $flash ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Book List -->
<?php if (count($books) > 0): ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($books as $book): ?>
            <div class="col">
                <div class="card book-card h-100">
                    <img src="assets/images/<?= $book['image'] ?: 'no-image.jpg' ?>"
                         class="book-img card-img-top" alt="<?= htmlspecialchars($book['title']) ?>">
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title"><?= htmlspecialchars($book['title']) ?></h6>
                        <p class="book-meta mb-1">
                            <i class="bi bi-person"></i> <?= htmlspecialchars($book['author']) ?>
                        </p>
                        <p class="book-meta mb-2">
                            <i class="bi bi-folder"></i> <?= htmlspecialchars($book['category_name']) ?>
                        </p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="book-price">Rp <?= number_format($book['price'], 0, ',', '.') ?></span>
                            <a href="detail.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="text-center py-5">
        <i class="bi bi-book display-1 text-muted"></i>
        <p class="text-muted mt-3">Tidak ada buku ditemukan.</p>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
