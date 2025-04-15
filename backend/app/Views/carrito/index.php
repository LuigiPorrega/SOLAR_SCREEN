<div class="container mt-5">
    <h2 class="mb-4">Mi Carrito</h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?php if (empty($carrito)): ?>
        <p>No tienes productos en el carrito.</p>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio Unitario</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php $totalCarrito = 0; ?>
                <?php foreach ($carrito as $item): ?>
                    <tr>
                        <td>
                            Funda: <?= esc($item['NombreFunda']) ?>
                        </td>
                        <td><?= number_format($item['Precio'], 2) ?> €</td>
                        <td>
                            <form action="<?= base_url('admin/carrito/update/' . $item['ID']) ?>" method="post" class="form-inline d-flex gap-2">
                                <?= csrf_field() ?>
                                <input type="number" name="Cantidad" value="<?= esc($item['Cantidad']) ?>" min="1" class="form-control form-control-sm" style="width: 80px;">
                                <button type="submit" class="btn btn-sm btn-primary">Actualizar</button>
                            </form>
                        </td>
                        <td>
                            <?php
                            $subtotal = $item['Cantidad'] * $item['Precio'];
                            $totalCarrito += $subtotal;
                            echo number_format($subtotal, 2) . ' €';
                            ?>
                        </td>
                        <td>
                            <form action="<?= base_url('admin/carrito/delete/' . $item['ID']) ?>" method="post" onsubmit="return confirm('¿Eliminar este producto del carrito?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                    <td colspan="2"><strong><?= number_format($totalCarrito, 2) ?> €</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Botón para vaciar el carrito con confirmación -->
        <form id="vaciarCarritoForm" action="<?= base_url('admin/carrito/vaciarCarrito') ?>" method="post" style="display: inline;">
            <?= csrf_field() ?>
            <button type="button" class="btn btn-warning" onclick="confirmarVaciarCarrito()">Vaciar Carrito</button>
        </form>

        <!-- Botón para volver al listado de fundas -->
        <a href="<?= base_url('admin/modelosFundas') ?>" class="btn btn-secondary ms-2">Volver al listado de fundas</a>

        <script>
            function confirmarVaciarCarrito() {
                if (confirm('¿Estás seguro de que deseas vaciar el carrito? Esta acción no se puede deshacer.')) {
                    document.getElementById('vaciarCarritoForm').submit();
                }
            }
        </script>
    <?php endif; ?>
</div>