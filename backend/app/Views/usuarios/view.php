<?php if (!session()->has('user_id') || !model('App\Models\UsersModel')->isAdmin(session()->get('user_id'))): ?>
    <p>No tienes permisos para acceder a esta página.</p>
<?php else: ?>
    <?= view('templates/header', ['title' => 'Detalle de Usuario']) ?>

    <section>
        <h2><?= esc($title) ?></h2>
        <h3>Nombre: <?= esc($user['Nombre']) ?></h3>
        <p>Correo: <?= esc($user['Correo']) ?></p>
        <p>Nombre de Usuario: <?= esc($user['Username']) ?></p>
        <p>Rol: <?= esc($user['Rol']) ?></p>

        <a href="<?= base_url('users') ?>" class="btn btn-secondary">Volver al listado</a>
    </section>

    <?= view('templates/footer') ?>
<?php endif ?>
