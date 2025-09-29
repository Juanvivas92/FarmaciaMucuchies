<?php 
include 'config.php';

// Procesar actualización de stock desde el inventario
if ($_POST && isset($_POST['accion']) && $_POST['accion'] == 'actualizar_stock_inventario') {
    $id = $_POST['id_producto'];
    $nuevo_stock = $_POST['nuevo_stock'];
    
    try {
        $sql = "UPDATE productos SET stock_actual=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nuevo_stock, $id]);
        $mensaje = "Stock actualizado exitosamente";
    } catch(Exception $e) {
        $error = "Error al actualizar el stock: " . $e->getMessage();
    }
}

// Obtener productos con estado
$sql = "SELECT *, 
        CASE 
            WHEN stock_actual = 0 THEN 'sin_stock'
            WHEN stock_actual <= stock_minimo THEN 'bajo_stock'
            WHEN stock_actual >= stock_maximo THEN 'exceso_stock'
            ELSE 'normal'
        END as estado
        FROM productos ORDER BY nombre_producto";
$stmt = $conn->prepare($sql);
$stmt->execute();
$productos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Farmacia Mucuchíes C.A.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="bi bi-clipboard-data"></i> Inventario Actual</h2>
                <hr>
            </div>
        </div>

        <?php if(isset($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="accordion" id="inventarioAccordion">
                    <!-- Estado del Inventario -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingEstado">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEstado" aria-expanded="true" aria-controls="collapseEstado">
                                <i class="bi bi-list-check"></i>
                                <span class="ms-2">Estado del Inventario</span>
                            </button>
                        </h2>
                        <div id="collapseEstado" class="accordion-collapse collapse show" aria-labelledby="headingEstado" data-bs-parent="#inventarioAccordion">
                            <div class="accordion-body">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Producto</th>
                                                        <th>Marca</th>
                                                        <th>Stock Actual</th>
                                                        <th>Stock Mínimo</th>
                                                        <th>Stock Máximo</th>
                                                        <th>Estado</th>
                                                        <th>Actualizar Stock</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($productos as $producto): ?>
                                                    <tr>
                                                        <td><?php echo $producto['nombre_producto']; ?></td>
                                                        <td><?php echo $producto['marca_producto']; ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php 
                                                                if($producto['estado'] == 'sin_stock') echo 'danger';
                                                                elseif($producto['estado'] == 'bajo_stock') echo 'warning';
                                                                else echo 'success';
                                                            ?>">
                                                                <?php echo $producto['stock_actual']; ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $producto['stock_minimo']; ?></td>
                                                        <td><?php echo $producto['stock_maximo']; ?></td>
                                                        <td>
                                                            <?php if($producto['estado'] == 'sin_stock'): ?>
                                                                <span class="badge bg-danger">Sin Stock</span>
                                                            <?php elseif($producto['estado'] == 'bajo_stock'): ?>
                                                                <span class="badge bg-warning text-dark">Stock Bajo</span>
                                                            <?php elseif($producto['estado'] == 'exceso_stock'): ?>
                                                                <span class="badge bg-info">Exceso Stock</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-success">Normal</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="accion" value="actualizar_stock_inventario">
                                                                <input type="hidden" name="id_producto" value="<?php echo $producto['id']; ?>">
                                                                <div class="input-group input-group-sm" style="width: 150px;">
                                                                    <input type="number" class="form-control form-control-sm" 
                                                                           name="nuevo_stock" value="<?php echo $producto['stock_actual']; ?>" 
                                                                           min="0" style="width: 80px;">
                                                                    <button class="btn btn-success btn-sm" type="submit">
                                                                        <i class="bi bi-check"></i>
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen de alertas -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingResumen">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseResumen" aria-expanded="false" aria-controls="collapseResumen">
                                <i class="bi bi-bar-chart"></i>
                                <span class="ms-2">Resumen de Alertas</span>
                            </button>
                        </h2>
                        <div id="collapseResumen" class="accordion-collapse collapse" aria-labelledby="headingResumen" data-bs-parent="#inventarioAccordion">
                            <div class="accordion-body">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="card text-center bg-danger text-white">
                                                    <div class="card-body">
                                                        <h5>Sin Stock</h5>
                                                        <?php 
                                                        $sin_stock = array_filter($productos, function($p) { return $p['estado'] == 'sin_stock'; });
                                                        echo '<h2>' . count($sin_stock) . '</h2>';
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card text-center bg-warning text-dark">
                                                    <div class="card-body">
                                                        <h5>Stock Bajo</h5>
                                                        <?php 
                                                        $bajo_stock = array_filter($productos, function($p) { return $p['estado'] == 'bajo_stock'; });
                                                        echo '<h2>' . count($bajo_stock) . '</h2>';
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card text-center bg-info text-white">
                                                    <div class="card-body">
                                                        <h5>Exceso Stock</h5>
                                                        <?php 
                                                        $exceso_stock = array_filter($productos, function($p) { return $p['estado'] == 'exceso_stock'; });
                                                        echo '<h2>' . count($exceso_stock) . '</h2>';
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card text-center bg-success text-white">
                                                    <div class="card-body">
                                                        <h5>Normal</h5>
                                                        <?php 
                                                        $normal = array_filter($productos, function($p) { return $p['estado'] == 'normal'; });
                                                        echo '<h2>' . count($normal) . '</h2>';
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>