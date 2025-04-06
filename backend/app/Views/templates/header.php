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

    <!-- CSS Común para las alertas y otros estilos -->
    <link rel="stylesheet" href="<?= base_url('assets/css/styles.css') ?>">

    <!-- Otros archivos CSS de la aplicación -->

    <!-- JS Común para las alertas -->
    <script src="<?= base_url('assets/js/alerts.js') ?>" defer></script>
</head>

<body>
    <?php
    // Asegurémonos de obtener el modelo de UsuariosModel
    $usuariosModel = model('App\Models\UsuariosModel'); ?>

    <!-- BARRA DE NAVEGACIÓN -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container d-flex flex-wrap">
            <a class="navbar-brand" href="<?= base_url('admin/inicio') ?>">Solar Screen</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('admin/simulaciones') ?>">Simulaciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('admin/ideas') ?>">Ideas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('admin/proveedores') ?>">Proveedores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('admin/modelosFundas') ?>">Modelos Fundas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('admin/condicionesMeteorologicas') ?>">Condiciones Meteorologicas</a>
                    </li>
                    <!-- Mostrar "Usuarios" solo si el usuario es administrador -->
                    <?php $session = session();
                    if ($session->has('user_id') && $usuariosModel->canAccessBackend($session->get('user_id'))) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('admin/users') ?>">Usuarios</a>
                        </li>
                    <?php endif ?>

                    <!-- Mostrar "LoginLog" solo si el usuario es administrador -->
                    <?php $session = session();
                    if ($session->has('user_id') && $usuariosModel->canAccessBackend($session->get('user_id'))) : ?>
                        <li class="nav-item ml-auto">
                            <a class="nav-link" href="<?= base_url('admin/loginlog') ?>">LoginLog</a>
                        </li>
                    <?php endif ?>
                </ul>
                <ul class="navbar-nav">
                    <!-- Mostrar "Logout" solo si el usuario está logueado -->
                    <?php $session = session();
                    if ($session->has('user_id')) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('admin/logout') ?>">Cerrar Sesión</a>
                        </li>
                    <?php endif ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="container">