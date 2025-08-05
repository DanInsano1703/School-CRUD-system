<?php
// Conexión a la base de datos
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ponys2');

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

// Crear conexión
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$grado = $seccion = $bloque = "";
$year = date("Y");
$fecha_actual = date("Y-m-d");
$startOfDay = $fecha_actual . ' 00:00:00';
$endOfDay = $fecha_actual . ' 23:59:59';

// Crear la tabla si no existe
$tableName = "asistencia_bloques" . $year;
$createTableSql = "CREATE TABLE IF NOT EXISTS $tableName (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rice VARCHAR(20) NOT NULL,
    curp VARCHAR(18) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    grado INT NOT NULL,
    seccion VARCHAR(2) NOT NULL,
    bloque INT NOT NULL,
    asistencia ENUM('Asistencia', 'Inasistencia', 'Falta Justificada') NOT NULL,
    comentario TEXT,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($createTableSql)) {
    die("Error creando tabla: " . $conn->error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['grado']) && isset($_POST['seccion']) && isset($_POST['bloque'])) {
        $grado = $_POST['grado'];
        $seccion = $_POST['seccion'];
        $bloque = $_POST['bloque'];

        // Verificar si ya se pasó lista para este grupo hoy
        $checkSql = "SELECT COUNT(*) AS total FROM $tableName WHERE grado = ? AND seccion = ? AND fecha_registro BETWEEN ? AND ?";
        $stmtCheck = $conn->prepare($checkSql);
        $stmtCheck->bind_param("isss", $grado, $seccion, $startOfDay, $endOfDay);

        if (!$stmtCheck->execute()) {
            die("Error verificando asistencia: " . $stmtCheck->error);
        }

        $checkResult = $stmtCheck->get_result();
        $checkRow = $checkResult->fetch_assoc();

        if ($checkRow['total'] > 0) {
            $error_message = "Ya se ha registrado la asistencia para el grupo $grado$seccion el día de hoy.";
        } else {
            // Si no se ha pasado lista hoy, continuar con la selección de alumnos
            $sql = "SELECT * FROM alumnos WHERE grado = ? AND seccion = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("is", $grado, $seccion);
            $stmt->execute();
            $result = $stmt->get_result();
        }
    } elseif (isset($_POST['asistencia']) && isset($_POST['bloque'])) {
        $bloque = $_POST['bloque'];

        // Guardar la asistencia en la tabla específica por año
        foreach ($_POST['asistencia'] as $id => $asistencia) {
            $comentario = isset($_POST['comentario'][$id]) ? $_POST['comentario'][$id] : '';

            // Obtener datos del alumno
            $alumnoSql = "SELECT rice, curp, nombre, grado, seccion FROM alumnos WHERE id = ?";
            $stmtAlumno = $conn->prepare($alumnoSql);
            $stmtAlumno->bind_param("i", $id);
            $stmtAlumno->execute();
            $alumnoResult = $stmtAlumno->get_result();
            $alumno = $alumnoResult->fetch_assoc();

            // Insertar en la tabla específica de año y bloque
            $sqlBloque = "INSERT INTO $tableName (rice, curp, nombre, grado, seccion, bloque, asistencia, comentario, fecha_registro) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmtBloque = $conn->prepare($sqlBloque);
            $stmtBloque->bind_param(
                "sssisiss",
                $alumno['rice'],
                $alumno['curp'],
                $alumno['nombre'],
                $alumno['grado'],
                $alumno['seccion'],
                $bloque,
                $asistencia,
                $comentario
            );
            if (!$stmtBloque->execute()) {
                die("Error guardando asistencia: " . $stmtBloque->error);
            }
        }
        $success_message = "Asistencia guardada correctamente.";
        $stmtBloque->close();
    }
}

// Consulta para obtener los grupos ya registrados hoy con manejo de errores
$registeredGroupsSql = "SELECT grado, seccion, bloque FROM $tableName 
                       WHERE fecha_registro BETWEEN ? AND ? 
                       GROUP BY grado, seccion, bloque";
$stmtRegisteredGroups = $conn->prepare($registeredGroupsSql);

if ($stmtRegisteredGroups === false) {
    die("Error preparando consulta: " . $conn->error);
}

$stmtRegisteredGroups->bind_param("ss", $startOfDay, $endOfDay);

if (!$stmtRegisteredGroups->execute()) {
    die("Error ejecutando consulta: " . $stmtRegisteredGroups->error);
}

$registeredGroupsResult = $stmtRegisteredGroups->get_result();
?>

<!DOCTYPE html>    
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasar lista</title>
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

        .attendance-radio {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .attendance-radio .form-check {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .registered-groups {
            margin-bottom: 30px;
        }


        /* Estilo para los radio buttons no seleccionados */
        .attendance-radio .form-check-input {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border: 2px solid #555;
            /* Borde oscuro bien visible */
            border-radius: 50%;
            background: white;
            margin-top: 0.2em;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
        }

        /* Estilo para cuando están seleccionados */
        .attendance-radio .form-check-input:checked {
            border-width: 6px;
        }

        /* Colores para cada estado seleccionado */
        .attendance-radio .form-check-input:checked[value="Asistencia"] {
            border-color: #28a745;
            /* Verde */
        }

        .attendance-radio .form-check-input:checked[value="Inasistencia"] {
            border-color: #dc3545;
            /* Rojo */
        }

        .attendance-radio .form-check-input:checked[value="Falta Justificada"] {
            border-color: #ffc107;
            /* Amarillo */
        }

        /* Efecto hover para mejor interactividad */
        .attendance-radio .form-check-input:hover {
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
        }
    </style>
    <?php include 'funciones/icon.php'; ?>
</head>

<body>
<?php include 'navbar.php'; ?>
   
<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="mb-0"><i class="bi bi-clipboard-check"></i> Pasar lista</h2>
        </div>
        
        <div class="card-body">
            <!-- Módulo de Instrucciones -->
            <div class="instruction-box mb-4 p-3 bg-light rounded">
            <p class="mb-0"><i class="bi bi-info-circle"></i> Este apartado permite registrar la asistencia de
                        los alumnos para un grupo específico. Para usarlo, selecciona el grado, la sección y el bloque
                        correspondiente, luego indica si cada alumno asistió, estuvo ausente o justificó su falta antes
                        de guardar la información.</p>
                    <p class="mb-0 mt-2"><strong>Nota:</strong> No pasar lista al mismo grupo más de una vez en el mismo
                        día.</p>
            </div>

            <!-- Módulo de Mensajes -->
            <div class="message-module mb-4">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <br>Revisa el menú <a href="reporteDetallado.php" class="alert-link">Registro de Asistencia</a>.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Módulo de Grupos Registrados -->
            <div class="registered-groups-module mb-4 p-3 border rounded">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">
                        <i class="bi bi-list-check me-2"></i>Grupos ya registrados hoy
                    </h4>
                    <span class="badge bg-primary"><?php echo $registeredGroupsResult->num_rows; ?> grupos</span>
                </div>
                
                <?php if ($registeredGroupsResult->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Grupo</th>
                                    <th>Bloque</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $registeredGroupsResult->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['grado']) . htmlspecialchars($row['seccion']); ?></td>
                                        <td><?php echo htmlspecialchars($row['bloque']); ?></td>
                                        <td><span class="badge bg-success"><i class="bi bi-check-circle"></i> Registrado</span></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary mb-0">
                        No se ha registrado asistencia para ningún grupo hoy.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Módulo de Selección de Grupo -->
            <div class="group-selection-module mb-4 p-3 bg-light rounded">
                <h4 class="mb-3">
                    <i class="bi bi-funnel me-2"></i>Seleccionar Grupo
                </h4>
                <form method="post" class="row g-3">
                    <div class="col-md-3">
                        <label for="grado" class="form-label">Grado</label>
                        <select name="grado" id="grado" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($grado == $i) ? "selected" : ""; ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="seccion" class="form-label">Sección</label>
                        <select name="seccion" id="seccion" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="A" <?php echo ($seccion == "A") ? "selected" : ""; ?>>A</option>
                            <option value="B" <?php echo ($seccion == "B") ? "selected" : ""; ?>>B</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="bloque" class="form-label">Bloque</label>
                        <select name="bloque" id="bloque" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($bloque == $i) ? "selected" : ""; ?>>
                                    Bloque <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Módulo de Lista de Alumnos -->
            <?php if (isset($result) && $result->num_rows > 0): ?>
                <div class="student-list-module p-3 border rounded">
                    <form method="post">
                        <input type="hidden" name="bloque" value="<?php echo $bloque; ?>">
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">
                                <i class="bi bi-people-fill me-2"></i>
                                Grupo <?php echo $grado . $seccion; ?> - Bloque <?php echo $bloque; ?>
                            </h4>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="todos_asistieron" onclick="marcarTodosAsistieron()">
                                <label class="form-check-label" for="todos_asistieron">Todos asistieron</label>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40%">Alumno</th>
                                        <th width="40%">Asistencia</th>
                                        <th width="20%">Comentario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <input type="radio" class="btn-check" name="asistencia[<?php echo $row['id']; ?>]" 
                                                        id="asistencia_<?php echo $row['id']; ?>" value="Asistencia" required>
                                                    <label class="btn btn-outline-success" for="asistencia_<?php echo $row['id']; ?>">
                                                        <i class="bi bi-check-circle"></i> Asistió
                                                    </label>

                                                    <input type="radio" class="btn-check" name="asistencia[<?php echo $row['id']; ?>]" 
                                                        id="inasistencia_<?php echo $row['id']; ?>" value="Inasistencia">
                                                    <label class="btn btn-outline-danger" for="inasistencia_<?php echo $row['id']; ?>">
                                                        <i class="bi bi-x-circle"></i> No asistió
                                                    </label>

                                                    <input type="radio" class="btn-check" name="asistencia[<?php echo $row['id']; ?>]" 
                                                        id="justificada_<?php echo $row['id']; ?>" value="Falta Justificada">
                                                    <label class="btn btn-outline-warning" for="justificada_<?php echo $row['id']; ?>">
                                                        <i class="bi bi-exclamation-circle"></i> Justificada
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" 
                                                    name="comentario[<?php echo $row['id']; ?>]" placeholder="Opcional">
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save me-2"></i> Guardar Asistencia
                            </button>
                        </div>
                    </form>
                </div>
            <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($error_message)): ?>
                <div class="alert alert-info">
                    No hay alumnos en este grupo o no se ha seleccionado un grupo.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function marcarTodosAsistieron() {
            const checkboxes = document.querySelectorAll('input[type="radio"][value="Asistencia"]');
            checkboxes.forEach((checkbox) => {
                checkbox.checked = true;
            });
        }
    </script>
</body>

</html>