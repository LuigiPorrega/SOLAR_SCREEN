<?= view('templates/header', ['title' => 'Crear Idea']) ?>

<section>
    <h2><?= esc($title) ?></h2>

    <?= session()->getFlashdata('error') ?>
    <?= validation_list_errors() ?>

    <form action="<?= base_url('ideas/create') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="Titulo">Título</label>
            <input type="text" name="Titulo" class="form-control" value="<?= set_value('Titulo') ?>">
        </div>

        <div class="form-group">
            <label for="Descripcion">Descripción</label>
            <textarea name="Descripcion" class="form-control" rows="5"><?= set_value('Descripcion') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Crear Idea</button>
    </form>
</section>

<?= view('templates/footer') ?>
