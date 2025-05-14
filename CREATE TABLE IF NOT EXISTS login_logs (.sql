CREATE TABLE IF NOT EXISTS login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(100) NOT NULL,
    status ENUM('gagal', 'sukses') DEFAULT 'gagal',
    waktu_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
