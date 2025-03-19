<?php if (!session()->has('user_id') || !model('App\Models\UsersModel')->isAdmin(session()->get('user_id'))): ?>
    <p>No tienes permisos para acceder a esta página.</p>
<?php else: ?>
    <?= view('templates/header', ['title' => 'Lista de Usuarios']) ?>

    <section>
        <h2><?= esc($title) ?></h2>

        <?php if ($users !== []): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Nombre de Usuario</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= esc($user['ID']) ?></td>
                            <td><?= esc($user['Nombre']) ?></td>
                            <td><?= esc($user['Correo']) ?></td>
                            <td><?= esc($user['Username']) ?></td>
                            <td><?= esc($user['Rol']) ?></td>
                            <td>
                                <a href="<?= base_url('users/' . $user['ID']) ?>" class="btn btn-info btn-sm">Ver</a>
                                <a href="<?= base_url('users/update/' . $user['ID']) ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="<?= base_url('users/delete/' . $user['ID']) ?>" class="btn btn-danger btn-sm">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php else: ?>
            <h3>No hay usuarios</h3>
            <p>No se encontraron usuarios.</p>
        <?php endif ?>

        <section>
            <a href="<?= base_url('users/new') ?>" class="btn btn-success">Añadir Usuario</a>
        </section>
    </section>

    <?= view('templates/footer') ?>
<?php endif ?>
