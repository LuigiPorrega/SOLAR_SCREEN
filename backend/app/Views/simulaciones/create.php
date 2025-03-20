<form action="<?= base_url('admin/simulaciones/create') ?>" method="post">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="UsuarioID">Usuario</label>
        <input type="text" name="UsuarioID" value="<?= session()->get('username') ?>" class="form-control" readonly>
    </div>

    <div class="form-group">
        <label for="CondicionLuz">Condición de Luz</label>
        <select name="CondicionLuz" class="form-control" aria-label="Selecciona la condición de luz">
            <option value="" disabled selected>Selecciona una opción...</option>
            <option value="Luz solar directa" <?= old('CondicionLuz') == 'Luz solar directa' ? 'selected' : '' ?>>Luz solar directa</option>
            <option value="Luz artificial" <?= old('CondicionLuz') == 'Luz artificial' ? 'selected' : '' ?>>Luz artificial</option>
        </select>
    </div>

    <div class="form-group">
        <label for="Tiempo">Tiempo (minutos)</label>
        <input type="number" name="Tiempo" value="<?= old('Tiempo') ?>" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary my-3">Crear Simulación</button>
</form>