<section class="container">
    <h2 class="d-flex justify-content-center">Detalle de la Simulación número <?= esc($simulacion['ID']) ?></h2>

    <!-- Botones de acción -->
    <div class="text-center mt-4">
        <a href="<?= base_url('admin/simulaciones') ?>" class="btn btn-secondary mx-2">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>

        <!-- update y delete disponibles solo con rol de administrador -->
        <?php
        // Asegurémonos de obtener el modelo de UsuariosModel
        $usuariosModel = model('App\Models\UsuariosModel');

        // Verificamos si el usuario tiene permisos de administrador
        $session = session();
        if ($session->has('user_id') && $usuariosModel->canAccessBackend($session->get('user_id'))) : ?>
            <a href="<?= base_url('admin/simulaciones/update/' . $simulacion['ID']) ?>" class="btn btn-warning mx-2">
                <i class="fas fa-edit"></i> Editar Simulación
            </a>
            <a href="<?= base_url('admin/simulaciones/delete/' . $simulacion['ID']) ?>" class="btn btn-danger mx-2" onclick="return confirm('¿Estás seguro de eliminar esta simulación?')">
                <i class="fas fa-trash-alt"></i> Eliminar Simulación
            </a>
        <?php endif ?>
    </div>


    <!-- Información de la simulación -->
    <div class="card mb-4 border-info my-4">
        <div class="card-header border-info">
            <h4 class="text-primary">Información de la Simulación</h4>
        </div>
        <div class="card-body">
            <p><strong>Condición de luz:</strong> <?= esc($simulacion['CondicionLuz']) ?></p>
            <p><strong>Tiempo:</strong> <?= esc($simulacion['Tiempo']) ?> minutos</p>
            <p><strong>Energía generada:</strong> <?= esc($simulacion['EnergiaGenerada']) ?> kWh</p>
            <p><strong>Fecha:</strong> <?= date('d-m-Y', strtotime($simulacion['Fecha'])) ?></p>
        </div>
    </div>

    <!-- Condiciones Meteorológicas -->
    <div class="card mb-4 border-success">
        <div class="card-header border-success">
            <h4 class="text-success">Condiciones Meteorológicas</h4>
        </div>
        <div class="card-body">
            <?php if (isset($condicionesMeteorologicas)): ?>
                <p><strong>Luz Solar:</strong> <?= esc($condicionesMeteorologicas['LuzSolar']) ?> lux</p>
                <p><strong>Temperatura:</strong> <?= esc($condicionesMeteorologicas['Temperatura']) ?> °C</p>
                <p><strong>Humedad:</strong> <?= esc($condicionesMeteorologicas['Humedad']) ?> %</p>
                <p><strong>Velocidad del viento:</strong> <?= esc($condicionesMeteorologicas['Viento']) ?> km/h</p>
            <?php else: ?>
                <p><strong>Las condiciones meteorológicas no están disponibles.</strong></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Funda recomendada -->
    <div class="card mb-4 border-warning">
        <div class="card-header border-warning">
            <h4 class="text-warning">Funda Recomendada</h4>
        </div>
        <div class="card-body">
            <?php if (isset($fundaPropuesta)): ?>
                <div class="row align-items-center">
                    <!-- Columna para el texto (izquierda) -->
                    <div class="col-md-8">
                        <p><strong>Nombre de la funda:</strong> <?= esc($fundaPropuesta['Nombre']) ?></p>
                        <p><strong>Tipo de funda:</strong> <?= esc($fundaPropuesta['TipoFunda']) ?></p>
                        <p><strong>Capacidad de carga:</strong> <?= esc($fundaPropuesta['CapacidadCarga']) ?> kg</p>
                        <p><strong>Tamaño:</strong> <?= esc($fundaPropuesta['Tamaño']) ?></p>
                    </div>
                    <!-- Columna para la imagen (derecha) -->
                    <div class="col-md-4 text-center">
                        <img src="<?= base_url($fundaPropuesta['ImagenURL']) ?>" alt="Imagen de la funda" class="img-fluid" width="250">
                    </div>
                </div>
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

    <!-- Otras Fundas Propuestas -->
    <div class="card mb-4 border-info">
        <div class="card-header border-info">
            <h4 class="text-info">Otras Fundas similares</h4>
        </div>
        <div class="card-body">
            <?php if (isset($otrasFundasPropuestas) && count($otrasFundasPropuestas) > 0): ?>
                <div class="row">
                    <?php foreach ($otrasFundasPropuestas as $otraFunda): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card d-flex flex-column h-100 border-info">
                                <!-- Imagen de la funda -->
                                <img src="<?= base_url($otraFunda['ImagenURL']) ?>" alt="<?= esc($otraFunda['Nombre']) ?>" class="card-img-top" width="50" style="height: mt-auto; object-fit: cover;">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= esc($otraFunda['Nombre']) ?></h5>
                                    <p><strong>Tipo de funda:</strong> <?= esc($otraFunda['TipoFunda']) ?></p>
                                    <p><strong>Capacidad de carga:</strong> <?= esc($otraFunda['CapacidadCarga']) ?> kg</p>
                                    <a href="<?= base_url('admin/modelosFundas/' . $otraFunda['ID']) ?>" class="btn btn-primary mt-auto">Ver detalles</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p><strong>No hay otras fundas propuestas disponibles.</strong></p>
            <?php endif; ?>
        </div>
    </div>
</section>