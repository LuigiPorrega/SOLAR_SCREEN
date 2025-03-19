<?= view('templates/header', ['title' => 'Editar Idea']) ?>

<section>
    <h2><?= esc($title) ?></h2>

    <?= session()->getFlashdata('error') ?>
    <?= validation_list_errors() ?>

    <?php if ($idea !== null): ?>
        <form action="<?= base_url('ideas/update/updated/' . $idea['ID']) ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="Titulo">Título</label>
                <input type="text" name="Titulo" class="form-control" value="<?= esc($idea['Titulo']) ?>">
            </div>

            <div class="form-group">
                <label for="Descripcion">Descripción</label>
                <textarea name="Descripcion" class="form-control" rows="5"><?= esc($idea['Descripcion']) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Actualizar Idea</button>
        </form>
    <?php endif ?>
</section>

<?= view('templates/footer') ?>
