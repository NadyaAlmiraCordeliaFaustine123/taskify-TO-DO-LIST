<?php
include 'koneksi.php';

$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM tasks WHERE id = $id");
$task = mysqli_fetch_assoc($data);

if (isset($_POST['update'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $deadline = $_POST['deadline'];
    $prioritas = $_POST['prioritas'];
    $status = $_POST['status'];

    $query = "UPDATE tasks SET 
                judul = '$judul', 
                deskripsi = '$deskripsi', 
                deadline = '$deadline', 
                prioritas = '$prioritas', 
                status = '$status' 
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        $error = "Gagal memperbarui tugas: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas - Taskify</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-container { max-width: 600px; background: #fff; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; margin: 40px auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; color: #1e293b; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; }
        .form-group textarea { resize: vertical; height: 100px; }
        .form-actions { display: flex; justify-content: flex-end; gap: 10px; }
        .btn-secondary { background-color: #f1f5f9; color: #64748b; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; }
        .btn-secondary:hover { background-color: #e2e8f0; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2><i class="fa-solid fa-pen-to-square" style="color: #4f46e5;"></i> Edit Tugas</h2>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">Perbarui informasi atau status tugas kamu.</p>

        <form action="" method="POST">
            <div class="form-group">
                <label>Judul Tugas</label>
                <input type="text" name="judul" required value="<?= htmlspecialchars($task['judul']); ?>">
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi"><?= htmlspecialchars($task['deskripsi'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Tenggat Waktu (Deadline)</label>
                <input type="date" name="deadline" required value="<?= $task['deadline']; ?>">
            </div>
            <div class="form-group">
                <label>Prioritas</label>
                <select name="prioritas">
                    <option value="Rendah" <?= $task['prioritas'] == 'Rendah' ? 'selected' : ''; ?>>Rendah</option>
                    <option value="Sedang" <?= $task['prioritas'] == 'Sedang' ? 'selected' : ''; ?>>Sedang</option>
                    <option value="Tinggi" <?= $task['prioritas'] == 'Tinggi' ? 'selected' : ''; ?>>Tinggi</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status Tugas</label>
                <select name="status">
                    <option value="Tertunda" <?= $task['status'] == 'Tertunda' ? 'selected' : ''; ?>>Tertunda</option>
                    <option value="Selesai" <?= $task['status'] == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                </select>
            </div>
            <div class="form-actions">
                <a href="index.php" class="btn-secondary">Batal</a>
                <button type="submit" name="update" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</body>
</html>