<?php


// Conexión a la base de datos con manejo de errores
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ponys2');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Inicialización de variables
$search = $ejido = $grado = $seccion = $fecha_inicio = $fecha_fin = $asistencia = "";
$results = [];
$edit_data = [];
$edit_id = null;

// Procesamiento de formularios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_submit'])) {
        // Borrar asistencias por grupo, bloque y fecha
        $delete_grado = $_POST['delete_grado'] ?? '';
        $delete_seccion = $_POST['delete_seccion'] ?? '';
        $delete_bloque = $_POST['delete_bloque'] ?? '';
        $delete_fecha = $_POST['delete_fecha'] ?? '';

        if (!empty($delete_grado) && !empty($delete_seccion) && !empty($delete_bloque) && !empty($delete_fecha)) {
            $year = date("Y");
            $tableName = "asistencia_bloques" . $year;

            // Primero obtenemos los RICE de los alumnos del grupo seleccionado
            $get_rice_sql = "SELECT RICE FROM alumnos WHERE grado = ? AND seccion = ?";
            $stmt = $conn->prepare($get_rice_sql);
            $stmt->bind_param('is', $delete_grado, $delete_seccion);
            $stmt->execute();
            $result = $stmt->get_result();
            $alumnos = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            if (!empty($alumnos)) {
                // Preparamos los RICE para la consulta
                $rice_list = array_column($alumnos, 'RICE');
                $placeholders = implode(',', array_fill(0, count($rice_list), '?'));
                $types = str_repeat('s', count($rice_list));

                // Borramos las asistencias
                $delete_sql = "DELETE FROM $tableName WHERE RICE IN ($placeholders) AND bloque = ? AND DATE(fecha_registro) = ?";
                $stmt = $conn->prepare($delete_sql);

                // Construimos los parámetros para bind_param
                $params = array_merge($rice_list, [$delete_bloque, $delete_fecha]);
                $types .= 'is'; // 'i' para bloque (entero), 's' para fecha (string)

                $stmt->bind_param($types, ...$params);

                if ($stmt->execute()) {
                    $affected_rows = $stmt->affected_rows;
                    $_SESSION['message'] = [
                        'type' => 'success',
                        'text' => "Se borraron $affected_rows registros de asistencia para el grupo $delete_grado$delete_seccion, bloque $delete_bloque en la fecha $delete_fecha"
                    ];
                } else {
                    $_SESSION['message'] = [
                        'type' => 'danger',
                        'text' => 'Error al borrar los registros: ' . $conn->error
                    ];
                }
                $stmt->close();
            } else {
                $_SESSION['message'] = [
                    'type' => 'warning',
                    'text' => "No se encontraron alumnos en el grupo $delete_grado$delete_seccion"
                ];
            }

            header("Location: reporteDetallado.php");
            exit();
        }
    } elseif (isset($_POST['edit_id'])) {
        // Editar registro
        $edit_id = $_POST['edit_id'];
        $edit_asistencia = $_POST['edit_asistencia'] ?? '';
        $edit_comentario = $_POST['edit_comentario'] ?? '';

        $year = date("Y");
        $tableName = "asistencia_bloques" . $year;

        $update_sql = "UPDATE $tableName SET asistencia = ?, comentario = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        if ($stmt === false) {
            die("Error al preparar la consulta: " . $conn->error);
        }
        $stmt->bind_param('ssi', $edit_asistencia, $edit_comentario, $edit_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Registro actualizado correctamente'];
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error al actualizar el registro'];
        }
        $stmt->close();

        header("Location: reporteDetallado.php");
        exit();
    } else {
        // Búsqueda de registros
        $search = $_POST['search'] ?? '';
        $ejido = $_POST['ejido'] ?? '';
        $grado = $_POST['grado'] ?? '';
        $seccion = $_POST['seccion'] ?? '';
        $fecha_inicio = $_POST['fecha_inicio'] ?? '';
        $fecha_fin = $_POST['fecha_fin'] ?? '';
        $asistencia = $_POST['asistencia'] ?? '';

        $year = date("Y");
        $tableName = "asistencia_bloques" . $year;

        $sql = "SELECT a.RICE, a.nombre, a.grado, a.seccion, a.nombre_tutor, 
                       a.telefono_tutor, a.telefono_casa, a.ejido, 
                       ast.asistencia, ast.comentario, ast.fecha_registro, ast.id, ast.bloque 
                FROM alumnos a 
                JOIN $tableName ast ON a.RICE = ast.RICE
                WHERE 1=1";

        $conditions = [];
        $params = [];
        $types = '';

        if (!empty($search)) {
            $conditions[] = "(a.nombre LIKE ? OR a.CURP LIKE ? OR a.RICE LIKE ?)";
            $searchParam = "%$search%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
            $types .= 'sss';
        }
        if (!empty($ejido)) {
            $conditions[] = "a.ejido = ?";
            $params[] = $ejido;
            $types .= 's';
        }
        if (!empty($grado)) {
            $conditions[] = "a.grado = ?";
            $params[] = $grado;
            $types .= 'i';
        }
        if (!empty($seccion)) {
            $conditions[] = "a.seccion = ?";
            $params[] = $seccion;
            $types .= 's';
        }
        if (!empty($fecha_inicio) && !empty($fecha_fin)) {
            $conditions[] = "DATE(ast.fecha_registro) BETWEEN ? AND ?";
            $params = array_merge($params, [$fecha_inicio, $fecha_fin]);
            $types .= 'ss';
        }
        if (!empty($asistencia)) {
            $conditions[] = "ast.asistencia = ?";
            $params[] = $asistencia;
            $types .= 's';
        }

        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY ast.fecha_registro DESC";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error al preparar la consulta: " . $conn->error);
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $results = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
} elseif (isset($_GET['edit_id'])) {
    // Cargar datos para edición
    $edit_id = $_GET['edit_id'];
    $year = date("Y");
    $tableName = "asistencia_bloques" . $year;

    $edit_sql = "SELECT a.nombre, a.grado, a.seccion, ast.asistencia, ast.comentario, ast.bloque 
                 FROM alumnos a 
                 JOIN $tableName ast ON a.RICE = ast.RICE 
                 WHERE ast.id = ?";
    $stmt = $conn->prepare($edit_sql);
    if ($stmt === false) {
        die("Error al preparar la consulta: " . $conn->error);
    }
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Asistencia</title>
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

        .form-control,
        .form-select {
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

        .attendance-present {
            background-color: #d4edda !important;
        }

        .attendance-absent {
            background-color: #f8d7da !important;
        }

        .attendance-justified {
            background-color: #fff3cd !important;
        }

        .edit-form {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid #dee2e6;
        }

        .delete-form {
            background-color: #fff8f8;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #ffdddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .delete-form h4 {
            color: #dc3545;
            margin-bottom: 15px;
        }
    </style>
    <?php include 'funciones/icon.php'; ?>
</head>

<body>
<?php include 'navbar.php'; ?>


    <div class="container">
        <!-- Formulario de edición -->
        <?php if (!empty($edit_data)): ?>
            <div class="card mt-4" style="background-color:rgb(215, 252, 218); border: 1px solid #c8e6c9;">
                <div class="card-header">
                    <h3 class="mb-0"><i class="bi bi-pencil-square"></i> Editar Registro</h3>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($edit_id); ?>">

                        <div class="mb-3">
                            <p class="mb-1"><strong>Alumno:</strong>
                                <?php echo htmlspecialchars($edit_data['nombre']); ?></p>
                            <p class="mb-1"><strong>Grupo:</strong>
                                <?php echo htmlspecialchars($edit_data['grado']) . '-' . htmlspecialchars($edit_data['seccion']); ?>
                            </p>
                            <p class="mb-3"><strong>Bloque:</strong>
                                <?php echo htmlspecialchars($edit_data['bloque']); ?></p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="edit_asistencia" class="form-label">Asistencia</label>
                                <select class="form-select" name="edit_asistencia" id="edit_asistencia" required>
                                    <option value="Asistencia" <?php echo ($edit_data['asistencia'] == "Asistencia") ? "selected" : ""; ?>>Asistió</option>
                                    <option value="Inasistencia" <?php echo ($edit_data['asistencia'] == "Inasistencia") ? "selected" : ""; ?>>No Asistió
                                    </option>
                                    <option value="Falta Justificada" <?php echo ($edit_data['asistencia'] == "Falta Justificada") ? "selected" : ""; ?>>Justificado</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label for="edit_comentario" class="form-label">Comentario</label>
                                <textarea class="form-control" name="edit_comentario" id="edit_comentario"
                                    rows="2"><?php echo htmlspecialchars($edit_data['comentario'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Actualizar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    </div>
    </div>




    <div class="container">

 
        <!-- Tarjeta principal -->
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="bi bi-clipboard2-check"></i> Registro de Asistencia</h2>
            </div>
            <div class="card-body">
                <!-- Instrucciones generales -->
                <div class="instruction-box mb-4">
                    <p class="mb-0"><i class="bi bi-info-circle"></i> Usa este formulario para buscar y filtrar
                        registros de asistencia. Puedes editar un registro haciendo clic en "Editar" y actualizar la
                        información según sea necesario.</p>
                </div>

                <!-- Mostrar mensajes -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show mb-4"
                        role="alert">
                        <?php echo $_SESSION['message']['text']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <!-- Formulario de búsqueda -->
                <div class="search-form mb-4">
                    <h4 class="mb-3"><i class="bi bi-search"></i> Buscar Asistencias</h4>
                    <form method="post" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Nombre, CURP o RICE</label>
                            <input type="text" class="form-control" name="search" id="search"
                                value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="ejido" class="form-label">Ejido</label>
                            <input type="text" class="form-control" name="ejido" id="ejido"
                                value="<?php echo htmlspecialchars($ejido); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="grado" class="form-label">Grado</label>
                            <select class="form-select" name="grado" id="grado">
                                <option value="">Seleccione</option>
                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($grado == $i) ? "selected" : ""; ?>>
                                        <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="seccion" class="form-label">Sección</label>
                            <select class="form-select" name="seccion" id="seccion">
                                <option value="">Seleccione</option>
                                <option value="A" <?php echo ($seccion == "A") ? "selected" : ""; ?>>A</option>
                                <option value="B" <?php echo ($seccion == "B") ? "selected" : ""; ?>>B</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="asistencia" class="form-label">Asistencia</label>
                            <select class="form-select" name="asistencia" id="asistencia">
                                <option value="">Seleccione</option>
                                <option value="Asistencia" <?php echo ($asistencia == "Asistencia") ? "selected" : ""; ?>>
                                    Asistió</option>
                                <option value="Inasistencia" <?php echo ($asistencia == "Inasistencia") ? "selected" : ""; ?>>No Asistió</option>
                                <option value="Falta Justificada" <?php echo ($asistencia == "Falta Justificada") ? "selected" : ""; ?>>Justificado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio"
                                value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" name="fecha_fin" id="fecha_fin"
                                value="<?php echo htmlspecialchars($fecha_fin); ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                </div>

                <?php if (!empty($results)): ?>
                    <div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0"><i class="bi bi-list-check"></i> Resultados de la Búsqueda</h3>
        <button id="toggleTableBtn" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-eye-fill"></i> Ocultar Tabla
        </button>
    </div>
    <div class="card-body" id="resultsTableContainer">
        <style>
            /* Estilos para las celdas de asistencia */
            .attendance-cell-present {
                background-color: #d4edda !important;
                color: #155724;
                font-weight: 500;
                border-left: 3px solid #28a745 !important;
                border-right: 3px solid #28a745 !important;
            }
            .attendance-cell-absent {
                background-color: #f8d7da !important;
                color: #721c24;
                font-weight: 500;
                border-left: 3px solid #dc3545 !important;
                border-right: 3px solid #dc3545 !important;
            }
            .attendance-cell-justified {
                background-color: #fff3cd !important;
                color: #856404;
                font-weight: 500;
                border-left: 3px solid #ffc107 !important;
                border-right: 3px solid #ffc107 !important;
            }
            /* Estilo para el encabezado fijo */
            .sticky-header {
                position: sticky;
                top: 0;
                background: #2c3e50 !important;
                color: white;
                z-index: 10;
            }
            /* Efecto hover para filas */
            tr:hover td {
                background-color: #f1f1f1 !important;
            }
            /* Mantener color en hover para celdas de asistencia */
            tr:hover .attendance-cell-present {
                background-color: #c3e6cb !important;
            }
            tr:hover .attendance-cell-absent {
                background-color: #f5c6cb !important;
            }
            tr:hover .attendance-cell-justified {
                background-color: #ffeeba !important;
            }
        </style>
        
        <div class="table-responsive" style="max-height: 500px; overflow: auto;">
            <table class="table table-bordered table-hover" style="min-width: 150%; width: max-content;">
                <thead>
                    <tr>
                        <th class="sticky-header">Acciones</th>
                        <th class="sticky-header">Fecha</th>
                        <th class="sticky-header">RICE</th>
                        <th class="sticky-header">Nombre</th>
                        <th class="sticky-header">Grado/Sección</th>
                        <th class="sticky-header">Bloque</th>
                        <th class="sticky-header">Asistencia</th>
                        <th class="sticky-header">Nombre Tutor</th>
                        <th class="sticky-header">Teléfono Tutor</th>
                        <th class="sticky-header">Teléfono Casa</th>
                        <th class="sticky-header">Ejido</th>
                        <th class="sticky-header">Comentario</th>
                        <th class="sticky-header">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <?php
                        // Determinar clase CSS según asistencia
                        $cellClass = '';
                        if ($row['asistencia'] == 'Asistencia') {
                            $cellClass = 'attendance-cell-present';
                        } elseif ($row['asistencia'] == 'Inasistencia') {
                            $cellClass = 'attendance-cell-absent';
                        } elseif ($row['asistencia'] == 'Falta Justificada') {
                            $cellClass = 'attendance-cell-justified';
                        }
                        ?>
                        <tr>
                            <td>
                                <a href="reporteDetallado.php?edit_id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($row['fecha_registro']); ?></td>
                            <td><?php echo htmlspecialchars($row['RICE']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($row['grado']) . '-' . htmlspecialchars($row['seccion']); ?></td>
                            <td><?php echo htmlspecialchars($row['bloque']); ?></td>
                            <td class="<?php echo $cellClass; ?>">
                                <?php echo htmlspecialchars($row['asistencia']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['nombre_tutor']); ?></td>
                            <td><?php echo htmlspecialchars($row['telefono_tutor']); ?></td>
                            <td><?php echo htmlspecialchars($row['telefono_casa']); ?></td>
                            <td><?php echo htmlspecialchars($row['ejido']); ?></td>
                            <td><?php echo htmlspecialchars($row['comentario']); ?></td>
                            <td>
                                <a href="reporteDetallado.php?edit_id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const toggleBtn = document.getElementById('toggleTableBtn');
                            const tableContainer = document.getElementById('resultsTableContainer');
                            let isVisible = true;

                            toggleBtn.addEventListener('click', function () {
                                isVisible = !isVisible;

                                if (isVisible) {
                                    tableContainer.style.display = 'block';
                                    toggleBtn.innerHTML = '<i class="bi bi-eye-fill"></i> Ocultar Tabla';
                                } else {
                                    tableContainer.style.display = 'none';
                                    toggleBtn.innerHTML = '<i class="bi bi-eye-slash-fill"></i> Mostrar Tabla';
                                }
                            });
                        });
                    </script>
                <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                    <div class="alert alert-info mt-4">
                        No se encontraron resultados para los criterios de búsqueda especificados.
                    </div>
                <?php endif; ?>

                <!-- Sección de eliminación -->
                <div class="delete-form mb-4">
                    <div>
                        <h4 class="mb-0"><i class="bi bi-trash"></i> Eliminar Registros</h4>
                    </div>
                    <div class="card-body">
                        <div class="instruction-box mb-3">
                            <p class="mb-0"><i class="bi bi-info-circle"></i> Usa este formulario para buscar y eliminar
                                registros de asistencia. Selecciona los filtros correspondientes y haz clic en "🗑️
                                Borrar
                                Asistencias" para eliminar los registros.</p>
                        </div>

                        <form method="post" class="row g-3"
                            onsubmit="return confirm('¿Está seguro que desea borrar TODAS las asistencias de este grupo, bloque y fecha? Esta acción no se puede deshacer.');">
                            <div class="col-md-2">
                                <label for="delete_grado" class="form-label">Grado</label>
                                <select class="form-select" name="delete_grado" id="delete_grado" required>
                                    <option value="">Seleccione</option>
                                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="delete_seccion" class="form-label">Sección</label>
                                <select class="form-select" name="delete_seccion" id="delete_seccion" required>
                                    <option value="">Seleccione</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="delete_bloque" class="form-label">Bloque</label>
                                <select class="form-select" name="delete_bloque" id="delete_bloque" required>
                                    <option value="">Seleccione</option>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="delete_fecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control" name="delete_fecha" id="delete_fecha" required>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" name="delete_submit" class="btn btn-danger w-100">
                                    <i class="bi bi-trash"></i> Borrar Asistencias
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>



        <!-- Bootstrap 5 JS Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>