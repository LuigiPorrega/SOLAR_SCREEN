<section class="container mt-4">
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

    <h2 class="text-center mb-4"><?= esc($title) ?></h2>

    <!-- Insertar nueva condición meteorológica (solo para administradores) -->
    <?php
    // Asegurémonos de obtener el modelo de UsuariosModel
    $usuariosModel = model('App\Models\UsuariosModel'); ?>
    <?php $session = session();
    // Verificamos si el usuario tiene permisos de administrador
    if ($session->has('user_id') && $usuariosModel->canAccessBackend($session->get('user_id'))) : ?>
        <div class="text-center mb-4">
            <a href="<?= base_url('admin/condicionesMeteorologicas/new') ?>" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Agregar Nueva Condición Meteorológica
            </a>
        </div>
    <?php endif ?>
    <!-- Paginación -->
    <div class="d-flex justify-content-center my-2">
        <nav>
            <b style="font-size: 18px;"><?= $pager->links() ?></b>
        </nav>
    </div>
    <!-- Mostrar las condiciones meteorológicas -->
    <?php if ($condiciones !== []): ?>
        <div class="list-group">
            <?php foreach ($condiciones as $condicion): ?>
                <div class="list-group-item list-group-item-action my-3" style="border: 1px solid cornflowerblue; border-radius: 8px;">
                    <!-- Card para mostrar los datos y acciones -->
                    <div class="card p-3">
                        <div class="row">
                            <!-- Datos de la condición meteorológica -->
                            <div class="col-md-2 text-center">
                                <strong>ID</strong><br>
                                <span class="text-muted"><?= esc($condicion['ID']) ?></span>
                            </div>
                            <div class="col-md-2 text-center">
                                <strong>Fecha</strong><br>
                                <span class="text-muted"><?= date('d-m-Y', strtotime($condicion['Fecha'])) ?></span>
                            </div>
                            <div class="col-md-2 text-center">
                                <strong>Luz Solar</strong><br>
                                <span class="text-muted"><?= esc($condicion['LuzSolar']) ?> lux</span>
                            </div>
                            <div class="col-md-2 text-center">
                                <strong>Temperatura</strong><br>
                                <span class="text-muted"><?= esc($condicion['Temperatura']) ?>°C</span>
                            </div>
                            <div class="col-md-2 text-center">
                                <strong>Humedad</strong><br>
                                <span class="text-muted"><?= esc($condicion['Humedad']) ?>%</span>
                            </div>
                            <div class="col-md-2 text-center">
                                <strong>Viento</strong><br>
                                <span class="text-muted"><?= esc($condicion['Viento']) ?> km/h</span>
                            </div>
                        </div>

                        <!-- Botones de acción (Editar y Eliminar) -->
                        <div class="d-flex justify-content-center mt-4">
                            <?php
                            // Asegurémonos de obtener el modelo de UsuariosModel
                            $usuariosModel = model('App\Models\UsuariosModel');

                            // Verificamos si el usuario tiene permisos de administrador
                            $session = session();
                            if ($session->has('user_id') && $usuariosModel->canAccessBackend($session->get('user_id'))) : ?>
                                <a href="<?= base_url('admin/condicionesMeteorologicas/update/' . $condicion['ID']) ?>" class="btn btn-warning mx-2">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="<?= base_url('admin/condicionesMeteorologicas/delete/' . $condicion['ID']) ?>" class="btn btn-danger mx-2" onclick="return confirm('¿Estás seguro de eliminar esta Condición Meteorólogica ?')">
                                    <i class="fas fa-trash-alt"></i> Eliminar
                                </a>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    <?php else: ?>
        <h3 class="text-center">No hay condiciones meteorológicas</h3>
        <p class="text-center">No se encontraron condiciones meteorológicas.</p>
    <?php endif ?>
</section>