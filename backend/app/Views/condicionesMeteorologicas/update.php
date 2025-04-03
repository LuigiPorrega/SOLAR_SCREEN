<section class="container mt-4">
    <h2 class="text-center mb-4"><?= esc($title) ?></h2>

    <!-- Mensajes de error o éxito -->
    <?= session()->getFlashdata('error') ?>
    <?= validation_list_errors() ?>

    <?php if ($condicion !== null): ?>
        <form action="<?= base_url('admin/condicionesMeteorologicas/update/updated/' . $condicion['ID']) ?>" method="post" class="p-4 shadow-lg rounded bg-light">
            <?= csrf_field() ?>

            <!-- Campo para la Fecha -->
            <div class="form-group mb-3">
                <label for="Fecha" class="font-weight-bold">Fecha</label>
                <input type="date" name="Fecha" id="Fecha" class="form-control <?= isset($errors['Fecha']) ? 'is-invalid' : '' ?>" value="<?= esc($condicion['Fecha']) ?>" required>
                <?php if (isset($errors['Fecha'])): ?>
                    <div class="invalid-feedback">
                        <?= $errors['Fecha'] ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Campo para la Luz Solar -->
            <div class="form-group mb-3">
                <label for="LuzSolar" class="font-weight-bold">Luz Solar (en W/m²)</label>
                <input type="number" step="0.01" name="LuzSolar" id="LuzSolar" class="form-control <?= isset($errors['LuzSolar']) ? 'is-invalid' : '' ?>" value="<?= esc($condicion['LuzSolar']) ?>" required>
                <?php if (isset($errors['LuzSolar'])): ?>
                    <div class="invalid-feedback">
                        <?= $errors['LuzSolar'] ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Campo para la Temperatura -->
            <div class="form-group mb-3">
                <label for="Temperatura" class="font-weight-bold">Temperatura (en °C)</label>
                <input type="number" step="0.1" name="Temperatura" id="Temperatura" class="form-control <?= isset($errors['Temperatura']) ? 'is-invalid' : '' ?>" value="<?= esc($condicion['Temperatura']) ?>" required>
                <?php if (isset($errors['Temperatura'])): ?>
                    <div class="invalid-feedback">
                        <?= $errors['Temperatura'] ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Campo para la Humedad -->
            <div class="form-group mb-3">
                <label for="Humedad" class="font-weight-bold">Humedad (%)</label>
                <input type="number" step="0.1" name="Humedad" id="Humedad" class="form-control <?= isset($errors['Humedad']) ? 'is-invalid' : '' ?>" value="<?= esc($condicion['Humedad']) ?>" required>
                <?php if (isset($errors['Humedad'])): ?>
                    <div class="invalid-feedback">
                        <?= $errors['Humedad'] ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Campo para el Viento -->
            <div class="form-group mb-3">
                <label for="Viento" class="font-weight-bold">Viento (en km/h)</label>
                <input type="number" step="0.1" name="Viento" id="Viento" class="form-control <?= isset($errors['Viento']) ? 'is-invalid' : '' ?>" value="<?= esc($condicion['Viento']) ?>" required>
                <?php if (isset($errors['Viento'])): ?>
                    <div class="invalid-feedback">
                        <?= $errors['Viento'] ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Botón de actualización -->
            <div class="text-center mt-3">
                <button type="submit" class="btn btn-warning btn-lg w-50">Actualizar Condición Meteorológica</button>
            </div>
        </form>
    <?php endif ?>

    <!-- Botón para volver al listado -->
    <div class="text-center mt-4">
        <a href="<?= base_url('admin/condicionesMeteorologicas') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
</section>