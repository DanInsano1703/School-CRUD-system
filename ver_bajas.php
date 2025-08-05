<?php
// Conexión a la base de datos
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ponys2');

$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar la conexión
if ($mysqli === false) {
    die("ERROR: No se pudo conectar. " . $mysqli->connect_error);
}

// Definir los filtros de búsqueda
$rice = isset($_POST['rice']) ? $_POST['rice'] : '';
$curp = isset($_POST['curp']) ? $_POST['curp'] : '';
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
$fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '';
$fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : '';

// Construir la consulta con los filtros
$query = "SELECT * FROM bajasAcademicas WHERE 1=1";

if ($rice) {
    $query .= " AND antiguo_RICE LIKE '%" . $mysqli->real_escape_string($rice) . "%'";
}

if ($curp) {
    $query .= " AND CURP LIKE '%" . $mysqli->real_escape_string($curp) . "%'";
}

if ($nombre) {
    $query .= " AND nombre LIKE '%" . $mysqli->real_escape_string($nombre) . "%'";
}

if ($fecha_inicio && $fecha_fin) {
    $query .= " AND fecha_baja BETWEEN '" . $mysqli->real_escape_string($fecha_inicio) . "' AND '" . $mysqli->real_escape_string($fecha_fin) . "'";
}

$query .= " ORDER BY fecha_baja DESC";

// Ejecutar la consulta
$result = $mysqli->query($query);

// Manejar la actualización de los registros
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $fecha_baja = $_POST['fecha_baja'];
    $motivo_baja = $_POST['motivo_baja'];

    $updateQuery = "UPDATE bajasAcademicas SET fecha_baja = ?, motivo_baja = ? WHERE id = ?";
    $stmt = $mysqli->prepare($updateQuery);
    $stmt->bind_param('ssi', $fecha_baja, $motivo_baja, $id);
    $stmt->execute();
    $stmt->close();
    
    // Redirigir para evitar reenvíos del formulario
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de bajas</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
            margin: 0;
            padding: 0;
            box-sizing: border-box;

        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: none;
        }
        .card-header {
            background-color: #2c3e50;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 1.25rem;
        }
        .btn-primary {
            background-color: #2c3e50;
            border: none;
        }
        .btn-primary:hover {
            background-color: #1a252f;
        }
        .btn-back {
            background-color: #6c757d;
            border: none;
            margin-bottom: 20px;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table th {
            background-color: #2c3e50;
            color: white;
            position: sticky;
            top: 0;
        }
        .form-select, .form-control {
            border-radius: 8px;
            padding: 10px 15px;
        }
        .search-form {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .instruction-box {
            background-color: #e9ecef;
            border-left: 4px solid #2c3e50;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 8px 8px 0;
        }
        .action-btn {
            padding: 5px 10px;
            font-size: 0.875rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .table-container {
            max-height: 600px;
            overflow-y: auto;
        }
    </style>
    <?php include 'funciones/icon.php'; ?>
</head>
<?php include 'navbar.php'; ?>
<body>
    <div class="container">
    
        
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">Gestión de bajas</h2>
            </div>
            <div class="card-body">
                <div class="instruction-box">
                    <p class="mb-0">
                        <i class="bi bi-info-circle-fill"></i> Este apartado permite gestionar las bajas académicas de los alumnos. Puedes buscar registros utilizando filtros como RICE, CURP, nombre y fechas. En la tabla resultante, puedes actualizar la fecha de baja y el motivo de baja directamente desde el listado.
                    </p>
                </div>
                
                <div class="search-form">
                    <form action="ver_bajas.php" method="post" class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <label for="rice" class="form-label">Antiguo RICE:</label>
                            <input type="text" class="form-control" id="rice" name="rice" value="<?php echo htmlspecialchars($rice); ?>">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="curp" class="form-label">CURP:</label>
                            <input type="text" class="form-control" id="curp" name="curp" value="<?php echo htmlspecialchars($curp); ?>">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="nombre" class="form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio:</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="fecha_fin" class="form-label">Fecha Fin:</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="table-responsive table-container">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Antiguo RICE</th>
                                    <th>CURP</th>
                                    <th>Nombre</th>
                                    <th>Último Curso</th>
                                    <th>Sección</th>
                                    <th>Fecha de Baja</th>
                                    <th>Motivo de Baja</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = 1; ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><?php echo htmlspecialchars($row['antiguo_RICE']); ?></td>
                                        <td><?php echo htmlspecialchars($row['CURP']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ultimo_curso']); ?></td>
                                        <td><?php echo htmlspecialchars($row['seccion']); ?></td>
                                        <td>
                                            <form method="post" action="" class="row g-2">
                                                <div class="col-12">
                                                    <input type="date" class="form-control form-control-sm" name="fecha_baja" value="<?php echo htmlspecialchars($row['fecha_baja']); ?>">
                                                </div>
                                        </td>
                                        <td>
                                                <div class="col-12">
                                                    <input type="text" class="form-control form-control-sm" name="motivo_baja" value="<?php echo htmlspecialchars($row['motivo_baja']); ?>">
                                                </div>
                                        </td>
                                        <td>
                                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                                <button type="submit" name="update" class="btn btn-primary btn-sm action-btn">
                                                    <i class="bi bi-check-circle"></i> Actualizar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle"></i> No se encontraron resultados con los filtros aplicados.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php $mysqli->close(); ?>
</body>
</html>