<?php if (!session()->has('user_id') || !model('App\Models\UsersModel')->isAdmin(session()->get('user_id'))): ?>
    <p>No tienes permisos para acceder a esta página.</p>
<?php else: ?>
    <?= view('templates/header', ['title' => 'Detalle de Registro de Login']) ?>

    <section>
        <h2><?= esc($title) ?></h2>
        <h3>ID: <?= esc($log['ID']) ?></h3>
        <p>Usuario ID: <?= esc($log['UsuarioID']) ?></p>
        <p>Fecha y Hora: <?= esc($log['FechaHora']) ?></p>
        <p>Exitoso: <?= esc($log['success'] == 1 ? 'Exitoso' : 'Fallido') ?></p>
        <p>IP Address: <?= esc($log['ip_address']) ?></p>
        <p>User-Agent: <?= esc($log['user_agent']) ?></p>
        <p>Intentos: <?= esc($log['attempts']) ?></p>
        <p>Razón: <?= esc($log['reason']) ?></p>

        <a href="<?= base_url('loginlog') ?>" class="btn btn-secondary">Volver al listado</a>
    </section>

    <?= view('templates/footer') ?>
<?php endif ?>
