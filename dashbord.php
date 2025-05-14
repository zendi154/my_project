<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
?>

<?php
include 'config.php';

// Total setoran Jukir
$setoran_query = mysqli_query($conn, "SELECT SUM(total_setoran) AS total_setoran FROM pembayaran_juru_parkir");
$total_setoran = mysqli_fetch_assoc($setoran_query)['total_setoran'];

// Total nilai BPHTB
$nilai_query = mysqli_query($conn, "SELECT SUM(nilai_bphtb) AS total_nilai FROM bphtb_data");
$nilai = mysqli_fetch_assoc($nilai_query)['total_nilai'];

// Total data laporan PPAT
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_notaris_ppat");
$total_data = mysqli_fetch_assoc($total_query)['total'];

// Siapkan data per bulan
$bulan_arr = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
$kolom_bulan = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agt', 'sep', 'okt', 'nov', 'des'];
$setoran_per_bulan = [];

// Loop tiap bulan untuk menghitung total setoran per bulan
foreach ($kolom_bulan as $kolom) {
    $query = mysqli_query($conn, "SELECT SUM($kolom) AS total FROM pembayaran_juru_parkir");
    $row = mysqli_fetch_assoc($query);
    $setoran_per_bulan[] = (int)$row['total'];
}

// Ambil data laporan PPAT per bulan
$ppat_per_bulan = [];
foreach ($bulan_arr as $bulan) {
    $ppat_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM laporan_notaris_ppat WHERE laporan_bulan = '$bulan'");
    $ppat_count = mysqli_fetch_assoc($ppat_query)['total'];
    $ppat_per_bulan[] = (int)$ppat_count;
}

// Daftar nama bulan dan array untuk grafik
$bulan_arr = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$bphtb_per_bulan = [];

for ($i = 1; $i <= 12; $i++) {
    $query = mysqli_query($conn, "SELECT SUM(nilai_bphtb) AS total FROM bphtb_data WHERE MONTH(tanggal_transaksi) = $i");
    $row = mysqli_fetch_assoc($query);
    $bphtb_per_bulan[] = (int) $row['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>DASHBOARD.COM</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
             margin: 0; font-family: 'Segoe UI', sans-serif; background:rgb(131, 129, 129);
             }
        .sidebar {
            width: 220px; 
            height: 100vh; 
            background:rgb(201, 210, 219); 
            color: white;
            position: fixed;
             top: 0; 
             left: 0; 
             padding: 20px;
        }
        .sidebar h2 {
            margin-bottom: 30px;
             font-size: 20px; 
             text-align: center;
            border-bottom: 1px solid #555; 
            padding-bottom: 10px;
        }
        .sidebar a {
            color: white; 
            display: block; 
            padding: 10px; 
            margin-bottom: 10px;
            text-decoration: none; 
            border-radius: 4px; 
            transition: 0.2s;
        }

        .sidebar a:hover  {
        background: #495057; 

        }

        .main{ 
            margin-left: 240px; 
            padding: 30px; 
            color: #ffff;
        }

        .card {
            background: white; 
            padding: 20px; 
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(241, 229, 229, 0.76);
             margin-bottom: 20px;
        }

        /* Navbar abu-abu sticky top */
        .navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background-color: #ccc;
        padding: 15px 30px;
        z-index: 1000;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #999;
    }

    /* Link dalam navbar */
    .navbar a {
    text-decoration: none;
    color: blue;
    margin-right: 18px;
    }

    html, body {
      height: 100%;
      margin: 0;
      display: flex;
      flex-direction: column;
      font-family: Arial, sans-serif;
    }

    /* Sidebar */
    .sidebar {
      width: 222px;
      position: fixed;
      top: 60px;
      left: 0;
      height: 100%;
      background-color: rgb(169, 167, 167);
      padding: 10px;
      box-shadow: 2px 0 5px rgba(244, 233, 233, 0.91);
    }

    .sidebar h2, .sidebar a {
      color: white;
      text-align: center;
    }

        /*Card*/
        .card h3 { margin: 0 0 10px; }
        .dashboard-grid {
            display: flex; gap: 20px; flex-wrap: wrap;
        }
        .dashboard-grid .card {
            flex: 1; min-width: 220px; text-align: center;
        }

               /* Style untuk memastikan footer berada di bawah konten */
               html, body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        footer {
  position: fixed;
  bottom: 0;
  left: 242px; /* Sama dengan lebar sidebar */
  right: 0;
  background-color: #343a40;
  color: white;
  text-align: center;
  padding: 10px 20px;
  z-index: 999;
}

footer a {
  color: #f8f9fa;
  text-decoration: none;
  margin: 0 12px;
}

footer a:hover {
  text-decoration: underline;
}

.main-content {
  padding-bottom: 60px; /* Sesuaikan dengan tinggi footer */
}
    </style>
</head>

<body>
<!-- Navbar -->
<div class="navbar">
    <div><strong>🏢 | Badan Pendapatan Daerah Kota Bengkulu</strong></div>
    <div>
        <a href="#">👤 Profil</a>
        <a href="logout.php">🚪 Logout</a>
    </div>
</div>

<!-- Sidebar -->
<div class="sidebar">
    <h2>*📊 | Dashboard</h2>
    <a href="tampilan_laporan.php?bulan=&tahun=<?= date('Y') ?>">➕ Laporan Notaris & PPAT</a>
    <a href="index.php">➕ Laporan Retribusi Parkir</a>
    <a href="http://localhost/my_project/bphtb_data.php">➕ Laporan Bulan BPHTB</a>
</div>

<div class="main">
    <h1>Welcome To Websaite!</h1>
    <div class="card">
        <h3>Grafik Laporan PPAT Per-Bulan*</h3>
        <canvas id="ppatChart" height="100"></canvas>
    </div>
    <div class="card">
        <h3>Grafik Total Setoran Jukir Per-Bulan*</h3>
        <canvas id="jukirChart" height="100"></canvas>
    </div>
    <div class="card">
    <h3>Grafik Total Setoran BPHTB Per-Bulan*</h3>
        <canvas id="bphtbChart" height="100"></canvas>
    </div>
</div>
<br>
    <br>

        <!-- Footer -->
    <footer>
        <p>&copy;    2025 by : Zendi Afrian Saputra, S.H | Semua Hak Cipta Dilindungi Undang - Undang | 
            <a href="https://www.example.com/privacy">Kebijakan Privasi</a>| 
            <a href="https://www.example.com/terms">Syarat & Ketentuan</a>
        </p>
    </footer>

<script>
const bulanLabels = <?= json_encode($bulan_arr) ?>;
const ppatData = <?= json_encode($ppat_per_bulan) ?>;
const jukirData = <?= json_encode($setoran_per_bulan) ?>;
const bphtbData = <?= json_encode($bphtb_per_bulan) ?>;

new Chart(document.getElementById('ppatChart'), {
    type: 'bar',
    data: {
        labels: bulanLabels,
        datasets: [{
            label: 'Jumlah Laporan PPAT',
            data: ppatData,
            backgroundColor: '#3498db'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

new Chart(document.getElementById('jukirChart'), {
    type: 'line',
    data: {
        labels: bulanLabels,
        datasets: [{
            label: 'Setoran Jukir (Rp)',
            data: jukirData,
            borderColor: '#2ecc71',
            fill: false,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => 'Rp ' + value.toLocaleString('id-ID')
                }
            }
        }
    }
});

new Chart(document.getElementById('bphtbChart'), {
    type: 'line',
    data: {
        labels: bulanLabels,
        datasets: [{
            label: 'Total Nilai BPHTB (Rp)',
            data: bphtbData,
            borderColor: '#9b59b6',
            backgroundColor: 'rgba(230, 126, 34, 0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => 'Rp ' + value.toLocaleString('id-ID')
                }
            }
        }
    }
});
</script>

</body>
</html>
