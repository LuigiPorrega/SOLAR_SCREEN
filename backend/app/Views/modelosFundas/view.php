<section class="container mt-4">
    <h2 class="text-center mb-4"><?= esc($title) ?></h2>

    <!-- Card para el modelo de funda -->
    <div class="card shadow-lg mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title"><?= esc($modeloFunda['Nombre']) ?> - Detalles</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Columna de detalles de la funda -->
                <div class="col-md-5">
                    <div class="mb-3">
                        <p><strong>ID Modelo de Funda:</strong> <?= esc($modeloFunda['ID']) ?></p>
                    </div>
                    <div class="mb-3">
                        <p><strong>Nombre del Modelo:</strong> <?= esc($modeloFunda['Nombre']) ?></p>
                        <p><strong>Tamaño:</strong> <?= esc($modeloFunda['Tamaño']) ?></p>
                        <p><strong>Capacidad de Carga:</strong> <?= esc($modeloFunda['CapacidadCarga']) ?> mAh</p>
                        <p><strong>Tipo de Funda:</strong> <?= esc($modeloFunda['TipoFunda']) ?></p>
                        <p><strong>Expansible:</strong> <?= ($modeloFunda['Expansible'] == 1) ? 'Sí' : 'No' ?></p>
                        <p><strong>Cantidad en Stock:</strong> <?= esc($modeloFunda['Cantidad']) ?></p>
                        <p><strong>Precio:</strong> <?= number_format($modeloFunda['Precio'], 2) ?> €</p>
                        <p><strong>Fecha de Creación:</strong> <span class="font-italic"><?= date('d-m-Y', strtotime($modeloFunda['FechaCreacion'])) ?></span></p>
                    </div>
                </div>

                <!-- Columna de la imagen -->
                <div class="col-md-7 d-flex justify-content-center align-items-center">
                    <div class="text-center">
                        <p><strong>Imagen:</strong></p>
                        <!-- Mostrar imagen del producto con tamaño pequeño -->
                        <?php if (!empty($modeloFunda['ImagenURL'])): ?>
                            <img src="<?= base_url(esc($modeloFunda['ImagenURL'])) ?>" alt="<?= esc($modeloFunda['Nombre']) ?>" class="img-fluid mb-3" style="max-width: 80%; height: auto; object-fit: contain;">
                        <?php else: ?>
                            <p>No hay imagen disponible.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Proveedores Asociados al Modelo de Funda -->
    <div class="card shadow-lg mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="card-title">Proveedores Asociados</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($proveedores)): ?>
                <ul class="list-group">
                    <?php foreach ($proveedores as $proveedor): ?>
                        <li class="list-group-item">
                            <strong><?= esc($proveedor['Nombre']) ?></strong> (ID Proveedor: <?= esc($proveedor['ID']) ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No hay proveedores asociados a este modelo de funda.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="text-center mt-4">
        <a href="<?= base_url('admin/modelosFundas') ?>" class="btn btn-secondary mx-2">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>

        <!-- update y delete disponibles solo con rol de administrador -->
        <?php
        // Asegurémonos de obtener el modelo de UsuariosModel
        $usuariosModel = model('App\Models\UsuariosModel');

        // Verificamos si el usuario tiene permisos de administrador
        $session = session();
        if ($session->has('user_id') && $usuariosModel->canAccessBackend($session->get('user_id'))) : ?>
            <a href="<?= base_url('admin/modelosFundas/update/' . $modeloFunda['ID']) ?>" class="btn btn-warning mx-2">
                <i class="fas fa-edit"></i> Editar Modelo de Funda
            </a>
            <a href="<?= base_url('admin/modelosFundas/delete/' . $modeloFunda['ID']) ?>" class="btn btn-danger mx-2" onclick="return confirm('¿Estás seguro de eliminar este modelo de funda?')">
                <i class="fas fa-trash-alt"></i> Eliminar Modelo de Funda
            </a>
        <?php endif ?>
    </div>
</section>