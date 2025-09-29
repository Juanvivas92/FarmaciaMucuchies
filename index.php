<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmacia Mucuchíes C.A.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shop"></i> Farmacia Mucuchíes C.A.
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="productos.php">Productos</a>
                <a class="nav-link" href="clientes.php">Clientes</a>
                <a class="nav-link" href="inventario.php">Inventario</a>
                <a class="nav-link" href="pedidos.php">Pedidos</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h1 class="display-4">Sistema de Gestión de Inventario</h1>
                <p class="lead">Bienvenido al sistema de gestión de Farmacia Mucuchíes C.A.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card text-center shadow">
                    <div class="card-body">
                        <i class="bi bi-box-seam fs-1 text-primary"></i>
                        <h5 class="card-title mt-3">Productos</h5>
                        <p class="card-text">Gestionar productos del inventario</p>
                        <a href="productos.php" class="btn btn-primary">Ir</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card text-center shadow">
                    <div class="card-body">
                        <i class="bi bi-people fs-1 text-success"></i>
                        <h5 class="card-title mt-3">Clientes</h5>
                        <p class="card-text">Registrar y gestionar clientes</p>
                        <a href="clientes.php" class="btn btn-success">Ir</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card text-center shadow">
                    <div class="card-body">
                        <i class="bi bi-clipboard-data fs-1 text-warning"></i>
                        <h5 class="card-title mt-3">Inventario</h5>
                        <p class="card-text">Ver estado actual del inventario</p>
                        <a href="inventario.php" class="btn btn-warning">Ir</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card text-center shadow">
                    <div class="card-body">
                        <i class="bi bi-truck fs-1 text-info"></i>
                        <h5 class="card-title mt-3">Pedidos</h5>
                        <p class="card-text">Lista de pedidos semanales</p>
                        <a href="pedidos.php" class="btn btn-info">Ir</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas del sistema - CUADRO DE DESPLIEGUE -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="accordion" id="alertasAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="alertasHeading">
                            <button class="accordion-button bg-danger text-white" type="button" data-bs-toggle="collapse" data-bs-target="#alertasCollapse" aria-expanded="true" aria-controls="alertasCollapse">
                                <i class="bi bi-exclamation-triangle"></i>
                                <span class="ms-2">Alertas del Sistema</span>
                                <span class="badge bg-light text-danger ms-2" id="contadorAlertas">0</span>
                            </button>
                        </h2>
                        <div id="alertasCollapse" class="accordion-collapse collapse show" aria-labelledby="alertasHeading" data-bs-parent="#alertasAccordion">
                            <div class="accordion-body">
                                <?php
                                // Consultar productos con stock bajo o sin stock
                                $sql = "SELECT * FROM productos WHERE stock_actual <= stock_minimo ORDER BY stock_actual ASC";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute();
                                $productos_alerta = $stmt->fetchAll();
                                
                                if(count($productos_alerta) > 0) {
                                    echo '<div class="list-group">';
                                    foreach($productos_alerta as $producto) {
                                        if($producto['stock_actual'] == 0) {
                                            echo '<div class="list-group-item list-group-item-danger d-flex justify-content-between align-items-center">';
                                            echo '<div>';
                                            echo '<i class="bi bi-exclamation-circle-fill me-2"></i>';
                                            echo '<strong>Sin stock:</strong> ' . $producto['nombre_producto'] . ' (' . $producto['marca_producto'] . ')';
                                            echo '</div>';
                                            echo '<span class="badge bg-danger">0 unidades</span>';
                                            echo '</div>';
                                        } else {
                                            echo '<div class="list-group-item list-group-item-warning d-flex justify-content-between align-items-center">';
                                            echo '<div>';
                                            echo '<i class="bi bi-exclamation-triangle-fill me-2"></i>';
                                            echo '<strong>Stock bajo:</strong> ' . $producto['nombre_producto'] . ' (' . $producto['marca_producto'] . ')';
                                            echo '</div>';
                                            echo '<span class="badge bg-warning text-dark">' . $producto['stock_actual'] . ' unidades</span>';
                                            echo '</div>';
                                        }
                                    }
                                    echo '</div>';
                                } else {
                                    echo '<div class="alert alert-success mb-0">';
                                    echo '<i class="bi bi-check-circle-fill me-2"></i>';
                                    echo 'Todos los productos tienen stock adecuado';
                                    echo '</div>';
                                }
                                
                                // Contar alertas para el badge
                                $total_alertas = count($productos_alerta);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Actualizar contador de alertas
        document.addEventListener('DOMContentLoaded', function() {
            const contadorAlertas = document.getElementById('contadorAlertas');
            const alertas = document.querySelectorAll('.list-group-item').length;
            if (contadorAlertas && alertas > 0) {
                contadorAlertas.textContent = alertas;
            }
        });
    </script>
</body>
</html>