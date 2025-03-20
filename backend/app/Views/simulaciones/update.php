<section>
    <h2><?= esc($title) ?></h2>

    <?= session()->getFlashdata('error') ?>
    <?= validation_list_errors() ?>

    <?php if ($simulacion !== null): ?>
        <form action="<?= base_url('admin/simulaciones/update/updated/' . $simulacion['ID']) ?>" method="post">
            <?= csrf_field() ?>

            <label for="CondicionLuz">Condición de Luz</label>
            <select name="CondicionLuz" class="form-control">
                <option value="Luz solar directa" <?= $simulacion['CondicionLuz'] == 'Luz solar directa' ? 'selected' : '' ?>>Luz solar directa</option>
                <option value="Luz artificial" <?= $simulacion['CondicionLuz'] == 'Luz artificial' ? 'selected' : '' ?>>Luz artificial</option>
            </select>

            <label for="EnergiaGenerada">Energía Generada</label>
            <input type="number" name="EnergiaGenerada" step="0.01" value="<?= esc($simulacion['EnergiaGenerada']) ?>" class="form-control">

            <input type="submit" name="submit" value="Actualizar Simulación">
        </form>

    <?php endif ?>
</section>