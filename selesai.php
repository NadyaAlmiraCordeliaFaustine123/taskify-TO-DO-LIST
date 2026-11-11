<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Ambil status saat ini berdasarkan ID
    $query = mysqli_query($conn, "SELECT status FROM tasks WHERE id = $id");
    if ($row = mysqli_fetch_assoc($query)) {
        // Ubah status kebalikannya
        $status_baru = ($row['status'] == 'Selesai') ? 'Tertunda' : 'Selesai';

        // Update ke database
        mysqli_query($conn, "UPDATE tasks SET status = '$status_baru' WHERE id = $id");
    }
}

// Redirect kembali ke halaman dashboard
header("Location: index.php#tabel-tugas");
exit();
?>