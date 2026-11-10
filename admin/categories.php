<?php
/**
 * admin/categories.php - Kelola kategori buku (CRUD sederhana)
 */

require_once '../config/database.php';
require_once '../includes/auth.php';

// Cek admin login
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$errors = [];
$success = '';
$action = $_GET['action'] ?? 'list';
$editCategory = null;

// --- Handle Add ---
if (isset($_POST['submit']) && $_POST['submit'] === 'add') {
    $name = trim($_POST['name'] ?? '');

    if (empty($name)) {
        $errors[] = 'Nama kategori wajib diisi.';
    }

    if (empty($errors)) {
        // Cek duplikat
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            $errors[] = 'Kategori sudah ada.';
        } else {
            $pdo->prepare("INSERT INTO categories (name) VALUES (?)")->execute([$name]);
            $success = 'Kategori berhasil ditambahkan!';
        }
    }
}

// --- Handle Edit ---
if (isset($_POST['submit']) && $_POST['submit'] === 'edit') {
    $id   = (int)$_POST['id'];
    $name = trim($_POST['name'] ?? '');

    if (empty($name)) {
        $errors[] = 'Nama kategori wajib diisi.';
    }

    if (empty($errors)) {
        // Cek duplikat
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
        $stmt->execute([$name, $id]);
        if ($stmt->fetch()) {
            $errors[] = 'Kategori sudah ada.';
        } else {
            $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?")->execute([$name, $id]);
            $success = 'Kategori berhasil diperbarui!';
            $action = 'list';
        }
    }
}

// --- Handle Delete ---
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$deleteId]);
    $success = 'Kategori berhasil dihapus!';
}

// --- Ambil kategori untuk edit ---
if ($action === 'edit' && isset($_GET['id'])) {
    $editId = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$editId]);
    $editCategory = $stmt->fetch();
    if (!$editCategory) {
        $action = 'list';
    }
}

// --- Ambil semua kategori ---
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<h1 class="mb-4"><i class="bi bi-folder"></i> Kelola Kategori</h1>

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

<div class="row">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-<?= $action === 'edit' ? 'pencil' : 'plus' ?>"></i>
                    <?= $action === 'edit' ? 'Edit Kategori' : 'Tambah Kategori' ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="categories.php">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $editCategory['id'] ?>">
                        <input type="hidden" name="submit" value="edit">
                    <?php else: ?>
                        <input type="hidden" name="submit" value="add">
                    <?php endif; ?>

                    <label class="form-label">Nama Kategori</label>
                    <input type="text" name="name" class="form-control"
                           value="<?= $action === 'edit' ? htmlspecialchars($editCategory['name']) : '' ?>"
                           placeholder="Masukkan nama kategori" required>

                    <button type="submit" class="btn btn-primary mt-3 w-100">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <?php if ($action === 'edit'): ?>
                        <a href="categories.php" class="btn btn-outline-secondary mt-2 w-100">
                            <i class="bi bi-x"></i> Batal
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Daftar Kategori</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($categories) > 0): ?>
                            <?php $i = 1; foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($cat['name']) ?></td>
                                    <td class="text-center table-actions">
                                        <a href="categories.php?action=edit&id=<?= $cat['id'] ?>"
                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="categories.php?delete=<?= $cat['id'] ?>"
                                           class="btn btn-sm btn-outline-danger" title="Hapus"
                                           onclick="return confirm('Hapus kategori ini? Data buku yang terkait akan terputus.')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">Belum ada kategori.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
