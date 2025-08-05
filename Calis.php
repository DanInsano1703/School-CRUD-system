<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ponys2');

// Crear conexión
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obtener el año actual
$current_year = date("Y");

// Construir dinámicamente el nombre de la tabla
$table_name = "calificaciones_" . $current_year;

// Inicializar una variable de estado
$datosActualizados = false;

// Comprobar si se ha enviado el formulario para guardar calificaciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (preg_match('/^esp_(\d+)_B(\d+)$/', $key, $matches)) {
            $rice = $matches[1];  // El RICE del alumno
            $bloque = $matches[2];  // El número de bloque
            $esp = isset($_POST["esp_{$rice}_B{$bloque}"]) ? (float) $_POST["esp_{$rice}_B{$bloque}"] : 0;
            $mat = isset($_POST["mat_{$rice}_B{$bloque}"]) ? (float) $_POST["mat_{$rice}_B{$bloque}"] : 0;
            $ing = isset($_POST["ing_{$rice}_B{$bloque}"]) ? (float) $_POST["ing_{$rice}_B{$bloque}"] : 0;

            // Insertar o actualizar los datos en la tabla correspondiente
            $sql = "INSERT INTO $table_name (RICE, bloque, espanol, matematicas, ingles)
                    VALUES ('$rice', $bloque, $esp, $mat, $ing)
                    ON DUPLICATE KEY UPDATE espanol = VALUES(espanol), matematicas = VALUES(matematicas), ingles = VALUES(ingles)";

            if ($conn->query($sql) === TRUE) {
                $datosActualizados = true; // Indicar que al menos una actualización fue exitosa
            } else {
                echo "Error: " . $conn->error . "<br>";
            }
        }
    }

    // Mostrar mensaje si al menos una actualización fue exitosa
    if ($datosActualizados) {
        echo "Datos actualizados.<br>";
        echo "<br>";
    }
}

// Variables para los filtros
$filter_grado = isset($_GET['grado']) ? $_GET['grado'] : '';
$filter_seccion = isset($_GET['seccion']) ? $_GET['seccion'] : '';

// Crear la consulta SQL con los filtros
$sql = "SELECT RICE, nombre, grado, seccion FROM alumnos WHERE 1=1";

if (!empty($filter_grado)) {
    $sql .= " AND grado = '" . $conn->real_escape_string($filter_grado) . "'";
}
if (!empty($filter_seccion)) {
    $sql .= " AND seccion = '" . $conn->real_escape_string($filter_seccion) . "'";
}

// Ejecutar la consulta para obtener los alumnos
$result = $conn->query($sql);

// Crear un array para almacenar las calificaciones existentes
$calificaciones = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rice = $row['RICE'];
        $calificaciones[$rice] = [];

        // Consultar las calificaciones existentes para cada alumno
        $sql_calificaciones = "SELECT bloque, espanol, matematicas, ingles FROM $table_name WHERE RICE = '$rice'";
        $result_calificaciones = $conn->query($sql_calificaciones);
        
        if ($result_calificaciones->num_rows > 0) {
            while ($cal = $result_calificaciones->fetch_assoc()) {
                $bloque = $cal['bloque'];
                $calificaciones[$rice][$bloque] = $cal;
            }
        }
    }
}

// Obtener los datos de los alumnos para mostrar en la tabla de promedios
$sql_alumnos = "SELECT RICE, nombre, grado, seccion FROM alumnos WHERE 1=1";
if (!empty($filter_grado)) {
    $sql_alumnos .= " AND grado = '" . $conn->real_escape_string($filter_grado) . "'";
}
if (!empty($filter_seccion)) {
    $sql_alumnos .= " AND seccion = '" . $conn->real_escape_string($filter_seccion) . "'";
}

$result_alumnos = $conn->query($sql_alumnos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<link rel="stylesheet" href="css/Lectura.css">
<style>
        body {
    font-family: Calibri, Arial, sans-serif;
    margin: 20px;
    background-color: #f5f5f5; /* Fondo gris claro */
    color: #333; /* Texto en gris oscuro */
    }
    
    h2 {
        text-align: center;
    }

    form {
        margin-bottom: 20px;
    }

    select, input[type="submit"] {
        margin-right: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
    }

    th {
        background-color: #4d4d4d; /* Gris oscuro */
        color: white;
    }

    /* Colores para las columnas de materias */
    td input[type="text"] {
        width: 60px;
        text-align: center;
    }

    /* Matemáticas (invertido) */
    td:nth-child(5), td:nth-child(6), td:nth-child(7), td:nth-child(8), td:nth-child(9), td:nth-child(10) {
        background-color: #e8f5e9; /* Verde claro */
    }

    /* Español (invertido) */
    td:nth-child(11), td:nth-child(12), td:nth-child(13), td:nth-child(14), td:nth-child(15), td:nth-child(16) {
        background-color: #e0f7fa; /* Azul claro */
    }

    /* Inglés */
    td:nth-child(17), td:nth-child(18), td:nth-child(19), td:nth-child(20), td:nth-child(21), td:nth-child(22) {
        background-color: #fff9c4; /* Amarillo claro */
    }

    /* Promedio por bloque */
    td:nth-child(23), td:nth-child(24), td:nth-child(25), td:nth-child(26), td:nth-child(27), td:nth-child(28) {
        background-color: #f3e5f5; /* Lila claro */
    }

    /* Promedio General */
    td:nth-child(29) {
        background-color: #c8e6c9; /* Verde claro */
    }

    input[type="text"] {
        box-sizing: border-box;
    }

    input[type="submit"] {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 10px 20px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        margin: 4px 2px;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        background-color: #45a049;
    }
</style>
    <meta charset="UTF-8">
    <title>Registro de Calificaciones</title>
    <script>
        function calculateAverages() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                let espSum = 0, matSum = 0, ingSum = 0;
                let espCount = 0, matCount = 0, ingCount = 0;

                // Calcular promedios por bloque y general
                for (let i = 1; i <= 6; i++) {
                    const espInput = row.querySelector(input[name="esp_${row.cells[0].innerText}_B${i}"]);
                    const matInput = row.querySelector(input[name="mat_${row.cells[0].innerText}_B${i}"]);
                    const ingInput = row.querySelector(input[name="ing_${row.cells[0].innerText}_B${i}"]);

                    const espValue = parseFloat(espInput.value) || 0;
                    const matValue = parseFloat(matInput.value) || 0;
                    const ingValue = parseFloat(ingInput.value) || 0;

                    espSum += espValue;
                    matSum += matValue;
                    ingSum += ingValue;

                    espCount += espValue > 0 ? 1 : 0;
                    matCount += matValue > 0 ? 1 : 0;
                    ingCount += ingValue > 0 ? 1 : 0;

                    // Promedio por bloque
                    row.querySelector(.prom_bloque_${i}_${row.cells[0].innerText}).innerText = ((espValue + matValue + ingValue) / 3).toFixed(2);
                }

                // Promedio general
                const avgEsp = espCount > 0 ? (espSum / espCount) : 0;
                const avgMat = matCount > 0 ? (matSum / matCount) : 0;
                const avgIng = ingCount > 0 ? (ingSum / ingCount) : 0;

                const overallAverage = ((avgEsp + avgMat + avgIng) / 3).toFixed(2);
                row.querySelector(.prom_general_${row.cells[0].innerText}).innerText = overallAverage;
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('input[type="text"]').forEach(input => {
                input.addEventListener('input', calculateAverages);
            });
        });
    </script>
</head>
<body>
<a href="index.php">
    <button>Regresar a lista</button>
</a>

<h2>Registro de Calificaciones</h2>
<hr>
<p><i>Este apartado permite ingresar y actualizar las calificaciones de los alumnos por bloque. Selecciona el grado y la sección correspondientes, completa las calificaciones en los campos provistos, y presiona "Guardar Calificaciones" para almacenar los datos.</i></p>

<hr>

<!-- Formulario de filtros -->
<form method="GET" action="">
    <label for="grado">Grado:</label>
    <select name="grado" id="grado">
        <option value="">Todos</option>
        <?php for ($i = 1; $i <= 6; $i++): ?>
            <option value="<?php echo $i; ?>" <?php echo $filter_grado == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
        <?php endfor; ?>
    </select>

    <label for="seccion">Sección:</label>
    <select name="seccion" id="seccion">
        <option value="">Todas</option>
        <option value="A" <?php echo $filter_seccion == 'A' ? 'selected' : ''; ?>>A</option>
        <option value="B" <?php echo $filter_seccion == 'B' ? 'selected' : ''; ?>>B</option>
    </select>
    <input type="submit" value="Buscar" style="background-color: #333; color: #fff; border: 8px solid #333; padding: 5px 10px; cursor: pointer; border-radius: 8px; font-size: 12px;">

</form>
<br> 





<!-- Tabla de resultados -->
    <form method="POST" action="">
        <table>
            <thead>
                <tr>
                    <th>RICE</th>
                    <th>Nombre</th>
                    <th>Grado</th>
                    <th>Sección</th>
                    <th colspan="6">Español</th>
                    <th colspan="6">Matemáticas</th>
                    <th colspan="6">Inglés</th>
                    <th colspan="6">Promedio por Bloque</th>
                    <th>Promedio General</th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <th>B<?php echo $i; ?></th>
                    <?php endfor; ?>
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <th>B<?php echo $i; ?></th>
                    <?php endfor; ?>
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <th>B<?php echo $i; ?></th>
                    <?php endfor; ?>
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <th>Bloque <?php echo $i; ?></th>
                    <?php endfor; ?>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($alumno = $result_alumnos->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($alumno['RICE']); ?></td>
                        <td><?php echo htmlspecialchars($alumno['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($alumno['grado']); ?></td>
                        <td><?php echo htmlspecialchars($alumno['seccion']); ?></td>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <td><input type="text" name="esp_<?php echo htmlspecialchars($alumno['RICE']); ?>_B<?php echo $i; ?>" value="<?php echo isset($calificaciones[$alumno['RICE']][$i]['espanol']) ? htmlspecialchars($calificaciones[$alumno['RICE']][$i]['espanol']) : ''; ?>"></td>
                        <?php endfor; ?>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <td><input type="text" name="mat_<?php echo htmlspecialchars($alumno['RICE']); ?>_B<?php echo $i; ?>" value="<?php echo isset($calificaciones[$alumno['RICE']][$i]['matematicas']) ? htmlspecialchars($calificaciones[$alumno['RICE']][$i]['matematicas']) : ''; ?>"></td>
                        <?php endfor; ?>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <td><input type="text" name="ing_<?php echo htmlspecialchars($alumno['RICE']); ?>_B<?php echo $i; ?>" value="<?php echo isset($calificaciones[$alumno['RICE']][$i]['ingles']) ? htmlspecialchars($calificaciones[$alumno['RICE']][$i]['ingles']) : ''; ?>"></td>
                        <?php endfor; ?>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <td class="prom_bloque_<?php echo $i; ?>_<?php echo htmlspecialchars($alumno['RICE']); ?>"></td>
                        <?php endfor; ?>
                        <td class="prom_general_<?php echo htmlspecialchars($alumno['RICE']); ?>"></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <input type="submit" value="Guardar Calificaciones" style="background-color: #333; color: #fff; border: 8px solid #333; padding: 5px 10px; cursor: pointer; border-radius: 8px; font-size: 14px;">

    </form>

    <script>
        function calculateAverages() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                let espSum = 0, matSum = 0, ingSum = 0;
                let espCount = 0, matCount = 0, ingCount = 0;

                // Calcular promedios por bloque y general
                for (let i = 1; i <= 6; i++) {
                    const espInput = row.querySelector(`input[name="esp_${row.cells[0].innerText}_B${i}"]`);
                    const matInput = row.querySelector(`input[name="mat_${row.cells[0].innerText}_B${i}"]`);
                    const ingInput = row.querySelector(`input[name="ing_${row.cells[0].innerText}_B${i}"]`);

                    const espValue = parseFloat(espInput.value) || 0;
                    const matValue = parseFloat(matInput.value) || 0;
                    const ingValue = parseFloat(ingInput.value) || 0;

                    espSum += espValue;
                    matSum += matValue;
                    ingSum += ingValue;

                    espCount += espValue > 0 ? 1 : 0;
                    matCount += matValue > 0 ? 1 : 0;
                    ingCount += ingValue > 0 ? 1 : 0;

                    // Promedio por bloque
                    row.querySelector(`.prom_bloque_${i}_${row.cells[0].innerText}`).innerText = ((espValue + matValue + ingValue) / 3).toFixed(2);
                }

                // Promedio general
                const avgEsp = espCount > 0 ? (espSum / espCount) : 0;
                const avgMat = matCount > 0 ? (matSum / matCount) : 0;
                const avgIng = ingCount > 0 ? (ingSum / ingCount) : 0;

                const overallAverage = ((avgEsp + avgMat + avgIng) / 3).toFixed(2);
                row.querySelector(`.prom_general_${row.cells[0].innerText}`).innerText = overallAverage;
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('input[type="text"]').forEach(input => {
                input.addEventListener('input', calculateAverages);
            });
            // Calcular promedios iniciales
            calculateAverages();
        });
    </script>

</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>
