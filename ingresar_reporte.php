<?php
include('db.php');

$success_message = '';
$error_message = '';

// Verificar si se ha enviado un formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Conexión a la base de datos
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Determinar el año actual
    $current_year = date("Y");
    $table_name = "reportes_" . $current_year;

    // Verificar si la tabla de reportes para el año actual existe, si no, crearla
    $sql_check_table = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        alumno_id INT NOT NULL,
        tipo_reporte ENUM('conducta', 'trabajos') NOT NULL,
        comentario TEXT,
        fecha_ingreso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (alumno_id) REFERENCES alumnos(id)
    )";
    $mysqli->query($sql_check_table);

    // Verificar si se ha recibido el ID del alumno
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $alumno_id = $_GET['id'];

        // Verificar si se ha seleccionado un tipo de reporte
        if (isset($_POST['tipo_reporte']) && !empty($_POST['tipo_reporte'])) {
            $tipo_reporte = $_POST['tipo_reporte'];
            $comentario = $_POST['comentario'];

            // Insertar el reporte en la tabla de reportes con comentario correspondiente al año actual
            $sql_insert_reporte = "INSERT INTO $table_name (alumno_id, tipo_reporte, comentario) VALUES (?, ?, ?)";
            if ($stmt = $mysqli->prepare($sql_insert_reporte)) {
                $stmt->bind_param("iss", $alumno_id, $tipo_reporte, $comentario);
                if ($stmt->execute()) {
                    $success_message = "Reporte ingresado correctamente.";
                } else {
                    $error_message = "Error al ingresar el reporte.";
                }
                $stmt->close();
            }
        } else {
            $error_message = "Por favor, seleccione un tipo de reporte.";
        }
    } else {
        $error_message = "Error: No se ha proporcionado un ID de alumno.";
    }

    // Cerrar la conexión a la base de datos
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar Reporte</title>
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
        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .student-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .student-info p {
            margin-bottom: 5px;
        }
        .form-label {
            font-weight: 500;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
        }
        .action-links {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
    <?php include 'funciones/icon.php'; ?>
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Ingresar Reporte</h2>
                    </div>
                    <div class="card-body">
                        <?php
                        // Conexión a la base de datos
                        $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

                        // Verificar si se ha recibido el ID del alumno
                        if (isset($_GET['id']) && !empty($_GET['id'])) {
                            $alumno_id = $_GET['id'];

                            // Obtener información del alumno
                            $sql_alumno = "SELECT RICE, CURP, nombre FROM alumnos WHERE id = ?";
                            if ($stmt = $mysqli->prepare($sql_alumno)) {
                                $stmt->bind_param("i", $alumno_id);
                                $stmt->execute();
                                $stmt->bind_result($rice, $curp, $nombre);
                                $stmt->fetch();
                                $stmt->close();

                                // Mostrar información del alumno
                                echo '<div class="student-info">';
                                echo '<h4><i class="bi bi-person-vcard"></i> Datos del Alumno</h4>';
                                echo '<p><strong>Nombre:</strong> ' . htmlspecialchars($nombre) . '</p>';
                                echo '<p><strong>RICE:</strong> ' . htmlspecialchars($rice) . '</p>';
                                echo '<p><strong>CURP:</strong> ' . htmlspecialchars($curp) . '</p>';
                                echo '</div>';
                            } else {
                                $error_message = "Error al obtener información del alumno.";
                            }
                        } else {
                            $error_message = "Error: No se ha proporcionado un ID de alumno.";
                        }

                        // Mostrar mensajes de éxito o error
                        if (!empty($success_message)) {
                            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
                            echo $success_message;
                            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                            echo '</div>';
                        }

                        if (!empty($error_message)) {
                            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                            echo $error_message;
                            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                            echo '</div>';
                        }
                        ?>

                        <form method="post">
                            <div class="mb-3">
                                <label for="tipo_reporte" class="form-label">Tipo de Reporte</label>
                                <select class="form-select" name="tipo_reporte" id="tipo_reporte" required>
                                    <option value="">Seleccione un tipo...</option>
                                    <option value="conducta">Conducta</option>
                                    <option value="trabajos">Trabajos</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="comentario" class="form-label">Comentario</label>
                                <textarea class="form-control" name="comentario" id="comentario" rows="4" required></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-save"></i> Ingresar Reporte
                                </button>
                            </div>
                        </form>

                        <div class="action-links">
                            <a href="ReportarAlumno.php" class="btn btn-secondary">
                                <i class="bi bi-plus-circle"></i> Ingresar un nuevo reporte
                            </a>
                     
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>