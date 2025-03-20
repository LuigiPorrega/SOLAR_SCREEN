<section>
    <h2><?= esc($title) ?></h2>
    <h3>Simulación ID: <?= esc($simulacion['ID']) ?></h3>
    <p>Condición de Luz: <?= esc($simulacion['CondicionLuz']) ?></p>
    <p>Energía Generada: <?= esc($simulacion['EnergiaGenerada']) ?></p>
    <p>Fecha:  <?= date('d-m-Y', strtotime(esc($simulacion['Fecha']))) ?></p>

    <p>
    &nbsp;
                <?php $session = session(); 
                // Verificamos si el usuario está autenticado y es admin
                if ($session->get('isLoggedIn')): ?>
                    <?php if ($session->get('role') === 'admin'): ?>
                        <a href="<?= base_url('admin/simulaciones/delete/' . $simulacion['ID']) ?>">Eliminar</a>
                        &nbsp;
                        <a href="<?= base_url('admin/simulaciones/update/' . $simulacion['ID']) ?>">Actualizar</a>
                    <?php endif; ?>
                <?php endif; ?>
    </p>
    <p>
        <a href="<?= base_url('admin/simulaciones') ?>">Volver al listado</a>
    </p>
</section>

