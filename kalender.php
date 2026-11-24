<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// Ambil statistik kalender dari database
$total_bulan_ini = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tasks"));
$tugas_selesai = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tasks WHERE status='Selesai'"));
$tugas_aktif = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tasks WHERE status='Tertunda'"));
$tugas_tinggi = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tasks WHERE prioritas='Tinggi' AND status='Tertunda'"));
// Hitung jumlah tugas yang masih tertunda untuk badge sidebar
$badge_tertunda = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tasks WHERE status='Tertunda'"));

// Logika Navigasi Bulan & Tahun Kalender
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// Hitung bulan sebelumnya dan sesudahnya untuk tombol panah
$prev_bulan = $bulan - 1;
$prev_tahun = $tahun;
if ($prev_bulan < 1) {
    $prev_bulan = 12;
    $prev_tahun--;
}

$next_bulan = $bulan + 1;
$next_tahun = $tahun;
if ($next_bulan > 12) {
    $next_bulan = 1;
    $next_tahun++;
}

// Nama-nama bulan dalam bahasa Indonesia
$nama_bulan_arr = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Hitung jumlah hari dalam bulan tersebut & hari pertama bulan itu
$jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
$tanggal_pertama = mktime(0, 0, 0, $bulan, 1, $tahun);
$hari_pertama = date('N', $tanggal_pertama); // 1 (Senin) sampai 7 (Minggu)

// Ambil semua tugas dari database untuk dicocokkan dengan tanggal kalender
$query_all_tasks = mysqli_query($conn, "SELECT * FROM tasks");
$tasks_by_date = [];
while($row = mysqli_fetch_assoc($query_all_tasks)) {
    $tasks_by_date[$row['deadline']][] = $row;
}

// Tanggal hari ini (mengikuti sistem yaitu 11 November 2026)
$current_date_system = date('Y-m-d');

// Ambil data tugas mendatang mulai dari hari ini ke depan untuk sidebar kanan
$result_tasks = mysqli_query($conn, "SELECT * FROM tasks WHERE deadline >= '$current_date_system' ORDER BY deadline ASC LIMIT 5");
if(mysqli_num_rows($result_tasks) == 0) {
    // Jika tidak ada yang >= hari ini, fallback tampilkan dari yang terdekat
    $result_tasks = mysqli_query($conn, "SELECT * FROM tasks ORDER BY deadline DESC LIMIT 5");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Tugas - Taskify</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .calendar-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        .calendar-main-card, .calendar-sidebar-card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .calendar-header-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            text-align: center;
        }
        .calendar-day-name {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            padding-bottom: 10px;
        }
        .calendar-date {
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            padding: 8px 4px;
            min-height: 85px;
            text-align: left;
            font-size: 12px;
            position: relative;
        }
        .calendar-date.empty {
            background: transparent;
            border: none;
        }
        .calendar-date.active {
            border-color: #4f46e5;
            background: #eef2ff;
        }
        .calendar-date .date-num {
            font-weight: 600;
            color: #1e293b;
            display: block;
            margin-bottom: 4px;
        }
        .event-dot {
            font-size: 9px;
            margin-top: 3px;
            padding: 2px 4px;
            border-radius: 4px;
            background: #e0e7ff;
            color: #4f46e5;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .event-dot.tinggi {
            background: #fee2e2;
            color: #dc2626;
        }
        .selected-task-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .btn-nav-cal {
            padding: 6px 12px;
            border: 1px solid #e2e8f0;
            background: #fff;
            border-radius: 6px;
            cursor: pointer;
            color: #1e293b;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-nav-cal:hover {
            background: #f1f5f9;
        }
    </style>
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

            <div class="menu-section">
    <p class="menu-label">MENU UTAMA</p>
    <ul>
        <li><a href="index.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
        <li>
            <a href="index.php#tabel-tugas">
                <i class="fa-solid fa-list-check"></i> Tugas Saya 
                <?php if($badge_tertunda > 0): ?>
                    <span class="sidebar-badge"><?= $badge_tertunda; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="active"><a href="kalender.php"><i class="fa-solid fa-calendar-days"></i> Kalender</a></li>
        <li><a href="statistik.php"><i class="fa-solid fa-chart-pie"></i> Statistik</a></li>
    </ul>
</div>

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

            <!-- HEADER HALAMAN -->
            <div class="welcome-section">
                <div class="welcome-text">
                    <span style="font-size: 11px; font-weight: 600; color: #4f46e5; letter-spacing: 0.5px;">MANAJEMEN WAKTU</span>
                    <h1>Kalender Tugas</h1>
                    <p>Lihat jadwal, tenggat waktu, dan kelola target harian Anda secara teratur.</p>
                </div>
            </div>

            <!-- STATISTIK KALENDER -->
            <section class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
                <div class="stat-card">
                    <div class="stat-header"><span>Tugas Bulan Ini</span><i class="fa-solid fa-calendar text-blue"></i></div>
                    <h2><?= $total_bulan_ini; ?></h2>
                    <span class="badge-sub">Total</span>
                </div>
                <div class="stat-card">
                    <div class="stat-header"><span>Terselesaikan</span><i class="fa-solid fa-circle-check text-green"></i></div>
                    <h2><?= $tugas_selesai; ?></h2>
                    <span class="badge-sub">Selesai</span>
                </div>
                <div class="stat-card">
                    <div class="stat-header"><span>Menunggu Proses</span><i class="fa-solid fa-clock text-orange"></i></div>
                    <h2><?= $tugas_aktif; ?></h2>
                    <span class="badge-sub">Aktif</span>
                </div>
                <div class="stat-card highlight">
                    <div class="stat-header"><span>Tenggat Kritis</span><i class="fa-solid fa-triangle-exclamation text-red"></i></div>
                    <h2><?= $tugas_tinggi; ?></h2>
                    <span class="badge-sub">Prioritas Tinggi</span>
                </div>
            </section>

            <!-- MAIN GRID KALENDER & DETAIL -->
            <div class="calendar-layout">
                <!-- KIRI: KOTAK KALENDER DINAMIS -->
                <div class="calendar-main-card">
                    <div class="calendar-header-nav">
                        <div>
                            <h3 style="font-size: 18px; font-weight: 700;"><?= $nama_bulan_arr[$bulan] . ' ' . $tahun; ?></h3>
                        </div>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <a href="kalender.php?bulan=<?= $prev_bulan; ?>&tahun=<?= $prev_tahun; ?>" class="btn-nav-cal"><i class="fa-solid fa-chevron-left"></i></a>
                            <span style="font-weight: 600; font-size: 14px; padding: 0 5px;"><?= $nama_bulan_arr[$bulan] . ' ' . $tahun; ?></span>
                            <a href="kalender.php?bulan=<?= $next_bulan; ?>&tahun=<?= $next_tahun; ?>" class="btn-nav-cal"><i class="fa-solid fa-chevron-right"></i></a>
                            <a href="kalender.php" style="background: #4f46e5; color: white; border: none; padding: 6px 14px; border-radius: 6px; font-weight: 500; font-size: 13px; text-decoration: none;">Hari Ini</a>
                        </div>
                    </div>

                    <!-- GRID HARI -->
                    <div class="calendar-grid">
                        <div class="calendar-day-name">Sen</div>
                        <div class="calendar-day-name">Sel</div>
                        <div class="calendar-day-name">Rab</div>
                        <div class="calendar-day-name">Kam</div>
                        <div class="calendar-day-name">Jum</div>
                        <div class="calendar-day-name">Sab</div>
                        <div class="calendar-day-name" style="color: #ef4444;">Min</div>

                        <!-- Kotak kosong sebelum tanggal 1 -->
                        <?php for($x = 1; $x < $hari_pertama; $x++): ?>
                            <div class="calendar-date empty"></div>
                        <?php endfor; ?>

                        <!-- Rendering Tanggal Bulan Ini (Otomatis dari Database) -->
                        <?php for($i = 1; $i <= $jumlah_hari; $i++): ?>
                            <?php 
                                $current_date_str = sprintf('%04d-%02d-%02d', $tahun, $bulan, $i);
                                $is_today = ($current_date_str == $current_date_system);
                            ?>
                            <div class="calendar-date <?= $is_today ? 'active' : ''; ?>">
                                <span class="date-num"><?= $i; ?></span>
                                
                                <?php 
                                if(isset($tasks_by_date[$current_date_str])): 
                                    foreach($tasks_by_date[$current_date_str] as $task_item):
                                        $dot_class = ($task_item['prioritas'] == 'Tinggi') ? 'event-dot tinggi' : 'event-dot';
                                ?>
                                    <div class="<?= $dot_class; ?>" title="<?= htmlspecialchars($task_item['judul']); ?>">
                                        <?= htmlspecialchars($task_item['judul']); ?>
                                    </div>
                                <?php 
                                    endforeach;
                                endif; 
                                ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- KANAN: DETAIL TUGAS TERPILIH -->
                <div class="calendar-sidebar-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div>
                            <span style="font-size: 11px; color: #64748b;">DETAIL TUGAS TERPILIH</span>
                            <h3 style="font-size: 16px; font-weight: 700;">11 November 2026</h3>
                        </div>
                        <span style="background: #eef2ff; color: #4f46e5; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">Hari Ini</span>
                    </div>

                    <!-- Card Detail Tugas (Otomatis ambil tugas hari ini atau tugas terdekat berikutnya) -->
                    <div class="selected-task-box">
                        <?php 
                        $detail_query = mysqli_query($conn, "SELECT * FROM tasks WHERE deadline = '$current_date_system' LIMIT 1");
                        if(mysqli_num_rows($detail_query) == 0) {
                            $detail_query = mysqli_query($conn, "SELECT * FROM tasks WHERE deadline >= '$current_date_system' ORDER BY deadline ASC LIMIT 1");
                        }
                        if(mysqli_num_rows($detail_query) == 0) {
                            $detail_query = mysqli_query($conn, "SELECT * FROM tasks ORDER BY deadline DESC LIMIT 1");
                        }
                        if($dt = mysqli_fetch_assoc($detail_query)):
                        ?>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                            <h4 style="font-size: 14px; font-weight: 600;"><i class="fa-solid fa-circle" style="font-size: 8px; color: <?= $dt['prioritas'] == 'Tinggi' ? '#ef4444' : '#4f46e5'; ?>;"></i> <?= htmlspecialchars($dt['judul']); ?></h4>
                            <span class="priority-badge <?= strtolower($dt['prioritas']); ?>"><?= $dt['prioritas']; ?></span>
                        </div>
                        <p style="font-size: 12px; color: #64748b; margin-bottom: 4px;"><?= htmlspecialchars($dt['deskripsi'] ? $dt['deskripsi'] : 'Tidak ada deskripsi tambahan.'); ?></p>
                        <div style="font-size: 11px; color: #64748b; margin-top: 6px;">
                            <span><i class="fa-regular fa-clock"></i> Deadline: <?= date('d M Y', strtotime($dt['deadline'])); ?></span>
                        </div>
                        <?php else: ?>
                            <p style="font-size: 12px; color: #64748b; text-align: center; margin: 10px 0;">Belum ada tugas tersedia.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Mendatang Minggu Ini -->
                    <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 10px;">Mendatang Minggu Ini</h4>
                    <?php 
                    if(mysqli_num_rows($result_tasks) > 0):
                        while($t = mysqli_fetch_assoc($result_tasks)): 
                    ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 12px;">
                        <div>
                            <strong><?= htmlspecialchars($t['judul']); ?></strong>
                            <div style="color: #64748b; font-size: 11px;"><?= date('d M Y', strtotime($t['deadline'])); ?></div>
                        </div>
                        <span class="status-badge <?= strtolower($t['status'] == 'Selesai' ? 'selesai' : 'tertunda'); ?>"><?= $t['status']; ?></span>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <p style="font-size: 12px; color: #64748b; text-align: center; margin: 10px 0;">Tidak ada tugas mendatang.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
<script>
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeIcon = document.getElementById('darkModeIcon');
        const darkModeText = document.getElementById('darkModeText');
        const htmlElement = document.documentElement;

        function setDarkMode(isDark) {
            if (isDark) {
                htmlElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                if (darkModeIcon) darkModeIcon.classList.replace('fa-moon', 'fa-sun');
                if (darkModeText) darkModeText.textContent = 'Mode Terang';
            } else {
                htmlElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                if (darkModeIcon) darkModeIcon.classList.replace('fa-sun', 'fa-moon');
                if (darkModeText) darkModeText.textContent = 'Mode Gelap';
            }
        }

        // Terapkan tema tersimpan saat halaman dibuka
        if (localStorage.getItem('theme') === 'dark') {
            setDarkMode(true);
        }

        // Toggle saat tombol diklik
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', function(e) {
                e.preventDefault();
                const isDark = htmlElement.getAttribute('data-theme') === 'dark';
                setDarkMode(!isDark);
            });
        }
    </script>
</body>
</html>