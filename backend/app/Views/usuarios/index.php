<?php
// Asegurémonos de obtener el modelo de UsuariosModel
$usuariosModel = model('App\Models\UsuariosModel');

// Verificamos si el usuario tiene permisos para acceder
if (!session()->has('user_id') || !$usuariosModel->canAccessBackend(session()->get('user_id'))): ?>
    <div class="alert alert-danger">
        <p>No tienes permisos para acceder a esta página.</p>
    </div>
<?php else: ?>
    <section class="container mt-4">
        <h2 class="text-center"><?= esc($title) ?></h2>

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


        <?php if (!empty($users)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center">ID</th>
                            <th class="text-center">Nombre</th>
                            <th class="text-center">Correo</th>
                            <th class="text-center">Nombre de Usuario</th>
                            <th class="text-center">Rol</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="text-center"><?= esc($user['ID']) ?></td>
                                <td class="text-center"><?= esc($user['Nombre']) ?></td>
                                <td class="text-center"><?= esc($user['Correo']) ?></td>
                                <td class="text-center"><?= esc($user['Username']) ?></td>
                                <td class="text-center"><?= esc($user['Rol']) ?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('admin/users/' . $user['ID']) ?>" class="btn btn-info btn-sm">Detalles</a>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <h3>No hay usuarios</h3>
                <p>No se encontraron usuarios.</p>
            </div>
        <?php endif ?>

        <div class="text-center mt-4">
            <a href="<?= base_url('admin/users/new') ?>" class="btn btn-success">Añadir Usuario</a>
        </div>
    </section>
<?php endif ?>