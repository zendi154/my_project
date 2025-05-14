<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
?>

<?php
include 'config.php';

// Tambah / Edit Data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? '';
    $nomor_sspd = $_POST['nomor_sspd'];
    $tanggal = $_POST['tanggal_transaksi'];
    $nama_penerima = $_POST['nama_penerima'];
    $nama_ppat = $_POST['nama_ppat'];
    $nilai = $_POST['nilai_bphtb'];
    $keterangan = $_POST['keterangan'];

    if ($id) {
        // Update
        mysqli_query($conn, "UPDATE bphtb_data SET 
            nomor_sspd='$nomor_sspd',
            tanggal_transaksi='$tanggal',
            nama_penerima='$nama_penerima',
            nama_ppat='$nama_ppat',
            nilai_bphtb='$nilai',
            keterangan='$keterangan'
            WHERE id=$id");
    } else {
        // Tambah
        mysqli_query($conn, "INSERT INTO bphtb_data 
            (nomor_sspd, tanggal_transaksi, nama_penerima, nama_ppat, nilai_bphtb, keterangan)
            VALUES ('$nomor_sspd', '$tanggal', '$nama_penerima', '$nama_ppat', '$nilai', '$keterangan')");
    }
}

// Hapus Data
if (isset($_GET['hapus'])) {
    $hapus_id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM bphtb_data WHERE id=$hapus_id");
}

// Filter Bulan/Tahun
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';

$where = '';
if ($bulan && $tahun) {
    $where = "WHERE MONTH(tanggal_transaksi) = $bulan AND YEAR(tanggal_transaksi) = $tahun";
} elseif ($tahun) {
    $where = "WHERE YEAR(tanggal_transaksi) = $tahun";
}

$data = mysqli_query($conn, "SELECT * FROM bphtb_data $where ORDER BY tanggal_transaksi DESC");

// Ambil data untuk edit
$edit = null;
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $edit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM bphtb_data WHERE id=$id_edit"));
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

// Ambil total data laporan PPAT (laporan_notaris_ppat)
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_notaris_ppat");
$total_data = mysqli_fetch_assoc($total_query)['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>BPHTB.COM</title>
    <style>
   body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f9fafb;
    padding: 40px;
    color: #333;
    margin-left: 240px; /* ruang untuk sidebar */
}

.navbar {
    position: fixed;
    top: 0;
    left: 1px; /* Sesuaikan jika ada sidebar */
    right: 0;
    background-color: #ccc;
    padding: 10px 20px;
    z-index: 1000;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #999;
    height: 33px;
}

    /* Link dalam navbar */
    .navbar a {
    text-decoration: none;
    color: blue;
    margin-right: 12px;
    }

    /* Sidebar */
    .sidebar {
    width: 222px;
    position: fixed;
    top: 60px; /* diberi jarak supaya tidak tertutup navbar */
    left: 0;
    height: 100%;
    background-color:rgb(169, 167, 167);
    padding: 20px;
    box-shadow: 2px 0 5px rgba(244, 233, 233, 0.91);
    }

.sidebar h2 {
 margin-bottom: 30px;
 font-size: 18px;
 text-align: center;
 border-bottom: 1px solid;
 padding-bottom: 10px;
 color: white;
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

.sidebar a:hover {
    background: #495057;
}

h2, h3 {
    color: #1f2937;
}

        .filter-form, .input-form {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .input-form input, .input-form select, .filter-form input, .filter-form select {
            padding: 8px;
            font-size: 14px;
        }

    .filter-form .btn {
    background-color: #3b82f6;
    color: white;
    border: none;
    padding: 10px 8px;
    margin-top: 36px;
    margin-left: 24px;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s;
}


.btn:hover {
    background-color: #2563eb;
}
    

label {
    display: block;
    margin-top: 10px;
    font-weight: 600;
    color: #374151;
}

input[type="text"],
input[type="date"],
input[type="number"],
select,
textarea {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    background: #f9fafb;
    transition: border-color 0.3s;
}

input:focus,
textarea:focus,
select:focus {
    border-color: #3b82f6;
    outline: none;
}

.btn {
    background-color: #3b82f6;
    color: white;
    border: none;
    padding: 10px 16px;
    margin-top: 15px;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn:hover {
    background-color: #2563eb;
}

.table-controls {
    display: flex;
    justify-content: flex-start;
    margin-top: 20px;
}

.btn-toggle {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 10px 16px;
    font-size: 15px;
    font-weight: 500;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.btn-toggle:hover {
    background-color: #45a049;
    transform: scale(1.02);
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
    background: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

thead {
    background-color: #e5e7eb;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    font-size: 14px;
    border-bottom: 1px solid #e5e7eb;
}

th {
    color: #1f2937;
}

td {
    color: #374151;
}

td a {
    text-decoration: none;
    color: #3b82f6;
    font-weight: bold;
}

td a:hover {
    color: #1d4ed8;
}

th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ccc;
        }
        th {
            background: #f1f1f1;
        }

textarea {
    resize: vertical;
    min-height: 80px;
}

/* Responsive Styling */
@media (max-width: 1024px) {
    body {
        padding: 20px;
        margin-left: 0;
    }

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
        padding: 10px;
    }

    .sidebar h2 {
        font-size: 18px;
        margin-bottom: 15px;
    }

    .table-controls {
        flex-direction: column;
        gap: 10px;
    }
}

@media (max-width: 768px) {
    table, thead, tbody, th, td, tr {
        display: block;
    }

    thead {
        display: none;
    }

    tr {
        margin-bottom: 15px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    td {
        padding: 10px;
        text-align: right;
        position: relative;
    }

    td::before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        top: 10px;
        font-weight: bold;
        color: #6b7280;
        text-align: left;
    }

    .btn, .btn-toggle {
        width: 100%;
        padding: 12px;
        font-size: 16px;
    }

    input[type="text"],
    input[type="date"],
    input[type="number"],
    select,
    textarea {
        font-size: 16px;
    }
}

.card {
            background: white; padding: 20px; border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px;
        }
        .card h3 { margin: 0 0 10px; }
        .dashboard-grid {
            display: flex; gap: 20px; flex-wrap: wrap;
        }
        .dashboard-grid .card {
            flex: 1; min-width: 220px; text-align: center;
        }

        .pagination-controls {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.pagination-controls label {
    font-size: 14px;
}

.pagination-buttons {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-top: 10px;
}

.pagination-buttons button {
    background-color: #3b82f6;
    color: white;
    border: none;
    padding: 6px 10px;
    border-radius: 4px;
    cursor: pointer;
}

footer {
  position: fixed;
  bottom: 0;
  left: 260px; /* Sama dengan lebar sidebar */
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
  padding-bottom: 40px; /* Sesuaikan dengan tinggi footer */
}
</style>
</head>

<body>
<!-- Navbar -->
<div class="navbar">
    <div><strong>🏢 | Badan Pendapatan Daerah Kota Bengkulu</strong></div>
    <div>
        <a href="#">👤 Profil</a>
        </div>
    </div>
</div>
<div class="sidebar">
    <h2>*⚖️ | Laporan BPHTB</h2>
    <a href="http://localhost/my_project/dashbord.php">🏠 Home</a>
    <a href="tampilan_laporan.php?bulan=&tahun=<?= date('Y') ?>">➕ Laporan Notaris & PPAT</a>
    <a href="index.php">➕ Laporan Retribusi Parkir</a>
</div>
</div>

<div class="main">
<h1>Bidang Pengendalian Dan Evaluasi Pendapatan Daerah</h1>
    <div class="dashboard-grid">
    <div class="card">
    <h3>Total Laporan PPAT</h3>
    <p style="font-size: 28px; font-weight: bold; color:rgb(16, 41, 230);"><?= $total_data ?></p>
</div>
<div class="card">
    <h3>Total Setoran Jukir</h3>
    <p style="font-size: 28px; font-weight: bold; color: #27ae60;">
        Rp <?= number_format($total_setoran, 0, ',', '.') ?>
    </p>
</div>
<div class="card">
    <h3>Total Nilai Bphtb</h3>
    <p style="font-size: 28px; font-weight: bold; color:rgb(173, 20, 239);">
        Rp <?= number_format($nilai, 0, ',', '.') ?>
    </p>
</div>
<div class="card">
    <h3>Periode Aktif</h3>
    <p style="font-size: 28px; font-weight: bold; color:rgb(235, 90, 23);"><?= date('F Y') ?></p>
</div>
</div>

<!-- Form Tampilkan -->
<form method="get" class="filter-form">
    <label>Bulan:
        <select name="bulan">
            <option value="">-- Semua --</option>
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>" <?= ($bulan == $i ? 'selected' : '') ?>><?= date('F', mktime(0, 0, 0, $i, 10)) ?></option>
            <?php endfor; ?>
        </select>
    </label>
    <label>Tahun:
        <input type="number" name="tahun" value="<?= $tahun ?>" placeholder="Contoh: 2025">
    </label>
    <button type="submit" class="btn">🔍 Tampilkan</button>
</form>
<button type="button" class="btn" onclick="toggleForm()">➕ Tambah Data</button>

<!-- Kontrol Pagination -->
<div class="pagination-controls">
    <label for="rowsPerPage">Tampilkan:
        <select id="rowsPerPage" onchange="changeRowsPerPage(this.value)">
            <option value="5" selected>5</option>
            <option value="15">15</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="all">Semua</option>
        </select> Data Per-halaman
    </label>

    <div class="pagination-buttons">
        <button onclick="previousPage()">⬅️</button>
        <span id="currentPageDisplay">Halaman 1</span>
        <button onclick="nextPage()">➡️</button>
    </div>
</div>

<!-- Tabel Data -->
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nomor SSPD</th>
            <th>Tanggal Transaksi</th>
            <th>Nama Penerima</th>
            <th>Nama PPAT</th>
            <th>Nilai BPHTB</th>
            <th>Keterangan</th>
        </tr>
    </thead>
   <tbody id="dataTbody"></tbody>
    <?php
    if (mysqli_num_rows($data) > 0) {
        $no = 1;
        while ($row = mysqli_fetch_assoc($data)) {
            echo "<tr>
                <td>{$no}</td>
                <td>{$row['nomor_sspd']}</td>
                <td>{$row['tanggal_transaksi']}</td>
                <td>{$row['nama_penerima']}</td>
                <td>{$row['nama_ppat']}</td>
                <td>Rp " . number_format($row['nilai_bphtb'], 0, ',', '.') . "</td>
                <td>{$row['keterangan']}</td>
            </tr>";
            $no++;
        }
    } else {
        echo "<tr><td colspan='15'>Tidak ada data ditemukan.</td></tr>";
    }
    ?>
</tbody>
</table>

<!-- Form Tambah/Edit -->
<div id="formContainer" style="display: none;">
    <h3><?= $edit ? 'Edit' : 'Tambah' ?> Data BPHTB</h3>
    <form method="post">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
        <label>Nomor SSPD BPHTB:</label>
        <input type="text" name="nomor_sspd" value="<?= $edit['nomor_sspd'] ?? '' ?>" required>

        <label>Tanggal Transaksi:</label>
        <input type="date" name="tanggal_transaksi" value="<?= $edit['tanggal_transaksi'] ?? '' ?>" required>

        <label>Nama Penerima:</label>
        <input type="text" name="nama_penerima" value="<?= $edit['nama_penerima'] ?? '' ?>" required>

        <label>Nama PPAT / Notaris:</label>
        <input type="text" name="nama_ppat" value="<?= $edit['nama_ppat'] ?? '' ?>" required>

        <label for="nilai_bphtb">Nilai BPHTB:</label>
        <input type="text" id="nilai_bphtb" value="<?= isset($edit['nilai_bphtb']) ? number_format($edit['nilai_bphtb'], 0, ',', '.') : '' ?>" required>
        <input type="hidden" name="nilai_bphtb" id="nilai_bphtb_raw" value="<?= $edit['nilai_bphtb'] ?? '' ?>">

        <label>Keterangan:</label>
        <textarea name="keterangan"><?= $edit['keterangan'] ?? '' ?></textarea>

        <button type="submit" class="btn"><?= $edit ? '💾 Update' : '➕ Simpan' ?></button>
    </form>
</div>
<br><br><br><br>
<!-- Footer -->
<footer>
        <p>&copy;    2025 by : Zendi Afrian Saputra, S.H | Semua Hak Cipta Dilindungi Undang - Undang | 
            <a href="https://www.example.com/privacy">Kebijakan Privasi</a> | 
            <a href="https://www.example.com/terms">Syarat & Ketentuan</a>
        </p>
    </footer>

<script>
let sspdRows = Array.from(document.querySelectorAll("tbody tr"));
let currentPage = 0; // mulai dari 0 secara internal
let rowsPerPage = 15;

function paginateSSPDTable() {
    let totalRows = sspdRows.length;
    let totalPages = rowsPerPage === "all" ? 1 : Math.ceil(totalRows / rowsPerPage);

    let start = rowsPerPage === "all" ? 0 : currentPage * rowsPerPage;
    let end = rowsPerPage === "all" ? totalRows : start + rowsPerPage;

    sspdRows.forEach((row, index) => {
        row.style.display = (index >= start && index < end) ? "" : "none";
    });

    document.getElementById("currentPageDisplay").textContent =
        rowsPerPage === "all"
            ? "Semua Data"
            : `Halaman ${currentPage + 1} dari ${totalPages}`;
}

function changeRowsPerPage(value) {
    rowsPerPage = value === "all" ? "all" : parseInt(value);
    currentPage = 0; // reset ke halaman pertama
    paginateSSPDTable();
}

function nextPage() {
    if (rowsPerPage === "all") return;

    const totalPages = Math.ceil(sspdRows.length / rowsPerPage);
    if (currentPage + 1 < totalPages) {
        currentPage++;
        paginateSSPDTable();
    }
}

function previousPage() {
    if (rowsPerPage === "all") return;

    if (currentPage > 0) {
        currentPage--;
        paginateSSPDTable();
    }
}

document.addEventListener("DOMContentLoaded", paginateSSPDTable);

function toggleForm() {
    const formDiv = document.getElementById('formContainer');
    formDiv.style.display = formDiv.style.display === 'none' ? 'block' : 'none';
}

const inputBPHTB = document.getElementById('nilai_bphtb');
  const inputBPHTBRaw = document.getElementById('nilai_bphtb_raw');

  inputBPHTB.addEventListener('input', function () {
    // Ambil angka tanpa karakter non-digit
    let angka = this.value.replace(/\D/g, '');

    // Simpan nilai mentah ke input hidden untuk dikirim ke PHP
    inputBPHTBRaw.value = angka;

    // Format angka menjadi ribuan dengan titik
    this.value = formatAngka(angka);
  });

  function formatAngka(angka) {
    return angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }
</script>

</body>
</html>
