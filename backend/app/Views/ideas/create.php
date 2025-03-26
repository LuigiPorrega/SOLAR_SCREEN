<section class="container mt-4">
    <h2 class="text-center mb-4"><?= esc($title) ?></h2>

    <!-- Mensajes de error o éxito -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <!-- Mostrar los errores de validación del formulario -->
    <?= validation_list_errors() ?>

    <form action="<?= base_url('admin/ideas/create') ?>" method="post" class="p-4 shadow-lg rounded bg-light">
        <?= csrf_field() ?>

        <!-- Campo para el Título -->
        <div class="form-group mb-3">
            <label for="Titulo" class="font-weight-bold">Título</label>
            <input type="text" name="Titulo" id="Titulo" class="form-control <?= session('errors.Titulo') ? 'is-invalid' : '' ?>" value="<?= set_value('Titulo') ?>" required>
            <!-- Mostrar error si existe -->
            <?php if (session('errors.Titulo')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.Titulo') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo para la Descripción -->
        <div class="form-group mb-3">
            <label for="Descripcion" class="font-weight-bold">Descripción</label>
            <textarea name="Descripcion" id="Descripcion" class="form-control <?= session('errors.Descripcion') ? 'is-invalid' : '' ?>" rows="5" required><?= set_value('Descripcion') ?></textarea>
            <!-- Mostrar error si existe -->
            <?php if (session('errors.Descripcion')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.Descripcion') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Botón de creación -->
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary btn-lg w-50">Crear Idea</button>
        </div>
    </form>

    <!-- Botón para volver al listado -->
    <div class="text-center mt-4">
        <a href="<?= base_url('admin/ideas') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
</section>
