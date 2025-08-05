<?php
include 'db.php'; // Incluye tu archivo de conexión a la base de datos

// Obtener término de búsqueda, si existe
$busqueda = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Consulta para obtener la cantidad de apellidos únicos (familias)
$queryFamilias = "SELECT COUNT(DISTINCT apellidos) AS total_familias FROM alumnos";
$resultFamilias = $conn->query($queryFamilias);

if (!$resultFamilias) {
    die("Error en la consulta: " . $conn->error);
}

$rowFamilias = $resultFamilias->fetch_assoc();
$totalFamilias = $rowFamilias['total_familias'];

// Consulta para obtener los apellidos y contar su frecuencia, ordenados por frecuencia descendente
$queryApellidos = "SELECT apellidos, COUNT(*) AS cantidad 
                   FROM alumnos 
                   WHERE apellidos LIKE ?
                   GROUP BY apellidos 
                   ORDER BY cantidad DESC";

$stmt = $conn->prepare($queryApellidos);
$busquedaParam = "%$busqueda%";
$stmt->bind_param("s", $busquedaParam);
$stmt->execute();
$resultApellidos = $stmt->get_result();

if (!$resultApellidos) {
    die("Error en la consulta: " . $conn->error);
}

// Array para almacenar los apellidos y sus frecuencias
$apellidosFrecuencias = [];

// Procesar resultados
while ($row = $resultApellidos->fetch_assoc()) {
    $apellidos = $row['apellidos'];
    $cantidad = $row['cantidad'];

    $apellidosFrecuencias[$apellidos] = $cantidad;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Familias</title>
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

        .apellido-link {
            color: #2c3e50;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .apellido-link:hover {
            color: #1a252f;
            text-decoration: underline;
        }

        .stats-card {
            background-color: #2c3e50;
            color: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
        }

        .stats-label {
            font-size: 1rem;
            opacity: 0.8;
        }
    </style>
    <script>
        function mostrarDetalles(apellido) {
            window.location.href = 'detalles.php?apellido=' + encodeURIComponent(apellido);
        }
    </script>
    <?php include 'funciones/icon.php'; ?>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="container">


        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">Familias</h2>
            </div>
            <div class="card-body">
                <div class="instruction-box">
                    <p class="mb-0">
                        <i class="bi bi-info-circle-fill"></i> Utiliza el campo de búsqueda para filtrar los apellidos
                        por los cuales deseas obtener información.
                        Simplemente ingresa parte del apellido en el campo de búsqueda y presiona el botón "Buscar" para
                        ver
                        los resultados que coinciden con tu búsqueda. Los apellidos se mostrarán ordenados por la
                        frecuencia
                        con la que aparecen en la base de datos.
                    </p>
                </div>

                <div class="row">
                    <div class="col-md-6 offset-md-3">
                        <div class="search-form">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-9">
                                    <input type="text" class="form-control" name="search"
                                        placeholder="Buscar apellidos..."
                                        value="<?php echo htmlspecialchars($busqueda); ?>">
                                </div>
                                <div class="col-3">
                                    <button type="submit" class="btn btn-primary w-600">
                                        <i class="bi bi-search"></i> Buscar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo htmlspecialchars($totalFamilias); ?></div>
                            <div class="stats-label">Familias registradas</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Apellido</th>
                                <th>Frecuencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $contador = 1;
                            foreach ($apellidosFrecuencias as $apellido => $cantidad):
                                ?>
                                <tr>
                                    <td><?php echo $contador++; ?></td>
                                    <td>
                                        <span class="apellido-link"
                                            onclick="mostrarDetalles('<?php echo htmlspecialchars($apellido); ?>')">
                                            <?php echo htmlspecialchars($apellido); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($cantidad); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>