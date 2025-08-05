<?php
include 'db.php';

$id = $_GET['id'];
$year = date("Y");

// [Las consultas PHP permanecen iguales...]
// Consulta principal para obtener los detalles del alumno
$sql = "SELECT * FROM alumnos WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $alumno = $result->fetch_assoc();
} else {
    echo "No se encontró el alumno.";
    exit();
}

// Determinar el nombre de la tabla de reportes para el año actual
$table_name = "reportes_" . $year;

// Consulta para obtener el historial de reportes del alumno para el año actual
$sql_reportes = "SELECT * FROM $table_name WHERE alumno_id = $id";
$result_reportes = $conn->query($sql_reportes);

// Determinar el nombre de la tabla de niveles de lectura para el año actual
$table_name_lectura = "niveles_lectura" . $year;

// Consulta para obtener los niveles de lectura del alumno para el año actual
$sql_lectura = "SELECT evaluacion1, evaluacion2, evaluacion3 FROM $table_name_lectura WHERE alumno_id = $id";
$result_lectura = $conn->query($sql_lectura);

if ($result_lectura->num_rows > 0) {
    $lectura = $result_lectura->fetch_assoc();
} else {
    $lectura = [
        'evaluacion1' => 'N/A',
        'evaluacion2' => 'N/A',
        'evaluacion3' => 'N/A'
    ];
}

// Consulta para obtener el conteo de asistencias del alumno en el año actual
$sql_asistencia = "SELECT 
    SUM(CASE WHEN asistencia = 'Asistencia' THEN 1 ELSE 0 END) AS total_asistencias,
    SUM(CASE WHEN asistencia = 'Inasistencia' THEN 1 ELSE 0 END) AS total_inasistencias,
    SUM(CASE WHEN asistencia = 'Falta Justificada' THEN 1 ELSE 0 END) AS total_faltas_justificadas
FROM asistencia_bloques$year 
WHERE curp = '{$alumno['CURP']}'";

$result_asistencia = $conn->query($sql_asistencia);

if ($result_asistencia->num_rows > 0) {
    $asistencias = $result_asistencia->fetch_assoc();
} else {
    $asistencias = [
        'total_asistencias' => 0,
        'total_inasistencias' => 0,
        'total_faltas_justificadas' => 0
    ];
}

// Consultas para obtener el conteo de faltas y asistencias por bloque
$asistencias_por_bloque = [];
for ($bloque = 1; $bloque <= 5; $bloque++) {
    $sql_bloque = "SELECT 
        SUM(CASE WHEN asistencia = 'Asistencia' THEN 1 ELSE 0 END) AS total_asistencias,
        SUM(CASE WHEN asistencia = 'Inasistencia' THEN 1 ELSE 0 END) AS total_inasistencias,
        SUM(CASE WHEN asistencia = 'Falta Justificada' THEN 1 ELSE 0 END) AS total_faltas_justificadas
    FROM asistencia_bloques$year 
    WHERE curp = '{$alumno['CURP']}' AND bloque = $bloque";

    $result_bloque = $conn->query($sql_bloque);

    if ($result_bloque->num_rows > 0) {
        $asistencias_por_bloque[$bloque] = $result_bloque->fetch_assoc();
    } else {
        $asistencias_por_bloque[$bloque] = [
            'total_asistencias' => 0,
            'total_inasistencias' => 0,
            'total_faltas_justificadas' => 0
        ];
    }
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Alumno</title>
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
        }

        .btn-primary {
            background-color: #2c3e50;
            border: none;
        }

        .btn-primary:hover {
            background-color: #1a252f;
        }

        .btn-outline-primary {
            color: #2c3e50;
            border-color: #2c3e50;
        }

        .btn-outline-primary:hover {
            background-color: #2c3e50;
            color: white;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table th {
            background-color: #2c3e50;
            color: white;
        }

        .info-box {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            color: #2c3e50;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }

        .attendance-present {
            color: #28a745;
            font-weight: bold;
        }

        .attendance-absent {
            color: #dc3545;
            font-weight: bold;
        }

        .attendance-justified {
            color: #ffc107;
            font-weight: bold;
        }

        .pdf-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <!-- Tarjeta principal -->
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="bi bi-person-vcard"></i> Detalles del Alumno</h2>
            </div>
            <div class="card-body">
                <!-- Información básica del alumno -->
                <div class="info-box">
                    <h3 class="section-title"><i class="bi bi-info-circle"></i> Información general</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong><i class="bi bi-person-badge"></i> RICE:</strong> <?php echo $alumno['RICE']; ?>
                            </p>
                            <p><strong><i class="bi bi-file-person"></i> CURP:</strong> <?php echo $alumno['CURP']; ?>
                            </p>
                            <p><strong><i class="bi bi-person"></i> Nombre:</strong> <?php echo $alumno['nombre']; ?>
                            </p>
                            <p><strong><i class="bi bi-gender-ambiguous"></i> Sexo:</strong>
                                <?php echo $alumno['sexo']; ?></p>
                            <p><strong><i class="bi bi-droplet"></i> Tipo de Sangre:</strong>
                                <?php echo $alumno['tipo_sangre']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><i class="bi bi-book"></i> Grado:</strong> <?php echo $alumno['grado']; ?></p>
                            <p><strong><i class="bi bi-bookmark"></i> Sección:</strong>
                                <?php echo $alumno['seccion']; ?></p>
                            <p><strong><i class="bi bi-calendar"></i> Fecha de Nacimiento:</strong>
                                <?php echo $alumno['fecha_nacimiento']; ?></p>
                            <p><strong><i class="bi bi-telephone"></i> Teléfono de Casa:</strong>
                                <?php echo $alumno['telefono_casa']; ?></p>
                            <p><strong><i class="bi bi-phone"></i> Teléfono de Tutor:</strong>
                                <?php echo $alumno['telefono_tutor']; ?></p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <p><strong><i class="bi bi-person-lines-fill"></i> Nombre del Tutor:</strong>
                                <?php echo $alumno['nombre_tutor']; ?></p>
                            <p><strong><i class="bi bi-geo-alt"></i> Ejido:</strong> <?php echo $alumno['ejido']; ?></p>
                            <p><strong><i class="bi bi-people"></i> ¿Tiene hermanos en el colegio?:</strong>
                                <?php echo $alumno['tiene_hermanos']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Asistencia -->
                <div class="info-box">
                    <h3 class="section-title"><i class="bi bi-clipboard-check"></i> Asistencia <?php echo $year; ?></h3>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="attendance-present"><i class="bi bi-check-circle"></i> Asistencias:
                                <?php echo $asistencias['total_asistencias']; ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="attendance-absent"><i class="bi bi-x-circle"></i> Inasistencias:
                                <?php echo $asistencias['total_inasistencias']; ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="attendance-justified"><i class="bi bi-exclamation-circle"></i> Justificadas:
                                <?php echo $asistencias['total_faltas_justificadas']; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Asistencia por bloque -->
                <div class="info-box">
                    <h3 class="section-title"><i class="bi bi-calendar-week"></i> Asistencia por Bloque</h3>
                    <div class="row">
                        <?php for ($bloque = 1; $bloque <= 5; $bloque++): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Bloque <?php echo $bloque; ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="attendance-present"><i class="bi bi-check-circle"></i> Asistencias:
                                            <?php echo $asistencias_por_bloque[$bloque]['total_asistencias']; ?>
                                        </p>
                                        <p class="attendance-absent"><i class="bi bi-x-circle"></i> Inasistencias:
                                            <?php echo $asistencias_por_bloque[$bloque]['total_inasistencias']; ?>
                                        </p>
                                        <p class="attendance-justified"><i class="bi bi-exclamation-circle"></i>
                                            Justificadas:
                                            <?php echo $asistencias_por_bloque[$bloque]['total_faltas_justificadas']; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Niveles de lectura -->
                <div class="info-box">
                    <h3 class="section-title"><i class="bi bi-book-half"></i> Evaluaciones de Lectura</h3>
                    <p class="text-center text-muted"><em>Niveles de lectura actuales de este alumno en este año.</em>
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Evaluación 1</th>
                                    <th>Evaluación 2</th>
                                    <th>Evaluación 3</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo $lectura['evaluacion1']; ?></td>
                                    <td><?php echo $lectura['evaluacion2']; ?></td>
                                    <td><?php echo $lectura['evaluacion3']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Historial de reportes -->
                <div class="info-box">
                    <h3 class="section-title"><i class="bi bi-exclamation-triangle"></i> Historial de Reportes</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Tipo de Reporte</th>
                                    <th>Comentario</th>
                                    <th>Fecha de Ingreso</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_reportes->num_rows > 0): ?>
                                    <?php while ($row = $result_reportes->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['tipo_reporte']; ?></td>
                                            <td><?php echo $row['comentario']; ?></td>
                                            <td><?php echo $row['fecha_ingreso']; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No hay reportes registrados para este año ツ</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="info-box">
                    <h3 class="section-title"><i class="bi bi-gear"></i> Acciones</h3>
                    <p class="text-center text-muted"><em>Seleccione una opción para generar un PDF con la información
                            de este alumno.</em></p>
                    <div class="pdf-buttons">
                        <a href="generate_pdf.php?id=<?php echo $id; ?>&report=constancia"
                            class="btn btn-outline-primary">
                            <i class="bi bi-file-earmark-text"></i> Constancia de Estudios
                        </a>
                        <a href="generate_pdf.php?id=<?php echo $id; ?>&report=conducta"
                            class="btn btn-outline-primary">
                            <i class="bi bi-file-earmark-text"></i> Conducta Académica
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>