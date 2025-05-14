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

// Ambil total data laporan PPAT (laporan_notaris_ppat)
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_notaris_ppat");
$total_data = mysqli_fetch_assoc($total_query)['total'];

// Proses tambah data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_data'])) {
    $nama = htmlspecialchars($_POST['nama_notaris']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $nomor_surat = htmlspecialchars($_POST['nomor_surat']);
    $tanggal_disposisi = $_POST['tanggal_disposisi'];
    $bulan = $_POST['laporan_bulan'];
    $tahun = $_POST['laporan_tahun'];

    $ajb = (int) $_POST['ajb'];
    $apht = (int) $_POST['apht'];
    $skmht = (int) $_POST['skmht'];
    $tukar = (int) $_POST['akta_tukar_menukar'];
    $hibah = (int) $_POST['hibah'];
    $bphtb = (int) $_POST['bphtb'];
    $jumlah = $ajb + $apht + $skmht + $tukar + $hibah + $bphtb;
    $keterangan = htmlspecialchars($_POST['keterangan']);

    $sql_insert = "INSERT INTO laporan_notaris_ppat 
        (nama_notaris, alamat, nomor_surat, tanggal_disposisi, laporan_bulan, laporan_tahun, ajb, apht, skmht, akta_tukar_menukar, hibah, bphtb, jumlah_laporan, keterangan)
        VALUES ('$nama', '$alamat', '$nomor_surat', '$tanggal_disposisi', '$bulan', '$tahun', $ajb, $apht, $skmht, $tukar, $hibah, $bphtb, $jumlah, '$keterangan')";

    mysqli_query($conn, $sql_insert);
}

// Ambil filter jika ada
$filter_bulan = $_GET['bulan'] ?? '';
$filter_tahun = $_GET['tahun'] ?? '';

// Query data
$sql = "SELECT * FROM laporan_notaris_ppat WHERE 1=1";
if ($filter_bulan !== '') {
    $sql .= " AND laporan_bulan = '$filter_bulan'";
}
if ($filter_tahun !== '') {
    $sql .= " AND laporan_tahun = '$filter_tahun'";
}
$sql .= " ORDER BY tanggal_disposisi DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles.css">
    <title>NOTARIS_PPAT.COM</title>
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
        font-size: 17px;
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
        .main {
            margin-left: 240px;
            padding: 30px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card h3 {
            margin: 0 0 10px;
        }
        .dashboard-grid {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .dashboard-grid .card {
            flex: 1;
            min-width: 220px;
            text-align: center;
        }

        body {
        font-family: Arial, sans-serif;
        background: #f4f4f4;
        padding: 20px;
        margin-left: 0; /* Menghilangkan margin kiri */
    }

.container {
    max-width: 1100px;
    margin: 0; /* Menghilangkan margin tengah */
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
    margin-bottom: 20px;
}

table {
    width: 80% !important;
    margin-top: 15px !important;
    margin-left: 265px; /* geser ke kanan */
    border-collapse: collapse !important;
    font-size: 14px !important;
}

th, td {
    padding: 10px !important;
    text-align: center !important;
    border: 1px solid #ccc !important;
}

th {
    background: #f1f1f1;
}

.filter-form, .input-form {
    margin-bottom: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.input-form input, .input-form select, .filter-form input, .filter-form select {
    padding: 6px;
    font-size: 14px;
}

button {
    padding: 6px 12px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background: #0056b3;
}

.text-left {
    text-align: left;
}

.form-group {
    margin-bottom: 10px;
    display: flex;
    flex-direction: column;
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
    <h2>*🧑‍⚖️ | Laporan NOT/PPAT</h2>
    <a href="http://localhost/my_project/dashbord.php">🏠 Home</a>
    <a href="http://localhost/my_project/index.php">➕ Laporan Retribusi</a>
    <a href="http://localhost/my_project/bphtb_data.php">➕ Laporan BPHTB</a>
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
<div class="container">
    <!-- Form Tambah Data -->
    <form method="POST" class="input-form" id="formTambahData" style="display: none;">
        <input type="hidden" name="tambah_data" value="1">
        <div class="form-group">
            <label>Nama Notaris</label>
            <input type="text" name="nama_notaris" required>
        </div>
        <div class="form-group">
            <label>Alamat</label>
            <input type="text" name="alamat" required>
        </div>
        <div class="form-group">
            <label>No. Surat</label>
            <input type="text" name="nomor_surat" required>
        </div>
        <div class="form-group">
            <label>Tanggal Disposisi</label>
            <input type="date" name="tanggal_disposisi" required>
        </div>
        <div class="form-group">
            <label>Bulan</label>
            <select name="laporan_bulan" required>
                <?php
                foreach (["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"] as $b) {
                    echo "<option value='$b'>$b</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label>Tahun</label>
            <input type="number" name="laporan_tahun" required>
        </div>
        <div class="form-group"><label>AJB</label><input type="number" name="ajb" value="0"></div>
        <div class="form-group"><label>APHT</label><input type="number" name="apht" value="0"></div>
        <div class="form-group"><label>SKMHT</label><input type="number" name="skmht" value="0"></div>
        <div class="form-group"><label>Tukar Menukar</label><input type="number" name="akta_tukar_menukar" value="0"></div>
        <div class="form-group"><label>Hibah</label><input type="number" name="hibah" value="0"></div>
        <div class="form-group"><label>BPHTB</label><input type="number" name="bphtb" value="0"></div>
        <div class="form-group"><label>Keterangan</label><input type="text" name="keterangan"></div>
        <div class="form-group" style="justify-content: end;"><label>&nbsp;</label><button type="submit">Simpan Data</button></div>
    </form>
</div>
<br>

    <!-- Form Filter -->
    <form method="GET" class="filter-form">
        <label for="bulan">Bulan:</label>
        <select name="bulan" id="bulan">
            <option value="">-- Semua --</option>
            <?php
            foreach (["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"] as $b) {
                $selected = ($filter_bulan == $b) ? "selected" : "";
                echo "<option value='$b' $selected>$b</option>";
            }
            ?>
        </select>

        <label for="tahun">Tahun:</label>
        <input type="number" name="tahun" id="tahun" value="<?= htmlspecialchars($filter_tahun) ?>" placeholder="Contoh: 2025">

        <button type="submit">🔍 Tampilkan</button>
    </form>

<!-- Kontrol Pagination -->
<div class="pagination-controls">
    <label for="rowsPerPage">Tampilkan:
        <select id="rowsPerPage" onchange="changeRowsPerPage(this.value)">
            <option value="5" selected>5</option>
            <option value="15">15</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="all">--Semua--</option>
        </select> Data Per-halaman
    </label>

    <div class="pagination-buttons">
        <button onclick="previousPage()">⬅️</button>
        <span id="currentPageDisplay">Halaman 1</span>
        <button onclick="nextPage()">➡️</button>
    </div>
</div>

<h2>Data Laporan Notaris & PPAT</h2>
<!-- Tombol Tambah Data -->
<button id="toggleFormBtn">➕ Tambah Data</button>
</div>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Notaris</th>
            <th>Alamat</th>
            <th>Nomor Surat</th>
            <th>Tanggal Disposisi</th>
            <th>Bulan</th>
            <th>Tahun</th>
            <th>AJB</th>
            <th>APHT</th>
            <th>SKMHT</th>
            <th>Tukar Menukar</th>
            <th>Hibah</th>
            <th>BPHTB</th>
            <th>Jumlah Laporan</th>
            <th>Keterangan</th>
        </tr>
    </thead>

    <tbody id="dataTbody">
    <?php
    if (mysqli_num_rows($result) > 0) {
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                <td>{$no}</td>
                <td class='text-left'>{$row['nama_notaris']}</td>
                <td class='text-left'>{$row['alamat']}</td>
                <td>{$row['nomor_surat']}</td>
                <td>{$row['tanggal_disposisi']}</td>
                <td>{$row['laporan_bulan']}</td>
                <td>{$row['laporan_tahun']}</td>
                <td>{$row['ajb']}</td>
                <td>{$row['apht']}</td>
                <td>{$row['skmht']}</td>
                <td>{$row['akta_tukar_menukar']}</td>
                <td>{$row['hibah']}</td>
                <td>{$row['bphtb']}</td>
                <td>{$row['jumlah_laporan']}</td>
                <td class='text-left'>{$row['keterangan']}</td>
            </tr>";
            $no++;
        }
    } else {
        echo "<tr><td colspan='15'>Tidak ada data.</td></tr>";
    }
    ?>
    </tbody>
</table>
<br>
    <br>
        <br>
<!-- Footer -->
<footer>
        <p>&copy;    2025 by : Zendi Afrian Saputra, S.H | Semua Hak Cipta Dilindungi Undang - Undang | 
            <a href="https://www.example.com/privacy">Kebijakan Privasi</a> | 
            <a href="https://www.example.com/terms">Syarat & Ketentuan</a>
        </p>
    </footer>

<script>
let allRows = Array.from(document.querySelectorAll("tbody tr"));
let currentPage = 1;
let rowsPerPage = 15;

function paginateTable() {
    let totalRows = allRows.length;
    let totalPages = Math.ceil(totalRows / rowsPerPage);
    let start = (currentPage - 1) * rowsPerPage;
    let end = rowsPerPage === "all" ? totalRows : start + parseInt(rowsPerPage);

    allRows.forEach((row, index) => {
        row.style.display = (rowsPerPage === "all" || (index >= start && index < end)) ? "" : "none";
    });

    document.getElementById("currentPageDisplay").textContent =
        rowsPerPage === "all" ? "Semua Data" : `Halaman ${currentPage} dari ${totalPages}`;
}

function changeRowsPerPage(value) {
    rowsPerPage = value === "all" ? "all" : parseInt(value);
    currentPage = 1;
    paginateTable();
}

function nextPage() {
    if (rowsPerPage !== "all" && currentPage < Math.ceil(allRows.length / rowsPerPage)) {
        currentPage++;
        paginateTable();
    }
}

function previousPage() {
    if (rowsPerPage !== "all" && currentPage > 1) {
        currentPage--;
        paginateTable();
    }
}

// Inisialisasi saat load
document.addEventListener("DOMContentLoaded", paginateTable);

document.getElementById("toggleFormBtn").addEventListener("click", function () {
    const form = document.getElementById("formTambahData");
    if (form.style.display === "none" || form.style.display === "") {
        form.style.display = "block";
    } else {
        form.style.display = "none";
    }
});

</script>

</body>
</html>
