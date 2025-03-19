<?= view('templates/header', ['title' => 'Detalle de Simulación']) ?>

<section>
    <h2><?= esc($title) ?></h2>
    <h3>Simulación ID: <?= esc($simulacion['ID']) ?></h3>
    <p>Condición de Luz: <?= esc($simulacion['CondicionLuz']) ?></p>
    <p>Energía Generada: <?= esc($simulacion['EnergiaGenerada']) ?></p>
    <p>Fecha: <?= esc($simulacion['Fecha']) ?></p>
    <p>
        <a href="<?= base_url('simulaciones') ?>">Volver al listado</a>
    </p>
</section>

<?= view('templates/footer') ?>
