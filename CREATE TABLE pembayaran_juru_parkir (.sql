CREATE TABLE pembayaran_juru_parkir (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100),
    lokasi_parkir VARCHAR(100),
    zona_parkir VARCHAR(50),
    titik_parkir VARCHAR(100),
    target_bulan INT,

    jan INT DEFAULT 0,
    feb INT DEFAULT 0,
    mar INT DEFAULT 0,
    apr INT DEFAULT 0,
    mei INT DEFAULT 0,
    jun INT DEFAULT 0,
    jul INT DEFAULT 0,
    agt INT DEFAULT 0,
    sep INT DEFAULT 0,
    okt INT DEFAULT 0,
    nov INT DEFAULT 0,
    des INT DEFAULT 0,

    total_setoran INT GENERATED ALWAYS AS (
        jan + feb + mar + apr + mei + jun +
        jul + agt + sep + okt + nov + des
    ) STORED
);
