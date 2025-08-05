<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Reporte</title>
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
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
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
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
        }
        .student-info {
            background-color: #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .student-info p {
            margin-bottom: 5px;
        }
        textarea.form-control {
            min-height: 120px;
        }
        .alert-success {
            border-radius: 8px;
        }
    </style>
    <?php include 'funciones/icon.php'; ?>
</head>
<?php include 'navbar.php'; ?>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="bi bi-pencil-square"></i> Editar Reporte</h2>
            </div>
            <div class="card-body">
                <?php
                // Conexión a la base de datos
                define('DB_SERVER', 'localhost');
                define('DB_USERNAME', 'root');
                define('DB_PASSWORD', '');
                define('DB_NAME', 'ponys');

                $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

                // Verificar la conexión
                if($mysqli->connect_errno){
                    die("<div class='alert alert-danger'>ERROR: No se pudo conectar. " . $mysqli->connect_error . "</div>");
                }

                // Inicializar variables
                $rice = $curp = $nombre = $tipo_reporte = $comentario = $fecha_ingreso = "";

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $table_name = $_POST['table'];
                    $reporte_id = $_POST['id'];
                    $tipo_reporte = $_POST['tipo_reporte'];
                    $comentario = $_POST['comentario'];

                    $sql = "UPDATE $table_name SET tipo_reporte=?, comentario=? WHERE id=?";
                    if ($stmt = $mysqli->prepare($sql)) {
                        $stmt->bind_param("ssi", $tipo_reporte, $comentario, $reporte_id);
                        if ($stmt->execute()) {
                            echo "<div class='alert alert-success'><i class='bi bi-check-circle'></i> Reporte actualizado correctamente.</div>";
                        } else {
                            echo "<div class='alert alert-danger'>ERROR: No se pudo ejecutar $sql. " . $stmt->error . "</div>";
                        }
                        $stmt->close();
                    } else {
                        echo "<div class='alert alert-danger'>ERROR: No se pudo preparar la consulta: $sql. " . $mysqli->error . "</div>";
                    }
                }

                if ($_SERVER["REQUEST_METHOD"] == "GET" || ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']))) {
                    if(isset($_GET['table']) && isset($_GET['id']) || isset($_POST['id'])) {
                        $table_name = isset($_GET['table']) ? $_GET['table'] : $_POST['table'];
                        $reporte_id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
                        
                        $sql = "SELECT a.RICE, a.CURP, a.nombre, r.tipo_reporte, r.comentario, r.fecha_ingreso 
                                FROM $table_name r
                                JOIN alumnos a ON r.alumno_id = a.id
                                WHERE r.id = ?";
                        if ($stmt = $mysqli->prepare($sql)) {
                            $stmt->bind_param("i", $reporte_id);
                            if ($stmt->execute()) {
                                $stmt->bind_result($rice, $curp, $nombre, $tipo_reporte, $comentario, $fecha_ingreso);
                                $stmt->fetch();
                                $stmt->close();
                            } else {
                                echo "<div class='alert alert-danger'>ERROR: No se pudo ejecutar $sql. " . $stmt->error . "</div>";
                            }
                        } else {
                            echo "<div class='alert alert-danger'>ERROR: No se pudo preparar la consulta: $sql. " . $mysqli->error . "</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger'>ERROR: No se recibieron los parámetros necesarios.</div>";
                    }
                }

                // Cerrar la conexión a la base de datos
                $mysqli->close();
                ?>
             
             
             
                
                <!-- Información del alumno -->
                <div class="student-info">
                    <h3><i class="bi bi-person-vcard"></i> Datos del alumno</h3>
                    <hr>
                    <p><strong>RICE:</strong> <?php echo htmlspecialchars($rice); ?></p>
                    <p><strong>CURP:</strong> <?php echo htmlspecialchars($curp); ?></p>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($nombre); ?></p>
                    <p><strong>Registro de reporte:</strong> <?php echo htmlspecialchars($fecha_ingreso); ?></p>
                </div>

                <!-- Formulario de edición -->
                <form method="post" action="">
                    <input type="hidden" name="table" value="<?php echo htmlspecialchars($table_name); ?>">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($reporte_id); ?>">
                    
                    <div class="mb-3">
                        <h3><i class="bi bi-pencil"></i> Editar</h3>
                        <hr>
                        <label for="tipo_reporte" class="form-label">Tipo de Reporte:</label>
                        <select class="form-select" name="tipo_reporte" id="tipo_reporte">
                            <option value="conducta" <?php echo ($tipo_reporte === 'conducta') ? 'selected' : ''; ?>>Conducta</option>
                            <option value="trabajos" <?php echo ($tipo_reporte === 'trabajos') ? 'selected' : ''; ?>>Trabajos</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="comentario" class="form-label">Comentario:</label>
                        <textarea class="form-control" name="comentario" id="comentario" rows="4"><?php echo htmlspecialchars($comentario); ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Actualizar
                        </button>
                        <a href="GestionReporte.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Regresar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>