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

        <?= session()->getFlashdata('error') ?>
        <?= validation_list_errors() ?>

        <?php if ($user !== null): ?>

            <form action="<?= base_url('admin/users/update/updated/' . $user['ID']) ?>" method="post">
                <?= csrf_field() ?>

                <!-- Nombre -->
                <div class="form-group mb-3">
                    <label for="Nombre"><b>Nombre</b></label>
                    <input type="text" id="Nombre" name="Nombre" class="form-control" value="<?= esc($user['Nombre']) ?>" required>
                </div>

                <!-- Correo -->
                <div class="form-group mb-3">
                    <label for="Correo"><b>Correo</b></label>
                    <input type="email" id="Correo" name="Correo" class="form-control" value="<?= esc($user['Correo']) ?>" required>
                </div>

                <!-- Fecha de Nacimiento -->
                <div class="form-group mb-3">
                    <label for="FechaNacimiento"><b>Fecha de Nacimiento</b></label>
                    <input type="date" id="FechaNacimiento" name="FechaNacimiento" class="form-control" value="<?= esc($user['FechaNacimiento']) ?>" required>
                </div>

                <!-- Nombre de Usuario -->
                <div class="form-group mb-3">
                    <label for="Username"><b>Nombre de Usuario</b></label>
                    <input type="text" id="Username" name="Username" class="form-control" value="<?= esc($user['Username']) ?>" required>
                </div>

                <!-- Rol -->
                <div class="form-group mb-3">
                    <label for="Rol"><b>Rol</b></label>
                    <select id="Rol" name="Rol" class="form-control select-dropdown">
                        <option value="usuario" <?= ($user['Rol'] === 'usuario') ? 'selected' : '' ?>>Usuario</option>
                        <option value="admin" <?= ($user['Rol'] === 'admin') ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>

                <!-- Contraseña -->
                <div class="form-group mb-3">
                    <label for="Password"><b>Contraseña</b></label>
                    <input type="password" id="Password" name="Password" class="form-control" placeholder="Dejar en blanco si no deseas cambiarla">
                </div>

                <button type="submit" class="btn btn-primary mt-3">Actualizar Usuario</button>
            </form>
        <?php endif ?>
    </section>

<?php endif ?>