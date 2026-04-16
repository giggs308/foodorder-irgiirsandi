<?php
include 'config.php';

if ($conn) {
    echo "<h2 style='color:green;'>✅ Koneksi ke database berhasil!</h2>";
} else {
    echo "<h2 style='color:red;'>❌ Koneksi gagal: " . mysqli_connect_error() . "</h2>";
}
?>
