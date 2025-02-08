<?php
session_start(); // Tambahkan ini di paling atas!
// Include database connection
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Prevent unexpected output
    if (ob_get_level() == 0) {
        ob_start();
    }

    // Ambil username dan password dari form
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Cek username di database
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verifikasi password
    if ($user && password_verify($password, $user['password'])) { // Menggunakan password_verify()
        // Login berhasil, simpan informasi pengguna di session
        $_SESSION['id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['loggedin'] = true; // Tambahkan ini

        // Redirect berdasarkan role pengguna
        if ($user['role'] === 'admin') {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'Login Berhasil!',
                            text: 'Anda akan diarahkan ke dashboard.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'dashboard.php';
                        });
                    });
                  </script>";
        } else {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'Login Berhasil!',
                            text: 'Anda akan diarahkan ke halaman gambar.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'edit.php';
                        });
                    });
                  </script>";
        }
    } else {
        // Login gagal
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Login Gagal!',
                        text: 'Username atau Password salah, silakan coba lagi.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 0;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background-image: url('pexels-pixabay-459728.jpg');
        background-size: cover;
        background-position: center;
    }

    .container {
        display: flex;
        align-items: stretch;
        /* Make containers stretch to the same height */
        justify-content: center;
        width: 100%;
        gap: 0;
        /* No gap between containers */
    }

    .login-container {
        width: 350px;
        padding: 40px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        backdrop-filter: blur(10px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .login-container:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
    }

    .login-container h2 {
        margin-bottom: 20px;
        font-size: 24px;
        color: #fff;
    }

    .login-container form {
        display: flex;
        flex-direction: column;
    }

    .login-container input[type="text"],
    .login-container input[type="password"] {
        width: 90%;
        height: 50px;
        margin-bottom: 20px;
        padding: 10px 15px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 25px;
        font-size: 16px;
        background-color: rgba(255, 255, 255, 0.1);
        color: #fff;
        transition: border-color 0.3s ease, background-color 0.3s ease;
    }

    .login-container input[type="text"]::placeholder,
    .login-container input[type="password"]::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .login-container input[type="text"]:focus,
    .login-container input[type="password"]:focus {
        border-color: rgba(255, 255, 255, 0.7);
        background-color: rgba(255, 255, 255, 0.2);
        outline: none;
    }

    .login-container button[type="submit"] {
        width: 100%;
        height: 50px;
        background-color: rgba(255, 255, 255, 0.2);
        color: #fff;
        border: none;
        border-radius: 25px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .login-container button[type="submit"]:hover {
        background-color: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-container">
            <h2>Silahkan Masuk</h2>
            <form method="POST" action="">
                <input type="text" name="username" placeholder="Masukkan Username" required>
                <input type="password" name="password" placeholder="Masukkan Password" required>
                <button type="submit">Login</button>
            </form>
        </div>

        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>