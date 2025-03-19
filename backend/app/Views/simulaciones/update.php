<?= view('templates/header', ['title' => 'Actualizar Simulación']) ?>

<section>
    <h2><?= esc($title) ?></h2>

    <?= session()->getFlashdata('error') ?>
    <?= validation_list_errors() ?>

    <?php if ($simulacion !== null): ?>
        <form action="<?= base_url('simulaciones/update/updated/' . $simulacion['ID']) ?>" method="post">
            <?= csrf_field() ?>

            <label for="CondicionLuz">Condición de Luz</label>
            <input type="text" name="CondicionLuz" value="<?= esc($simulacion['CondicionLuz']) ?>">
            <br><br>

            <label for="EnergiaGenerada">Energía Generada</label>
            <input type="number" name="EnergiaGenerada" step="0.01" value="<?= esc($simulacion['EnergiaGenerada']) ?>">
            <br><br>

            <input type="submit" name="submit" value="Actualizar Simulación">
        </form>
    <?php endif ?>
</section>

<?= view('templates/footer') ?>
