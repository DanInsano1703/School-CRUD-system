<?php
include('db.php');

// Conexión a la base de datos
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar la conexión a la base de datos
if ($mysqli->connect_error) {
    die("Error en la conexión: " . $mysqli->connect_error);
}

// Determinar el año actual
$current_year = date("Y");
$table_name = "reportes_" . $current_year;

// Verificar si la tabla de reportes para el año actual existe
$sql_check_table = "CREATE TABLE IF NOT EXISTS $table_name (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumno_id INT NOT NULL,
    tipo_reporte ENUM('conducta', 'trabajos') NOT NULL,
    comentario TEXT,
    fecha_ingreso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alumno_id) REFERENCES alumnos(id)
)";
$mysqli->query($sql_check_table);

// Obtener los parámetros de búsqueda
$search_rice = isset($_GET['rice']) ? trim($_GET['rice']) : '';
$search_curp = isset($_GET['curp']) ? trim($_GET['curp']) : '';
$search_nombre = isset($_GET['nombre']) ? trim($_GET['nombre']) : '';

// Paginación
$limit = 20; // Número de filas por página
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Consulta SQL optimizada para obtener los datos de los alumnos y los reportes
$sql_alumnos = "
    SELECT a.nombre, a.CURP, a.RICE,
           SUM(CASE WHEN r.tipo_reporte = 'conducta' THEN 1 ELSE 0 END) AS conducta,
           SUM(CASE WHEN r.tipo_reporte = 'trabajos' THEN 1 ELSE 0 END) AS trabajos,
           COUNT(r.id) AS total_reportes
    FROM alumnos a
    LEFT JOIN $table_name r ON a.id = r.alumno_id
    WHERE a.RICE LIKE ? AND a.CURP LIKE ? AND a.nombre LIKE ?
    GROUP BY a.id
    ORDER BY total_reportes DESC
    LIMIT ?, ?
";

// Preparar y ejecutar la consulta
$stmt = $mysqli->prepare($sql_alumnos);
$search_rice_param = "%$search_rice%";
$search_curp_param = "%$search_curp%";
$search_nombre_param = "%$search_nombre%";
$stmt->bind_param("sssii", $search_rice_param, $search_curp_param, $search_nombre_param, $start, $limit);
$stmt->execute();
$result_alumnos = $stmt->get_result();

// Calcular el número total de páginas
$total_alumnos_sql = "
    SELECT COUNT(*) as total
    FROM alumnos
    WHERE RICE LIKE ? AND CURP LIKE ? AND nombre LIKE ?
";
$stmt_total = $mysqli->prepare($total_alumnos_sql);
$stmt_total->bind_param("sss", $search_rice_param, $search_curp_param, $search_nombre_param);
$stmt_total->execute();
$total_alumnos = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_alumnos / $limit);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de reportes</title>
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

        .form-control {
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

        .warning-row {
            background-color: #fff3cd !important;
        }

        .danger-row {
            background-color: #f8d7da !important;
        }

        .critical-row {
            background-color: #dc3545 !important;
            color: white;
        }

        .critical-row a {
            color: white;
        }

        .pagination .page-item.active .page-link {
            background-color: #2c3e50;
            border-color: #2c3e50;
        }

        .pagination .page-link {
            color: #2c3e50;
        }
    </style><?php include 'funciones/icon.php'; ?>
</head>

<body>
<?php include 'navbar.php'; ?>

    <div class="container">
     

        <!-- Tarjeta principal -->
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="bi bi-clipboard2-pulse"></i> Historial de reportes</h2>
            </div>
            <div class="card-body">
                <!-- Formulario de búsqueda -->
                <div class="search-form">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label for="rice" class="form-label">RICE</label>
                            <input type="text" class="form-control" id="rice" name="rice"
                                value="<?php echo htmlspecialchars($search_rice); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="curp" class="form-label">CURP</label>
                            <input type="text" class="form-control" id="curp" name="curp"
                                value="<?php echo htmlspecialchars($search_curp); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                value="<?php echo htmlspecialchars($search_nombre); ?>">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Resultados -->
                <?php if ($result_alumnos->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>CURP</th>
                                    <th>RICE</th>
                                    <th>Reportes Conducta</th>
                                    <th>Reportes Trabajos</th>
                                    <th>Total Reportes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result_alumnos->fetch_assoc()): ?>
                                    <tr class="<?php
                                    echo $row['total_reportes'] == 2 ? 'warning-row' : '';
                                    echo $row['total_reportes'] == 3 ? 'danger-row' : '';
                                    echo $row['total_reportes'] >= 4 ? 'critical-row' : '';
                                    ?>">
                                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($row['CURP']); ?></td>
                                        <td><?php echo htmlspecialchars($row['RICE']); ?></td>
                                        <td><?php echo $row['conducta']; ?></td>
                                        <td><?php echo $row['trabajos']; ?></td>
                                        <td><strong><?php echo $row['total_reportes']; ?></strong></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link"
                                        href="?page=<?php echo $i; ?>&rice=<?php echo urlencode($search_rice); ?>&curp=<?php echo urlencode($search_curp); ?>&nombre=<?php echo urlencode($search_nombre); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php else: ?>
                    <div class="alert alert-info">
                        No se encontraron alumnos con los criterios de búsqueda especificados.
                    </div>
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