<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// Hitung ringkasan utama
$total_tugas = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM tasks"));
$tugas_selesai = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM tasks WHERE status='Selesai'"));
$tugas_pending = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM tasks WHERE status='Tertunda'"));
$badge_tertunda = $tugas_pending;
$persentase = $total_tugas > 0 ? round(($tugas_selesai / $total_tugas) * 100) : 0;

// Hitung jumlah tugas per prioritas
$prioritas_counts = [
    'Tinggi' => 0,
    'Sedang' => 0,
    'Rendah' => 0
];
$res_prioritas = mysqli_query($conn, "SELECT prioritas, COUNT(*) AS jumlah FROM tasks GROUP BY prioritas");
while ($row_p = mysqli_fetch_assoc($res_prioritas)) {
    $nama_p = $row_p['prioritas'];
    if (array_key_exists($nama_p, $prioritas_counts)) {
        $prioritas_counts[$nama_p] = (int) $row_p['jumlah'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taskify - Diagram Statistik</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
        }
        .chart-card {
            background: #ffffff;
            padding: 22px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .chart-card h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .chart-card p {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 18px;
        }
        .chart-box {
            position: relative;
            height: 290px;
        }
        [data-theme="dark"] .chart-card {
            background-color: var(--bg-card);
            border-color: var(--border-color);
        }
        [data-theme="dark"] .chart-card p {
            color: var(--text-muted);
        }
        @media (max-width: 900px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
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

            <!-- MENU UTAMA -->
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
                    <li><a href="kalender.php"><i class="fa-solid fa-calendar-days"></i> Kalender</a></li>
                    <li class="active"><a href="statistik.php"><i class="fa-solid fa-chart-pie"></i> Statistik</a></li>
                </ul>
            </div>

            <!-- MENU PREFERENSI -->
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
            <header class="topbar">
                <div></div>
            </header>

            <section class="welcome-section">
                <div class="welcome-text">
                    <h1>Diagram Statistik Tugas</h1>
                    <p>Visualisasi sebaran prioritas dan tingkat penyelesaian tugas Anda.</p>
                </div>
                <a href="index.php" class="btn-primary"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
            </section>

            <section class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
                <div class="stat-card">
                    <div class="stat-header">
                        <span>Total Tugas</span>
                        <i class="fa-solid fa-layer-group text-blue"></i>
                    </div>
                    <h2><?= $total_tugas; ?></h2>
                    <span class="badge-sub">Seluruh data di database</span>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span>Tugas Selesai</span>
                        <i class="fa-solid fa-circle-check text-green"></i>
                    </div>
                    <h2><?= $tugas_selesai; ?></h2>
                    <span class="badge-sub"><?= $persentase; ?>% tingkat penyelesaian</span>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span>Tugas Tertunda</span>
                        <i class="fa-solid fa-clock text-orange"></i>
                    </div>
                    <h2><?= $tugas_pending; ?></h2>
                    <span class="badge-sub">Perlu ditindaklanjuti</span>
                </div>
            </section>

            <section class="charts-grid">
                <div class="chart-card">
                    <h3>Jumlah Tugas per Prioritas</h3>
                    <p>Perbandingan banyaknya tugas berdasarkan tingkat prioritas</p>
                    <div class="chart-box">
                        <canvas id="priorityChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <h3>Status Penyelesaian Tugas</h3>
                    <p>Proporsi tugas berstatus Selesai dibandingkan Tertunda</p>
                    <div class="chart-box">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeIcon = document.getElementById('darkModeIcon');
        const darkModeText = document.getElementById('darkModeText');
        const htmlElement = document.documentElement;

        let priorityChart;
        let statusChart;

        function getChartColors() {
            const isDark = htmlElement.getAttribute('data-theme') === 'dark';
            return {
                textColor: isDark ? '#f8fafc' : '#1e293b',
                mutedColor: isDark ? '#94a3b8' : '#64748b',
                gridColor: isDark ? 'rgba(148, 163, 184, 0.15)' : 'rgba(100, 116, 139, 0.15)',
                borderColor: isDark ? '#1e293b' : '#ffffff'
            };
        }

        function renderCharts() {
            const colors = getChartColors();

            if (priorityChart) priorityChart.destroy();
            if (statusChart) statusChart.destroy();

            const ctxPrioritas = document.getElementById('priorityChart').getContext('2d');
            priorityChart = new Chart(ctxPrioritas, {
                type: 'bar',
                data: {
                    labels: ['Tinggi', 'Normal', 'Rendah'],
                    datasets: [{
                        label: 'Jumlah Tugas',
                        data: [
                            <?= $prioritas_counts['Tinggi']; ?>,
                            <?= $prioritas_counts['Sedang']; ?>,
                            <?= $prioritas_counts['Rendah']; ?>
                        ],
                        backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6'],
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            ticks: { color: colors.textColor },
                            grid: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                color: colors.mutedColor
                            },
                            grid: { color: colors.gridColor }
                        }
                    }
                }
            });

            const ctxStatus = document.getElementById('statusChart').getContext('2d');
            statusChart = new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: ['Selesai', 'Tertunda'],
                    datasets: [{
                        data: [<?= $tugas_selesai; ?>, <?= $tugas_pending; ?>],
                        backgroundColor: ['#10b981', '#f59e0b'],
                        borderColor: colors.borderColor,
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: colors.textColor }
                        }
                    }
                }
            });
        }

        const currentTheme = localStorage.getItem('theme');
        if (currentTheme === 'dark') {
            htmlElement.setAttribute('data-theme', 'dark');
            darkModeIcon.classList.replace('fa-moon', 'fa-sun');
            darkModeText.textContent = 'Mode Terang';
        }

        renderCharts();

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

            renderCharts();
        });
    </script>
</body>
</html>