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

    <!-- Insert disponible solo para administradores -->
    <?php
    $usuariosModel = model('App\Models\UsuariosModel');
    $session = session();
    if ($session->has('user_id') && $usuariosModel->canAccessBackend($session->get('user_id'))) : ?>
        <div class="text-center mb-4">
            <a href="<?= base_url('admin/proveedores/new') ?>" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Agregar Nuevo Proveedor
            </a>
        </div>
    <?php endif; ?>

    <?php if (!empty($proveedores)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th class="text-center">ID</th>
                        <th class="text-center">Nombre</th>
                        <th class="text-center">País</th>
                        <th class="text-center">Contacto</th>
                        <th class="text-center">Teléfono</th>
                        <th class="text-center">Activo</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proveedores as $proveedor): ?>
                        <tr>
                            <td class="text-center"><?= esc($proveedor['ID']) ?></td>
                            <td class="text-center"><?= esc($proveedor['Nombre']) ?></td>
                            <td class="text-center"><?= esc($proveedor['Pais']) ?></td>
                            <td class="text-center"><?= esc($proveedor['ContactoNombre']) ?></td>
                            <td class="text-center"><?= esc($proveedor['ContactoTelefono']) ?></td>
                            <td class="text-center"><?= esc($proveedor['Activo']) == 1 ? 'Sí' : 'No' ?></td>
                            <td class="text-center">
                                <a href="<?= base_url('admin/proveedores/' . $proveedor['ID']) ?>" class="btn btn-info btn-sm">Ver</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <h3 class="text-center">No hay proveedores</h3>
            <p class="text-center">No se encontraron proveedores en el sistema.</p>
        </div>
    <?php endif; ?>
</section>