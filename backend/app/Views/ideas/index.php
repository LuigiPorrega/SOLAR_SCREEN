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

    <!-- Paginación arriba -->
    <div class="d-flex justify-content-center my-2">
        <nav>
            <b style="font-size: 18px;"><?= $pager->links() ?></b>
        </nav>
    </div>
    
    <!--Insert disponible solo para administradores-->
    <?php
    // Asegurémonos de obtener el modelo de UsuariosModel
    $usuariosModel = model('App\Models\UsuariosModel'); ?>
    <?php $session = session();
    // Verificamos si el usuario tiene permisos de administrador
    if ($session->has('user_id') && $usuariosModel->canAccessBackend($session->get('user_id'))) : ?>
        <div class="text-center mb-4">
            <a href="<?= base_url('admin/ideas/new') ?>" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Agregar Nueva Idea
            </a>
        </div>
    <?php endif ?>

    <?php if ($ideas !== []): ?>
        <div class="list-group">
            <?php foreach ($ideas as $idea): ?>
                <div class="list-group-item list-group-item-action my-2" style="border: 1px solid cornflowerblue; border-radius: 8px;">
                    <div class="row">
                        <div class="col-md-2 text-center">
                            <strong>Número Idea</strong><br>
                            <span class="text-muted"><?= esc($idea['ID']) ?></span>
                        </div>
                        <div class="col-md-3 text-center">
                            <strong>Username</strong><br>
                            <span class="text-muted"><?= esc($idea['Username']) ?></span>
                        </div>
                        <div class="col-md-3 text-center">
                            <strong>Título</strong><br>
                            <span class="text-muted"><?= esc($idea['Titulo']) ?></span>
                        </div>
                        <div class="col-md-2 text-center">
                            <strong>Fecha</strong><br>
                            <span class="text-muted"><?= date('d-m-Y', strtotime($idea['FechaCreacion'])) ?></span>
                        </div>
                        <div class="col-md-2 d-flex justify-content-center align-items-center">
                            <div>
                                <a href="<?= base_url('admin/ideas/' . $idea['ID']) ?>" class="btn btn-info">Ver</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    <?php else: ?>
        <h3 class="text-center">No hay ideas</h3>
        <p class="text-center">No se encontraron ideas.</p>
    <?php endif ?>
</section>