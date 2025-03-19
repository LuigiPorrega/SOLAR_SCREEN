<?php if (!session()->has('user_id') || !model('App\Models\UsersModel')->isAdmin(session()->get('user_id'))): ?>
    <p>No tienes permisos para acceder a esta página.</p>
<?php else: ?>
    <?= view('templates/header', ['title' => 'Editar Usuario']) ?>

    <section>
        <h2><?= esc($title) ?></h2>

        <?= session()->getFlashdata('error') ?>
        <?= validation_list_errors() ?>

        <?php if ($user !== null): ?>
            <form action="<?= base_url('users/update/updated/' . $user['ID']) ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="Nombre">Nombre</label>
                    <input type="text" name="Nombre" class="form-control" value="<?= esc($user['Nombre']) ?>">
                </div>

                <div class="form-group">
                    <label for="Correo">Correo</label>
                    <input type="email" name="Correo" class="form-control" value="<?= esc($user['Correo']) ?>">
                </div>

                <div class="form-group">
                    <label for="Username">Nombre de Usuario</label>
                    <input type="text" name="Username" class="form-control" value="<?= esc($user['Username']) ?>">
                </div>

                <div class="form-group">
                    <label for="Rol">Rol</label>
                    <select name="Rol" class="form-control">
                        <option value="usuario" <?= ($user['Rol'] === 'usuario') ? 'selected' : '' ?>>Usuario</option>
                        <option value="admin" <?= ($user['Rol'] === 'admin') ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
            </form>
        <?php endif ?>
    </section>

    <?= view('templates/footer') ?>
<?php endif ?>
