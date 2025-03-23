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

        <div class="d-flex justify-content-center my-5">
            <a href="<?= base_url('admin/loginlog/export') ?>" class="btn btn-primary">Exportar LoginLog</a>
        </div>

        <!-- Mostrar los intentos exitosos -->
        <h3>Intentos Exitosos</h3>
        <?php if ($logs['successfulLogs'] !== []): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Fecha y Hora</th>
                        <th>IP Address</th>
                        <th>User-Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs['successfulLogs'] as $log): ?>
                        <tr>
                            <td><?= esc($log['ID']) ?></td>
                            <td><?= esc($log['Username']) ?> (<?= esc($log['Nombre']) ?>)</td>
                            <td><?= esc($log['FechaHora']) ?></td>
                            <td><?= esc($log['IpAddress']) ?></td>
                            <td><?= esc($log['UserAgent']) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php else: ?>
            <h3>No hay registros de login exitosos</h3>
            <p>No se encontraron registros de inicio de sesión exitoso.</p>
        <?php endif ?>

        <!-- Mostrar los intentos fallidos -->
        <h3>Intentos Fallidos</h3>
        <?php if ($logs['failedLogs'] !== []): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Usuario ID</th>
                        <th>Intentos Fallidos</th>
                        <th>Última Fecha</th>
                        <th>IP Address</th>
                        <th>User-Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs['failedLogs'] as $log): ?>
                        <tr>
                            <td><?= esc($log['UsuarioID'] ?? 'Usuario no encontrado') ?></td>
                            <td><?= esc($log['numeros_intentos']) ?> intentos fallidos</td>
                            <td><?= esc($log['ultima_fecha']) ?></td>
                            <td><?= esc($log['IpAddress']) ?></td>
                            <td><?= esc($log['UserAgent']) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php else: ?>
            <h3>No hay intentos fallidos registrados</h3>
            <p>No se encontraron intentos de login fallidos.</p>
        <?php endif ?>
    </section>

<?php endif ?>