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

        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><?= esc($user['Nombre']) ?> - Detalles</h5>
            </div>
            <div class="card-body">
                <!-- Mostrar todos los campos de la tabla, con formato de fecha -->
                <p><strong>ID:</strong> <?= esc($user['ID']) ?></p>
                <p><strong>Nombre:</strong> <?= esc($user['Nombre']) ?></p>
                <p><strong>Correo:</strong> <?= esc($user['Correo']) ?></p>
                <p><strong>Fecha de Nacimiento:</strong> <?= date('d-m-Y', strtotime($user['FechaNacimiento'])) ?></p>
                <p><strong>GoogleID:</strong> <?= esc($user['GoogleID']) ?></p>
                <p><strong>Nombre de Usuario:</strong> <?= esc($user['Username']) ?></p>
                <p><strong>Password Hash:</strong> <?= esc($user['PasswordHash']) ?></p>
                <p><strong>Fecha de Registro:</strong> <?= date('d-m-Y', strtotime($user['Fecha_Registro'])) ?></p>
                <p><strong>Rol:</strong> <?= esc($user['Rol']) ?></p>

                <!-- Botones de acción -->
                <div class="mt-4">
                    <a href="<?= base_url('admin/users/update/' . $user['ID']) ?>" class="btn btn-warning btn-sm">Editar Usuario</a>
                    <a href="<?= base_url('admin/users/delete/' . $user['ID']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">Eliminar Usuario</a>
                </div>
            </div>
        </div>

        <!-- Botón de Volver al Listado centrado fuera de la tarjeta -->
        <div class="text-center mt-4">
            <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary btn-sm">Volver al Listado</a>
        </div>
    </section>
<?php endif ?>