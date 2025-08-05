<?php
// Conexión a la base de datos
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ponys2');

$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexión
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Procesar formulario de búsqueda
$resultados = [];
if (isset($_POST['submit_busqueda'])) {
    // Preparar consulta
    $condiciones = [];

    if (!empty($_POST['busqueda_curp'])) {
        $curp = $mysqli->real_escape_string($_POST['busqueda_curp']);
        $condiciones[] = "CURP LIKE '%$curp%'";
    }
    if (!empty($_POST['busqueda_rice'])) {
        $rice = $mysqli->real_escape_string($_POST['busqueda_rice']);
        $condiciones[] = "RICE LIKE '%$rice%'";
    }
    if (!empty($_POST['busqueda_nombre'])) {
        $nombre = $mysqli->real_escape_string($_POST['busqueda_nombre']);
        $condiciones[] = "nombre LIKE '%$nombre%'";
    }
    if (!empty($_POST['busqueda_grado'])) {
        $grado = $mysqli->real_escape_string($_POST['busqueda_grado']);
        $condiciones[] = "grado = '$grado'";
    }
    if (!empty($_POST['busqueda_seccion'])) {
        $seccion = $mysqli->real_escape_string($_POST['busqueda_seccion']);
        $condiciones[] = "seccion = '$seccion'";
    }

    if (count($condiciones) > 0) {
        $sql = "SELECT * FROM alumnos WHERE " . implode(' AND ', $condiciones);
        $result = $mysqli->query($sql);

        if ($result->num_rows > 0) {
            $resultados = $result->fetch_all(MYSQLI_ASSOC);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Alumno</title>
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
        }

        .action-link:hover {
            transform: translateX(3px);
        }
    </style>
    <?php include 'funciones/icon.php'; ?>
</head>

<body>  <?php include 'navbar.php'; ?>
    <div class="container">
   

        <!-- Tarjeta principal -->
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Reportar Alumno</h2>
            </div>
            <div class="card-body">
                <!-- Instrucciones -->
                <div class="instruction-box">
                    <p class="mb-0"><i class="bi bi-info-circle"></i> Use los filtros de búsqueda para seleccionar al
                        alumno al que se le asignará un reporte.</p>
                </div>

                <!-- Formulario de búsqueda -->
                <div class="search-form">
                    <form method="post">
                        <h4><i class="bi bi-funnel"></i> Filtros de Búsqueda</h4>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="filtro_curp" class="form-label">CURP</label>
                                <input type="text" class="form-control" name="busqueda_curp" id="filtro_curp"
                                    value="<?php echo isset($_POST['busqueda_curp']) ? htmlspecialchars($_POST['busqueda_curp']) : ''; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="filtro_rice" class="form-label">RICE</label>
                                <input type="text" class="form-control" name="busqueda_rice" id="filtro_rice"
                                    value="<?php echo isset($_POST['busqueda_rice']) ? htmlspecialchars($_POST['busqueda_rice']) : ''; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="filtro_nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="busqueda_nombre" id="filtro_nombre"
                                    value="<?php echo isset($_POST['busqueda_nombre']) ? htmlspecialchars($_POST['busqueda_nombre']) : ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_grado" class="form-label">Grado</label>
                                <select class="form-select" name="busqueda_grado" id="filtro_grado">
                                    <option value="">Seleccione...</option>
                                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo (isset($_POST['busqueda_grado']) && $_POST['busqueda_grado'] == $i) ? 'selected' : ''; ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_seccion" class="form-label">Sección</label>
                                <select class="form-select" name="busqueda_seccion" id="filtro_seccion">
                                    <option value="">Seleccione...</option>
                                    <option value="A" <?php echo (isset($_POST['busqueda_seccion']) && $_POST['busqueda_seccion'] == 'A') ? 'selected' : ''; ?>>A</option>
                                    <option value="B" <?php echo (isset($_POST['busqueda_seccion']) && $_POST['busqueda_seccion'] == 'B') ? 'selected' : ''; ?>>B</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" name="submit_busqueda" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Resultados de la búsqueda -->
                <?php if (isset($_POST['submit_busqueda'])): ?>
                    <?php if (count($resultados) > 0): ?>
                        <div class="table-responsive">
                            <h4><i class="bi bi-list-check"></i> Resultados de la Búsqueda</h4>
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>RICE</th>
                                        <th>CURP</th>
                                        <th>Nombre</th>
                                        <th>Grado</th>
                                        <th>Sección</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resultados as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['RICE']); ?></td>
                                            <td><?php echo htmlspecialchars($row['CURP']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($row['grado']); ?></td>
                                            <td><?php echo htmlspecialchars($row['seccion']); ?></td>
                                            <td>
                                                <a href="ingresar_reporte.php?id=<?php echo $row['id']; ?>"
                                                    class="action-link text-decoration-none">
                                                    <i class="bi bi-pencil-square"></i> Ingresar Reporte
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif (count($condiciones) > 0): ?>
                        <div class="alert alert-info">
                            No se encontraron resultados para la búsqueda.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Por favor, ingresa al menos un criterio de búsqueda.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
// Cerrar la conexión a la base de datos
$mysqli->close();
?>