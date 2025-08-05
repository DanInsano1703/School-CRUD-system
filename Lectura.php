<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Niveles de Lectura</title>
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
        .form-control, .form-select {
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
        .group-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .letter-count {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .letter-count ul {
            column-count: 3;
        }
        .letter-count li {
            margin-bottom: 5px;
        }
        .form-check-label {
            margin-right: 15px;
        }
        .form-check-input {
            margin-right: 5px;
        }
        .success-message {
            color: green;
            font-style: italic;
        }
        .info-text {
            color: gray;
            font-style: italic;
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
                <h2 class="mb-0"><i class="bi bi-book"></i> Niveles de Lectura</h2>
            </div>
            <div class="card-body">
                <!-- Instrucciones -->
                <div class="instruction-box">
                    <p class="mb-0"><i class="bi bi-info-circle"></i> Selecciona el año y los grados/secciones que deseas visualizar. Completa los campos de evaluación y presiona Registrar Niveles para actualizar los datos. Para generar un PDF con la información del año, ingresa el año y haz clic en Generar PDF.</p>
                </div>

                <?php
                // Definiciones y conexión a la base de datos
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

                // Obtener el año de la URL, si no se especifica usar el año actual
                $año = isset($_GET['año']) ? intval($_GET['año']) : date('Y');

                // Función para insertar o actualizar datos en la base de datos
                function guardar_niveles_lectura($conn, $alumno_id, $año, $evaluacion1, $evaluacion2, $evaluacion3) {
                    // Asignar cadenas vacías si los valores son nulos
                    $evaluacion1 = $evaluacion1 ?? '';
                    $evaluacion2 = $evaluacion2 ?? '';
                    $evaluacion3 = $evaluacion3 ?? '';

                    // Definir el nombre de la tabla según el año
                    $tabla = 'niveles_lectura' . $año;

                    // Verificar si ya existe un registro para el alumno y el año
                    $sql_check = "SELECT * FROM $tabla WHERE alumno_id = ? AND año = ?";
                    $stmt_check = $conn->prepare($sql_check);
                    $stmt_check->bind_param("ii", $alumno_id, $año);
                    $stmt_check->execute();
                    $result_check = $stmt_check->get_result();

                    if ($result_check->num_rows > 0) {
                        // Si existe, actualizar el registro
                        $sql = "UPDATE $tabla SET evaluacion1 = ?, evaluacion2 = ?, evaluacion3 = ? WHERE alumno_id = ? AND año = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sssii", $evaluacion1, $evaluacion2, $evaluacion3, $alumno_id, $año);
                    } else {
                        // Si no existe, insertar un nuevo registro
                        $sql = "INSERT INTO $tabla (alumno_id, año, evaluacion1, evaluacion2, evaluacion3) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("iisss", $alumno_id, $año, $evaluacion1, $evaluacion2, $evaluacion3);
                    }

                    $success = $stmt->execute();
                    
                    $stmt->close();
                    $stmt_check->close();
                    
                    return $success;
                }

                // Función para calcular las letras repetidas y sus porcentajes
                function calcular_repeticion_letras($evaluaciones) {
                    $letras = [];
                    $total = 0;

                    foreach ($evaluaciones as $evaluacion) {
                        // Divide la cadena de evaluación en partes de 1 o 2 caracteres
                        $letras_eval = preg_split('//u', $evaluacion, -1, PREG_SPLIT_NO_EMPTY);
                        for ($i = 0; $i < count($letras_eval); $i++) {
                            $letra = $letras_eval[$i];
                            if (isset($letras_eval[$i + 1])) {
                                $letra .= $letras_eval[$i + 1];
                                $i++;
                            }
                            $letra = strtoupper($letra);
                            if (!isset($letras[$letra])) {
                                $letras[$letra] = 0;
                            }
                            $letras[$letra]++;
                            $total++;
                        }
                    }

                    $resultados = [];
                    foreach ($letras as $letra => $count) {
                        $porcentaje = ($count / $total) * 100;
                        $resultados[$letra] = [
                            'count' => $count,
                            'porcentaje' => $porcentaje
                        ];
                    }

                    return $resultados;
                }

                // Inicializar $alumno_ids como un array vacío
                $alumno_ids = array();

                // Verificar si el formulario de niveles de lectura fue enviado
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_niveles'])) {
                    $alumno_ids = $_POST['alumno_id'];
                    $evaluaciones1 = $_POST['evaluacion1'];
                    $evaluaciones2 = $_POST['evaluacion2'];
                    $evaluaciones3 = $_POST['evaluacion3'];

                    $actualizaciones = 0;
                    for ($i = 0; $i < count($alumno_ids); $i++) {
                        $evaluacion1 = !empty($evaluaciones1[$i]) ? $evaluaciones1[$i] : null;
                        $evaluacion2 = !empty($evaluaciones2[$i]) ? $evaluaciones2[$i] : null;
                        $evaluacion3 = !empty($evaluaciones3[$i]) ? $evaluaciones3[$i] : null;

                        if (guardar_niveles_lectura($conn, $alumno_ids[$i], $año, $evaluacion1, $evaluacion2, $evaluacion3)) {
                            $actualizaciones++;
                        }
                    }

                    if ($actualizaciones > 0) {
                        echo '<div class="alert alert-success">Datos actualizados correctamente.</div>';
                    } else {
                        echo '<div class="alert alert-info">No se realizaron actualizaciones.</div>';
                    }
                }

                // Función para calcular las letras repetidas y sus porcentajes de todos los grupos
                function calcular_repeticion_letras_todos($conn, $año) {
                    $evaluaciones_todos = [];

                    // Consultar todas las evaluaciones de todos los alumnos en todos los grados y secciones
                    $sql = "SELECT evaluacion1, evaluacion2, evaluacion3 FROM niveles_lectura$año";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $evaluaciones_todos[] = $row["evaluacion1"];
                            $evaluaciones_todos[] = $row["evaluacion2"];
                            $evaluaciones_todos[] = $row["evaluacion3"];
                        }
                    }

                    // Calcular conteo y porcentaje de letras repetidas
                    $resultados_todos = calcular_repeticion_letras($evaluaciones_todos);

                    return $resultados_todos;
                }

                // Llamar a la función para calcular los resultados de todos los grupos
                $resultados_todos = calcular_repeticion_letras_todos($conn, $año);

                // Función de comparación personalizada para ordenar las letras según tu lógica
                function custom_sort($a, $b) {
                    // Si ambas son iguales, devolver 0
                    if ($a === $b) {
                        return 0;
                    }

                    // Especifica el orden deseado
                    $order = array(
                        'AA', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 
                        'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'ZZ'
                    );

                    // Obteniendo los índices de las letras en el arreglo de orden
                    $indexA = array_search($a, $order);
                    $indexB = array_search($b, $order);

                    // Devolver la comparación de índices
                    return $indexA - $indexB;
                }

                // Ordenar las letras
                uksort($resultados_todos, 'custom_sort');
                ?>

                <!-- Estadísticas generales -->
                <div class="group-container">
                    <h3><i class="bi bi-graph-up"></i> Conteo y porcentajes de todos los grupos (<?php echo $año; ?>)</h3>
                    <div class="letter-count">
                        <ul>
                            <?php foreach ($resultados_todos as $letra => $data): ?>
                                <li>Total de "<?php echo $letra; ?>": <?php echo $data['count']; ?> = <?php echo round($data['porcentaje'], 2); ?>%</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Generar PDF -->
                <div class="group-container">
                    <h3><i class="bi bi-file-earmark-pdf"></i> Generar PDF con los datos de este año</h3>
                    <form method="post" action="generateLectura.php" class="row g-3">
                        <div class="col-md-4">
                            <label for="año" class="form-label">Selecciona el año:</label>
                            <input type="text" class="form-control" name="año" id="año" placeholder="Ej: 2025">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary" name="generar_pdf">
                                <i class="bi bi-file-earmark-pdf"></i> Generar PDF
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Opciones de visualización -->
                <div class="group-container">
                    <h3><i class="bi bi-eye"></i> Opciones de Visualización</h3>
                    <p class="info-text">Utilice este panel para seleccionar los grupos que desea visualizar y desmarque aquellos que no desea ver.</p>
                    
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="select_all" onclick="toggleAllTables()">
                        <label class="form-check-label" for="select_all">Seleccionar/Deseleccionar Todos</label>
                    </div>
                    
                    <form id="menu_form" class="mt-3">
                        <?php
                        $grados_secciones = [
                            '1A', '1B', '2A', '2B', '3A', '3B',
                            '4A', '4B', '5A', '5B', '6A', '6B'
                        ];
                        foreach ($grados_secciones as $grado_seccion) {
                            echo '<div class="form-check form-check-inline">';
                            echo '<input class="form-check-input" type="checkbox" id="checkbox_'.$grado_seccion.'" onclick="toggleTable(\''.$grado_seccion.'\')">';
                            echo '<label class="form-check-label" for="checkbox_'.$grado_seccion.'">'.$grado_seccion.'</label>';
                            echo '</div>';
                        }
                        ?>
                    </form>
                </div>

                <!-- Grupos de alumnos -->
                <h2 class="mt-4"><i class="bi bi-people-fill"></i> Grupos</h2>
                <hr>

                <?php
                foreach ($grados_secciones as $grado_seccion) {
                    $grado = (int) $grado_seccion[0];
                    $seccion = $grado_seccion[1];

                    // Consultar los alumnos por grado y sección
                    $sql = "SELECT * FROM alumnos WHERE grado = $grado AND seccion = '$seccion'";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        echo "<div id='container_$grado_seccion' style='display:none;' class='group-container'>";
                        echo "<h3><i class='bi bi-people'></i> Alumnos de $grado_seccion ($año)</h3>";
                        echo "<form method='post' action=''>";
                        echo "<div class='table-responsive'>";
                        echo "<table class='table table-bordered table-hover' id='table_$grado_seccion'>";
                        echo "<thead class='table-dark'>";
                        echo "<tr>
                                <th>CURP</th>
                                <th>RICE</th>
                                <th>Nombre</th>
                                <th>Grado</th>
                                <th>Sección</th>
                                <th style='width: 80px;'>Ev.1</th>
                                <th style='width: 80px;'>Ev.2</th>
                                <th style='width: 80px;'>Ev.3</th>
                              </tr>";
                        echo "</thead><tbody>";
                        
                        $alumnos = [];
                        $evaluaciones = [];

                        while ($row = $result->fetch_assoc()) {
                            $alumno_id = $row["id"];
                            // Consultar la última evaluación según el año especificado
                            $tabla_evaluaciones = 'niveles_lectura' . $año;
                            $ultimo_nivel_sql = "SELECT evaluacion1, evaluacion2, evaluacion3 FROM $tabla_evaluaciones WHERE alumno_id = $alumno_id ORDER BY año DESC LIMIT 1";
                            $ultimo_nivel_result = $conn->query($ultimo_nivel_sql);
                            $ultimo_nivel = $ultimo_nivel_result->fetch_assoc();
                            $ultima_evaluacion1 = $ultimo_nivel ? $ultimo_nivel['evaluacion1'] : '';
                            $ultima_evaluacion2 = $ultimo_nivel ? $ultimo_nivel['evaluacion2'] : '';
                            $ultima_evaluacion3 = $ultimo_nivel ? $ultimo_nivel['evaluacion3'] : '';
                            
                            $evaluaciones[] = $ultima_evaluacion1;
                            $evaluaciones[] = $ultima_evaluacion2;
                            $evaluaciones[] = $ultima_evaluacion3;
                            
                            echo "<tr>
                                    <td>".$row["CURP"]."</td>
                                    <td>".$row["RICE"]."</td>
                                    <td>".$row["nombre"]."</td>
                                    <td>".$row["grado"]."</td>
                                    <td>".$row["seccion"]."</td>
                                    <td>
                                        <input type='hidden' name='alumno_id[]' value='".$row["id"]."'>
                                        <input type='hidden' name='año' value='$año'>
                                        <input type='text' class='form-control form-control-sm' name='evaluacion1[]' value='$ultima_evaluacion1' pattern='[A-Za-z]{1,2}' oninput='this.value = this.value.toUpperCase();' maxlength='2'>
                                    </td>
                                    <td>
                                        <input type='text' class='form-control form-control-sm' name='evaluacion2[]' value='$ultima_evaluacion2' pattern='[A-Za-z]{1,2}' oninput='this.value = this.value.toUpperCase();' maxlength='2'>
                                    </td>
                                    <td>
                                        <input type='text' class='form-control form-control-sm' name='evaluacion3[]' value='$ultima_evaluacion3' pattern='[A-Za-z]{1,2}' oninput='this.value = this.value.toUpperCase();' maxlength='2'>
                                    </td>
                                  </tr>";
                        }
                        echo "</tbody></table></div>";
                        echo "<input type='hidden' name='registrar_niveles' value='1'>";
                        echo "<button type='submit' class='btn btn-primary'>";
                        echo "<i class='bi bi-save'></i> Registrar Niveles";
                        echo "</button>";
                        echo "</form>";
                        
                        $resultados = calcular_repeticion_letras($evaluaciones);

                        // Definir el orden personalizado de las letras
                        $orden_letras = array('AA','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','ZZ');
                        
                        // Ordenar el arreglo de resultados según el orden personalizado de letras
                        uksort($resultados, function($a, $b) use ($orden_letras) {
                            return array_search($a, $orden_letras) <=> array_search($b, $orden_letras);
                        });
                        
                        echo "<div class='letter-count mt-3'>";
                        echo "<h4><i class='bi bi-bar-chart'></i> Conteo y porcentajes de $grado_seccion ($año)</h4>";
                        echo "<ul>";
                        foreach ($resultados as $letra => $data) {
                            echo "<li>Total de \"$letra\": {$data['count']} = " . round($data['porcentaje'], 2) . "%</li>";
                        }
                        echo "</ul></div>";
                        echo "</div>";
                    } else {
                        echo "<div class='alert alert-warning'>No se encontraron alumnos en $grado_seccion</div>";
                    }
                }
                $conn->close();
                ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleTable(gradoSeccion) {
            var tableContainer = document.getElementById('container_' + gradoSeccion);
            var checkbox = document.getElementById('checkbox_' + gradoSeccion);
            if (checkbox.checked) {
                tableContainer.style.display = 'block';
            } else {
                tableContainer.style.display = 'none';
            }
        }

        function toggleAllTables() {
            var selectAll = document.getElementById('select_all');
            var checkboxes = document.querySelectorAll('#menu_form input[type="checkbox"]');
            
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = selectAll.checked;
                var gradoSeccion = checkbox.id.replace('checkbox_', '');
                toggleTable(gradoSeccion);
            });
        }
    </script>
</body>
</html>