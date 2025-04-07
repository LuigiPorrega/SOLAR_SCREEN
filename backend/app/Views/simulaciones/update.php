<section class="container">
    <h2 class="text-center"><?= esc($title) ?></h2>

    <?= session()->getFlashdata('error') ?>
    <?= validation_list_errors() ?>

    <?php if ($simulacion !== null): ?>
        <form action="<?= base_url('admin/simulaciones/update/updated/' . $simulacion['ID']) ?>" method="post">
            <?= csrf_field() ?>

            <!-- Campo: Condición de Luz -->
            <div class="form-group">
                <label for="CondicionLuz">Condición de Luz</label>
                <select name="CondicionLuz" id="CondicionLuz" class="form-control" required>
                    <option value="">Selecciona la condición de luz</option>
                    <option value="Luz Solar Directa" <?= strtolower($simulacion['CondicionLuz']) === 'luz solar directa' ? 'selected' : '' ?>>Luz Solar Directa</option>
                    <option value="Luz Artificial" <?= strtolower($simulacion['CondicionLuz']) === 'luz artificial' ? 'selected' : '' ?>>Luz Artificial</option>
                </select>
            </div><br>

            <!-- Campo: Tiempo -->
            <div class="form-group">
                <label for="Tiempo">Tiempo (minutos)</label>
                <input type="number" name="Tiempo" id="Tiempo" class="form-control" value="<?= set_value('Tiempo', $simulacion['Tiempo']) ?>" required>
            </div><br>

            <!-- Campo: Condiciones Meteorológicas -->
            <div class="form-group">
                <label for="CondicionesMeteorologicasID">Condiciones Meteorológicas</label>
                <select name="CondicionesMeteorologicasID" id="CondicionesMeteorologicasID" class="form-control" required>
                    <option value="">Selecciona una condición meteorológica</option>
                    <?php foreach ($condicionesMeteorologicas as $condicion): ?>
                        <option value="<?= $condicion['ID'] ?>" <?= set_select('CondicionesMeteorologicasID', $condicion['ID'], $condicion['ID'] == $simulacion['CondicionesMeteorologicasID']) ?>>
                            Luz Solar: <?= $condicion['LuzSolar'] ?>, Temperatura: <?= $condicion['Temperatura'] ?>°C
                        </option>
                    <?php endforeach; ?>
                </select>
            </div><br>

            <!-- Botones de envío -->
            <div class="d-flex justify-content-center mt-4">
                <button type="submit" class="btn btn-success me-3">Actualizar Simulación</button>
                <a href="<?= base_url('admin/simulaciones') ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    <?php endif; ?>
</section>