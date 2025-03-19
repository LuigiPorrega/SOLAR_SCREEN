<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <meta name="description" content="Panel de Administración Solar Screen">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="/favicon.ico">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.rtl.min.css') ?>">

    <style>
        body {
            padding-top: 70px; /* Ajuste para evitar que el contenido se oculte detrás de la barra de navegación */
        }

        .navbar {
            background-color: #343a40; /* Color de fondo de la barra de navegación */
        }

        .navbar-brand,
        .nav-link {
            color: rgba(255, 255, 255, 0.7) !important; /* Color del texto de la barra de navegación */
        }

        .nav-link:hover {
            color: rgba(255, 255, 255, 1) !important; /* Color del texto al pasar el ratón */
        }

        .container {
            margin-top: 20px; /* Espacio superior para el contenido */
        }
    </style>
</head>

<body>
    <!-- BARRA DE NAVEGACIÓN -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url('admin') ?>">Solar Screen</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('simulaciones') ?>">Simulaciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('ideas') ?>">Ideas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('users') ?>">Usuarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('admin') ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('loginlog') ?>">LoginLog</a>
                    </li>
                    <?php $session = session();
                    if ($session->has('user_id')) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('logout') ?>">Cerrar Sesión</a>
                        </li>
                    <?php endif ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="container">
