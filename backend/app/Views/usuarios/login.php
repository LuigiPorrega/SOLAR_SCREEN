<?= view('templates/header', ['title' => 'Iniciar Sesión']) ?>

<section>
    <h2><?= esc($title) ?></h2>

    <!-- Mostrar el mensaje de error si existe -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <!-- Mostrar los errores de validación si existen -->
    <?= validation_list_errors() ?>

    <form action="<?= base_url('login') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="username">Nombre de Usuario</label>
            <input type="text" name="username" class="form-control" value="<?= set_value('username') ?>">
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" name="password" class="form-control">
        </div> 

        <button type="submit" class="btn btn-primary mt-3">Iniciar Sesión</button>
    </form>
</section>

