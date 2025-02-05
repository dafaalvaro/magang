<?php
if (isset($_GET['file'])) {
    $file = basename($_GET['file']); // Ambil nama file
    $file_path = 'uploads/' . $file; // Path lengkap file

    if (file_exists($file_path)) {
        // Set header untuk membuka file di Excel
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=\"$file\"");
        header("Content-Length: " . filesize($file_path));
        readfile($file_path);
        exit;
    } else {
        echo "<script>alert('File tidak ditemukan!'); window.location.href = 'dashboard.php';</script>";
    }
} else {
    echo "<script>alert('Tidak ada file yang dipilih!'); window.location.href = 'dashboard.php';</script>";
}
?>