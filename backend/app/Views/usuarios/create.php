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

        <form action="<?= base_url('admin/users/create') ?>" method="post">
            <?= csrf_field() ?>

            <!-- Nombre -->
            <div class="form-group mb-3">
                <label for="Nombre"><b>Nombre</b></label>
                <input type="text" id="Nombre" name="Nombre" class="form-control" value="<?= set_value('Nombre', $Nombre) ?>" required>
            </div>

            <!-- Correo -->
            <div class="form-group mb-3">
                <label for="Correo"><b>Correo</b></label>
                <input type="email" id="Correo" name="Correo" class="form-control" value="<?= set_value('Correo', $Correo) ?>" required>
            </div>

            <!-- Fecha de Nacimiento -->
            <div class="form-group mb-3">
                <label for="FechaNacimiento"><b>Fecha de Nacimiento</b></label>
                <input type="date" id="FechaNacimiento" name="FechaNacimiento" class="form-control" value="<?= set_value('FechaNacimiento', $FechaNacimiento) ?>" required>
            </div>

            <!-- Nombre de Usuario -->
            <div class="form-group mb-3">
                <label for="Username"><b>Nombre de Usuario</b></label>
                <input type="text" id="Username" name="Username" class="form-control" value="<?= set_value('Username', $Username) ?>" required>
            </div>

            <!-- Contraseña -->
            <div class="form-group mb-3">
                <label for="Password"><b>Contraseña</b></label>
                <input type="password" id="Password" name="Password" class="form-control" required>
            </div>

            <!-- Rol -->
            <div class="form-group mb-3">
                <label for="Rol"><b>Rol</b></label>
                <select id="Rol" name="Rol" class="form-control" required>
                    <option value="usuario" <?= set_select('Rol', 'usuario', TRUE) ?>>Usuario</option>
                    <option value="admin" <?= set_select('Rol', 'admin') ?>>Administrador</option>
                </select>
            </div>

            <!-- Campo oculto para GoogleID -->
            <input type="hidden" id="GoogleID" name="GoogleID" value="<?= set_value('GoogleID', $GoogleID) ?>">

            <button type="submit" class="btn btn-primary">Crear Usuario</button>
        </form>

    </section>

<?php endif ?>