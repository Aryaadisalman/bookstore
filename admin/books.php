<?php
/**
 * admin/books.php - Kelola buku (CRUD: Tambah, Edit, Hapus)
 */

require_once '../config/database.php';
require_once '../includes/auth.php';

// Cek admin login
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// --- Ambil semua kategori untuk dropdown ---
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// --- Inisialisasi variabel ---
$action = $_GET['action'] ?? 'list';
$errors = [];
$success = '';

// --- Handle Form Submit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submit = $_POST['submit'] ?? '';

    // --- Tambah Buku ---
    if ($submit === 'add') {
        $title       = trim($_POST['title'] ?? '');
        $author      = trim($_POST['author'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $price       = (float)($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $image       = 'no-image.jpg';

        // Validasi
        if (empty($title)) $errors[] = 'Judul buku wajib diisi.';
        if (empty($author)) $errors[] = 'Penulis wajib diisi.';
        if ($price <= 0) $errors[] = 'Harga tidak valid.';
        if (empty($description)) $errors[] = 'Deskripsi wajib diisi.';

        // Upload gambar (opsional)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $image = uniqid('book_', true) . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], '../assets/images/' . $image);
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO books (title, author, category_id, price, description, image)
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $author, $category_id, $price, $description, $image]);
            $success = 'Buku berhasil ditambahkan!';
        }
    }

    // --- Edit Buku ---
    if ($submit === 'edit') {
        $id          = (int)$_POST['id'];
        $title       = trim($_POST['title'] ?? '');
        $author      = trim($_POST['author'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $price       = (float)($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $image       = $_POST['existing_image'] ?? 'no-image.jpg';

        // Validasi
        if (empty($title)) $errors[] = 'Judul buku wajib diisi.';
        if (empty($author)) $errors[] = 'Penulis wajib diisi.';
        if ($price <= 0) $errors[] = 'Harga tidak valid.';
        if (empty($description)) $errors[] = 'Deskripsi wajib diisi.';

        // Upload gambar baru (opsional)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $image = uniqid('book_', true) . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], '../assets/images/' . $image);
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE books SET title=?, author=?, category_id=?, price=?, description=?, image=?
                                   WHERE id=?");
            $stmt->execute([$title, $author, $category_id, $price, $description, $image, $id]);
            $success = 'Buku berhasil diperbarui!';
        }
    }
}

// --- Handle Delete ---
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM books WHERE id = ?")->execute([$deleteId]);
    $success = 'Buku berhasil dihapus!';
}

// --- Ambil semua buku ---
$stmt = $pdo->query("SELECT b.*, c.name AS category_name FROM books b
                     LEFT JOIN categories c ON b.category_id = c.id
                     ORDER BY b.created_at DESC");
$books = $stmt->fetchAll();

// --- Ambil buku untuk edit ---
$editBook = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $editId = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$editId]);
    $editBook = $stmt->fetch();
    if (!$editBook) {
        $action = 'list';
    }
}

require_once 'includes/header.php';
?>

<h1 class="mb-4"><i class="bi bi-book"></i> Kelola Buku</h1>

<!-- Flash / Success Message -->
<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
                <li><?= $e ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Form Tambah / Edit -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-<?= $action === 'edit' ? 'pencil' : 'plus' ?>"></i>
            <?= $action === 'edit' ? 'Edit Buku' : 'Tambah Buku Baru' ?>
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" action="books.php" enctype="multipart/form-data">
            <?php if ($action === 'edit'): ?>
                <input type="hidden" name="id" value="<?= $editBook['id'] ?>">
                <input type="hidden" name="submit" value="edit">
                <input type="hidden" name="existing_image" value="<?= $editBook['image'] ?>">
            <?php else: ?>
                <input type="hidden" name="submit" value="add">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Judul *</label>
                    <input type="text" name="title" class="form-control"
                           value="<?= $action === 'edit' ? htmlspecialchars($editBook['title']) : '' ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Penulis *</label>
                    <input type="text" name="author" class="form-control"
                           value="<?= $action === 'edit' ? htmlspecialchars($editBook['author']) : '' ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select">
                        <option value="0">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= ($action === 'edit' && $editBook['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Harga *</label>
                    <input type="number" name="price" class="form-control" min="0" step="1000"
                           value="<?= $action === 'edit' ? $editBook['price'] : '' ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Gambar Sampul</label>
                    <input type="file" name="image" class="form-control">
                    <?php if ($action === 'edit' && $editBook['image']): ?>
                        <div class="mt-2">
                            <img src="../assets/images/<?= $editBook['image'] ?>" alt="Current Image"
                                 style="max-height: 80px;">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi *</label>
                    <textarea name="description" class="form-control" rows="4" required><?=
                        $action === 'edit' ? htmlspecialchars($editBook['description']) : ''
                    ?></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">
                <i class="bi bi-save"></i> <?= $action === 'edit' ? 'Perbarui' : 'Simpan' ?>
            </button>
            <?php if ($action === 'edit'): ?>
                <a href="books.php" class="btn btn-outline-secondary mt-3">
                    <i class="bi bi-x"></i> Batal
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Daftar Buku -->
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Gambar</th>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($books) > 0): ?>
                <?php $i = 1; foreach ($books as $b): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td>
                            <img src="../assets/images/<?= $b['image'] ?: 'no-image.jpg' ?>"
                                 alt="<?= htmlspecialchars($b['title']) ?>"
                                 style="max-height: 60px;">
                        </td>
                        <td><?= htmlspecialchars($b['title']) ?></td>
                        <td><?= htmlspecialchars($b['author']) ?></td>
                        <td><?= htmlspecialchars($b['category_name']) ?></td>
                        <td>Rp <?= number_format($b['price'], 0, ',', '.') ?></td>
                        <td class="text-center table-actions">
                            <a href="books.php?action=edit&id=<?= $b['id'] ?>"
                               class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="books.php?delete=<?= $b['id'] ?>"
                               class="btn btn-sm btn-outline-danger" title="Hapus"
                               onclick="return confirm('Hapus buku ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">Belum ada buku.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
