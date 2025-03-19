<?= view('templates/header', ['title' => 'Dashboard']) ?>

<section>
    <h2>Bienvenido al Dashboard, <?= esc($nombreUsuario) ?></h2>
    <p>Selecciona una opción del menú para comenzar.</p>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Simulaciones</h5>
                    <p class="card-text">Gestiona las simulaciones.</p>
                    <a href="<?= base_url('simulaciones') ?>" class="btn btn-primary">Ir a Simulaciones</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Ideas</h5>
                    <p class="card-text">Gestiona las ideas.</p>
                    <a href="<?= base_url('ideas') ?>" class="btn btn-primary">Ir a Ideas</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Usuarios</h5>
                    <p class="card-text">Gestiona los usuarios.</p>
                    <a href="<?= base_url('users') ?>" class="btn btn-primary">Ir a Usuarios</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">LoginLog</h5>
                    <p class="card-text">Ver los registros de inicio de sesión.</p>
                    <a href="<?= base_url('loginlog') ?>" class="btn btn-primary">Ir a LoginLog</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?= view('templates/footer') ?>
