<section class="container mt-4">
    <h2 class="text-center mb-4"><?= esc($title) ?></h2>

    <!-- Mensajes de error o éxito -->
    <?= session()->getFlashdata('error') ?>
    <?= validation_list_errors() ?>

    <?php if ($idea !== null): ?>
        <form action="<?= base_url('admin/ideas/update/updated/' . $idea['ID']) ?>" method="post" class="p-4 shadow-lg rounded bg-light">
            <?= csrf_field() ?>

            <!-- Campo para el Título -->
            <div class="form-group mb-3">
                <label for="Titulo" class="font-weight-bold">Título</label>
                <input type="text" name="Titulo" id="Titulo" class="form-control <?= isset($errors['Titulo']) ? 'is-invalid' : '' ?>" value="<?= esc($idea['Titulo']) ?>" required>
                <?php if (isset($errors['Titulo'])): ?>
                    <div class="invalid-feedback">
                        <?= $errors['Titulo'] ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Campo para la Descripción -->
            <div class="form-group mb-3">
                <label for="Descripcion" class="font-weight-bold">Descripción</label>
                <textarea name="Descripcion" id="Descripcion" class="form-control <?= isset($errors['Descripcion']) ? 'is-invalid' : '' ?>" rows="5" required><?= esc($idea['Descripcion']) ?></textarea>
                <?php if (isset($errors['Descripcion'])): ?>
                    <div class="invalid-feedback">
                        <?= $errors['Descripcion'] ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Botón de actualización -->
            <div class="text-center mt-3">
                <button type="submit" class="btn btn-warning btn-lg w-50">Actualizar Idea</button>
            </div>
        </form>
    <?php endif ?>

    <!-- Botón para volver al listado -->
    <div class="text-center mt-4">
        <a href="<?= base_url('admin/ideas') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
</section>