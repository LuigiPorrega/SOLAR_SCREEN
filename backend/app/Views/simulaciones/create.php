<section class="container">
    <h2 class="text-center"><?= esc($title) ?></h2>

    <!-- Formulario para nueva simulación -->
    <form method="post" action="<?= base_url('admin/simulaciones/create') ?>" class="my-4">
        <?= csrf_field() ?> <!-- Protección contra CSRF -->

        <!-- Condición de Luz -->
        <div class="mb-3">
            <label for="CondicionLuz" class="form-label">Condición de Luz</label>
            <select class="form-select" id="CondicionLuz" name="CondicionLuz" required>
                <option value="">Selecciona una opción...</option>
                <option value="Luz solar directa">Luz solar directa</option>
                <option value="Luz artificial">Luz artificial</option>
                <option value="Nublado">Nublado</option>
            </select>
        </div>

        <!-- Tiempo de recarga (minutos) -->
        <div class="mb-3">
            <label for="Tiempo" class="form-label">Tiempo de recarga (en minutos)</label>
            <input type="number" class="form-control" id="Tiempo" name="Tiempo" min="1" required>
        </div>

        <!-- Condiciones meteorológicas -->
        <div class="mb-3">
            <label for="CondicionesMeteorologicasID" class="form-label">Condiciones Meteorológicas</label>
            <select class="form-select" id="CondicionesMeteorologicasID" name="CondicionesMeteorologicasID" required>
                <option value="">Selecciona las condiciones meteorológicas...</option>
                <?php foreach ($condicionesMeteorologicas as $condicion): ?>
                    <option value="<?= esc($condicion['ID']) ?>">
                        <?= esc($condicion['Descripcion']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Funda recomendada -->
        <div class="mb-3">
            <label for="FundaID" class="form-label">Funda recomendada</label>
            <select class="form-select" id="FundaID" name="FundaID" required>
                <option value="">Selecciona una funda...</option>
                <?php foreach ($fundas as $funda): ?>
                    <option value="<?= esc($funda['ID']) ?>">
                        <?= esc($funda['Nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Botones de envío -->
        <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-success me-3">Crear Simulación</button>
            <a href="<?= base_url('admin/simulaciones') ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</section>