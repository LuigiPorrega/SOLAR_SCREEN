<?php if (!session()->has('user_id') || !model('App\Models\UsersModel')->isAdmin(session()->get('user_id'))): ?>
    <p>No tienes permisos para acceder a esta página.</p>
<?php else: ?>
    <?= view('templates/header', ['title' => 'Crear Usuario']) ?>

    <section>
        <h2><?= esc($title) ?></h2>

        <?= session()->getFlashdata('error') ?>
        <?= validation_list_errors() ?>

        <form action="<?= base_url('users/create') ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="Nombre">Nombre</label>
                <input type="text" name="Nombre" class="form-control" value="<?= set_value('Nombre') ?>">
            </div>

            <div class="form-group">
                <label for="Correo">Correo</label>
                <input type="email" name="Correo" class="form-control" value="<?= set_value('Correo') ?>">
            </div>

            <div class="form-group">
                <label for="Username">Nombre de Usuario</label>
                <input type="text" name="Username" class="form-control" value="<?= set_value('Username') ?>">
            </div>

            <div class="form-group">
                <label for="Password">Contraseña</label>
                <input type="password" name="Password" class="form-control">
            </div>

            <div class="form-group">
                <label for="Rol">Rol</label>
                <select name="Rol" class="form-control">
                    <option value="usuario">Usuario</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Crear Usuario</button>
        </form>
    </section>

    <?= view('templates/footer') ?>
<?php endif ?>
