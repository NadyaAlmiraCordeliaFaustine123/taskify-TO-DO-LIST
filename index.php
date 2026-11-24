<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// Ambil pencarian & filter jika ada
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT * FROM tasks WHERE judul LIKE '%$search%'";
if ($filter_status != '') {
    $query .= " AND status = '$filter_status'";
}
$query .= " ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// Hitung statistik untuk ringkasan dashboard
$total_tugas = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tasks"));
$tugas_selesai = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tasks WHERE status='Selesai'"));
$tugas_pending = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tasks WHERE status='Tertunda'"));
$tugas_tinggi = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tasks WHERE prioritas='Tinggi' AND status='Tertunda'"));
// Hitung jumlah tugas yang masih tertunda untuk badge sidebar
$badge_tertunda = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tasks WHERE status='Tertunda'"));

$persentase = $total_tugas > 0 ? round(($tugas_selesai / $total_tugas) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taskify - Dashboard To-Do List</title>
    <link rel="stylesheet" href="assets/style.css">
    <!-- FontAwesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="app-container">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="brand">
                <h2><i class="fa-solid fa-list-check"></i> Taskify</h2>
            </div>
            <div class="user-profile">
                <div class="avatar">N</div>
                <div class="user-info">
                    <h4>Nadya Almira</h4>
                    <p>Product Designer</p>
                </div>
            </div>

            <!-- MENU UTAMA -->
            <div class="menu-section">
                <p class="menu-label">MENU UTAMA</p>
                <ul>
                    <li class="active"><a href="index.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
                    <li>
    <a href="index.php#tabel-tugas" id="link-tugas">
        <i class="fa-solid fa-list-check"></i> Tugas Saya 
        <?php if($badge_tertunda > 0): ?>
            <span class="sidebar-badge"><?= $badge_tertunda; ?></span>
        <?php endif; ?>
    </a>
</li>
                    <li><a href="kalender.php"><i class="fa-solid fa-calendar-days"></i> Kalender</a></li>
                    <li><a href="statistik.php"><i class="fa-solid fa-chart-pie"></i> Statistik</a></li>
                </ul>
            </div>

            <!-- MENU PREFERENSI (TARUH KODE DARK MODE DI SINI) -->
            <div class="menu-section preferensi">
                <p class="menu-label">PREFERENSI</p>
                <ul>
                    <li>
                        <a href="#" id="darkModeToggle">
                            <i class="fa-solid fa-moon" id="darkModeIcon"></i> 
                            <span id="darkModeText">Mode Gelap</span>
                        </a>
                    </li>
                    <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a></li>
                </ul>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- TOP BAR -->
<header class="topbar">
    <div></div>
</header>

            <!-- GREETING & BANNER -->
            <section class="welcome-section">
                <div class="welcome-text">
                    <h1>Halo, Nadya!</h1>
                    <p>Ringkasan aktivitas dan kemajuan tugas harian Anda.</p>
                </div>
                <a href="tambah.php" class="btn-primary"><i class="fa-solid fa-plus"></i> Tambah Tugas Baru</a>
            </section>

            <!-- IKHTISAR PRODUKTIVITAS (STATISTIK) -->
            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span>Total Tugas</span>
                        <i class="fa-solid fa-layer-group text-blue"></i>
                    </div>
                    <h2><?= $total_tugas; ?></h2>
                    <span class="badge-sub">+2 baru minggu ini</span>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span>Selesai</span>
                        <i class="fa-solid fa-circle-check text-green"></i>
                    </div>
                    <h2><?= $tugas_selesai; ?></h2>
                    <span class="badge-sub"><?= $persentase; ?>% Laju Tersisa</span>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span>Tertunda (Pending)</span>
                        <i class="fa-solid fa-clock text-orange"></i>
                    </div>
                    <h2><?= $tugas_pending; ?></h2>
                    <span class="badge-sub">Aktif</span>
                </div>
                <div class="stat-card highlight">
                    <div class="stat-header">
                        <span>Prioritas Tinggi</span>
                        <i class="fa-solid fa-triangle-exclamation text-red"></i>
                    </div>
                    <h2><?= $tugas_tinggi; ?></h2>
                    <span class="badge-sub">Mendesak</span>
                </div>
            </section>

            <!-- FOKUS HARI INI & TARGET -->
            <section class="focus-grid">
                <div class="focus-card">
                    <div class="focus-badge">Penting!</div>
                    <h3>Belajar untuk UKK</h3>
                    <p>Tugas berprioritas tinggi dengan batas waktu esok hari. Selesaikan materi bab 3 & 4 terlebih dahulu.</p>
                    <div class="focus-footer">
                        <span><i class="fa-regular fa-clock"></i> Deadline: 25 Okt 2026</span>
                        <span class="priority-badge tinggi">Tinggi</span>
                    </div>
                </div>

                <div class="target-card">
                    <div class="target-header">
                        <h3>Fokus Hari Ini</h3>
                        <span class="text-sub">Keep going! You're doing great today!</span>
                    </div>
                    <div class="progress-box">
                        <div class="progress-info">
                            <span>Tetap konsisten! Anda sudah menuntaskan <?= $persentase; ?>% target tugas</span>
                            <strong><?= $persentase; ?>%</strong>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $persentase; ?>%;"></div>
                        </div>
                    </div>
                    <p class="target-desc">Target Harian: <?= $tugas_selesai; ?>/<?= $total_tugas; ?> tugas telah diselesaikan</p>
                </div>
            </section>

            <!-- DAFTAR TUGAS TERBARU (Diberi id="tabel-tugas" dan scroll-margin agar tidak tertutup topbar) -->
            <section class="task-section" id="tabel-tugas" style="scroll-margin-top: 20px;">
                <div class="task-section-header">
                    <div>
                        <h3>Daftar Tugas Terbaru</h3>
                        <p>Kelola status dan batas waktu pengerjaan tugas Anda</p>
                    </div>
                    <!-- Form Pencarian & Filter -->
                    <form action="" method="GET" class="filter-form">
                        <input type="text" name="search" placeholder="Cari nama tugas..." value="<?= htmlspecialchars($search); ?>">
                        <div class="filter-links">
                            <a href="index.php" class="<?= $filter_status == '' ? 'active' : ''; ?>">Semua</a>
                            <a href="index.php?status=Tertunda" class="<?= $filter_status == 'Tertunda' ? 'active' : ''; ?>">Tertunda</a>
                            <a href="index.php?status=Selesai" class="<?= $filter_status == 'Selesai' ? 'active' : ''; ?>">Selesai</a>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>NO.</th>
                                <th>JUDUL TUGAS</th>
                                <th>TENGGAT WAKTU</th>
                                <th>PRIORITAS</th>
                                <th>STATUS</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td>
                                        <span class="<?= $row['status'] == 'Selesai' ? 'completed-text' : ''; ?>">
                                            <?= htmlspecialchars($row['judul']); ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($row['deadline'])); ?></td>
                                    <td>
    <span class="priority-dot <?= strtolower($row['prioritas']); ?>"></span>
    <?= ($row['prioritas'] == 'Sedang') ? 'Normal' : htmlspecialchars($row['prioritas']); ?>
</td>
                                    <td>
                                        <span class="status-badge <?= strtolower($row['status']); ?>">
                                            <?= $row['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="selesai.php?id=<?= $row['id']; ?>" class="btn-action <?= $row['status'] == 'Selesai' ? 'active-check' : ''; ?>" title="Ubah Status Selesai">
                                            <i class="fa-solid fa-check"></i>
                                        </a>
                                        <a href="edit.php?id=<?= $row['id']; ?>" class="btn-action edit" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <a href="hapus.php?id=<?= $row['id']; ?>" class="btn-action delete" title="Hapus" onclick="return confirm('Yakin ingin menghapus tugas ini?')"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada tugas tersedia.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <!-- Skrip JavaScript (Smooth Scroll & Dark Mode Toggle) -->
    <script>
        // Smooth Scroll untuk menu Tugas Saya
        document.getElementById('link-tugas').addEventListener('click', function(e) {
            e.preventDefault();
            const targetSection = document.getElementById('tabel-tugas');
            if (targetSection) {
                targetSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                targetSection.style.transition = 'all 0.4s ease';
                targetSection.style.boxShadow = '0 0 0 3px rgba(79, 70, 229, 0.25)';
                setTimeout(() => {
                    targetSection.style.boxShadow = 'none';
                }, 1200);
            }
        });

        // Logika Dark Mode Toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeIcon = document.getElementById('darkModeIcon');
        const darkModeText = document.getElementById('darkModeText');
        const htmlElement = document.documentElement;

        const currentTheme = localStorage.getItem('theme');
        if (currentTheme === 'dark') {
            htmlElement.setAttribute('data-theme', 'dark');
            darkModeIcon.classList.replace('fa-moon', 'fa-sun');
            darkModeText.textContent = 'Mode Terang';
        }

        darkModeToggle.addEventListener('click', function(e) {
            e.preventDefault();
            let theme = htmlElement.getAttribute('data-theme');

            if (theme === 'dark') {
                htmlElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                darkModeIcon.classList.replace('fa-sun', 'fa-moon');
                darkModeText.textContent = 'Mode Gelap';
            } else {
                htmlElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                darkModeIcon.classList.replace('fa-moon', 'fa-sun');
                darkModeText.textContent = 'Mode Terang';
            }
        });
    </script>
</body>
</html>