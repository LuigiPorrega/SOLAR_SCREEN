<?= view('templates/header', ['title' => 'Lista de Ideas']) ?>

<section>
    <h2><?= esc($title) ?></h2>

    <?php if ($ideas !== []): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Fecha de Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ideas as $idea): ?>
                    <tr>
                        <td><?= esc($idea['ID']) ?></td>
                        <td><?= esc($idea['Titulo']) ?></td>
                        <td><?= esc($idea['Descripcion']) ?></td>
                        <td><?= esc($idea['FechaCreacion']) ?></td>
                        <td>
                            <a href="<?= base_url('ideas/' . $idea['ID']) ?>" class="btn btn-info btn-sm">Ver</a>
                            <?php $session = session();
                            if (!empty($session->get('user'))) { ?>
                                <a href="<?= base_url('ideas/update/' . $idea['ID']) ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="<?= base_url('ideas/delete/' . $idea['ID']) ?>" class="btn btn-danger btn-sm">Eliminar</a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php else: ?>
        <h3>No hay ideas</h3>
        <p>No se encontraron ideas.</p>
    <?php endif ?>

    <?php $session = session();
    if (!empty($session->get('user'))) { ?>
        <section>
            <a href="<?= base_url('ideas/new') ?>" class="btn btn-success">Añadir Idea</a>
        </section>
    <?php } ?>
</section>

<?= view('templates/footer') ?>
