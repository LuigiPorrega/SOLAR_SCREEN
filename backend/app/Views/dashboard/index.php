<?php 
// Asegurémonos de obtener el modelo de UsuariosModel
$usuariosModel = model('App\Models\UsuariosModel');
?>

<section>
    <div class="container mt-4">

        <!-- Mostrar mensaje si hay error -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <h2 class="text-center mb-4">Bienvenido al Dashboard, <?= esc($nombreUsuario) ?></h2>
        <p class="text-center mb-5">Selecciona una opción del menú para comenzar.</p>

        <div class="row text-center">
            <!-- Simulaciones -->
            <div class="col-md-3 mb-4">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-bar-chart-line-fill"></i> Simulaciones</h5>
                        <p class="card-text">Gestiona las simulaciones de energía solar.</p>
                        <a href="<?= base_url('admin/simulaciones') ?>" class="btn btn-primary w-100">Ir a Simulaciones</a>
                    </div>
                </div>
            </div>

            <!-- Ideas -->
            <div class="col-md-3 mb-4">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-lightbulb-fill"></i> Ideas</h5>
                        <p class="card-text">Gestiona las ideas innovadoras.</p>
                        <a href="<?= base_url('admin/ideas') ?>" class="btn btn-success w-100">Ir a Ideas</a>
                    </div>
                </div>
            </div>

            <!-- Mostrar "Usuarios" solo si el usuario es administrador -->
            <?php $session = session();
            if ($session->has('user_id') && $usuariosModel->canAccessBackend($session->get('user_id'))) : ?>
                <!-- Usuarios -->
                <div class="col-md-3 mb-4">
                    <div class="card shadow-lg">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-person-lines-fill"></i> Usuarios</h5>
                            <p class="card-text">Gestiona los usuarios registrados.</p>
                            <a href="<?= base_url('admin/users') ?>" class="btn btn-warning w-100">Ir a Usuarios</a>
                        </div>
                    </div>
                </div>
            <?php endif ?>


            <!-- Mostrar "LoginLog" solo si el usuario es administrador -->
            <?php $session = session();
            if ($session->has('user_id') && $usuariosModel->canAccessBackend($session->get('user_id'))) : ?>
                <!-- LoginLog -->
                <div class="col-md-3 mb-4">
                    <div class="card shadow-lg">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-file-earmark-lock-fill"></i> LoginLog</h5>
                            <p class="card-text">Ver los registros de inicio de sesión.</p>
                            <a href="<?= base_url('admin/loginlog') ?>" class="btn btn-info w-100">Ir a LoginLog</a>
                        </div>
                    </div>
                </div>
            <?php endif ?>

        </div>
    </div>
</section>