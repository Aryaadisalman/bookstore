<?php
/**
 * admin/orders.php - Melihat daftar pesanan
 * Menampilkan semua pesanan dengan status.
 * Klik untuk melihat detail pesanan.
 */

require_once '../config/database.php';
require_once '../includes/auth.php';

// Cek admin login
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// --- Update status jika ada ---
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id     = (int)$_GET['id'];
    $status = $_GET['status'];
    $allowedStatuses = ['pending', 'processing', 'shipped', 'delivered'];
    if (in_array($status, $allowedStatuses)) {
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $id]);
    }
}

// --- Hapus pesanan jika ada ---
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $deleteId = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM order_details WHERE order_id = ?")->execute([$deleteId]);
    $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$deleteId]);
    redirect('orders.php');
}

// --- Ambil semua pesanan ---
$stmt = $pdo->query("SELECT o.*, u.name AS user_name
                     FROM orders o
                     LEFT JOIN users u ON o.user_id = u.id
                     ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();

$detailOrder = null;
if (isset($_GET['view']) && $_GET['view'] === 'detail' && isset($_GET['id'])) {
    $orderId = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT od.*, b.title, b.image
                           FROM order_details od
                           LEFT JOIN books b ON od.book_id = b.id
                           WHERE od.order_id = ?");
    $stmt->execute([$orderId]);
    $detailOrder = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $orderHeader = $stmt->fetch();
}

require_once 'includes/header.php';
?>

<h1 class="mb-4"><i class="bi bi-receipt"></i> Kelola Pesanan</h1>

<?php if (isset($orderHeader) && $orderHeader): ?>
    <!-- Detail Pesanan -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Pesanan #<?= $orderHeader['id'] ?></h5>
            <div class="d-flex gap-2">
                <a href="orders.php?delete=1&id=<?= $orderHeader['id'] ?>" class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Yakin ingin menghapus pesanan #<?= $orderHeader['id'] ?> ini? Tindakan ini tidak bisa dibatalkan.');">
                    <i class="bi bi-trash"></i> Hapus
                </a>
                <a href="orders.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h6>Data Pemesan</h6>
                    <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($orderHeader['name']) ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($orderHeader['email']) ?></p>
                    <p class="mb-1"><strong>Telepon:</strong> <?= htmlspecialchars($orderHeader['phone']) ?></p>
                    <p class="mb-0"><strong>Alamat:</strong> <?= nl2br(htmlspecialchars($orderHeader['address'])) ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6>Info Pesanan</h6>
                    <p class="mb-1"><strong>Tanggal:</strong> <?= date('d M Y H:i', strtotime($orderHeader['created_at'])) ?></p>
                    <p class="mb-1">
                        <strong>Status:</strong>
                        <?php
                        $statusLabels = [
                            'pending'    => 'warning',
                            'processing' => 'info',
                            'shipped'    => 'primary',
                            'delivered'  => 'success'
                        ];
                        $badge = $statusLabels[$orderHeader['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $badge ?>"><?= ucfirst($orderHeader['status']) ?></span>
                    </p>
                    <p class="mb-0"><strong>Total:</strong> Rp <?= number_format($orderHeader['total_amount'], 0, ',', '.') ?></p>
                </div>
            </div>

            <h6 class="mt-4">Daftar Buku</h6>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Buku</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($detailOrder): ?>
                        <?php $i = 1; $orderTotal = 0; foreach ($detailOrder as $item): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <?= htmlspecialchars($item['title']) ?>
                                </td>
                                <td class="text-end"><?= $item['quantity'] ?></td>
                                <td class="text-end">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                <td class="text-end">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                                <?php $orderTotal += $item['price'] * $item['quantity']; ?>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-active">
                            <td colspan="4" class="fw-bold text-end">Total:</td>
                            <td class="fw-bold text-end">Rp <?= number_format($orderTotal, 0, ',', '.') ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Tidak ada detail pesanan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Update Status -->
            <h6 class="mt-4">Ubah Status</h6>
            <div class="d-flex gap-2">
                <?php foreach (['pending' => 'Pending', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'] as $val => $label): ?>
                    <a href="orders.php?status=<?= $val ?>&id=<?= $orderHeader['id'] ?>"
                       class="btn btn-sm btn-outline-secondary">
                        <?= $label ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Daftar Semua Pesanan -->
<?php if (!isset($orderHeader) || !$orderHeader): ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Pemesan</th>
                    <th>Email</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php $i = 1; foreach ($orders as $o): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($o['name']) ?></td>
                            <td><?= htmlspecialchars($o['email']) ?></td>
                            <td class="text-end">Rp <?= number_format($o['total_amount'], 0, ',', '.') ?></td>
                            <td>
                                <?php
                                $badge = [
                                    'pending'    => 'warning',
                                    'processing' => 'info',
                                    'shipped'    => 'primary',
                                    'delivered'  => 'success'
                                ][$o['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= ucfirst($o['status']) ?></span>
                            </td>
                            <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="orders.php?view=detail&id=<?= $o['id'] ?>"
                                       class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="orders.php?delete=1&id=<?= $o['id'] ?>"
                                       class="btn btn-sm btn-outline-danger" title="Hapus Pesanan"
                                       onclick="return confirm('Yakin ingin menghapus pesanan #<?= $o['id'] ?> dari <?= htmlspecialchars(addslashes($o['name'])) ?>? Tindakan ini tidak bisa dibatalkan.');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada pesanan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
