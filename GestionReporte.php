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

// Determinar la tabla basada en el año ingresado o el año actual
$currentYear = date("Y");
$year = isset($_POST['year']) ? $_POST['year'] : $currentYear;
$table_name = "reportes_" . $year;

// Verificar si la tabla existe
$table_exists = $mysqli->query("SHOW TABLES LIKE '$table_name'")->num_rows > 0;

$resultados = [];
if ($table_exists) {
    // Construir la consulta SQL con filtros
    $conditions = [];
    if (!empty($_POST['rice'])) {
        $rice = $mysqli->real_escape_string($_POST['rice']);
        $conditions[] = "a.RICE LIKE '%$rice%'";
    }
    if (!empty($_POST['curp'])) {
        $curp = $mysqli->real_escape_string($_POST['curp']);
        $conditions[] = "a.CURP LIKE '%$curp%'";
    }

    // MODIFICACIÓN IMPORTANTE: Manejo de fechas
    if (!empty($_POST['fecha_inicio']) || !empty($_POST['fecha_fin'])) {
        // Validar y formatear fechas
        $fecha_inicio = !empty($_POST['fecha_inicio']) ?
            $mysqli->real_escape_string($_POST['fecha_inicio']) :
            '1970-01-01'; // Fecha muy antigua si no se especifica inicio

        $fecha_fin = !empty($_POST['fecha_fin']) ?
            $mysqli->real_escape_string($_POST['fecha_fin']) :
            date('Y-m-d'); // Fecha actual si no se especifica fin

        // Asegurarse de que las fechas estén en formato correcto
        if (DateTime::createFromFormat('Y-m-d', $fecha_inicio) && DateTime::createFromFormat('Y-m-d', $fecha_fin)) {
            // Convertir a formato DATETIME de MySQL (añadiendo tiempo si es necesario)
            $conditions[] = "r.fecha_ingreso BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
        } else {
            $error_message = "Formato de fecha inválido. Use YYYY-MM-DD.";
        }
    }

    $sql = "SELECT a.RICE, a.CURP, a.nombre, a.grado, a.seccion, r.id AS reporte_id, r.tipo_reporte, r.comentario, r.fecha_ingreso 
            FROM $table_name r
            JOIN alumnos a ON r.alumno_id = a.id";

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY r.fecha_ingreso DESC";

    if ($result = $mysqli->query($sql)) {
        if ($result->num_rows > 0) {
            $resultados = $result->fetch_all(MYSQLI_ASSOC);
        }
    } else {
        $error_message = "ERROR: No se pudo ejecutar $sql. " . $mysqli->error;
    }
}

// Cerrar la conexión a la base de datos
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reportes</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #2c3e50;
            color: white;
            border-radius: 10px 10px 0 0 !important;
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
        }

        .form-select,
        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
        }

        .instruction-box {
            background-color: #e9ecef;
            border-left: 4px solid #2c3e50;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 8px 8px 0;
        }

        .search-form {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-link {
            transition: all 0.3s;
            text-decoration: none;
        }

        .action-link:hover {
            transform: translateY(-2px);
        }

        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
    <?php include 'funciones/icon.php'; ?>
</head>

<body>
<?php include 'navbar.php'; ?>
    <div class="container">
    

        <!-- Tarjeta principal -->
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="bi bi-clipboard2-pulse"></i> Gestión de Reportes</h2>
            </div>
            <div class="card-body">
                <!-- Instrucciones -->
                <div class="instruction-box">
                    <p class="mb-0"><i class="bi bi-info-circle"></i> Para buscar reportes, ingrese el año
                        correspondiente en el campo "Ingrese el año". Puede usar los filtros de RICE, CURP y rango de
                        fechas para refinar su búsqueda. Una vez que haya ingresado los datos, presione el botón
                        "Buscar" para ver los resultados.</p>
                </div>

                <!-- Formulario de búsqueda -->
                <div class="search-form">
                    <form method="post" class="row g-3">
                        <div class="col-md-2">
                            <label for="year" class="form-label">Año</label>
                            <input type="text" class="form-control" name="year" id="year"
                                onkeypress="validateYearInput(event)" value="<?php echo htmlspecialchars($year); ?>"
                                required>
                        </div>
                        <div class="col-md-2">
                            <label for="rice" class="form-label">RICE</label>
                            <input type="text" class="form-control" name="rice" id="rice"
                                value="<?php echo isset($_POST['rice']) ? htmlspecialchars($_POST['rice']) : ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="curp" class="form-label">CURP</label>
                            <input type="text" class="form-control" name="curp" id="curp"
                                value="<?php echo isset($_POST['curp']) ? htmlspecialchars($_POST['curp']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio"
                                value="<?php echo isset($_POST['fecha_inicio']) ? htmlspecialchars($_POST['fecha_inicio']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" name="fecha_fin" id="fecha_fin"
                                value="<?php echo isset($_POST['fecha_fin']) ? htmlspecialchars($_POST['fecha_fin']) : ''; ?>">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Resultados de la búsqueda -->
                <?php if (!$table_exists): ?>
                    <div class="alert alert-warning">
                        No se encontraron registros para el año <?php echo htmlspecialchars($year); ?>.
                    </div>
                <?php elseif (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                <?php elseif (count($resultados) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>RICE</th>
                                    <th>CURP</th>
                                    <th>Nombre</th>
                                    <th>Grupo</th>
                                    <th>Tipo</th>
                                    <th>Comentario</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resultados as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['RICE']); ?></td>
                                        <td><?php echo htmlspecialchars($row['CURP']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($row['grado']) . htmlspecialchars($row['seccion']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['tipo_reporte']); ?></td>
                                        <td><?php echo htmlspecialchars($row['comentario']); ?></td>
                                        <td><?php echo htmlspecialchars($row['fecha_ingreso']); ?></td>
                                        <td class="action-buttons">
                                            <a href="editar_reporte.php?table=<?php echo $table_name; ?>&id=<?php echo $row['reporte_id']; ?>"
                                                class="btn btn-sm btn-primary action-link" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="eliminar_reporte.php?table=<?php echo $table_name; ?>&id=<?php echo $row['reporte_id']; ?>"
                                                class="btn btn-sm btn-danger action-link"
                                                onclick="return confirmarEliminacion('<?php echo htmlspecialchars(addslashes($row['nombre'])); ?>', <?php echo $row['reporte_id']; ?>, '<?php echo $table_name; ?>');"
                                                title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                    <div class="alert alert-info">
                        No se encontraron reportes para los criterios seleccionados.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function validateYearInput(event) {
            var charCode = (event.which) ? event.which : event.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                event.preventDefault();
            }
        }

        function confirmarEliminacion(nombre, id, tabla) {
            return confirm(`¿Está seguro de que desea eliminar el reporte de ${nombre}?\nEsta acción no se podrá deshacer.`);
        }
    </script>
</body>

</html>