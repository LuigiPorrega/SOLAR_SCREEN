<section class="container mt-4">
    <h2 class="text-center mb-4"><?= esc($title) ?></h2>

    <!-- Card para el usuario -->
    <div class="card shadow-lg mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="card-title">Información del Usuario</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <p><strong>Nombre de Usuario:</strong> <span class="font-weight-bold"><?= esc($idea['Username']) ?></span></p>
                <p><strong>Nombre Completo del Creador:</strong> <span class="font-weight-bold"><?= esc($idea['Nombre']) ?></span></p>
            </div>
        </div>
    </div>

    <!-- Card para la idea -->
    <div class="card shadow-lg mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title"><?= esc($idea['Titulo']) ?> - Detalles</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <p><strong>ID Idea:</strong> <?= esc($idea['ID']) ?></p>
            </div>
            <div class="mb-3">
                <p><strong>Título:</strong> <?= esc($idea['Titulo']) ?></p>
                <p><strong>Descripción:</strong> <?= esc($idea['Descripcion']) ?></p>
                <p><strong>Fecha de Creación:</strong> <span class="font-italic"><?= date('d-m-Y', strtotime($idea['FechaCreacion'])) ?></span></p>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="text-center mt-4">
        <a href="<?= base_url('admin/ideas') ?>" class="btn btn-secondary mx-2">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>

        <!-- update y delete disponibles solo con rol de administrador -->
        <?php
        // Asegurémonos de obtener el modelo de UsuariosModel
        $usuariosModel = model('App\Models\UsuariosModel');

        // Verificamos si el usuario tiene permisos de administrador
        $session = session();
        if ($session->has('user_id') && $usuariosModel->canAccessBackend($session->get('user_id'))) : ?>
            <a href="<?= base_url('admin/ideas/update/' . $idea['ID']) ?>" class="btn btn-warning  mx-2">
                <i class="fas fa-edit"></i> Editar Idea
            </a>
            <a href="<?= base_url('admin/ideas/delete/' . $idea['ID']) ?>" class="btn btn-danger mx-2" onclick="return confirm('¿Estás seguro de eliminar esta idea?')">
                <i class="fas fa-trash-alt"></i> Eliminar Idea
            </a>
        <?php endif ?>
    </div>
</section>