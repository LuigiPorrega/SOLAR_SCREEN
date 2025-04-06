<section class="container">
    <h2>Detalle de la Simulación #<?= esc($simulacion['ID']) ?></h2>

    <!-- Información de la simulación -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>Información de la Simulación</h4>
        </div>
        <div class="card-body">
            <p><strong>Condición de luz:</strong> <?= esc($simulacion['CondicionLuz']) ?></p>
            <p><strong>Tiempo:</strong> <?= esc($simulacion['Tiempo']) ?> horas</p>
            <p><strong>Energía generada:</strong> <?= esc($simulacion['EnergiaGenerada']) ?> kWh</p>
            <p><strong>Fecha:</strong> <?= esc($simulacion['Fecha']) ?></p>
        </div>
    </div>

    <!-- Condiciones Meteorológicas -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>Condiciones Meteorológicas</h4>
        </div>
        <div class="card-body">
            <?php if (isset($condicionesMeteorologicas)): ?>
                <p><strong>Luz Solar:</strong> <?= esc($condicionesMeteorologicas['LuzSolar']) ?> lux</p>
                <p><strong>Temperatura:</strong> <?= esc($condicionesMeteorologicas['Temperatura']) ?> °C</p>
                <p><strong>Humedad:</strong> <?= esc($condicionesMeteorologicas['Humedad']) ?> %</p>
                <p><strong>Velocidad del viento:</strong> <?= esc($condicionesMeteorologicas['Viento']) ?> km/h</p>
                <p><strong>Descripción:</strong> <?= esc($condicionesMeteorologicas['Descripcion']) ?></p>
            <?php else: ?>
                <p><strong>Las condiciones meteorológicas no están disponibles.</strong></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Funda recomendada -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>Funda Recomendada</h4>
        </div>
        <div class="card-body">
            <?php if (isset($fundaPropuesta)): ?>
                <p><strong>Nombre de la funda:</strong> <?= esc($fundaPropuesta['Nombre']) ?></p>
                <p><strong>Tipo de funda:</strong> <?= esc($fundaPropuesta['TipoFunda']) ?></p>
                <p><strong>Capacidad de carga:</strong> <?= esc($fundaPropuesta['CapacidadCarga']) ?> kg</p>
                <p><strong>Descripción:</strong> <?= esc($fundaPropuesta['Descripcion']) ?></p>
                <img src="<?= esc($fundaPropuesta['ImagenURL'] ?: 'ruta/por/defecto.jpg') ?>" alt="Imagen de la funda" class="img-fluid" width="200">
            <?php else: ?>
                <p><strong>No hay funda propuesta disponible.</strong></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Explicación sobre la recomendación de la funda -->
    <div class="alert alert-info mb-4">
        <h5>Justificación de la Funda Recomendada</h5>
        <p><?= esc($justificacionFunda) ?></p>
    </div>

    <!-- Otras fundas propuestas -->
    <div class="card">
        <div class="card-header">
            <h4>Otras Fundas Propuestas</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($otrasFundasPropuestas as $otraFunda): ?>
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <img src="<?= esc($otraFunda['ImagenURL'] ?: 'ruta/por/defecto.jpg') ?>" alt="<?= esc($otraFunda['Nombre']) ?>" class="card-img-top" style="max-height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?= esc($otraFunda['Nombre']) ?></h5>
                                <p class="card-text"><?= esc($otraFunda['Descripcion']) ?></p>
                                <p><strong>Tipo de funda:</strong> <?= esc($otraFunda['TipoFunda']) ?></p>
                                <p><strong>Capacidad de carga:</strong> <?= esc($otraFunda['CapacidadCarga']) ?> kg</p>
                                <a href="/detalles-funda/<?= esc($otraFunda['ID']) ?>" class="btn btn-primary">Ver detalles</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</section>