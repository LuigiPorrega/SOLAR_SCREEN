<?php if (!session()->has('user_id') || !model('App\Models\UsersModel')->isAdmin(session()->get('user_id'))): ?>
    <p>No tienes permisos para acceder a esta página.</p>
<?php else: ?>
    <?= view('templates/header', ['title' => 'Lista de Registros de Login']) ?>

    <section>
        <h2><?= esc($title) ?></h2>

        <a href="<?= base_url('loginlog/export') ?>" class="btn btn-primary">Exportar LoginLog</a>

        <?php if ($logs !== []): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario ID</th>
                        <th>Fecha y Hora</th>
                        <th>Exitoso</th>
                        <th>IP Address</th>
                        <th>User-Agent</th>
                        <th>Intentos</th>
                        <th>Razón</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= esc($log['ID']) ?></td>
                            <td><?= esc($log['UsuarioID']) ?></td>
                            <td><?= esc($log['FechaHora']) ?></td>
                            <td><?= esc($log['success'] == 1 ? 'Exitoso' : 'Fallido') ?></td>
                            <td><?= esc($log['ip_address']) ?></td>
                            <td><?= esc($log['user_agent']) ?></td>
                            <td><?= esc($log['attempts']) ?></td>
                            <td><?= esc($log['reason']) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php else: ?>
            <h3>No hay registros de login</h3>
            <p>No se encontraron registros de inicio de sesión.</p>
        <?php endif ?>
    </section>

    <?= view('templates/footer') ?>
<?php endif ?>
