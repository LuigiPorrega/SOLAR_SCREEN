<section class="container">
    <h2 class="text-center"><?= esc($title) ?></h2>

    <!-- Paginación arriba -->
    <div class="d-flex justify-content-center my-2">
        <nav>
            <b style="font-size: 18px;"><?= $pager->links() ?></b>
        </nav>
    </div>

    <!-- Mostrar mensajes de éxito -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <!-- Mostrar mensajes de error -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Mostrar botón para añadir simulaciones si el usuario es admin -->
    <?php $session = session(); ?>
    <?php if ($session->get('isLoggedIn')): ?>
        <?php if ($session->get('role') === 'admin'): ?>
            <div class="text-center my-2 mb-5">
                <a href="<?= base_url('admin/simulaciones/new') ?>" class="btn btn-primary">Añadir Simulación</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Verificar si hay simulaciones -->
    <?php if (!empty($simulaciones)): ?>
        <div class="row">
            <?php foreach ($simulaciones as $simulacion): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-info">
                        <div class="card-body">
                            <h5 class="card-title">Simulación número <?= esc($simulacion['ID']) ?></h5>
                            <p><strong>Usuario:</strong> <?= esc($simulacion['UsuarioNombre']) ?></p>
                            <p><strong>Condición de Luz:</strong> <?= esc($simulacion['CondicionLuz']) ?></p>
                            <p><strong>Energía Generada:</strong> <?= esc($simulacion['EnergiaGenerada']) ?> Wh</p>
                            <p><strong>Fecha:</strong> <?= date('d-m-Y', strtotime(esc($simulacion['Fecha']))) ?></p>

                            <!-- Funda recomendada -->
                            <?php if (isset($simulacion['FundaID'])): ?>
                                <?php
                                // Obtener la funda usando el modelo fundasModel
                                $funda = $fundasModel->find($simulacion['FundaID']);
                                if ($funda):
                                ?>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p><strong>Funda recomendada:</strong><br> <?= esc($funda['Nombre']) ?></p>
                                        <!-- Mostrar la imagen de la funda a la derecha -->
                                        <img src="<?= base_url($funda['ImagenURL']) ?>" alt="Funda recomendada" style="width: 25vh; height: auto; object-fit: cover;">
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <div class="text-center mt-3">
                                <a href="<?= base_url('admin/simulaciones/' . $simulacion['ID']) ?>" class="btn btn-info">Ver detalles</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <h3>No hay simulaciones</h3>
        <p>No se encontraron simulaciones.</p>
    <?php endif; ?>
</section>