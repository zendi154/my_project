<?php
session_start(); // Pastikan session dimulai

// Koneksi ke database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "user_system";

$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

function logUnknownUser($conn, $identifier) {
    // Menyimpan log login gagal
    $stmt = $conn->prepare("INSERT INTO login_logs (identifier, status) VALUES (?, 'gagal')");
    $stmt->bind_param("s", $identifier);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil data dari form
    $identifier = trim($_POST["identifier"]);
    $password = $_POST["password"];

    // Pastikan identifier tidak kosong
    if (empty($identifier) || empty($password)) {
        echo "<p style='color:red'>Username/email atau password tidak boleh kosong.</p>";
    } else {
        // Query untuk mencari pengguna berdasarkan username atau email
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            // Mengambil hasil jika ditemukan
            $stmt->bind_result($id, $username, $hashed_password);
            $stmt->fetch();

            // Verifikasi password
            if (password_verify($password, $hashed_password)) {
                // Set session data
                $_SESSION["user_id"] = $id;
                $_SESSION["username"] = $username;

                // Log login sukses
                $log = $conn->prepare("INSERT INTO login_logs (identifier, status) VALUES (?, 'sukses')");
                $log->bind_param("s", $identifier);
                $log->execute();
                $log->close();

                // Arahkan ke dashboard
                header("Location: http://localhost/my_project/dashbord.php");
                    exit;
            } else {
                // Jika password salah
                echo "<p style='color:red'>Password salah.</p>";
            }
        } else {
            // Pengguna tidak ditemukan
            echo "<p style='color:red'>Pengguna tidak dikenal. <a href='register.php'>Daftar di sini</a>.</p>";
            logUnknownUser($conn, $identifier);
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>LOGIN</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #9face6);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        form {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            max-width: 400px;
            width: 100%;
        }

        form h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px 15px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #5b9bd5;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #5b9bd5;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #407ec9;
        }

        .link {
            text-align: center;
            margin-top: 15px;
        }

        .link a {
            color: #333;
            text-decoration: none;
            font-size: 14px;
        }

        .link a:hover {
            text-decoration: underline;
        }

        .error-message {
            background-color: #ffe6e6;
            color: #c0392b;
            border-left: 4px solid #e74c3c;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            font-size: 14px;
        }

        .error-message p {
            margin: 0;
        }
    </style>
</head>

<body>
    <form method="post">
        <h2>Login_Form</h2>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <input type="text" name="identifier" placeholder="Username atau Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>

        <div class="link">
            <a href="register.php" onclick="goToRegister()">Belum punya akun? Daftar</a>
        </div>
    </form>

    <script>
        function goToRegister() {
            window.location.href = "dashbord.php";
        }
    </script>
</body>
</html>
