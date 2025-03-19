<?= view('templates/header', ['title' => 'Lista de Simulaciones']) ?>

<section>
    <h2><?= esc($title) ?></h2>

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
                Fecha: <?= esc($simulacion['Fecha']) ?>
            </p>
            <p>
                <a href="<?= base_url('simulaciones/' . $simulacion['ID']) ?>">Ver detalles</a>
                &nbsp;
                <?php $session = session();
                if (!empty($session->get('user'))) { ?>
                    <a href="<?= base_url('simulaciones/delete/' . $simulacion['ID']) ?>">Eliminar</a>
                    &nbsp;
                    <a href="<?= base_url('simulaciones/update/' . $simulacion['ID']) ?>">Actualizar</a>
                <?php } ?>
            </p>
        <?php endforeach ?>
    <?php else: ?>
        <h3>No hay simulaciones</h3>
        <p>No se encontraron simulaciones.</p>
    <?php endif ?>

    <?php $session = session();
    if (!empty($session->get('user'))) { ?>
        <section>
            <a href="<?= base_url('simulaciones/new') ?>">Añadir Simulación</a>
        </section>
    <?php } ?>
</section>

<?= view('templates/footer') ?>
