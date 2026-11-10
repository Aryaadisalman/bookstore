<?php
/**
 * login.php - Halaman login user
 * Login menggunakan email dan password.
 * Password disimpan menggunakan password hashing (password_hash / password_verify).
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

// Jika sudah login, redirect ke beranda
if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input
    if (empty($email)) {
        $errors[] = 'Email wajib diisi.';
    }
    if (empty($password)) {
        $errors[] = 'Password wajib diisi.';
    }

    // Jika tidak ada error, cek ke database
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login berhasil - simpan data ke session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            setFlash('message', 'Login berhasil! Selamat belanja di Bookstore.');

            // Jika ada cart di session dan user baru login, tetap simpan
            redirect('index.php');
        } else {
            $errors[] = 'Email atau password salah.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header text-center bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-person-circle"></i> Login</h4>
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

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               placeholder="contoh@email.com" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control"
                               placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>

                <div class="text-center mt-3">
                    <small>Belum punya akun? <a href="register.php">Daftar sekarang</a></small>
                </div>
                <div class="text-center mt-2">
                    <small><a href="admin/login.php">Login sebagai Admin</a></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
