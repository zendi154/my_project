<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "user_system";

$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    // Validasi password cocok
    if ($password !== $confirm_password) {
        $errors[] = "Password dan konfirmasi tidak cocok.";
    }

    // Cek jika username atau email sudah digunakan
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    if (!$check) {
        die("Query error: " . $conn->error); // Tambahan untuk debug error
    }
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $errors[] = "Username atau email sudah digunakan.";
    }

    $check->close();

    // Simpan ke database jika tidak ada error
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if (!$stmt) {
            die("Query error: " . $conn->error); // Tambahan untuk debug error
        }
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            echo "✅ Registrasi berhasil. <a href='login.php'>Login di sini</a>";
        } else {
            $errors[] = "❌ Gagal menyimpan data ke database.";
        }

        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
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
        input[type="email"],
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
        <h2>Register_Form</h2>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email Aktif" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Ulangi Password" required>
        <button type="submit">Daftar</button>

        <div class="link">
            <a href="login.php" onclick="goToLogin()">Sudah punya akun? Login</a>
        </div>
    </form>

    <script>
        function goToLogin() {
            window.location.href = "login.php";
        }
    </script>
</body>
</html>
