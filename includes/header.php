<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RENDAL OPERASI PLTU</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>

    <style>
    /* Styling Header agar Full Width dan Lebih Menarik */
    .custom-navbar {
        background: linear-gradient(135deg, #43698f, #2c3e50);
        /* Gradasi warna untuk tampilan modern */
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        padding: 15px 0;
        /* Memberikan ruang agar lebih estetis */
    }

    /* Styling Navbar agar tulisan di tengah */
    .navbar-nav {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 30px;
        /* Menjaga jarak antar menu */
        margin: 0 auto;
        /* Mencegah navbar keluar dari area tengah */
    }

    .navbar-nav .nav-item {
        text-align: center;
    }

    .navbar-nav .nav-link {
        font-size: 16px;
        font-weight: bold;
        color: white !important;
        text-transform: uppercase;
        transition: 0.3s;
        padding: 10px 20px;
        border-radius: 8px;
    }

    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link.active {
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 8px;
    }

    /* Styling tombol logout */
    .nav-light {
        font-weight: bold;
        text-transform: uppercase;
        margin-right: 20px;
        transition: 0.3s;
        color: white !important;
    }

    .nav-light:hover {
        color: #ffcc00 !important;
    }
    </style>
</head>

<body class="container">
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark custom-navbar w-100" style="background-color: #43698f ">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                    <img src="Logo-PLN-Nusantara-Power-Putih.png" alt="Logo PLTU Tenayan" width="180" height="35"
                        class="d-inline-block align-text-top">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <!-- Memusatkan item navbar -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo(basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>"
                                href="grafik.php">PLTU TENAYAN </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo(basename($_SERVER['PHP_SELF']) == 'Edit.php') ? 'active' : ''; ?>"
                                href="Edit.php"></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo(basename($_SERVER['PHP_SELF']) == 'grafik.php') ? 'active' : ''; ?>"
                                href="grafik.php"></a>
                        </li>
                    </ul>

                    <a class="nav-light text-white" href="?logout=true">Logout</a> <!-- Logout di paling kanan -->
                </div>

        </nav>
    </header>