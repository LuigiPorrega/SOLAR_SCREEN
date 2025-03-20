<section>
    <h2><?= esc($title) ?></h2>

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

    <!-- Verificar si hay simulaciones -->
    <?php if ($simulaciones !== []): ?>
        <?php foreach ($simulaciones as $simulacion): ?>
            <h3>Simulación ID: <?= esc($simulacion['ID']) ?></h3>
            <div class="main">
                Condición de Luz: <?= esc($simulacion['CondicionLuz']) ?>
            </div>
            <p>
                Energía Generada: <?= esc($simulacion['EnergiaGenerada']) ?>
            </p>
            <p>
                Fecha: <?= date('d-m-Y', strtotime(esc($simulacion['Fecha']))) ?>
            </p>
            <p>
                <a href="<?= base_url('admin/simulaciones/' . $simulacion['ID']) ?>">Ver detalles</a>
            </p>
        <?php endforeach ?>
    <?php else: ?>
        <h3>No hay simulaciones</h3>
        <p>No se encontraron simulaciones.</p>
    <?php endif ?>

    <!-- Mostrar botón para añadir simulaciones si el usuario es admin -->
    <?php $session = session(); ?>
    <?php if ($session->get('isLoggedIn')): ?>
        <?php if ($session->get('role') === 'admin'): ?>
            <section>
                <a href="<?= base_url('admin/simulaciones/new') ?>">Añadir Simulación</a>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</section>