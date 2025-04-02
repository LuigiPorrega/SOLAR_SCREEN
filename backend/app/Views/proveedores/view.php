<section class="container mt-4">
    <h2 class="text-center mb-4"><?= esc($title) ?></h2>


    <!-- Card para el proveedor -->
    <div class="card shadow-lg mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title"><?= esc($proveedor['Nombre']) ?> - Detalles</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <p><strong>ID Proveedor:</strong> <?= esc($proveedor['ID']) ?></p>
            </div>
            <div class="mb-3">
                <p><strong>Nombre del Proveedor:</strong> <?= esc($proveedor['Nombre']) ?></p>
                <p><strong>Pais:</strong> <?= esc($proveedor['Pais']) ?></p>
                <p><strong>Nombre de Contacto:</strong> <?= esc($proveedor['ContactoNombre']) ?></p>
                <p><strong>Teléfono de Contacto:</strong> <?= esc($proveedor['ContactoTelefono']) ?></p>
                <p><strong>Email de Contacto:</strong> <?= esc($proveedor['ContactoEmail']) ?></p>
                <p><strong>Sitio Web:</strong> <a href="<?= esc($proveedor['SitioWeb']) ?>" target="_blank"><?= esc($proveedor['SitioWeb']) ?></a></p>
                <p><strong>Dirección:</strong> <?= esc($proveedor['Direccion']) ?></p>
                <p><strong>Descripción:</strong> <?= esc($proveedor['Descripcion']) ?></p>
                <p><strong>Fecha de Creación:</strong> <span class="font-italic"><?= date('d-m-Y', strtotime($proveedor['FechaCreacion'])) ?></span></p>
            </div>
        </div>
    </div>

    <!-- Fundas Asociadas al Proveedor -->
    <div class="card shadow-lg mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="card-title">Fundas Proveídas</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($fundas)): ?>
                <ul class="list-group">
                    <?php foreach ($fundas as $funda): ?>
                        <li class="list-group-item">
                            <strong><?= esc($funda['Nombre']) ?></strong> ( ID Funda: <?= esc($funda['ID']) ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No hay fundas asociadas a este proveedor.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="text-center mt-4">
        <a href="<?= base_url('admin/proveedores') ?>" class="btn btn-secondary mx-2">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>

        <!-- update y delete disponibles solo con rol de administrador -->
        <?php
        // Asegurémonos de obtener el modelo de UsuariosModel
        $usuariosModel = model('App\Models\UsuariosModel');

        // Verificamos si el usuario tiene permisos de administrador
        $session = session();
        if ($session->has('user_id') && $usuariosModel->canAccessBackend($session->get('user_id'))) : ?>
            <a href="<?= base_url('admin/proveedores/update/' . $proveedor['ID']) ?>" class="btn btn-warning mx-2">
                <i class="fas fa-edit"></i> Editar Proveedor
            </a>
            <a href="<?= base_url('admin/proveedores/delete/' . $proveedor['ID']) ?>" class="btn btn-danger mx-2" onclick="return confirm('¿Estás seguro de eliminar este proveedor?')">
                <i class="fas fa-trash-alt"></i> Eliminar Proveedor
            </a>
        <?php endif ?>
    </div>
</section>