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

    <form action="<?= base_url('admin/proveedores/create') ?>" method="post" class="p-4 shadow-lg rounded bg-light">
        <?= csrf_field() ?>

        <!-- Campo para el Nombre del Proveedor -->
        <div class="form-group mb-3">
            <label for="Nombre" class="font-weight-bold">Nombre del Proveedor</label>
            <input type="text" name="Nombre" id="Nombre" class="form-control <?= session('errors.Nombre') ? 'is-invalid' : '' ?>" value="<?= set_value('Nombre') ?>" required>
            <?php if (session('errors.Nombre')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.Nombre') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo para el País -->
        <div class="form-group mb-3">
            <label for="Pais" class="font-weight-bold">País</label>
            <input type="text" name="Pais" id="Pais" class="form-control <?= session('errors.Pais') ? 'is-invalid' : '' ?>" value="<?= set_value('Pais') ?>" required>
            <?php if (session('errors.Pais')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.Pais') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo para el Nombre de Contacto -->
        <div class="form-group mb-3">
            <label for="ContactoNombre" class="font-weight-bold">Nombre de Contacto</label>
            <input type="text" name="ContactoNombre" id="ContactoNombre" class="form-control <?= session('errors.ContactoNombre') ? 'is-invalid' : '' ?>" value="<?= set_value('ContactoNombre') ?>" required>
            <?php if (session('errors.ContactoNombre')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.ContactoNombre') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo para el Teléfono de Contacto -->
        <div class="form-group mb-3">
            <label for="ContactoTelefono" class="font-weight-bold">Teléfono de Contacto</label>
            <input type="tel" name="ContactoTelefono" id="ContactoTelefono" class="form-control <?= session('errors.ContactoTelefono') ? 'is-invalid' : '' ?>" value="<?= set_value('ContactoTelefono') ?>" placeholder="+34 123 456 789" required>
            <?php if (session('errors.ContactoTelefono')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.ContactoTelefono') ?>
                </div>
            <?php endif; ?>
            <small class="form-text text-muted">Por favor ingrese el número en formato internacional, por ejemplo: +34 123 456 789</small>
        </div>

        <!-- Campo para el Correo de Contacto -->
        <div class="form-group mb-3">
            <label for="ContactoEmail" class="font-weight-bold">Correo de Contacto</label>
            <input type="email" name="ContactoEmail" id="ContactoEmail" class="form-control <?= session('errors.ContactoEmail') ? 'is-invalid' : '' ?>" value="<?= set_value('ContactoEmail') ?>" required>
            <?php if (session('errors.ContactoEmail')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.ContactoEmail') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo para el Sitio Web -->
        <div class="form-group mb-3">
            <label for="SitioWeb" class="font-weight-bold">Sitio Web</label>
            <input type="text" name="SitioWeb" id="SitioWeb" class="form-control <?= session('errors.SitioWeb') ? 'is-invalid' : '' ?>" value="<?= set_value('SitioWeb') ?>" placeholder="Nombre de dominio, ej. ejemplo.com" required>
            <?php if (session('errors.SitioWeb')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.SitioWeb') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo para la Dirección -->
        <div class="form-group mb-3">
            <label for="Direccion" class="font-weight-bold">Dirección</label>
            <textarea name="Direccion" id="Direccion" class="form-control <?= session('errors.Direccion') ? 'is-invalid' : '' ?>" rows="3" required><?= set_value('Direccion') ?></textarea>
            <?php if (session('errors.Direccion')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.Direccion') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo para la Descripción -->
        <div class="form-group mb-3">
            <label for="Descripcion" class="font-weight-bold">Descripción</label>
            <textarea name="Descripcion" id="Descripcion" class="form-control <?= session('errors.Descripcion') ? 'is-invalid' : '' ?>" rows="5" required><?= set_value('Descripcion') ?></textarea>
            <?php if (session('errors.Descripcion')): ?>
                <div class="invalid-feedback">
                    <?= session('errors.Descripcion') ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Campo para seleccionar las Fundas -->
        <div class="form-group mb-3">
            <label for="Fundas" class="font-weight-bold">Selecciona Fundas</label>
            <div class="row mt-4">
                <?php foreach ($fundas as $funda): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="FundaID[]" value="<?= $funda['ID']; ?>" id="Funda_<?= $funda['ID']; ?>">
                            <label class="form-check-label" for="Funda_<?= $funda['ID']; ?>">
                                <?= esc($funda['Nombre']) ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Botón de creación -->
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary btn-lg w-50">Crear Proveedor</button>
        </div>
    </form>

    <!-- Botón para volver al listado -->
    <div class="text-center mt-4">
        <a href="<?= base_url('admin/proveedores') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
</section>