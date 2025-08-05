<?php
// Definiciones y conexión a la base de datos
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ponys2');

// Conexión a la base de datos
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexión
if ($mysqli === false) {
    die("<div class='alert alert-danger'>ERROR: No se pudo conectar. " . $mysqli->connect_error . "</div>");
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mover alumnos</title>
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

        .instruction-box {
            background-color: #e9ecef;
            border-left: 4px solid #2c3e50;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 8px 8px 0;
        }

        .info-text {
            color: gray;
            font-style: italic;
        }

        .filter-form {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .update-form {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
                <h2 class="mb-0"><i class="bi bi-people-fill"></i> Mover alumnos</h2>
            </div>
            <div class="card-body">
                <!-- Instrucciones -->
                <div class="instruction-box">
                    <p class="mb-0"><i class="bi bi-info-circle"></i> Este menú te permite filtrar y actualizar la
                        información de los alumnos. Primero, selecciona el grado y la sección de los alumnos que deseas
                        ver. Luego, puedes elegir los alumnos que quieres modificar. Finalmente, selecciona el nuevo
                        grado o sección para actualizar la información de todos los alumnos seleccionados al mismo
                        tiempo.</p>
                </div>

                <!-- Formulario de filtrado -->
                <div class="filter-form">
                    <h3><i class="bi bi-funnel"></i> Filtrar Alumnos por Grado y Sección</h3>
                    <form method="post" action="" class="row g-3">
                        <div class="col-md-4">
                            <label for="grado" class="form-label">Grado:</label>
                            <select class="form-select" name="grado" id="grado" required>
                                <option value="">Selecciona un grado</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="seccion" class="form-label">Sección:</label>
                            <select class="form-select" name="seccion" id="seccion" required>
                                <option value="">Selecciona una sección</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" name="submit_filter" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>

                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_filter'])) {
                    // Obtener valores del formulario
                    $grado = $_POST['grado'];
                    $seccion = $_POST['seccion'];

                    // Consulta SQL para filtrar por grado y sección y ordenar alfabéticamente por nombre
                    $sql = "SELECT id, RICE, nombre, grado, seccion FROM alumnos WHERE grado = ? AND seccion = ? ORDER BY nombre ASC";

                    if ($stmt = $mysqli->prepare($sql)) {
                        $stmt->bind_param("is", $grado, $seccion);
                        $stmt->execute();
                        $stmt->store_result();

                        if ($stmt->num_rows > 0) {
                            $stmt->bind_result($id, $rice, $nombre, $grado, $seccion);
                            echo "<div class='update-form'>";
                            echo "<form method='post' action=''>";
                            echo "<div class='table-responsive'>";
                            echo "<table class='table table-bordered table-hover'>";
                            echo "<thead class='table-dark'>";
                            echo "<tr>
                                    <th>Seleccionar</th>
                                    <th>RICE</th>
                                    <th>Nombre</th>
                                    <th>Grado</th>
                                    <th>Sección</th>
                                </tr>";
                            echo "</thead><tbody>";
                            while ($stmt->fetch()) {
                                echo "<tr>
                                        <td><input type='checkbox' class='form-check-input' name='selected_ids[]' value='$id'></td>
                                        <td>$rice</td>
                                        <td>$nombre</td>
                                        <td>$grado</td>
                                        <td>$seccion</td>
                                    </tr>";
                            }
                            echo "</tbody></table></div>";
                            echo "<div class='row g-3 mt-3'>
                                    <div class='col-md-4'>
                                        <label for='new_grado' class='form-label'>Nuevo Grado:</label>
                                        <select class='form-select' name='new_grado' id='new_grado'>
                                            <option value=''>No cambiar</option>
                                            <option value='1'>1</option>
                                            <option value='2'>2</option>
                                            <option value='3'>3</option>
                                            <option value='4'>4</option>
                                            <option value='5'>5</option>
                                            <option value='6'>6</option>
                                        </select>
                                    </div>
                                    <div class='col-md-4'>
                                        <label for='new_seccion' class='form-label'>Nueva Sección:</label>
                                        <select class='form-select' name='new_seccion' id='new_seccion'>
                                            <option value=''>No cambiar</option>
                                            <option value='A'>A</option>
                                            <option value='B'>B</option>
                                        </select>
                                    </div>
                                    <div class='col-md-4 d-flex align-items-end'>
                                        <button type='submit' name='submit_update' class='btn btn-primary w-100'>
                                            <i class='bi bi-arrow-repeat'></i> Actualizar Seleccionados
                                        </button>
                                    </div>
                                </div>";
                            echo "</form>";
                            echo "</div>";
                        } else {
                            echo "<div class='alert alert-warning'>No se encontraron alumnos para el grado y sección seleccionados.</div>";
                        }

                        $stmt->close();
                    }
                }
                ?>

                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_update'])) {
                    $selected_ids = $_POST['selected_ids'];
                    $new_grado = $_POST['new_grado'];
                    $new_seccion = $_POST['new_seccion'];

                    if (!empty($selected_ids)) {
                        // Construir consulta para actualizar los registros seleccionados
                        $sql = "UPDATE alumnos SET ";
                        $params = [];
                        $types = "";

                        if (!empty($new_grado)) {
                            $sql .= "grado = ?, ";
                            $params[] = $new_grado;
                            $types .= "i";
                        }

                        if (!empty($new_seccion)) {
                            $sql .= "seccion = ?, ";
                            $params[] = $new_seccion;
                            $types .= "s";
                        }

                        // Eliminar la última coma y añadir la cláusula WHERE
                        $sql = rtrim($sql, ", ") . " WHERE id IN (" . implode(",", array_fill(0, count($selected_ids), "?")) . ")";

                        // Añadir los IDs seleccionados a los parámetros
                        foreach ($selected_ids as $id) {
                            $params[] = $id;
                            $types .= "i";
                        }

                        if ($stmt = $mysqli->prepare($sql)) {
                            $stmt->bind_param($types, ...$params);
                            if ($stmt->execute()) {
                                echo "<div class='alert alert-success'><i class='bi bi-check-circle'></i> Los alumnos seleccionados han sido actualizados exitosamente.</div>";
                            } else {
                                echo "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> ERROR: No se pudo ejecutar la actualización.</div>";
                            }
                            $stmt->close();
                        } else {
                            echo "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> ERROR: No se pudo preparar la consulta SQL.</div>";
                        }
                    } else {
                        echo "<div class='alert alert-warning'><i class='bi bi-exclamation-triangle'></i> Por favor selecciona al menos un alumno.</div>";
                    }
                }
                ?>
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