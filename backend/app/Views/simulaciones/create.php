<?= view('templates/header', ['title' => 'Crear Simulación']) ?>

<section>
    <h2><?= esc($title) ?></h2>

    <?= session()->getFlashdata('error') ?>
    <?= validation_list_errors() ?>

    <form action="<?= base_url('simulaciones/create') ?>" method="post">
        <?= csrf_field() ?>

        <label for="CondicionLuz">Condición de Luz</label>
        <input type="text" name="CondicionLuz" value="<?= set_value('CondicionLuz') ?>">
        <br><br>

        <label for="EnergiaGenerada">Energía Generada</label>
        <input type="number" name="EnergiaGenerada" step="0.01" value="<?= set_value('EnergiaGenerada') ?>">
        <br><br>

        <input type="submit" name="submit" value="Crear Simulación">
    </form>
</section>

<?= view('templates/footer') ?>

