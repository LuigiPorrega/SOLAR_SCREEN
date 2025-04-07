<section class="container">
    <h2 class="text-center"><?= esc($title) ?></h2>
    <form action="<?= base_url('admin/simulaciones/create') ?>" method="post">
        <?= csrf_field() ?>

        <!-- Campo: Condición de Luz -->
        <div class="form-group">
            <label for="CondicionLuz">Condición de Luz</label>
            <select name="CondicionLuz" id="CondicionLuz" class="form-control">
                <option value="">Selecciona la condición de luz</option>
                <option value="Luz Solar Directa" <?= set_select('CondicionLuz', 'Luz Solar Directa') ?>>Luz Solar Directa</option>
                <option value="Luz Artificial" <?= set_select('CondicionLuz', 'Luz Artificial') ?>>Luz Artificial</option>
            </select>
        </div>

        <!-- Campo: Tiempo -->
        <div class="form-group">
            <label for="Tiempo">Tiempo (minutos)</label>
            <input type="number" name="Tiempo" id="Tiempo" class="form-control" value="<?= set_value('Tiempo') ?>" required>
        </div>

        <!-- Campo: Condiciones Meteorológicas -->
        <div class="form-group">
            <label for="CondicionesMeteorologicasID">Condiciones Meteorológicas</label>
            <select name="CondicionesMeteorologicasID" id="CondicionesMeteorologicasID" class="form-control" required>
                <option value="">Selecciona una condición meteorológica</option>
                <?php foreach ($condicionesMeteorologicas as $condicion): ?>
                    <option value="<?= $condicion['ID'] ?>" <?= set_select('CondicionesMeteorologicasID', $condicion['ID']) ?>>
                        Luz Solar: <?= $condicion['LuzSolar'] ?>, Temperatura: <?= $condicion['Temperatura'] ?>°C
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Mostrar la energía generada -->
        <?php if (isset($energiaGenerada)): ?>
            <h3>Energía Generada: <?= $energiaGenerada ?> kWh</h3>

            <h4>Funda Recomendada:</h4>
            <p><strong><?= esc($fundaRecomendada) ?></strong></p>
            <p><em><?= esc($justificacionFunda) ?></em></p>

            <h4>Fundas Opcionales:</h4>
            <ul>
                <?php foreach ($fundasOpcionales as $funda): ?>
                    <li>
                        <label>
                            <input type="radio" name="FundaID" value="<?= $funda['ID'] ?>"> <?= esc($funda['Nombre']) ?> - Capacidad de Carga: <?= esc($funda['CapacidadCarga']) ?> kg
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- Botones de envío -->
        <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-success me-3">Crear Simulación</button>
            <a href="<?= base_url('admin/simulaciones') ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</section>