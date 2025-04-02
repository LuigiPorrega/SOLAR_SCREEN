<section class="container mt-4">
    <h2 class="text-center mb-4">Modelos de Fundas</h2>

    <!-- Mostrar mensajes de éxito -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <!-- Mostrar mensajes de error -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Insert disponible solo para administradores -->
    <?php
    $usuariosModel = model('App\Models\UsuariosModel');
    $session = session();
    if ($session->has('user_id') && $usuariosModel->canAccessBackend($session->get('user_id'))) : ?>
        <div class="text-center mb-4">
            <a href="<?= base_url('admin/modelosFundas/new') ?>" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Agregar Nuevo Modelo de Funda
            </a>
        </div>
    <?php endif; ?>

    <!-- Fundas Fijas -->
    <div class="mb-5">
        <h3 class="text-center text-primary mb-4">Fundas Fijas</h3>
        <div class="row">
            <?php if (!empty($modelosFundas)): ?>
                <?php foreach ($modelosFundas as $modelo): ?>
                    <?php if ($modelo['TipoFunda'] == 'fija'): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card shadow-lg border-info">
                                <div class="card-body">
                                    <h5 class="card-title text-info"><?= esc($modelo['Nombre']) ?></h5>
                                    <p class="card-text"><?= esc($modelo['TipoFunda']) ?></p>

                                    <?php
                                    // Ruta de la imagen
                                    $imagenPath = base_url(esc($modelo['ImagenURL']));
                                    ?>

                                    <!-- Mostrar la imagen -->
                                    <?php if (!empty($imagenPath)): ?>
                                        <img src="<?= $imagenPath ?>" alt="<?= esc($modelo['Nombre']) ?>" class="img-fluid">
                                    <?php else: ?>
                                        <p>No hay imagen disponible.</p>
                                    <?php endif; ?>

                                    <a href="<?= base_url('admin/modelosFundas/' . $modelo['ID']) ?>" class="btn btn-info w-100 mt-3">Ver Detalles</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning col-12">
                    <h3 class="text-center">No hay modelos de fundas fijas disponibles.</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Fundas Extendibles -->
    <div>
        <h3 class="text-center text-success mb-4">Fundas Extendibles</h3>
        <div class="row">
            <?php if (!empty($modelosFundas)): ?>
                <?php foreach ($modelosFundas as $modelo): ?>
                    <?php if ($modelo['TipoFunda'] == 'extendible' || $modelo['TipoFunda'] == 'expandible'): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card shadow-lg border-success">
                                <div class="card-body">
                                    <h5 class="card-title text-success"><?= esc($modelo['Nombre']) ?></h5>
                                    <p class="card-text"><?= esc($modelo['TipoFunda']) ?></p>

                                    <?php
                                    // Ruta de la imagen
                                    $imagenPath = base_url(esc($modelo['ImagenURL']));
                                    ?>

                                    <!-- Mostrar la imagen -->
                                    <?php if (!empty($imagenPath)): ?>
                                        <img src="<?= $imagenPath ?>" alt="<?= esc($modelo['Nombre']) ?>" class="img-fluid">
                                    <?php else: ?>
                                        <p>No hay imagen disponible.</p>
                                    <?php endif; ?>

                                    <a href="<?= base_url('admin/modelosFundas/' . $modelo['ID']) ?>" class="btn btn-success w-100 mt-3">Ver Detalles</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning col-12">
                    <h3 class="text-center">No hay modelos de fundas extendibles disponibles.</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>

</section>