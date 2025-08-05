<?php
include 'db.php';

$year = date("Y");

// Inicialización de variables
$alumnos = [];
$asistencias_totales = [];
$faltas_justificadas_totales = [];
$asistencias_por_bloque = [];
$faltas_justificadas_por_bloque = [];
$faltas_injustificadas_por_bloque = [];

// Variables de filtro
$nombre_filtro = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$grado_filtro = isset($_GET['grado']) ? intval($_GET['grado']) : 0;
$seccion_filtro = isset($_GET['seccion']) ? $_GET['seccion'] : '';

// Construir la consulta de búsqueda
$sql_filtro = "SELECT id, nombre, grado, seccion, curp FROM alumnos WHERE 1=1";

if ($nombre_filtro !== '') {
    $sql_filtro .= " AND nombre LIKE ?";
}
if ($grado_filtro > 0) {
    $sql_filtro .= " AND grado = ?";
}
if ($seccion_filtro !== '') {
    $sql_filtro .= " AND seccion = ?";
}

// Agregar la ordenación por inasistencias
$sql_filtro .= " ORDER BY (SELECT SUM(CASE WHEN asistencia = 'Inasistencia' THEN 1 ELSE 0 END) 
                        FROM asistencia_bloques$year 
                        WHERE curp = alumnos.curp) DESC";

$stmt_filtro = $conn->prepare($sql_filtro);

// Vincular los parámetros de búsqueda
$params = [];
$types = '';

if ($nombre_filtro !== '') {
    $params[] = "%$nombre_filtro%";
    $types .= 's';
}
if ($grado_filtro > 0) {
    $params[] = $grado_filtro;
    $types .= 'i';
}
if ($seccion_filtro !== '') {
    $params[] = $seccion_filtro;
    $types .= 's';
}

if (!empty($params)) {
    $stmt_filtro->bind_param($types, ...$params);
}

$stmt_filtro->execute();
$result_filtro = $stmt_filtro->get_result();

// Obtener los resultados
if ($result_filtro->num_rows > 0) {
    while ($alumno = $result_filtro->fetch_assoc()) {
        $alumnos[] = $alumno;
    }
} else {
    echo "No se encontraron alumnos con los criterios especificados.";
    exit();
}

// Procesar datos para cada alumno encontrado
foreach ($alumnos as $alumno) {
    $id = $alumno['id'];
    
    // Consulta para obtener el total de asistencias, inasistencias y faltas justificadas por alumno
    $sql_asistencia_total = "SELECT 
        SUM(CASE WHEN asistencia = 'Asistencia' THEN 1 ELSE 0 END) AS total_asistencias,
        SUM(CASE WHEN asistencia = 'Inasistencia' THEN 1 ELSE 0 END) AS total_inasistencias,
        SUM(CASE WHEN asistencia = 'Falta Justificada' THEN 1 ELSE 0 END) AS total_faltas_justificadas
    FROM asistencia_bloques$year
    WHERE curp = ?";

    $stmt_asistencia_total = $conn->prepare($sql_asistencia_total);
    $stmt_asistencia_total->bind_param("s", $alumno['curp']);
    $stmt_asistencia_total->execute();
    $result_asistencia_total = $stmt_asistencia_total->get_result();

    if ($result_asistencia_total->num_rows > 0) {
        $asistencias_totales[$id] = $result_asistencia_total->fetch_assoc();
    }

    // Consultas para obtener el total de asistencias, faltas justificadas y faltas injustificadas por bloque
    $asistencias_por_bloque[$id] = [];
    $faltas_justificadas_por_bloque[$id] = [];
    $faltas_injustificadas_por_bloque[$id] = [];
    
    for ($bloque = 1; $bloque <= 5; $bloque++) {
        $sql_asistencia_bloque = "SELECT 
            SUM(CASE WHEN asistencia = 'Asistencia' THEN 1 ELSE 0 END) AS total_asistencias,
            SUM(CASE WHEN asistencia = 'Falta Justificada' THEN 1 ELSE 0 END) AS total_faltas_justificadas,
            SUM(CASE WHEN asistencia = 'Inasistencia' THEN 1 ELSE 0 END) AS total_faltas_injustificadas
        FROM asistencia_bloques$year
        WHERE curp = ? AND bloque = ?";

        $stmt_asistencia_bloque = $conn->prepare($sql_asistencia_bloque);
        $stmt_asistencia_bloque->bind_param("si", $alumno['curp'], $bloque);
        $stmt_asistencia_bloque->execute();
        $result_asistencia_bloque = $stmt_asistencia_bloque->get_result();

        if ($result_asistencia_bloque->num_rows > 0) {
            $data = $result_asistencia_bloque->fetch_assoc();
            $asistencias_por_bloque[$id][$bloque] = $data['total_asistencias'];
            $faltas_justificadas_por_bloque[$id][$bloque] = $data['total_faltas_justificadas'];
            $faltas_injustificadas_por_bloque[$id][$bloque] = $data['total_faltas_injustificadas'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Alumno</title>
   <link rel="stylesheet" href="css/FaltasAlumno.css">
</head>

<body>
<a href="index.php">
        <button>Regresar al inicio</button>
    </a>
    <div class="container">
        
        <h1>Historial de asistencias</h1>
        <hr>
        <p class="instructions">
    Utiliza el formulario para buscar alumnos filtrando por nombre, grado y sección. Una vez realizado el filtro, los resultados se mostrarán en una tabla. La tabla incluye información sobre asistencias, inasistencias, y faltas justificadas totales, así como datos desglosados por bloques. Los datos se ordenan por la cantidad total de inasistencias.
</p>
<hr>
<br>


        <!-- Formulario de búsqueda -->
        <form method="GET" action="">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre_filtro); ?>">
            
            <label for="grado">Grado:</label>
            <input type="number" id="grado" name="grado" value="<?php echo htmlspecialchars($grado_filtro); ?>">
            
            <label for="seccion">Sección:</label>
            <input type="text" id="seccion" name="seccion" value="<?php echo htmlspecialchars($seccion_filtro); ?>">
            
            <button type="submit">Buscar</button>
        </form>

       

        <!-- Resultados de búsqueda -->
        <h2>Resultados de Búsqueda</h2>
        <?php if (!empty($alumnos)): ?>
            <table border="1">
    <thead>
        <tr>
        <th style="color: black;">Nombre</th>
        <th style="color: black;">Grado</th>
        <th style="color: black;">Sección</th>

            <th class="asistencias">Total Asistencias</th>
            <th class="inasistencias">Total Inasistencias</th>
            <th class="justificadas">Total Justificadas</th>
            <th class="asistencias">Asistencias Bloque 1</th>
            <th class="asistencias">Asistencias Bloque 2</th>
            <th class="asistencias">Asistencias Bloque 3</th>
            <th class="asistencias">Asistencias Bloque 4</th>
            <th class="asistencias">Asistencias Bloque 5</th>
            <th class="justificadas">Justificadas Bloque 1</th>
            <th class="justificadas">Justificadas Bloque 2</th>
            <th class="justificadas">Justificadas Bloque 3</th>
            <th class="justificadas">Justificadas Bloque 4</th>
            <th class="justificadas">Justificadas Bloque 5</th>
            <th class="inasistencias">Inasistencias Bloque 1</th>
            <th class="inasistencias">Inasistencias Bloque 2</th>
            <th class="inasistencias">Inasistencias Bloque 3</th>
            <th class="inasistencias">Inasistencias Bloque 4</th>
            <th class="inasistencias">Inasistencias Bloque 5</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($alumnos as $alumno): ?>
            <tr>
                <td><?php echo htmlspecialchars($alumno['nombre']); ?></td>
                <td><?php echo htmlspecialchars($alumno['grado']); ?></td>
                <td><?php echo htmlspecialchars($alumno['seccion']); ?></td>
                <td class="asistencias"><?php echo isset($asistencias_totales[$alumno['id']]['total_asistencias']) ? htmlspecialchars($asistencias_totales[$alumno['id']]['total_asistencias']) : '0'; ?></td>
                <td class="inasistencias"><?php echo isset($asistencias_totales[$alumno['id']]['total_inasistencias']) ? htmlspecialchars($asistencias_totales[$alumno['id']]['total_inasistencias']) : '0'; ?></td>
                <td class="justificadas"><?php echo isset($asistencias_totales[$alumno['id']]['total_faltas_justificadas']) ? htmlspecialchars($asistencias_totales[$alumno['id']]['total_faltas_justificadas']) : '0'; ?></td>
                <?php for ($bloque = 1; $bloque <= 5; $bloque++): ?>
                    <td class="asistencias"><?php echo isset($asistencias_por_bloque[$alumno['id']][$bloque]) ? htmlspecialchars($asistencias_por_bloque[$alumno['id']][$bloque]) : '0'; ?></td>
                <?php endfor; ?>
                <?php for ($bloque = 1; $bloque <= 5; $bloque++): ?>
                    <td class="justificadas"><?php echo isset($faltas_justificadas_por_bloque[$alumno['id']][$bloque]) ? htmlspecialchars($faltas_justificadas_por_bloque[$alumno['id']][$bloque]) : '0'; ?></td>
                <?php endfor; ?>
                <?php for ($bloque = 1; $bloque <= 5; $bloque++): ?>
                    <td class="inasistencias"><?php echo isset($faltas_injustificadas_por_bloque[$alumno['id']][$bloque]) ? htmlspecialchars($faltas_injustificadas_por_bloque[$alumno['id']][$bloque]) : '0'; ?></td>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

        <?php else: ?>
            <p>No se encontraron resultados.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
