<div class="container">
    <h1>Editar Modelo de Funda</h1>

    <!-- Verificar si hay un mensaje de éxito o error -->
    <?php if (session()->get('message')): ?>
        <div class="alert alert-success">
            <?= session()->get('message') ?>
        </div>
    <?php elseif (session()->get('error')): ?>
        <div class="alert alert-danger">
            <?= session()->get('error') ?>
        </div>
    <?php endif; ?>

    <form action="<?= site_url('admin/modelosFundas/update/updated/' . $modeloFunda['ID']) ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- Nombre del modelo -->
        <div class="form-group">
            <label for="Nombre">Nombre del Modelo</label>
            <input type="text" name="Nombre" id="Nombre" class="form-control" value="<?= old('Nombre', $modeloFunda['Nombre']) ?>" required>
        </div>

        <!-- Tamaño -->
        <div class="form-group">
            <label for="Tamaño">Tamaño</label>
            <input type="text" name="Tamaño" id="Tamaño" class="form-control" value="<?= old('Tamaño', $modeloFunda['Tamaño']) ?>" required>
        </div>

        <!-- Capacidad de carga -->
        <div class="form-group">
            <label for="CapacidadCarga">Capacidad de Carga</label>
            <input type="number" name="CapacidadCarga" id="CapacidadCarga" class="form-control" value="<?= old('CapacidadCarga', $modeloFunda['CapacidadCarga']) ?>" required>
        </div>

        <!-- ¿Es expandible? -->
        <div class="form-group">
            <label for="Expansible">Extensible</label>
            <select name="Expansible" id="Expansible" class="form-control" required>
                <option value="1" <?= old('Expansible', $modeloFunda['Expansible']) == 1 ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= old('Expansible', $modeloFunda['Expansible']) == 0 ? 'selected' : '' ?>>No</option>
            </select>
        </div>

        <!-- Tipo de funda -->
        <div class="form-group">
            <label for="TipoFunda">Tipo de Funda</label>
            <select name="TipoFunda" id="TipoFunda" class="form-control" required>
                <option value="fija" <?= old('TipoFunda', $modeloFunda['TipoFunda']) == 'fija' ? 'selected' : '' ?>>Fija</option>
                <option value="expandible" <?= old('TipoFunda', $modeloFunda['TipoFunda']) == 'expandible' ? 'selected' : '' ?>>Expandible</option>
            </select>
        </div>

        <!-- Barra de selección de imagen y previsualización (izquierda para la barra, derecha para la imagen) -->
        <div class="form-group d-flex justify-content-between align-items-center">
            <!-- Barra de selección de imagen -->
            <div class="d-flex flex-column" style="width: 50%; padding-right: 16px;">
                <label for="ImagenURL" class="mr-3">Imagen </br>(Deja en blanco si no deseas cambiarla)</label>
                <!-- Campo para seleccionar una nueva imagen -->
                <input type="file" name="ImagenURL" id="ImagenURL" class="form-control" onchange="previewImage(event)" style="width: 100%;">
            </div>

            <!-- Previsualización de la imagen actual a la derecha -->
            <?php if (!empty($modeloFunda['ImagenURL'])): ?>
                <div class="current-image mt-5 ms-4" id="currentImageContainer" style="width: 50%;">
                    <img id="currentImage" src="<?= base_url($modeloFunda['ImagenURL']) ?>" alt="Imagen actual" style="width: 250px; height: auto; object-fit: cover;">
                    <br>
                    <small>Imagen actual</small>
                </div>
            <?php endif; ?>

            <!-- Previsualización de la nueva imagen seleccionada (cuando el usuario la elige) -->
            <div id="newImagePreview" class="mb-2 ml-3 ms-4 mt-5 w-50" style="display: none;">
                <img id="newImage" src="#" alt="Nueva imagen" style="width: 250px; height: auto; object-fit: cover;">
                <br>
                <small>Previsualización de la nueva imagen</small>
            </div>
        </div>

        <!-- Proveedores asociados con checkboxes -->
        <div class="form-group mt-4">
            <label for="ProveedorID">Proveedores Asociados</label><br><br>
            <?php foreach ($todosLosProveedores as $proveedor): ?>
                <div class="form-check" style="display: flex; align-items: center; margin-bottom: 10px;">
                    <!-- Checkbox con margen derecho -->
                    <input type="checkbox" class="form-check-input" name="ProveedorID[]" value="<?= $proveedor['ID'] ?>"
                        <?php if (in_array($proveedor['ID'], array_column($proveedores, 'ID'))) echo 'checked'; ?>
                        style="margin-right: 15px;">
                    <!-- Etiqueta con margen izquierdo para separarla del checkbox -->
                    <label class="form-check-label" style="margin-bottom: 0; padding-left: 0;">
                        <?= $proveedor['Nombre'] ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Botones de acción -->
<div class="form-group text-center mt-5">
    <button type="submit" class="btn btn-primary mx-3">Actualizar Modelo de Funda</button>
    <a href="<?= site_url('admin/modelosFundas') ?>" class="btn btn-secondary mx-3">Cancelar</a>
</div>


        <script>
            // Función para previsualizar la nueva imagen
            function previewImage(event) {
                const file = event.target.files[0];
                const reader = new FileReader();

                reader.onload = function(e) {
                    const newImage = document.getElementById('newImage');
                    const newImagePreview = document.getElementById('newImagePreview');
                    const currentImageContainer = document.getElementById('currentImageContainer');

                    // Cambiar la imagen de previsualización
                    newImage.src = e.target.result;

                    // Mostrar la previsualización de la nueva imagen
                    newImagePreview.style.display = 'block';

                    // Ocultar la imagen actual y el texto "Imagen actual" si se selecciona una nueva imagen
                    if (currentImageContainer) {
                        currentImageContainer.style.display = 'none';
                    }
                };

                // Leer el archivo
                if (file) {
                    reader.readAsDataURL(file);
                }
            }
        </script>