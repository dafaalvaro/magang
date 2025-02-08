<?php
session_start();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Definisikan fungsi validateFile
function validateFile($filename)
{
    $allowedExtensions = [
        'xlsx', 'xls', 'xlsm',
        'png', 'jpg', 'jpeg', 'gif', 'bmp', 'svg', 'webp',
        'doc', 'docx', 'docm',
        'pdf',
        'ppt', 'pptx', 'pptm',
        'zip', 'rar', '7z', 'tar', 'gz',
        'txt', 'log', 'md',
        'php', 'html', 'css', 'js', 'py', 'java', 'cpp', 'c', 'cs',
        'mp4', 'avi', 'mov', 'wmv', 'flv', 'webm',
        'mp3', 'wav', 'ogg', 'wma', 'm4a',
    ];

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowedExtensions);
}
// Panggil fungsi validateFile
if (isset($_FILES['file'])) {
    $filename = $_FILES['file']['name'];
    if (validateFile($filename)) {
        // Proses file jika valid
    } else {
        // Tampilkan pesan kesalahan
    }
}

// Handle rename file
if (isset($_GET['rename']) && isset($_GET['new_name'])) {
    $oldName = $_GET['rename'];
    $newName = $_GET['new_name'];

    // Get file extension from old name
    $ext = pathinfo($oldName, PATHINFO_EXTENSION);

    // Add extension to new name if it doesn't have one
    if (!preg_match('/\.' . $ext . '$/', $newName)) {
        $newName .= '.' . $ext;
    }

    $oldPath = "uploads/" . $oldName;
    $newPath = "uploads/" . $newName;

    if (file_exists($oldPath) && validateFile($oldName)) {
        if (rename($oldPath, $newPath)) {
            $_SESSION['alert'] = [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'text' => 'File berhasil direname',
            ];
        } else {
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Gagal!',
                'text' => 'Gagal rename file',
            ];
        }
    }
    header("Location: dashboard.php");
    exit();
}

// Handle delete file
if (isset($_GET['delete'])) {
    $filename = $_GET['delete'];
    error_log("Attempting to delete file: " . $filename); // Tambahkan ini untuk debug

    if (validateFile($filename)) {
        $filepath = "uploads/" . $filename;

        if (file_exists($filepath) && unlink($filepath)) {
            // Simpan pesan sukses di session
            $_SESSION['alert'] = [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'text' => 'File berhasil dihapus',
            ];
        } else {
            // Simpan pesan error di session
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Gagal!',
                'text' => 'Gagal menghapus file',
            ];
        }
    } else {
        // Simpan pesan error di session
        $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => 'Error!',
            'text' => 'File tidak valid',
        ];
    }

    // Redirect ke dashboard.php
    header("Location: dashboard.php");
    exit();
}
// Handle file upload
if (isset($_POST['upload'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    $filename = preg_replace("/[^a-zA-Z0-9.-]/", "_", basename($_FILES["file"]["name"]));
    $target_file = $target_dir . $filename;
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($target_file)) {
        $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => 'Gagal!',
            'text' => 'File dengan nama yang sama sudah ada.',
        ];
        header("Location: dashboard.php");
        exit();
    }

    // Allow certain file formats
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        $_SESSION['alert'] = [
            'icon' => 'success',
            'title' => 'Berhasil!',
            'text' => 'File berhasil diupload.',
        ];
    } else {
        $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => 'Gagal!',
            'text' => 'Terjadi kesalahan saat mengupload file.',
        ];
    }
    header("Location: dashboard.php");
    exit();
}

include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 Library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <!-- Konten HTML -->
    <div class="flex-container">
        <!-- Form Upload File -->
        <div class="upload-form">
            <h1 class="text-center mb-4">RENDAL OPERASI PLTU TENAYAN</h1>
            <form action="" method="post" enctype="multipart/form-data" class="upload-box">
                <div class="upload-area mb-3" id="uploadArea">
                    <i class=" fas fa-cloud-upload-alt upload-icon"></i>
                    <p>Drag & Drop file atau klik untuk memilih</p>
                    <input type="file" class="form-control" name="file" id="file">
                    <span class="selected-file-name"></span>
                </div>
                <button type="submit" name="upload" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-upload me-2"></i>Upload File
                </button>
            </form>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.querySelector('input[type="file"]');
            const fileNameDisplay = document.querySelector('.selected-file-name');

            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            // Highlight drop zone when item is dragged over it
            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, unhighlight, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            function highlight(e) {
                uploadArea.classList.add('highlight');
            }

            function unhighlight(e) {
                uploadArea.classList.remove('highlight');
            }

            // Handle dropped files
            uploadArea.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                fileInput.files = files;
                updateFileName(files[0]);
            }

            // Handle selected files
            fileInput.addEventListener('change', function(e) {
                updateFileName(this.files[0]);
            });

            function updateFileName(file) {
                if (file) {
                    fileNameDisplay.textContent = `Selected file: ${file.name}`;
                    fileNameDisplay.style.display = 'block';
                } else {
                    fileNameDisplay.textContent = '';
                    fileNameDisplay.style.display = 'none';
                }
            }
        });
        </script>

        <!-- Carousel -->
        <div class="carousel-container">
            <div class="carousel">
                <?php
$imageFiles = glob("uploads/*.{png,jpg,jpeg}", GLOB_BRACE);
if (count($imageFiles) > 0) {
    foreach ($imageFiles as $image) {
        $imageName = basename($image);
        echo "
                    <div class='carousel-slide'>
                        <img src='uploads/{$imageName}' alt='{$imageName}' class='carousel-image' />
                    </div>";
    }
} else {
    echo "<p class='no-images'>Tidak ada gambar yang tersedia.</p>";
}
?>
            </div>
            <button class="carousel-btn prev">❮</button>
            <button class="carousel-btn next">❯</button>
            <div class="carousel-dots">
                <?php if (isset($imageFiles)): ?>
                <?php for ($i = 0; $i < count($imageFiles); $i++): ?>
                <div class="dot <?php echo $i === 0 ? 'active' : ''; ?>"></div>
                <?php endfor; ?>
                <?php endif; ?>
            </div>
            <div class="slide-counter">1 / <?php echo isset($imageFiles) ? count($imageFiles) : 0; ?></div>
        </div>
    </div>

    <!-- Tampilkan SweetAlert2 jika ada pesan di session -->
    <?php if (isset($_SESSION['alert'])): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: '<?php echo htmlspecialchars($_SESSION['alert']['icon']); ?>',
            title: '<?php echo htmlspecialchars($_SESSION['alert']['title']); ?>',
            text: '<?php echo htmlspecialchars($_SESSION['alert']['text']); ?>',
            showConfirmButton: true,
            timer: 1500 // Otomatis tutup setelah 1.5 detik (opsional)
        });
    });
    </script>
    <?php
// Hapus pesan dari session setelah ditampilkan
unset($_SESSION['alert']);
endif;
?>

    <script>
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

        // Event Listeners
        prevBtn?.addEventListener('click', prevSlide);
        nextBtn?.addEventListener('click', nextSlide);

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                updateCarousel();
            });
        });

        // Auto-advance every 5 seconds
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
<!-- Form Upload File -->


<!-- Card Layout untuk File -->
<div class="card-container">
    <?php
$files = glob("uploads/*.{xlsx,xls,xlsm,png,jpg,jpeg,gif,bmp,svg,webp,doc,docx,docm,pdf,ppt,pptx,pptm,zip,rar,7z,tar,gz,txt,log,md,php,html,css,js,py,java,cpp,c,cs,mp4,avi,mov,wmv,flv,webm,mp3,wav,ogg,wma,m4a}", GLOB_BRACE);

if (count($files) > 0) {
    foreach ($files as $file) {
        $file_name = basename($file);
        $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $upload_time = date("d-m-Y H:i", filemtime($file));
        $file_size = round(filesize($file) / 1024, 2); // Convert to KB

        // Determine icon based on file type
        $icon_class = match ($file_ext) {
            // Microsoft Excel
            'xlsx', 'xls', 'xlsm' => '<i class="fas fa-file-excel file-icon excel-icon"></i>',

            // Images
            'png', 'jpg', 'jpeg', 'gif', 'bmp', 'svg', 'webp' => '<i class="fas fa-file-image file-icon image-icon"></i>',

            // Microsoft Word
            'doc', 'docx', 'docm' => '<i class="fas fa-file-word file-icon word-icon"></i>',

            // PDF
            'pdf' => '<i class="fas fa-file-pdf file-icon pdf-icon"></i>',

            // PowerPoint
            'ppt', 'pptx', 'pptm' => '<i class="fas fa-file-powerpoint file-icon powerpoint-icon"></i>',

            // Archives
            'zip', 'rar', '7z', 'tar', 'gz' => '<i class="fas fa-file-archive file-icon archive-icon"></i>',

            // Text
            'txt', 'log', 'md' => '<i class="fas fa-file-alt file-icon text-icon"></i>',

            // Code
            'php', 'html', 'css', 'js', 'py', 'java', 'cpp', 'c', 'cs' => '<i class="fas fa-file-code file-icon code-icon"></i>',

            // Video
            'mp4', 'avi', 'mov', 'wmv', 'flv', 'webm' => '<i class="fas fa-file-video file-icon video-icon"></i>',

            // Audio
            'mp3', 'wav', 'ogg', 'wma', 'm4a' => '<i class="fas fa-file-audio file-icon audio-icon"></i>',

        // Default untuk file lainnya
            default => '<i class="fas fa-file file-icon"></i>'
        };

        echo "
            <div class='file-card'>
                <div class='file-icon-wrapper'>
                    {$icon_class}
                </div>
                <div class='file-info'>
                    <h4 class='file-name' title='{$file_name}'>{$file_name}</h4>
                    <div class='file-details'>
                        <span><i class='fas fa-calendar-alt'></i> {$upload_time}</span>
                        <span><i class='fas fa-weight-hanging'></i> {$file_size} KB</span>
                    </div>
                </div>
                <div class='file-actions'>
                    <button onclick='window.location.href=\"?delete=" . urlencode($file_name) . "\"' class='action-btn delete-btn' title='Hapus'>
                        <i class='fas fa-trash-alt'></i>
                    </button>
                    <button onclick='window.location.href=\"open.php?file=" . urlencode($file_name) . "\"' class='action-btn download-btn' title='Download'>
                        <i class='fas fa-download'></i>
                    </button>
                    <button onclick='window.location.href=\"edit.php?file=" . urlencode($file_name) . "\"' class='action-btn edit-btn' title='Buka'>
                        <i class='fas fa-external-link-alt'></i>
                    </button>
                    <button onclick='openRenameModal(\"{$file_name}\")' class='action-btn rename-btn' title='Rename'>
                        <i class='fas fa-pencil-alt'></i>
                    </button>
                </div>
            </div>";
    }
} else {
    echo "<div class='no-files'>
                <i class='fas fa-folder-open'></i>
                <p>Tidak ada file yang ditemukan</p>
              </div>";
}

if (isset($_POST['upload'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'File sudah ada.',
                showConfirmButton: true
            }).then(() => {
                window.location.href = 'dashboard.php';
            });
        </script>";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'File tidak terupload.',
                showConfirmButton: true
            }).then(() => {
                window.location.href = 'dashboard.php';
            });
        </script>";
    } else {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'File berhasil diupload.',
                    showConfirmButton: true
                }).then(() => {
                    window.location.href = 'dashboard.php';
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan saat mengupload file.',
                    showConfirmButton: true
                }).then(() => {
                    window.location.href = 'dashboard.php';
                });
            </script>";
        }
    }
}
?>
</div>

<!-- Modal Rename yang Lebih Menarik -->
<div id="renameModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-pencil-alt me-2"></i>Rename File</h3>
            <button onclick="closeModal()" class="close-btn"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="input-group">
                <span class="input-icon"><i class="fas fa-file-alt"></i></span>
                <input type="text" id="newFileName" placeholder="Masukkan nama baru">
            </div>
            <input type="hidden" id="oldFileName">
        </div>
        <div class="modal-footer">
            <button onclick="closeModal()" class="btn-secondary">
                <i class="fas fa-times me-2"></i>Batal
            </button>
            <button onclick="renameFile()" class="btn-primary">
                <i class="fas fa-check me-2"></i>Simpan
            </button>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['alert'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: '<?php echo $_SESSION['alert']['icon']; ?>',
        title: '<?php echo $_SESSION['alert']['title']; ?>',
        text: '<?php echo $_SESSION['alert']['text']; ?>',
        showConfirmButton: true,
        timer: 1500
    });
});
</script>
<?php
unset($_SESSION['alert']);
endif;
?>

<style>
.selected-file-name {
    margin-top: 10px;
    padding: 8px;
    background-color: #f8f9fa;
    border-radius: 4px;
    word-break: break-all;
    display: none;
}

.upload-area {
    position: relative;
    padding: 40px 20px;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    text-align: center;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
    cursor: pointer;
}

.upload-area.highlight {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.1);
}

.upload-area input[type="file"] {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    opacity: 0;
    cursor: pointer;
}

.flex-container {
    display: flex;
    gap: 20px;
    /* Jarak antara form dan carousel */
    align-items: flex-start;
    /* Agar elemen sejajar di bagian atas */
    max-width: 1200px;
    /* Lebar maksimum container */
    margin: 0 auto;
    /* Pusatkan container */
    padding: 20px;
}

.carousel-container {
    position: relative;
    margin: 0 auto 20px;
    overflow: hidden;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    flex: 1;
    /* Carousel akan mengambil ruang yang tersedia */
    max-width: 600px;
    /* Lebar maksimum carousel */
}

.carousel {
    display: flex;
    transition: transform 0.5s ease-in-out;
    height: 350px;
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

.upload-form {
    max-inline-size: 600px;
    margin: 0 auto;
    flex: 1;
    /* Form akan mengambil ruang yang tersedia */
}

.upload-box {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.upload-box:hover {
    border-color: #0d6efd;
    background: #f1f3f5;
}

.upload-icon {
    font-size: 48px;
    color: #6c757d;
    margin-block-end: 10px;
}

.upload-area {
    position: relative;
    padding: 20px;
    cursor: pointer;
}

.upload-area input[type="file"] {
    position: absolute;
    inline-size: 100%;
    height: 100%;
    top: 0;
    left: 0;
    opacity: 0;
    cursor: pointer;
}

.card-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    padding: 20px;
}

.file-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.file-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.file-icon-wrapper {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.file-icon {
    font-size: 2.5em;
}

.excel-icon {
    color: #217346;
}

.image-icon {
    color: #ff4081;
}

.file-info {
    flex-grow: 1;
    overflow: hidden;
}

.file-name {
    margin: 0;
    font-size: 1rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.file-details {
    display: flex;
    gap: 15px;
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 5px;
}

.file-details span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.file-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 35px;
    height: 35px;
    border: none;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    color: white;
}

.delete-btn {
    background: #dc3545;
}

.download-btn {
    background: #198754;
}

.edit-btn {
    background: #ffc107;
}

.rename-btn {
    background: #0dcaf0;
}

.action-btn:hover {
    filter: brightness(110%);
    transform: scale(1.05);
}

.no-files {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px;
    background: #f8f9fa;
    border-radius: 12px;
}

.no-files i {
    font-size: 48px;
    color: #dee2e6;
    margin-bottom: 10px;
}

/* Modal Styling */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(5px);
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 400px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.input-group {
    position: relative;
    margin-bottom: 15px;
}

.input-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.input-group input {
    width: 100%;
    padding: 10px 10px 10px 35px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-size: 1rem;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    color: #6c757d;
}

.close-btn:hover {
    color: #343a40;
}

.btn-primary,
.btn-secondary {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
}

.btn-primary {
    background: #0d6efd;
    color: white;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-primary:hover,
.btn-secondary:hover {
    filter: brightness(110%);
}

.file-name {

    margin-top: 10px;
    font-size: 14px;
    font-weight: bold;
    color: #333;
}

.highlight {
    border: 2px dashed #4a90e2;
    background-color: rgba(74, 144, 226, 0.1);

}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openRenameModal(fileName) {
    const modal = document.getElementById('renameModal');
    const input = document.getElementById('newFileName');
    const oldFileNameInput = document.getElementById('oldFileName');
    input.value = fileName.replace(/\.[^/.]+$/, "");
    oldFileNameInput.value = fileName;
    modal.style.display = 'flex';
}

function closeModal() {
    const modal = document.getElementById('renameModal');
    modal.style.display = 'none';
}

function renameFile() {
    const newFileName = document.getElementById('newFileName').value;
    const oldFileName = document.getElementById('oldFileName').value;
    if (newFileName && oldFileName) {
        window.location.href =
            `dashboard.php?rename=${encodeURIComponent(oldFileName)}&new_name=${encodeURIComponent(newFileName)}`;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('renameModal');
    if (event.target == modal) {
        closeModal();
    }
}

// File upload preview
const fileInput = document.querySelector('input[type="file"]');
const fileNameDisplay = document.createElement('span'); // Elemen untuk menampilkan nama file

fileNameDisplay.classList.add('file-name');
uploadArea.appendChild(fileNameDisplay); // Tambahkan elemen ke dalam area drag and drop

// Event untuk mencegah default behavior
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

// Tambahkan highlight saat drag masuk
['dragenter', 'dragover'].forEach(eventName => {
    uploadArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, unhighlight, false);
});

function highlight() {
    uploadArea.classList.add('highlight');
}

function unhighlight() {
    uploadArea.classList.remove('highlight');
}

// Event saat file di-drop
uploadArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    fileInput.files = files;
    displayFileName(files);
}

// Event saat file dipilih dari input
fileInput.addEventListener('change', function() {
    displayFileName(fileInput.files);
});
</script>

<?php include 'includes/footer.php'; ?>