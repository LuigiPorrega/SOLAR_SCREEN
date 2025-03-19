<?= view('templates/header', ['title' => 'Detalle de Idea']) ?>

<section>
    <h2><?= esc($title) ?></h2>
    <h3>Título: <?= esc($idea['Titulo']) ?></h3>
    <p>Descripción: <?= esc($idea['Descripcion']) ?></p>
    <p>Fecha de Creación: <?= esc($idea['FechaCreacion']) ?></p>

    <a href="<?= base_url('ideas') ?>" class="btn btn-secondary">Volver al listado</a>
</section>

<?= view('templates/footer') ?>
