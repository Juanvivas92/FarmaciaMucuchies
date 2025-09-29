<?php 
include 'config.php';

// Variable para mensajes
$mensaje = '';
$error = '';

// Procesar acciones (agregar pedido, eliminar pedido)
if ($_POST) {
    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];
        
        // ELIMINAR PEDIDO
        if ($accion == 'eliminar') {
            $id_pedido = $_POST['id_pedido'];
            
            try {
                $sql = "DELETE FROM lista_pedidos WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$id_pedido]);
                $mensaje = "Pedido eliminado exitosamente";
            } catch(Exception $e) {
                $error = "Error al eliminar el pedido: " . $e->getMessage();
            }
        }
    }
    
    // AGREGAR NUEVO PEDIDO
    else {
        $id_producto = $_POST['id_producto'];
        $cantidad_pedir = $_POST['cantidad_pedir'];
        $proveedor = $_POST['proveedor'];
        
        try {
            $sql = "INSERT INTO lista_pedidos (id_producto, cantidad_pedir, proveedor) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id_producto, $cantidad_pedir, $proveedor]);
            $mensaje = "Producto agregado a la lista de pedidos";
        } catch(Exception $e) {
            $error = "Error al agregar el pedido: " . $e->getMessage();
        }
    }
}

// Obtener lista de pedidos con información completa
$sql = "SELECT lp.*, p.nombre_producto, p.marca_producto, p.stock_actual, p.rotacion
        FROM lista_pedidos lp
        JOIN productos p ON lp.id_producto = p.id
        ORDER BY 
            CASE p.rotacion 
                WHEN 'alta' THEN 1 
                WHEN 'media' THEN 2 
                WHEN 'baja' THEN 3 
            END,
            lp.fecha_pedido DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$pedidos = $stmt->fetchAll();

// Obtener productos para el select
$sql_productos = "SELECT id, nombre_producto, marca_producto, stock_actual, stock_minimo, rotacion
                  FROM productos 
                  ORDER BY nombre_producto";
$stmt_productos = $conn->prepare($sql_productos);
$stmt_productos->execute();
$productos = $stmt_productos->fetchAll();

// Función para generar CSV de pedidos
if (isset($_GET['descargar_csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=pedidos_farmacia_mucuchies_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // Encabezados CSV
    fputcsv($output, ['ID', 'Producto', 'Marca', 'Cantidad a Pedir', 'Proveedor', 'Fecha de Pedido', 'Stock Actual', 'Rotación']);
    
    // Datos de pedidos
    foreach($pedidos as $pedido) {
        fputcsv($output, [
            $pedido['id'],
            $pedido['nombre_producto'],
            $pedido['marca_producto'],
            $pedido['cantidad_pedir'],
            $pedido['proveedor'],
            date('d/m/Y', strtotime($pedido['fecha_pedido'])),
            $pedido['stock_actual'],
            $pedido['rotacion']
        ]);
    }
    
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - Farmacia Mucuchíes C.A.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="bi bi-truck"></i> Lista de Pedidos Semanal</h2>
                <hr>
            </div>
        </div>

        <?php if($mensaje): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Formulario de nuevo pedido -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Agregar a Lista de Pedidos</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Producto *</label>
                                <select class="form-select" name="id_producto" required>
                                    <option value="">Seleccionar producto...</option>
                                    <?php foreach($productos as $producto): ?>
                                    <option value="<?php echo $producto['id']; ?>">
                                        <?php echo $producto['nombre_producto'] . ' (' . $producto['marca_producto'] . ')'; ?>
                                        - Stock: <?php echo $producto['stock_actual']; ?>
                                        <?php
                                        switch($producto['rotacion']) {
                                            case 'alta': echo ' 🔴'; break;
                                            case 'media': echo ' 🟡'; break;
                                            case 'baja': echo ' 🟢'; break;
                                        }
                                        ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Cantidad a Pedir *</label>
                                <input type="number" class="form-control" name="cantidad_pedir" min="1" value="10" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Proveedor *</label>
                                <input type="text" class="form-control" name="proveedor" placeholder="Nombre del proveedor" required>
                            </div>
                            
                            <button type="submit" class="btn btn-info text-white">
                                <i class="bi bi-cart-plus"></i> Agregar a Pedidos
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Lista de pedidos -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list"></i> Pedidos Pendientes (<?php echo count($pedidos); ?>)</h5>
                        <!-- Botón de descarga CSV -->
                        <?php if (count($pedidos) > 0): ?>
                        <a href="pedidos.php?descargar_csv=1" class="btn btn-light btn-sm" title="Descargar lista de pedidos">
                            <i class="bi bi-download"></i> Descargar CSV
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (count($pedidos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Rotación</th>
                                        <th>Stock Actual</th>
                                        <th>Cantidad</th>
                                        <th>Proveedor</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($pedidos as $pedido): ?>
                                    <tr>
                                        <td>
                                            <small><?php echo $pedido['nombre_producto']; ?></small><br>
                                            <span class="badge bg-secondary"><?php echo $pedido['marca_producto']; ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            switch($pedido['rotacion']) {
                                                case 'alta':
                                                    echo '<span class="badge bg-danger">Alta</span>';
                                                    break;
                                                case 'media':
                                                    echo '<span class="badge bg-warning text-dark">Media</span>';
                                                    break;
                                                case 'baja':
                                                    echo '<span class="badge bg-success">Baja</span>';
                                                    break;
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            // Verificar que la clave exista antes de usarla
                                            $stock_actual = isset($pedido['stock_actual']) ? $pedido['stock_actual'] : 0;
                                            $stock_minimo = isset($pedido['stock_minimo']) ? $pedido['stock_minimo'] : 0;
                                            
                                            if($stock_actual == 0): ?>
                                                <span class="badge bg-danger"><?php echo $stock_actual; ?></span>
                                            <?php elseif(isset($pedido['stock_minimo']) && $stock_actual <= $stock_minimo): ?>
                                                <span class="badge bg-warning text-dark"><?php echo $stock_actual; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><?php echo $stock_actual; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $pedido['cantidad_pedir']; ?></span>
                                        </td>
                                        <td>
                                            <small><?php echo $pedido['proveedor']; ?></small>
                                        </td>
                                        <td>
                                            <small><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></small>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="confirmarEliminacion(<?php echo $pedido['id']; ?>, '<?php echo addslashes($pedido['nombre_producto']); ?>')"
                                                    title="Eliminar pedido">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-cart-x fs-1 text-muted"></i>
                            <p class="mt-2">No hay pedidos pendientes</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div class="modal fade" id="confirmarEliminarModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar el pedido de <strong id="nombreProductoEliminar"></strong>?</p>
                    <p class="text-danger">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" id="formEliminar" style="display: inline;">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id_pedido" id="idPedidoEliminar">
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function confirmarEliminacion(id, nombre) {
            document.getElementById('nombreProductoEliminar').textContent = nombre;
            document.getElementById('idPedidoEliminar').value = id;
            var modal = new bootstrap.Modal(document.getElementById('confirmarEliminarModal'));
            modal.show();
        }
    </script>
</body>
</html>