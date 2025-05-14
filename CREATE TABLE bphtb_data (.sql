CREATE TABLE bphtb_data (
    id INT AUTO_INCREMENT PRIMARY KEY,           -- ID unik data
    nomor_sspd VARCHAR(50) NOT NULL,             -- Nomor SSPD BPHTB
    tanggal_transaksi DATE NOT NULL,             -- Tanggal transaksi
    nama_penerima VARCHAR(100) NOT NULL,         -- Nama penerima hak
    nama_ppat VARCHAR(100) NOT NULL,             -- Nama PPAT/Notaris
    nilai_bphtb BIGINT NOT NULL,                 -- Nilai BPHTB
    keterangan TEXT                               -- Keterangan tambahan (opsional)
);
