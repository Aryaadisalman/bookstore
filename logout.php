<?php
/**
 * logout.php - Mengakhiri session pengguna
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

session_unset();
session_destroy();

setFlash('message', 'Anda telah keluar. Sampai jumpa!');
redirect('index.php');
?>
