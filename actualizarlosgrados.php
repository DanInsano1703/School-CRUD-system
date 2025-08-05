<?php
// Incluir el archivo de conexión a la base de datos
include 'db.php';

// Inicializar variables de mensaje
$message = '';
$messageType = '';

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $success = true;

    // Obtener todos los alumnos
    $sql = "SELECT id, grado FROM alumnos"; // Solo seleccionamos las columnas necesarias
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // Iniciar transacción para operaciones atómicas
        $conn->begin_transaction();

        try {
            // Iterar sobre cada alumno
            while ($row = $result->fetch_assoc()) {
                $id = $row['id'];
                $gradoActual = $row['grado'];

                // Verificar si el alumno pasa a séptimo grado
                if ($action == 'sumar' && $gradoActual == 6) {
                    // Obtener el año actual
                    $currentYear = date('Y');

                    // Lista de prefijos de tablas relacionadas que siguen el patrón anual
                    $relatedTablePrefixes = [
                        'niveles_lectura',
                        'reportes',
                        // Añade aquí cualquier otra tabla relacionada con el mismo patrón
                    ];

                    // Primero eliminar todos los registros relacionados en tablas anualizadas
                    foreach ($relatedTablePrefixes as $prefix) {
                        for ($year = 2024; $year <= $currentYear; $year++) {
                            $tableName = $prefix . ($prefix == 'niveles_lectura' ? '' : '_') . $year;

                            // Verificar si la tabla existe antes de intentar eliminar
                            $checkTableSql = "SHOW TABLES LIKE '$tableName'";
                            $tableResult = $conn->query($checkTableSql);

                            if ($tableResult && $tableResult->num_rows > 0) {
                                $deleteSql = "DELETE FROM $tableName WHERE alumno_id = $id";
                                if (!$conn->query($deleteSql)) {
                                    throw new Exception("Error al eliminar registros de $tableName: " . $conn->error);
                                }
                            }
                        }
                    }

                    // Luego eliminar al alumno de la base de datos
                    $deleteSql = "DELETE FROM alumnos WHERE id = $id";
                    if (!$conn->query($deleteSql)) {
                        throw new Exception("Error al eliminar alumno: " . $conn->error);
                    }
                } else {
                    // Actualizar el grado según la acción seleccionada
                    $nuevoGrado = $gradoActual + ($action == 'sumar' ? 1 : -1);

                    // Validar que el nuevo grado esté en rango válido (1-6)
                    if ($nuevoGrado < 1 || $nuevoGrado > 6) {
                        throw new Exception("Grado fuera de rango para el alumno ID $id");
                    }

                    // Actualizar el grado en la base de datos
                    $updateSql = "UPDATE alumnos SET grado = $nuevoGrado WHERE id = $id";
                    if (!$conn->query($updateSql)) {
                        throw new Exception("Error al actualizar grado: " . $conn->error);
                    }
                }
            }

            // Confirmar transacción si todo fue exitoso
            $conn->commit();
            $message = "¡Los grados se han actualizado con éxito!";
            $messageType = "success";
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $conn->rollback();
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
            $success = false;
        }
    } else {
        $message = "No hay alumnos registrados para actualizar";
        $messageType = "info";
    }
}

// Cerrar la conexión a la base de datos
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Grados</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
 body {
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-danger:hover {
            background-color: #bb2d3b;
        }

        .btn-back {
            background-color: #6c757d;
            border: none;
            margin-top: 20px;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }

        .note-card {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 0 8px 8px 0;
        }

        .warning-card {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 0 8px 8px 0;
        }

        .form-check-input:checked {
            background-color: #2c3e50;
            border-color: #2c3e50;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-container img {
            max-height: 100px;
        }
    </style>
    <?php include 'funciones/icon.php'; ?>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="container">


        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="bi bi-arrow-repeat"></i> Actualizar Grados de los Alumnos</h2>
            </div>
            <div class="card-body">
                <?php if (isset($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <h4><i class="bi bi-info-circle"></i> Función y uso</h4>
                <p>Este formulario facilita la actualización de los grados de todos los alumnos en la base de datos.</p>
                <p>Seleccione una opción de la siguiente lista y haga clic en "Actualizar Grados" para aplicar los
                    cambios:</p>

                <div class="warning-card">
                    <h5><i class="bi bi-exclamation-triangle"></i> Notas importantes:</h5>
                    <ol>
                        <li>Se recomienda utilizar esta función exclusivamente cuando los alumnos avancen al siguiente
                            grado escolar.</li>
                        <li>La actualización debe realizarse <strong>ANTES</strong> de registrar nuevos alumnos de
                            primer grado.</li>
                        <li>Los alumnos de 6° grado serán <strong>ELIMINADOS PERMANENTEMENTE</strong> del sistema.</li>
                    </ol>
                </div>

                <form id="updateForm" method="POST" action="">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="action" id="sumar" value="sumar" required>
                        <label class="form-check-label" for="sumar">
                            <strong>Sumar +1</strong> a todos los grados (avanzar al siguiente año escolar)
                        </label>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="radio" name="action" id="restar" value="restar" required>
                        <label class="form-check-label" for="restar">
                            <strong>Restar -1</strong> a todos los grados (solo para correcciones)
                        </label>
                    </div>

                    <div class="d-grid gap-2 d-md-block">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Actualizar Grados
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('updateForm').addEventListener('submit', function (event) {
            const action = document.querySelector('input[name="action"]:checked').value;
            const message = action === 'sumar'
                ? "¿Está seguro que desea avanzar todos los grados? Los alumnos de 6° grado serán eliminados permanentemente."
                : "¿Está seguro que desea retroceder todos los grados? Esta acción solo debe usarse para correcciones.";

            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    </script>
</body>

</html>