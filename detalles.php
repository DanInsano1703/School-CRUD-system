<?php
include 'db.php'; // Incluye tu archivo de conexión a la base de datos

// Obtener el apellido desde la URL
$apellido = isset($_GET['apellido']) ? $conn->real_escape_string($_GET['apellido']) : '';

// Consulta para obtener los alumnos con el apellido específico
$queryAlumnos = "SELECT id, nombre, grado, seccion, tiene_hermanos FROM alumnos WHERE apellidos='$apellido'";
$resultAlumnos = $conn->query($queryAlumnos);

if (!$resultAlumnos) {
    die("Error en la consulta: " . $conn->error);
}

// Procesar los datos de alumnos en un array para manejo posterior
$alumnos = [];
while ($row = $resultAlumnos->fetch_assoc()) {
    $alumnos[] = $row;
}

// Manejar la lógica del formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $todosHermanos = isset($_POST['todos_hermanos']) ? $_POST['todos_hermanos'] : '';
    $hermanosSeleccionados = isset($_POST['hermanos']) ? $_POST['hermanos'] : [];

    if ($todosHermanos == 'si') {
        // Si todos son hermanos, marcar todos los alumnos del apellido con tiene_hermanos='SI'
        $queryActualizarTodos = "UPDATE alumnos SET tiene_hermanos='SI' WHERE apellidos='$apellido'";
        if (!$conn->query($queryActualizarTodos)) {
            die("Error al actualizar hermanos: " . $conn->error);
        }
    } else {
        // Si solo algunos son hermanos, actualizar los seleccionados
        if (!empty($hermanosSeleccionados)) {
            $hermanosIDs = implode(',', array_map('intval', $hermanosSeleccionados));
            // Marcar como hermanos los seleccionados
            $queryActualizarAlgunos = "UPDATE alumnos SET tiene_hermanos='SI' WHERE id IN ($hermanosIDs)";
            if (!$conn->query($queryActualizarAlgunos)) {
                die("Error al actualizar hermanos seleccionados: " . $conn->error);
            }

            // Marcar como no hermanos los que no fueron seleccionados
            $queryActualizarNoHermanos = "UPDATE alumnos SET tiene_hermanos='NO' WHERE apellidos='$apellido' AND id NOT IN ($hermanosIDs)";
            if (!$conn->query($queryActualizarNoHermanos)) {
                die("Error al actualizar no hermanos: " . $conn->error);
            }
        }
    }

    // Redirigir después de actualizar
    header("Location: familias.php");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumnos con el mismo apellido</title>
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
        }
        .form-check-input:checked {
            background-color: #2c3e50;
            border-color: #2c3e50;
        }
        .instruction-box {
            background-color: #e9ecef;
            border-left: 4px solid #2c3e50;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 8px 8px 0;
        }
        .apellido-title {
            color: #2c3e50;
            font-weight: bold;
        }
        .form-check-label {
            margin-left: 0.5rem;
        }
        .form-actions {
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }
    </style>
    <?php include 'funciones/icon.php'; ?>
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="container">
    
        
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">Alumnos con el mismo apellido </h2>
            </div>
            <div class="card-body">
                <div class="instruction-box">
                    <p class="mb-0">
                        <i class="bi bi-info-circle-fill"></i> Este apartado te permite identificar a los alumnos que comparten el mismo apellido y marcar si son hermanos. Puedes seleccionar todos los hermanos a la vez o elegir cuáles de ellos lo son, actualizando así la información.
                    </p>
                </div>
                
                <form method="POST">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre Completo</th>
                                    <th>Grado</th>
                                    <th>Sección</th>
                                    <th>¿Es hermano?</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alumnos as $alumno): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($alumno['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($alumno['grado']); ?></td>
                                        <td><?php echo htmlspecialchars($alumno['seccion']); ?></td>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="hermanos[]" value="<?php echo $alumno['id']; ?>" <?php echo ($alumno['tiene_hermanos'] == 'SI') ? 'checked' : ''; ?>>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="todos_hermanos" id="todosSi" value="si" onclick="marcarTodos(true)">
                        <label class="form-check-label" for="todosSi">
                            Todos son hermanos
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="todos_hermanos" id="todosNo" value="no" onclick="marcarTodos(false)" checked>
                        <label class="form-check-label" for="todosNo">
                            Solo algunos son hermanos (selecciona cuáles)
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Actualizar datos
                        </button>
                        <a href="familias.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left-circle"></i> Volver
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function marcarTodos(valor) {
            var checkboxes = document.querySelectorAll('input[name="hermanos[]"]');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = valor;
            });
        }
        
        // Verificar si todos los checkboxes están marcados para seleccionar automáticamente "Todos son hermanos"
        document.addEventListener('DOMContentLoaded', function() {
            var checkboxes = document.querySelectorAll('input[name="hermanos[]"]');
            var radioTodosSi = document.getElementById('todosSi');
            
            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    var allChecked = true;
                    checkboxes.forEach(function(cb) {
                        if (!cb.checked) allChecked = false;
                    });
                    radioTodosSi.checked = allChecked;
                });
            });
        });
    </script>
</body>
</html>