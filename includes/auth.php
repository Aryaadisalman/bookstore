<?php
/**
 * includes/auth.php
 * Session-based authentication helper functions.
 */

session_start();

/**
 * Cek apakah user sudah login.
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Cek apakah yang login adalah admin.
 */
function isAdmin(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Dapatkan user ID yang sedang login (atau null).
 */
function getUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Dapatkan nama user yang sedang login (atau null).
 */
function getUserName(): ?string
{
    return $_SESSION['name'] ?? null;
}

/**
 * Redirect ke halaman lain.
 */
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

/**
 * Set flash message untuk ditampilkan sekali di halaman berikutnya.
 */
function setFlash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

/**
 * Ambil dan hapus flash message.
 */
function getFlash(string $key): ?string
{
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}
?>
