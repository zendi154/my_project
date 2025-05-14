<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
?>

<?php
include 'config.php';

// Proses Simpan atau Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? '';
    $nama = $_POST['nama'];
    $lokasi = $_POST['lokasi_parkir'];
    $zona = $_POST['zona_parkir'];
    $titik = $_POST['titik_parkir'];
    $target = $_POST['target_bulan'];

    $bulan = ['jan','feb','mar','apr','mei','jun','jul','agt','sep','okt','nov','des'];
    $setoran = [];
    foreach ($bulan as $b) {
        $setoran[$b] = isset($_POST[$b]) ? $_POST[$b] : 0;
    }

    function getAvailableId($conn) {
        $ids = [];
        $result = mysqli_query($conn, "SELECT id FROM pembayaran_juru_parkir ORDER BY id ASC");
        while ($row = mysqli_fetch_assoc($result)) {
            $ids[] = (int)$row['id'];
        }
    
        // Cari ID terkecil yang belum dipakai
        $i = 1;
        while (in_array($i, $ids)) {
            $i++;
        }
        return $i;
    }    

    if ($id) {
        // Update data
        $query = "UPDATE pembayaran_juru_parkir SET 
            nama='$nama', lokasi_parkir='$lokasi', zona_parkir='$zona', titik_parkir='$titik', target_bulan='$target',
            jan='{$setoran['jan']}', feb='{$setoran['feb']}', mar='{$setoran['mar']}', apr='{$setoran['apr']}',
            mei='{$setoran['mei']}', jun='{$setoran['jun']}', jul='{$setoran['jul']}', agt='{$setoran['agt']}',
            sep='{$setoran['sep']}', okt='{$setoran['okt']}', nov='{$setoran['nov']}', des='{$setoran['des']}'
            WHERE id=$id";
    } else {
       // Insert data baru dengan ID manual
        $new_id = getAvailableId($conn);
        $query = "INSERT INTO pembayaran_juru_parkir (
            id, nama, lokasi_parkir, zona_parkir, titik_parkir, target_bulan,
            jan, feb, mar, apr, mei, jun, jul, agt, sep, okt, nov, des
        ) VALUES (
             $new_id, '$nama', '$lokasi', '$zona', '$titik', '$target',
            '{$setoran['jan']}', '{$setoran['feb']}', '{$setoran['mar']}', '{$setoran['apr']}',
            '{$setoran['mei']}', '{$setoran['jun']}', '{$setoran['jul']}', '{$setoran['agt']}',
            '{$setoran['sep']}', '{$setoran['okt']}', '{$setoran['nov']}', '{$setoran['des']}'
        )";
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data berhasil disimpan'); window.location.href='index.php';</script>";
        exit;
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($conn);
    }
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM pembayaran_juru_parkir WHERE id=$id");
    echo "<script>alert('Data berhasil dihapus'); window.location.href='index.php';</script>";
    exit;
}

// Proses Ambil Data untuk Edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM pembayaran_juru_parkir WHERE id=$id");
    $edit_data = mysqli_fetch_assoc($result);
}

// Total setoran Jukir
$setoran_query = mysqli_query($conn, "SELECT SUM(total_setoran) AS total_setoran FROM pembayaran_juru_parkir");
$total_setoran = mysqli_fetch_assoc($setoran_query)['total_setoran'];

// Total nilai BPHTB
$nilai_query = mysqli_query($conn, "SELECT SUM(nilai_bphtb) AS total_nilai FROM bphtb_data");
$nilai = mysqli_fetch_assoc($nilai_query)['total_nilai'];

// Ambil total data laporan PPAT (laporan_notaris_ppat)
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_notaris_ppat");
$total_data = mysqli_fetch_assoc($total_query)['total'];


// Ambil semua data dari tabel
$sql = "SELECT * FROM pembayaran_juru_parkir";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>RETRIBUSI_PARKIR.COM</title>
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
    height: 52px;
}

    /* Link dalam navbar */
    .navbar a {
    text-decoration: none;
    color: blue;
    margin-right: 12px;
    }

    /* Sidebar */
    .sidebar {
    width: 246px;
    position: fixed;
    top: 60px; /* diberi jarak supaya tidak tertutup navbar */
    left: 0;
    height: 100%;
    background-color:rgb(169, 167, 167);
    padding: 10px;
    box-shadow: 2px 0 5px rgba(244, 233, 233, 0.91);
    }

        .sidebar h2 {
        margin-bottom: 20px;
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
            margin-bottom: 12px;
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

         /* Reset dasar */
         * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f7f9fc;
    padding: 30px 10px; /* Mengurangi padding kiri dan kanan */
    color: #333;
    margin: 0; /* Menghilangkan margin default */
}

h2 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 24px;
    color: #2c3e50;
}

table {
    width: 100%;
    margin-left: 0; /* Menghilangkan margin kiri */
    border-collapse: collapse;
    background-color: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

thead {
    background-color: #3498db;
    color: white;
}

th, td {
    padding: 10px 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

tbody tr:hover {
    background-color: #f1f1f1;
}

td strong {
    color: #27ae60;
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
        background-color: #fff;
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 5px;
    }

    td {
        text-align: left;
        padding: 8px 10px;
        position: relative;
    }

    td::before {
        content: attr(data-label);
        font-weight: bold;
        display: block;
        margin-bottom: 5px;
        color: #555;
    }
}

form {
    background-color: #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 40px;
    max-width: 800px;
    margin-left: 0; /* Menggeser form ke kiri */
    margin-right: auto;
}

label {
    display: block;
    font-weight: 600;
    margin-top: 15px;
    margin-bottom: 5px;
}

input[type="text"],
input[type="number"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    box-sizing: border-box;
    font-size: 14px;
}

.bulan {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.bulan label {
    font-weight: 500;
}

button[type="submit"] {
    background-color: #007bff;
    color: #fff;
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 20px;
    display: block;
    width: 100%;
    transition: background-color 0.3s ease;
}

button[type="submit"]:hover {
    background-color: #0056b3;
}

#toggleFormButton {
    display: inline-block;
    margin: 20px 0;
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 600;
    background-color: #17a2b8;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

#toggleFormButton:hover {
    background-color: #138496;
}

footer {
  position: fixed;
  bottom: 0;
  left: 246px; /* Sama dengan lebar sidebar */
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
        </div>
    </div>
</div>
<div class="sidebar">
    <h2>*🚗 | Retribusi Parkir</h2>
    <a href="http://localhost/my_project/dashbord.php">🏠 Home</a>
    <a href="tampilan_laporan.php?bulan=&tahun=<?= date('Y') ?>">➕ Laporan NOT & PPAT</a>
    <a href="http://localhost/my_project/bphtb_data.php">➕ Laporan BPHTB</a>
</div>

<div class="main">
<h1>Bidang Pengendalian Dan Evaluasi Pendapatan Daerah</h1><br>
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

<div id="formInput" style="display: none;">
    <form method="POST">
       
<h2><?= $edit_data ? 'Edit Data' : 'Form Input Data' ?> Juru Parkir</h2>
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
    
        <label>Nama</label><input type="text" name="nama" required value="<?= $edit_data['nama'] ?? '' ?>">

        <label>Lokasi Parkir</label><input type="text" name="lokasi_parkir" required value="<?= $edit_data['lokasi_parkir'] ?? '' ?>">

        <label>Zona Parkir</label><input type="text" name="zona_parkir" required value="<?= $edit_data['zona_parkir'] ?? '' ?>">

        <label>Titik Parkir</label><input type="text" name="titik_parkir" required value="<?= $edit_data['titik_parkir'] ?? '' ?>">

        <!-- Target per Bulan -->
        <label for="target_bulan_display">Target per Bulan</label>
        <input type="text" id="target_bulan_display" value="<?= isset($edit_data['target_bulan']) ? number_format($edit_data['target_bulan'], 0, ',', '.') : '' ?>" required>
        <input type="hidden" name="target_bulan" id="target_bulan">

        <!-- Setoran per Bulan -->
        <h4>Setoran per Bulan</h4>
        <div class="bulan">
        <?php
        $bulan_full = ['jan'=>'Januari','feb'=>'Februari','mar'=>'Maret','apr'=>'April','mei'=>'Mei','jun'=>'Juni',
                'jul'=>'Juli','agt'=>'Agustus','sep'=>'September','okt'=>'Oktober','nov'=>'November','des'=>'Desember'];
        foreach ($bulan_full as $key => $label) {
        $value = $edit_data[$key] ?? 0;
        $formatted = number_format($value, 0, ',', '.');
        echo "
        <div>
            <label>$label</label>
            <input type='text' id='{$key}_display' value='{$formatted}'>
            <input type='hidden' name='{$key}' id='{$key}' value='{$value}'>
        </div>";
    }
    ?>
</div>
        <br>
        <button type="submit"><?= $edit_data ? 'Update' : 'Simpan' ?></button>
        </form>
    </div>
       
    <h2>Data Pembayaran Retribusi Parkir Tepi Jalan Umum</h2>
    <table>
    <button id="toggleFormButton" onclick="toggleFormulir()">➕ Tambah Data</button>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Jukir</th>
                <th>Lokasi Parkir</th>
                <th>Zona</th>
                <th>Titik</th>
                <th>Target/Bulan</th>
                <?php
                    $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
                    foreach ($bulan as $b) echo "<th>$b</th>";
                ?>
                <th>Total Setoran</th>
            </tr>
        </thead>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
    <label>
        Tampilkan:
        <select onchange="changeRowsPerPage(this.value)">
            <option value="5" selected>5</option>
            <option value="15">15</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="all">--Semua--</option>
        </select> Data Per-halaman
    </label>

    <div style="display: flex; align-items: center; gap: 10px;">
        <button onclick="previousPage()">⬅️</button>
        <span id="currentPageDisplay">Halaman 1 dari 1</span>
        <button onclick="nextPage()">➡️</button>
    </div>
</div>

<tbody id="dataTbody"></tbody>
<?php
$dataArray = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $dataArray[] = $row;
    }

    foreach ($dataArray as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
        echo "<td>" . htmlspecialchars($row['lokasi_parkir']) . "</td>";
        echo "<td>" . htmlspecialchars($row['zona_parkir']) . "</td>";
        echo "<td>" . htmlspecialchars($row['titik_parkir']) . "</td>";
        echo "<td>" . number_format($row['target_bulan']) . "</td>";

        foreach ($bulan as $b) {
            $kolom = strtolower($b);
            echo "<td>" . number_format($row[$kolom]) . "</td>";
        }

        echo "<td><strong>" . number_format($row['total_setoran']) . "</strong></td>";
        echo "</tr>";
    }
} else {
    $totalKolom = 6 + count($bulan) + 1;
    echo "<tr><td colspan='{$totalKolom}'>Tidak ada data ditemukan.</td></tr>";
}
?>
</tbody>
    </table>

    <!-- Footer -->
<footer>
        <p>&copy;    2025 by : Zendi Afrian Saputra, S.H | Semua Hak Cipta Dilindungi Undang - Undang | 
            <a href="https://www.example.com/privacy">Kebijakan Privasi</a> | 
            <a href="https://www.example.com/terms">Syarat & Ketentuan</a>
        </p>
    </footer>

    <script>
    function toggleFormulir() {
    const form = document.getElementById("formInput");
    const button = document.getElementById("toggleFormButton");

    if (form.style.display === "none" || form.style.display === "") {
        form.style.display = "block";
        button.textContent = "Sembunyikan Formulir";
    } else {
        form.style.display = "none";
        button.textContent = "➕ Tambah Data";
    }
}

// Saat halaman dimuat: form disembunyikan, tombol “Tambah Data”
window.onload = () => {
    const form = document.getElementById("formInput");
    const button = document.getElementById("toggleFormButton");

    if (form && button) {
        form.style.display = "none";
        button.textContent = "➕ Tambah Data";
    }
};

let allRows;
let currentPage = 1;
let rowsPerPage = 15;

function paginateTable() {
    allRows = Array.from(document.querySelectorAll("tbody tr"));
    const totalRows = allRows.length;
    const totalPages = rowsPerPage === "all" ? 1 : Math.ceil(totalRows / rowsPerPage);
    const start = (currentPage - 1) * rowsPerPage;
    const end = rowsPerPage === "all" ? totalRows : start + parseInt(rowsPerPage);

    allRows.forEach((row, index) => {
        row.style.display = (rowsPerPage === "all" || (index >= start && index < end)) ? "" : "none";
    });

    const pageInfo = document.getElementById("currentPageDisplay");
    if (pageInfo) {
        pageInfo.textContent = rowsPerPage === "all"
            ? `Menampilkan semua data (${totalRows})`
            : `Halaman ${currentPage} dari ${totalPages}`;
    }
}

function changeRowsPerPage(value) {
    rowsPerPage = value === "all" ? "all" : parseInt(value);
    currentPage = 1;
    paginateTable();
}

function nextPage() {
    const totalPages = rowsPerPage === "all" ? 1 : Math.ceil(allRows.length / rowsPerPage);
    if (rowsPerPage !== "all" && currentPage < totalPages) {
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

document.addEventListener("DOMContentLoaded", paginateTable);

function formatRibuan(angka) {
    return angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function handleRupiahInput(displayId, hiddenId) {
    const inputDisplay = document.getElementById(displayId);
    const inputHidden = document.getElementById(hiddenId);

    inputDisplay.addEventListener('input', function () {
        let angka = this.value.replace(/\D/g, '');
        inputHidden.value = angka;
        this.value = formatRibuan(angka);
    });
}

// Inisialisasi untuk target
handleRupiahInput('target_bulan_display', 'target_bulan');

// Inisialisasi untuk tiap bulan
<?php foreach ($bulan_full as $key => $label): ?>
handleRupiahInput('<?= $key ?>_display', '<?= $key ?>');
<?php endforeach; ?>
</script>

</body>
</html>
