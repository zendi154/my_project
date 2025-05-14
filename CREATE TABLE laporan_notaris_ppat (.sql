CREATE TABLE laporan_notaris (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Informasi Notaris
    nama_notaris VARCHAR(100),
    alamat TEXT,
    nomor_surat VARCHAR(100),
    tanggal_disposisi DATE,

    -- Periode Laporan
    laporan_bulan VARCHAR(20),     -- Contoh: 'Januari'
    laporan_tahun YEAR,            -- Contoh: 2025

    -- Jumlah Laporan per Jenis
    ajb INT DEFAULT 0,
    apht INT DEFAULT 0,
    skmht INT DEFAULT 0,
    akta_tukar_menukar INT DEFAULT 0,
    hibah INT DEFAULT 0,
    bphtb INT DEFAULT 0,

    -- Kolom Jumlah Otomatis (GENERATED)
    jumlah_laporan INT GENERATED ALWAYS AS (
        ajb + apht + skmht + akta_tukar_menukar + hibah + bphtb
    ) STORED,

    -- Catatan Tambahan
    keterangan TEXT
);
