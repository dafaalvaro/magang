<?php
session_start();
// Cek apakah user sudah login
// Pastikan ada session role
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$showError = false;
$errorMessage = "";

// Direktori penyimpanan file
$uploadDirectory = "uploads/";

// Ambil semua file gambar dari direktori uploads
$imageFiles = glob($uploadDirectory . "*.{png,jpg,jpeg}", GLOB_BRACE);

// Jika tidak ada file gambar di direktori
if (empty($imageFiles)) {
    $showError = true;
    $errorMessage = "Tidak ada gambar tersedia. Silakan hubungi admin untuk upload gambar!";
} else {
    // Jika ada parameter file di URL
    if (isset($_GET['file'])) {
        $file_name = urldecode($_GET['file']);
        $file_path = $uploadDirectory . $file_name;

        // Validasi apakah file ada dan merupakan gambar
        if (!file_exists($file_path)) {
            $showError = true;
            $errorMessage = "File gambar tidak ditemukan!";
        } elseif (!in_array(strtolower(pathinfo($file_path, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg'])) {
            $showError = true;
            $errorMessage = "Format file tidak valid! Hanya file PNG, JPG, dan JPEG yang diizinkan.";
        }
    } else {
        // Jika tidak ada parameter file di URL, tampilkan file pertama (jika hanya ada 1 file)
        if (count($imageFiles) === 1) {
            $file_path = $imageFiles[0];
        } else {
            // Jika lebih dari 1 file, tampilkan dropdown untuk memilih (hanya untuk selain admin)
            if (!$isAdmin) {
                $showError = false; // Tidak error, tampilkan dropdown
            } else {
                $showError = true;
                $errorMessage = "Tidak ada file gambar yang dipilih!";
            }
        }
    }
}

// Tambahkan pengecekan khusus untuk tombol kembali dashboard
if (isset($_GET['action']) && $_GET['action'] == 'dashboard') {
    if (!$isAdmin) {
        $showError = true;
        $errorMessage = "Dashboard hanya tersedia untuk admin!";
    }
}
// Proses logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy(); // Hapus session
    header("Location: index.php"); // Redirect ke halaman login
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Gambar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
    body {
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        font-family: 'Poppins', sans-serif;
        background-image: url('pltut.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;
    }

    .container {
        background: rgba(255, 255, 255, 0.2);
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        text-align: center;
        max-width: 90%;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: transform 0.3s ease;
    }

    .error-container {
        color: #fff;
        text-align: center;
        padding: 40px;
    }

    .error-icon {
        font-size: 60px;
        margin-bottom: 20px;
        color: #ff6b6b;
    }

    .error-title {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #ff6b6b;
    }

    .error-message {
        font-size: 18px;
        margin-bottom: 30px;
        color: #fff;
        line-height: 1.6;
    }

    .btn-back {
        display: inline-block;
        padding: 12px 30px;
        background-color: #4a90e2;
        color: white;
        border: none;
        border-radius: 25px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        margin-top: 20px;
    }

    .btn-back:hover {
        background-color: #357abd;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .image-preview {
        max-width: 100%;
        max-height: 80vh;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .branding {
        position: fixed;
        bottom: 20px;
        right: 20px;
        font-size: 14px;
        color: #fff;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
    }

    .file-selector {
        margin-top: 20px;
    }

    .file-selector select {
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 16px;
    }

    @media (max-width: 768px) {
        .container {
            padding: 20px;
            margin: 20px;
        }

        .error-title {
            font-size: 20px;
        }

        .error-message {
            font-size: 16px;
        }
    }

    .btn-logout {
        display: inline-block;
        padding: 12px 30px;
        background-color: #e74c3c;
        color: white;
        border: none;
        border-radius: 25px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        margin-top: 20px;
        margin-left: 10px;
    }

    .btn-logout:hover {
        background-color: #c0392b;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    /* Style untuk tampilan fullscreen */
    .fullscreen-image {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .fullscreen-image img {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: 10px;
    }
    </style>


    <script>
    function showAlert(message) {
        alert(message);
    }

    function redirectToSelectedImage(select) {
        var selectedFile = select.value;
        if (selectedFile) {
            window.location.href = "?file=" + encodeURIComponent(selectedFile);
        }
    }

    // Tampilkan alert sesuai dengan pesan error
    <?php if ($showError): ?>
    window.onload = function() {
        showAlert("<?php echo $errorMessage; ?>");
    };
    <?php endif; ?>
    </script>
</head>

<body>
    <div class="container">
        <?php if ($showError): ?>
        <div class="error-container">
            <div class="error-icon">⚠️</div>
            <h1 class="error-title">Oops!</h1>
            <p class="error-message"><?php echo $errorMessage; ?></p>
            <div>
                <a href="dashboard.php<?php echo !$isAdmin ? '?action=dashboard' : ''; ?>"
                    <?php echo !$isAdmin ? 'onclick="showAlert(\'Dashboard hanya tersedia untuk admin!\'); return false;"' : ''; ?>
                    class="btn-back">Kembali ke Dashboard</a>
                <a href="?action=logout" class="btn-logout">Logout</a>
            </div>
        </div>
        <?php else: ?>

        <!-- Tampilkan gambar jika ada -->
        <?php if (isset($file_path)): ?>
        <img src="<?php echo $file_path; ?>" alt="Preview Gambar" class="image-preview">
        <?php endif; ?>

        <!-- Tampilkan dropdown hanya untuk selain admin jika ada lebih dari 1 file -->
        <?php if (!$isAdmin && count($imageFiles) > 1): ?>
        <div class="file-selector">
            <select onchange="redirectToSelectedImage(this)">
                <option value="">Pilih gambar lainnya</option>
                <?php foreach ($imageFiles as $imageFile): ?>
                <option value="<?php echo basename($imageFile); ?>"><?php echo basename($imageFile); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <script>
        function openFullscreen(imageSrc) {
            const fullscreenDiv = document.createElement('div');
            fullscreenDiv.className = 'fullscreen-image';
            fullscreenDiv.innerHTML = `<img src="${imageSrc}" alt="Fullscreen Image">`;
            document.body.appendChild(fullscreenDiv);

            // Tutup fullscreen saat diklik
            fullscreenDiv.addEventListener('click', () => {
                document.body.removeChild(fullscreenDiv);
            });
        }

        // Tambahkan event listener ke gambar
        document.addEventListener('DOMContentLoaded', () => {
            const imagePreview = document.querySelector('.image-preview');
            if (imagePreview) {
                imagePreview.addEventListener('click', () => {
                    openFullscreen(imagePreview.src);
                });
            }
        });
        </script>
        <div>
            <a href="dashboard.php<?php echo !$isAdmin ? '?action=dashboard' : ''; ?>"
                <?php echo !$isAdmin ? 'onclick="showAlert(\'Dashboard hanya tersedia untuk admin!\'); return false;"' : ''; ?>
                class="btn-back">Kembali ke Dashboard</a>
            <a href="?action=logout" class="btn-logout">Logout</a>
        </div>
        <?php endif; ?>
    </div>
    <div class="branding">
        Slide ini milik Randal Operasi
    </div>
</body>

</html>