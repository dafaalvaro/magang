<?php
session_start();
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (ob_get_level() == 0) {
        ob_start();
    }

    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    // Validasi input
    if ($password !== $confirm_password) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Password tidak cocok!',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
              </script>";
    } else {
        // Cek apakah username sudah ada
        $stmt = $pdo->prepare('SELECT username FROM users WHERE username = ?');
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Username sudah digunakan!',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
                  </script>";
        } else {
            // Hash password dan simpan user baru
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');

            try {
                $stmt->execute([$username, $hashed_password, 'user']);
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Akun berhasil dibuat! Silahkan login.',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = 'index.php';
                            });
                        });
                      </script>";
            } catch (PDOException $e) {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Terjadi kesalahan saat membuat akun.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                      </script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
        background-image: url('bg.jpg');
        background-size: cover;
        background-position: center;
    }

    .register-container {
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

    .register-container:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
    }

    .register-container h2 {
        margin-bottom: 20px;
        font-size: 24px;
        color: #fff;
    }

    .register-container form {
        display: flex;
        flex-direction: column;
    }

    .register-container input[type="text"],
    .register-container input[type="password"] {
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

    .register-container input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .register-container input:focus {
        border-color: rgba(255, 255, 255, 0.7);
        background-color: rgba(255, 255, 255, 0.2);
        outline: none;
    }

    .register-container button[type="submit"] {
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

    .register-container button[type="submit"]:hover {
        background-color: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }

    .login-link {
        margin-top: 15px;
        font-size: 14px;
        color: rgba(255, 255, 255, 0.7);
    }

    .login-link a {
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .login-link a:hover {
        color: #fff;
    }
    </style>
</head>

<body>
    <div class="register-container">
        <h2>Registrasi Akun</h2>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Masukkan Username" required>
            <input type="password" name="password" placeholder="Masukkan Password" required>
            <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required>
            <button type="submit">Daftar</button>
            <div class="login-link">
                Sudah punya akun? <a href="index.php">Login</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
