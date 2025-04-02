<section class="container mt-4">
    <h2 class="text-center mb-4"><?= esc($title) ?></h2>

    <!-- Mensajes de error o éxito -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <!-- Mostrar los errores de validación del formulario -->
    <?= validation_list_errors() ?>

    <form action="<?= base_url('admin/modelosFundas/create') ?>" method="post" enctype="multipart/form-data" class="p-4 shadow-lg rounded bg-light">
        <?= csrf_field() ?>

        <!-- Campo para el Nombre del Modelo de Funda -->
        <div class="form-group mb-3">
            <label for="Nombre" class="font-weight-bold">Nombre del Modelo de Funda</label>
            <input type="text" name="Nombre" id="Nombre" class="form-control <?= session('errors.Nombre') ? 'is-invalid' : '' ?>" value="<?= set_value('Nombre') ?>" required>
            <?php if (session('errors.Nombre')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.Nombre') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo para el Tamaño -->
        <div class="form-group mb-3">
            <label for="Tamaño" class="font-weight-bold">Tamaño: cm (Longitud x Ancho)</label>
            <input type="text" name="Tamaño" id="Tamaño" class="form-control <?= session('errors.Tamaño') ? 'is-invalid' : '' ?>" value="<?= set_value('Tamaño') ?>" required>
            <?php if (session('errors.Tamaño')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.Tamaño') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo para la Capacidad de Carga -->
        <div class="form-group mb-3">
            <label for="CapacidadCarga" class="font-weight-bold">Capacidad de Carga (mAh)</label>
            <input type="number" name="CapacidadCarga" id="CapacidadCarga" class="form-control <?= session('errors.CapacidadCarga') ? 'is-invalid' : '' ?>" value="<?= set_value('CapacidadCarga') ?>" required>
            <?php if (session('errors.CapacidadCarga')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.CapacidadCarga') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo para el Tipo de Funda (ahora con select) -->
        <div class="form-group mb-3">
            <label for="TipoFunda" class="font-weight-bold">Tipo de Funda</label>
            <select name="TipoFunda" id="TipoFunda" class="form-control <?= session('errors.TipoFunda') ? 'is-invalid' : '' ?>" onchange="actualizarExpansible()" required>
                <option value="fija" <?= set_select('TipoFunda', 'fija'); ?>>Fija</option>
                <option value="expandible" <?= set_select('TipoFunda', 'expandible'); ?>>Expandible</option>
            </select>
            <?php if (session('errors.TipoFunda')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.TipoFunda') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo oculto para el Expansible -->
        <input type="hidden" name="Expansible" id="Expansible" value="0">

        <!-- Campo para la Imagen -->
        <div class="form-group mb-3">
            <label for="ImagenURL" class="font-weight-bold">Selecciona Imagen</label>
            <input type="file" name="ImagenURL" id="ImagenURL" class="form-control <?= session('errors.ImagenURL') ? 'is-invalid' : '' ?>" accept="image/jpeg, image/png, image/jpg, image/gif" required>
            <?php if (session('errors.ImagenURL')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.ImagenURL') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo para seleccionar múltiples proveedores -->
        <div class="form-group mb-3">
            <label for="ProveedorID" class="font-weight-bold">Selecciona Proveedor</label><br>
            <?php if (!empty($proveedores)): ?>
                <div class="d-flex flex-wrap">
                    <?php foreach ($proveedores as $proveedor): ?>
                        <div class="form-check mr-3 mb-2">
                            <input type="checkbox" class="form-check-input" name="ProveedorID[]" value="<?= $proveedor['ID']; ?>" id="proveedor<?= $proveedor['ID']; ?>" <?= set_checkbox('ProveedorID[]', $proveedor['ID']); ?>>
                            <label class="form-check-label" for="proveedor<?= $proveedor['ID']; ?>"><?= esc($proveedor['Nombre']) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No hay proveedores disponibles.</p>
            <?php endif; ?>
            <?php if (session('errors.ProveedorID')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.ProveedorID') ?>
                </div>
            <?php endif; ?>
        </div>


        <!-- Botón de creación -->
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary btn-lg w-50">Crear Modelo de Funda</button>
        </div>
    </form>

    <!-- Botón para volver al listado -->
    <div class="text-center mt-4">
        <a href="<?= base_url('admin/modelosFundas') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
</section>

<!-- Script para actualizar el campo oculto "Expansible" -->
<script>
    function actualizarExpansible() {
        var tipoFunda = document.getElementById("TipoFunda").value;
        var campoExpansible = document.getElementById("Expansible");

        if (tipoFunda === "expandible") {
            campoExpansible.value = 1; // Si es expandible, poner 1
        } else {
            campoExpansible.value = 0; // Si es fija, poner 0
        }
    }
</script>