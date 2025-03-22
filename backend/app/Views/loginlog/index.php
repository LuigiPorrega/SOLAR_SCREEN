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
  

        <a href="<?= base_url('loginlog/export') ?>" class="btn btn-primary mt-4">Exportar LoginLog</a>

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
