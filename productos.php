<?php 
include 'config.php';

// Variable para mensajes
$mensaje = '';
$error = '';

// Procesar acciones (editar, eliminar, actualizar stock)
if ($_POST) {
    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];
        
        // EDITAR PRODUCTO
        if ($accion == 'editar') {
            $id = $_POST['id_producto'];
            $nombre = $_POST['nombre_producto'];
            $marca = $_POST['marca_producto'];
            $stock_minimo = $_POST['stock_minimo'];
            $stock_maximo = $_POST['stock_maximo'];
            $rotacion = $_POST['rotacion'];
            
            try {
                $sql = "UPDATE productos SET nombre_producto=?, marca_producto=?, stock_minimo=?, stock_maximo=?, rotacion=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$nombre, $marca, $stock_minimo, $stock_maximo, $rotacion, $id]);
                $mensaje = "Producto actualizado exitosamente";
            } catch(Exception $e) {
                $error = "Error al actualizar el producto: " . $e->getMessage();
            }
        }
        
        // ACTUALIZAR STOCK
        elseif ($accion == 'actualizar_stock') {
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
        
        // ELIMINAR PRODUCTO
        elseif ($accion == 'eliminar') {
            $id = $_POST['id_producto'];
            
            try {
                // Verificar si el producto está referenciado en otras tablas
                $sql_check = "SELECT COUNT(*) as total FROM cliente_productos_favoritos WHERE id_producto = ?";
                $stmt_check = $conn->prepare($sql_check);
                $stmt_check->execute([$id]);
                $result = $stmt_check->fetch();
                
                if ($result['total'] > 0) {
                    $error = "No se puede eliminar el producto porque está asignado como favorito de clientes";
                } else {
                    $sql = "DELETE FROM productos WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$id]);
                    $mensaje = "Producto eliminado exitosamente";
                }
            } catch(Exception $e) {
                $error = "Error al eliminar el producto: " . $e->getMessage();
            }
        }
    }
    
    // CREAR NUEVO PRODUCTO
    else {
        $nombre = $_POST['nombre_producto'];
        $marca = $_POST['marca_producto'];
        $stock_inicial = $_POST['stock_inicial'];
        $stock_minimo = $_POST['stock_minimo'];
        $stock_maximo = $_POST['stock_maximo'];
        $rotacion = $_POST['rotacion'];
        
        try {
            $sql = "INSERT INTO productos (nombre_producto, marca_producto, stock_actual, stock_minimo, stock_maximo, rotacion) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nombre, $marca, $stock_inicial, $stock_minimo, $stock_maximo, $rotacion]);
            $mensaje = "Producto creado exitosamente";
        } catch(Exception $e) {
            $error = "Error al crear el producto: " . $e->getMessage();
        }
    }
}

// Obtener todos los productos
$sql = "SELECT * FROM productos ORDER BY nombre_producto";
$stmt = $conn->prepare($sql);
$stmt->execute();
$productos = $stmt->fetchAll();

// Obtener un producto específico para editar
$producto_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $sql_editar = "SELECT * FROM productos WHERE id = ?";
    $stmt_editar = $conn->prepare($sql_editar);
    $stmt_editar->execute([$id_editar]);
    $producto_editar = $stmt_editar->fetch();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Farmacia Mucuchíes C.A.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="bi bi-box-seam"></i> Gestión de Productos</h2>
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
            <!-- Formulario de nuevo producto o editar producto -->
            <div class="col-md-12">
                <div class="accordion" id="productosAccordion">
                    <!-- Formulario de nuevo producto -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFormulario">
                            <button class="accordion-button <?php echo $producto_editar ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFormulario" aria-expanded="<?php echo $producto_editar ? 'false' : 'true'; ?>" aria-controls="collapseFormulario">
                                <i class="bi <?php echo $producto_editar ? 'bi-pencil' : 'bi-plus-circle'; ?>"></i>
                                <span class="ms-2"><?php echo $producto_editar ? 'Editar Producto' : 'Nuevo Producto'; ?></span>
                            </button>
                        </h2>
                        <div id="collapseFormulario" class="accordion-collapse collapse <?php echo $producto_editar ? '' : 'show'; ?>" aria-labelledby="headingFormulario" data-bs-parent="#productosAccordion">
                            <div class="accordion-body">
                                <div class="card">
                                    <div class="card-body">
                                        <form method="POST">
                                            <?php if($producto_editar): ?>
                                                <input type="hidden" name="accion" value="editar">
                                                <input type="hidden" name="id_producto" value="<?php echo $producto_editar['id']; ?>">
                                            <?php endif; ?>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nombre del Producto *</label>
                                                        <input type="text" class="form-control" name="nombre_producto" 
                                                               value="<?php echo $producto_editar ? $producto_editar['nombre_producto'] : ''; ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Marca del Producto *</label>
                                                        <input type="text" class="form-control" name="marca_producto" 
                                                               value="<?php echo $producto_editar ? $producto_editar['marca_producto'] : ''; ?>" required>
                                                    </div>
                                                    
                                                    <?php if(!$producto_editar): ?>
                                                    <div class="mb-3">
                                                        <label class="form-label">Stock Inicial *</label>
                                                        <input type="number" class="form-control" name="stock_inicial" value="0" required>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Stock Mínimo *</label>
                                                        <input type="number" class="form-control" name="stock_minimo" 
                                                               value="<?php echo $producto_editar ? $producto_editar['stock_minimo'] : '10'; ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Stock Máximo *</label>
                                                        <input type="number" class="form-control" name="stock_maximo" 
                                                               value="<?php echo $producto_editar ? $producto_editar['stock_maximo'] : '100'; ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Rotación *</label>
                                                        <select class="form-select" name="rotacion" required>
                                                            <option value="">Seleccionar rotación...</option>
                                                            <option value="alta" <?php echo ($producto_editar && $producto_editar['rotacion'] == 'alta') ? 'selected' : ''; ?>>Alta Rotación</option>
                                                            <option value="media" <?php echo ($producto_editar && $producto_editar['rotacion'] == 'media') ? 'selected' : ''; ?>>Media Rotación</option>
                                                            <option value="baja" <?php echo ($producto_editar && $producto_editar['rotacion'] == 'baja') ? 'selected' : ''; ?>>Baja Rotación</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <button type="submit" class="btn <?php echo $producto_editar ? 'btn-warning' : 'btn-primary'; ?>">
                                                <i class="bi <?php echo $producto_editar ? 'bi-save' : 'bi-save'; ?>"></i>
                                                <?php echo $producto_editar ? 'Actualizar Producto' : 'Guardar Producto'; ?>
                                            </button>
                                            
                                            <?php if($producto_editar): ?>
                                            <a href="productos.php" class="btn btn-secondary">
                                                <i class="bi bi-x"></i> Cancelar
                                            </a>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario para actualizar stock (solo si no se está editando) -->
                    <?php if(!$producto_editar): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingActualizarStock">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseActualizarStock" aria-expanded="false" aria-controls="collapseActualizarStock">
                                <i class="bi bi-arrow-repeat"></i>
                                <span class="ms-2">Actualizar Stock</span>
                            </button>
                        </h2>
                        <div id="collapseActualizarStock" class="accordion-collapse collapse" aria-labelledby="headingActualizarStock" data-bs-parent="#productosAccordion">
                            <div class="accordion-body">
                                <div class="card">
                                    <div class="card-body">
                                        <form method="POST">
                                            <input type="hidden" name="accion" value="actualizar_stock">
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Producto *</label>
                                                        <select class="form-select" name="id_producto" required>
                                                            <option value="">Seleccionar producto...</option>
                                                            <?php foreach($productos as $producto): ?>
                                                            <option value="<?php echo $producto['id']; ?>">
                                                                <?php echo $producto['nombre_producto'] . ' (' . $producto['marca_producto'] . ')'; ?>
                                                            </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nuevo Stock *</label>
                                                        <input type="number" class="form-control" name="nuevo_stock" min="0" required>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-arrow-repeat"></i> Actualizar Stock
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Lista de productos -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingLista">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLista" aria-expanded="true" aria-controls="collapseLista">
                                <i class="bi bi-list"></i>
                                <span class="ms-2">Productos Registrados (<?php echo count($productos); ?>)</span>
                            </button>
                        </h2>
                        <div id="collapseLista" class="accordion-collapse collapse show" aria-labelledby="headingLista" data-bs-parent="#productosAccordion">
                            <div class="accordion-body">
                                <div class="card">
                                    <div class="card-body">
                                        <?php
                                        // Aplicar filtro si existe
                                        $filtro_rotacion = isset($_GET['rotacion']) ? $_GET['rotacion'] : '';
                                        if ($filtro_rotacion) {
                                            $sql_filtrado = "SELECT * FROM productos WHERE rotacion = ? ORDER BY nombre_producto";
                                            $stmt_filtrado = $conn->prepare($sql_filtrado);
                                            $stmt_filtrado->execute([$filtro_rotacion]);
                                            $productos_mostrar = $stmt_filtrado->fetchAll();
                                            
                                            echo '<div class="alert alert-info">';
                                            echo 'Mostrando productos de <strong>';
                                            switch($filtro_rotacion) {
                                                case 'alta': echo 'Alta Rotación'; break;
                                                case 'media': echo 'Media Rotación'; break;
                                                case 'baja': echo 'Baja Rotación'; break;
                                            }
                                            echo '</strong>';
                                            echo ' <a href="productos.php" class="btn btn-sm btn-outline-primary">Ver todos</a>';
                                            echo '</div>';
                                        } else {
                                            $productos_mostrar = $productos;
                                        }
                                        ?>
                                        
                                        <!-- Filtros por rotación -->
                                        <div class="mb-3">
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="filtroRotacion" data-bs-toggle="dropdown">
                                                    <i class="bi bi-funnel"></i> Filtrar
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="productos.php">Todos</a></li>
                                                    <li><a class="dropdown-item" href="productos.php?rotacion=alta">Alta Rotación</a></li>
                                                    <li><a class="dropdown-item" href="productos.php?rotacion=media">Media Rotación</a></li>
                                                    <li><a class="dropdown-item" href="productos.php?rotacion=baja">Baja Rotación</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Producto</th>
                                                        <th>Marca</th>
                                                        <th>Stock</th>
                                                        <th>Rotación</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($productos_mostrar as $producto): ?>
                                                    <tr>
                                                        <td><?php echo $producto['nombre_producto']; ?></td>
                                                        <td><?php echo $producto['marca_producto']; ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php 
                                                                if($producto['stock_actual'] == 0) echo 'danger';
                                                                elseif($producto['stock_actual'] <= $producto['stock_minimo']) echo 'warning';
                                                                else echo 'success';
                                                            ?>">
                                                                <?php echo $producto['stock_actual']; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            switch($producto['rotacion']) {
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
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <a href="productos.php?editar=<?php echo $producto['id']; ?>" 
                                                                   class="btn btn-warning btn-sm" title="Editar">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <button type="button" class="btn btn-danger btn-sm" 
                                                                        onclick="confirmarEliminacion(<?php echo $producto['id']; ?>, '<?php echo addslashes($producto['nombre_producto']); ?>')"
                                                                        title="Eliminar">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </div>
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
                    <p>¿Está seguro que desea eliminar el producto <strong id="nombreProductoEliminar"></strong>?</p>
                    <p class="text-danger">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" id="formEliminar" style="display: inline;">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id_producto" id="idProductoEliminar">
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
            document.getElementById('idProductoEliminar').value = id;
            var modal = new bootstrap.Modal(document.getElementById('confirmarEliminarModal'));
            modal.show();
        }
    </script>
</body>
</html>