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

    <form action="<?= base_url('admin/condicionesMeteorologicas/create') ?>" method="post" class="p-4 shadow-lg rounded bg-light">
        <?= csrf_field() ?>

        <!-- Campo para la Fecha -->
        <div class="form-group mb-3">
            <label for="Fecha" class="font-weight-bold">Fecha</label>
            <input type="date" name="Fecha" id="Fecha" class="form-control <?= session('errors.Fecha') ? 'is-invalid' : '' ?>" value="<?= set_value('Fecha') ?>" required>
            <!-- Mostrar error si existe -->
            <?php if (session('errors.Fecha')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.Fecha') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo para Luz Solar (en W/m²) -->
        <div class="form-group mb-3">
            <label for="LuzSolar" class="font-weight-bold">Luz Solar (W/m²)</label>
            <input type="number" name="LuzSolar" id="LuzSolar" class="form-control <?= session('errors.LuzSolar') ? 'is-invalid' : '' ?>" value="<?= set_value('LuzSolar') ?>" step="0.01" required>
            <!-- Mostrar error si existe -->
            <?php if (session('errors.LuzSolar')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.LuzSolar') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo para Temperatura (en °C) -->
        <div class="form-group mb-3">
            <label for="Temperatura" class="font-weight-bold">Temperatura (°C)</label>
            <input type="number" name="Temperatura" id="Temperatura" class="form-control <?= session('errors.Temperatura') ? 'is-invalid' : '' ?>" value="<?= set_value('Temperatura') ?>" step="0.01" required>
            <!-- Mostrar error si existe -->
            <?php if (session('errors.Temperatura')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.Temperatura') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo para Humedad (en %) -->
        <div class="form-group mb-3">
            <label for="Humedad" class="font-weight-bold">Humedad (%)</label>
            <input type="number" name="Humedad" id="Humedad" class="form-control <?= session('errors.Humedad') ? 'is-invalid' : '' ?>" value="<?= set_value('Humedad') ?>" step="0.01" required>
            <!-- Mostrar error si existe -->
            <?php if (session('errors.Humedad')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.Humedad') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo para Viento (en m/s) -->
        <div class="form-group mb-3">
            <label for="Viento" class="font-weight-bold">Viento (m/s)</label>
            <input type="number" name="Viento" id="Viento" class="form-control <?= session('errors.Viento') ? 'is-invalid' : '' ?>" value="<?= set_value('Viento') ?>" step="0.01" required>
            <!-- Mostrar error si existe -->
            <?php if (session('errors.Viento')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.Viento') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Botón de creación -->
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary btn-lg w-50">Crear Condición Meteorológica</button>
        </div>
    </form>

    <!-- Botón para volver al listado -->
    <div class="text-center mt-4">
        <a href="<?= base_url('admin/condicionesMeteorologicas') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
</section>
