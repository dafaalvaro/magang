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

// Ambil semua file PDF dari direktori uploads
$pdfFiles = glob($uploadDirectory . "*.pdf");

// Jika tidak ada file gambar di direktori
if (empty($imageFiles)) {
    $showError = true;
    $errorMessage = "Tidak ada gambar tersedia. Silakan hubungi admin untuk upload gambar!";
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
    session_destroy();
    header("Location: index.php");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    body {
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
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

    .carousel-container {
        position: relative;
        max-width: 800px;
        margin: 0 auto 20px;
        overflow: hidden;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .carousel {
        display: flex;
        transition: transform 0.5s ease-in-out;
        height: 450px;
    }

    .carousel-slide {
        min-width: 100%;
        position: relative;
        cursor: pointer;
    }

    .carousel-slide img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .carousel-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.3);
        color: white;
        padding: 16px;
        cursor: pointer;
        border: none;
        border-radius: 50%;
        font-size: 20px;
        backdrop-filter: blur(5px);
        transition: all 0.3s ease;
        z-index: 10;
    }

    .carousel-btn:hover {
        background: rgba(255, 255, 255, 0.5);
    }

    .carousel-btn.prev {
        left: 20px;
    }

    .carousel-btn.next {
        right: 20px;
    }

    .carousel-dots {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
    }

    .dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .dot.active {
        background: white;
        transform: scale(1.2);
    }

    .slide-counter {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.6);
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 14px;
        backdrop-filter: blur(5px);
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
        cursor: pointer;
    }

    .fullscreen-image img {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: 10px;
    }

    .branding {
        position: fixed;
        bottom: 20px;
        right: 20px;
        font-size: 14px;
        color: #fff;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
    }

    .sidebar {
        position: fixed;
        top: 0;
        left: -300px;
        /* Sembunyikan sidebar di luar layar */
        width: 300px;
        height: 100%;
        background: rgba(16, 18, 27, 0.85);
        transition: left 0.3s ease;
        /* Transisi halus */
        overflow-y: auto;
        /* Tambahkan scroll jika konten melebihi tinggi */
        z-index: 1000;
        /* Pastikan sidebar di atas konten lainnya */
    }

    .sidebar.active {
        left: 0;
        /* Tampilkan sidebar */
    }

    .sidebar.open {
        width: 200px;
    }

    .sidebar.active {
        left: 0;
    }

    .sidebar-toggle {
        position: fixed;
        top: 20px;
        left: 20px;
        background: rgba(16, 18, 27, 0.85);
        color: white;
        border: none;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 1001;
        transition: all 0.3s ease;
        backdrop-filter: blur(20px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .sidebar-toggle:hover {
        transform: scale(1.1);
        background: rgba(255, 255, 255, 0.1);
    }

    .sidebar-header {
        padding: 20px 0;
        margin-bottom: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        text-align: center;
    }

    .sidebar-header h2 {
        font-size: 24px;
        margin: 0;
        color: #fff;
        font-weight: 600;
        letter-spacing: 1px;
        white-space: nowrap;
        /* Mencegah teks membungkus */
        overflow: hidden;
        /* Sembunyikan teks yang melampaui */
        text-overflow: ellipsis;
        /* Tampilkan elipsis (...) jika teks terlalu panjang */
    }

    .pdf-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .pdf-list li {
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .pdf-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
        text-decoration: none;
        color: #fff;
        transition: all 0.3s ease;
    }

    .pdf-item:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateX(5px);
    }

    .pdf-icon {
        margin-right: 12px;
        color: #ff4757;
        font-size: 20px;
    }

    .pdf-name {
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 14px;
    }

    .pdf-info {
        margin-top: 4px;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.6);
    }

    .no-pdf {
        text-align: center;
        padding: 20px;
        color: rgba(255, 255, 255, 0.6);
        font-style: italic;
    }

    /* Custom Scrollbar */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    @media (max-width: 768px) {
        .sidebar {
            width: 260px;
        }
    }

    .sidebar ul li a {
        color: white;
        text-decoration: none;
        padding: 10px;
        display: block;
        /* Agar area klik lebih besar */
        transition: background 0.3s;
    }
    </style>
</head>

<body>

    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>PDF Documents</h2>
        </div>
        <?php if (!empty($pdfFiles)): ?>
        <ul class="pdf-list">
            <?php foreach ($pdfFiles as $pdfFile):
    $fileName = basename($pdfFile);
    $fileSize = round(filesize($pdfFile) / 1024, 2); // Size in KB
    $modifiedDate = date("d M Y", filemtime($pdfFile));
    ?>
            <li>
                <a href="#" onclick="openPDF('<?php echo $pdfFile; ?>')" class="pdf-item">
                    <i class="fas fa-file-pdf pdf-icon"></i>
                    <div class="pdf-content">
                        <div class="pdf-name"><?php echo $fileName; ?></div>
                        <div class="pdf-info">
                            <?php echo $fileSize; ?> KB • <?php echo $modifiedDate; ?>
                        </div>
                    </div>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <div class="no-pdf">
            <i class="fas fa-folder-open"></i>
            <p>No PDF files available</p>
        </div>
        <?php endif; ?>
    </div>
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
        <?php if (!empty($imageFiles)): ?>
        <div class="carousel-container">
            <div class="carousel">
                <?php foreach ($imageFiles as $index => $imageFile): ?>
                <div class="carousel-slide" onclick="openFullscreen('<?php echo $imageFile; ?>')">
                    <img src="<?php echo $imageFile; ?>" alt="Slide <?php echo $index + 1; ?>">
                    <p class="image-description" id="description-<?php echo $index; ?>" style="display: none;">Gambar :
                        <?php echo basename($imageFile); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-btn prev">❮</button>
            <button class="carousel-btn next">❯</button>
            <div class="carousel-dots">
                <?php for ($i = 0; $i < count($imageFiles); $i++): ?>
                <div class="dot <?php echo $i === 0 ? 'active' : ''; ?>"></div>
                <?php endfor; ?>
            </div>
            <div class="slide-counter">1 / <?php echo count($imageFiles); ?></div>
        </div>
        <div>
            <p id="current-description">Deskripsi untuk <?php echo basename($imageFiles[0]); ?></p>
            <a href="dashboard.php<?php echo !$isAdmin ? '?action=dashboard' : ''; ?>"
                <?php echo !$isAdmin ? 'onclick="showAlert(\'Dashboard hanya tersedia untuk admin!\'); return false;"' : ''; ?>
                class="btn-back">Kembali ke Dashboard</a>
            <a href="?action=logout" class="btn-logout">Logout</a>
        </div>
    </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    </div>
    <div class="branding">
        Slide ini milik Randal Operasi
    </div>

    <script>
    function toggleSidebar() {
        const sidebar = document.getElementById("sidebar");
        sidebar.classList.toggle("active"); // Menambahkan atau menghapus kelas 'active'
    }
    // Tutup sidebar ketika mengklik di luar sidebar
    document.addEventListener('click', (e) => {
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.querySelector('.sidebar-toggle');

        if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target) && sidebar.classList.contains(
                'active')) {
            toggleSidebar();
        }
    });

    // Fungsi untuk membuka PDF dalam tab baru dengan animasi loading
    function openPDF(pdfSrc) {
        // Tambahkan efek loading pada item yang diklik
        const clickedItem = event.currentTarget;
        clickedItem.style.opacity = '0.7';

        // Buka PDF dalam tab baru
        window.open(pdfSrc, '_blank');

        // Kembalikan tampilan normal setelah 500ms
        setTimeout(() => {
            clickedItem.style.opacity = '1';
        }, 500);
    }

    function showAlert(message) {
        alert(message);
    }

    function openFullscreen(imageSrc) {
        const fullscreenDiv = document.createElement('div');
        fullscreenDiv.className = 'fullscreen-image';
        fullscreenDiv.innerHTML = `<img src="${imageSrc}" alt="Fullscreen Image">`;
        document.body.appendChild(fullscreenDiv);

        fullscreenDiv.addEventListener('click', () => {
            document.body.removeChild(fullscreenDiv);
        });
    }

    function openPDF(pdfSrc) {
        window.open(pdfSrc, '_blank');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const carousel = document.querySelector('.carousel');
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.dot');
        const prevBtn = document.querySelector('.carousel-btn.prev');
        const nextBtn = document.querySelector('.carousel-btn.next');
        const counter = document.querySelector('.slide-counter');

        let currentSlide = 0;
        const totalSlides = slides.length;

        function updateCarousel() {
            carousel.style.transform = `translateX(-${currentSlide * 100}%)`;
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
            counter.textContent = `${currentSlide + 1} / ${totalSlides}`;

            // Update deskripsi gambar
            const currentDescription = document.getElementById('current-description');
            currentDescription.textContent = document.getElementById(`description-${currentSlide}`).textContent;
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateCarousel();
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateCarousel();
        }

        // Swipe detection for mobile
        let touchStartX = 0;
        let touchEndX = 0;

        carousel.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
        });

        carousel.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });

        function handleSwipe() {
            if (touchEndX < touchStartX) {
                nextSlide();
            } else if (touchEndX > touchStartX) {
                prevSlide();
            }
        }

        // Enhanced keyboard navigation
        document.addEventListener('keydown', (e) => {
            switch (e.key) {
                case 'ArrowLeft':
                    prevSlide();
                    break;
                case 'ArrowRight':
                    nextSlide();
                    break;
                case 'Escape':
                    const fullscreenImage = document.querySelector('.fullscreen-image');
                    if (fullscreenImage) {
                        document.body.removeChild(fullscreenImage);
                    }
                    break;
            }
        });

        // Event Listeners
        prevBtn?.addEventListener('click', prevSlide);
        nextBtn?.addEventListener('click', nextSlide);

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                updateCarousel();
            });
        });

        // Improved fullscreen with zoom and pan
        function openFullscreen(imageSrc) {
            const fullscreenDiv = document.createElement('div');
            fullscreenDiv.className = 'fullscreen-image';

            const img = document.createElement('img');
            img.src = imageSrc;
            img.alt = 'Fullscreen Image';

            let scale = 1;
            let translateX = 0;
            let translateY = 0;

            img.addEventListener('wheel', (e) => {
                e.preventDefault();
                const delta = e.deltaY * -0.01;
                scale = Math.min(Math.max(0.5, scale + delta), 3);
                img.style.transform = `scale(${scale}) translate(${translateX}px, ${translateY}px)`;
            });

            let isDragging = false;
            let startX, startY;

            img.addEventListener('mousedown', (e) => {
                isDragging = true;
                startX = e.clientX - translateX;
                startY = e.clientY - translateY;
                img.style.cursor = 'grabbing';
            });

            document.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                translateX = e.clientX - startX;
                translateY = e.clientY - startY;
                img.style.transform = `scale(${scale}) translate(${translateX}px, ${translateY}px)`;
            });

            document.addEventListener('mouseup', () => {
                isDragging = false;
                img.style.cursor = 'grab';
            });

            img.style.cursor = 'grab';
            fullscreenDiv.appendChild(img);
            document.body.appendChild(fullscreenDiv);

            fullscreenDiv.addEventListener('click', (e) => {
                if (e.target === fullscreenDiv) {
                    document.body.removeChild(fullscreenDiv);
                }
            });
        }

        // Attach to existing fullscreen function
        window.openFullscreen = openFullscreen;

        // Auto-advance every 5 seconds with pause on hover
        let autoAdvanceInterval = setInterval(nextSlide, 5000);

        carousel.addEventListener('mouseenter', () => {
            clearInterval(autoAdvanceInterval);
        });

        carousel.addEventListener('mouseleave', () => {
            autoAdvanceInterval = setInterval(nextSlide, 5000);
        });
    });
    </script>
</body>

</html>