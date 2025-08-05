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
$year = date("Y");

// Definir el nombre de la tabla basado en el grado y sección
$table_name = "5A_promedios_" . $year;

// Crear la tabla si no existe
$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
    RICE VARCHAR(255) NOT NULL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    grado INT NOT NULL,
    seccion VARCHAR(255) NOT NULL,
    espanol_b1 FLOAT,
    espanol_b2 FLOAT,
    espanol_b3 FLOAT,
    espanol_b4 FLOAT,
    espanol_b5 FLOAT,
    espanol_b6 FLOAT,
    mate_b1 FLOAT,
    mate_b2 FLOAT,
    mate_b3 FLOAT,
    mate_b4 FLOAT,
    mate_b5 FLOAT,
    mate_b6 FLOAT,
    ingles_b1 FLOAT,
    ingles_b2 FLOAT,
    ingles_b3 FLOAT,
    ingles_b4 FLOAT,
    ingles_b5 FLOAT,
    ingles_b6 FLOAT,
    promedio_bloque1 FLOAT,
    promedio_bloque2 FLOAT,
    promedio_bloque3 FLOAT,
    promedio_bloque4 FLOAT,
    promedio_bloque5 FLOAT,
    promedio_bloque6 FLOAT,
    promedio_general FLOAT
)";

if ($conn->query($sql) !== TRUE) {
    echo "Error al crear la tabla: " . $conn->error;
}

// Procesar el formulario si se envía
$promedios = []; // Array para almacenar los promedios de cada alumno
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['rice'] as $index => $rice) {
        $espanol_b1 = floatval($_POST['espanol_b1'][$index]);
        $espanol_b2 = floatval($_POST['espanol_b2'][$index]);
        $espanol_b3 = floatval($_POST['espanol_b3'][$index]);
        $espanol_b4 = floatval($_POST['espanol_b4'][$index]);
        $espanol_b5 = floatval($_POST['espanol_b5'][$index]);
        $espanol_b6 = floatval($_POST['espanol_b6'][$index]);
        $mate_b1 = floatval($_POST['mate_b1'][$index]);
        $mate_b2 = floatval($_POST['mate_b2'][$index]);
        $mate_b3 = floatval($_POST['mate_b3'][$index]);
        $mate_b4 = floatval($_POST['mate_b4'][$index]);
        $mate_b5 = floatval($_POST['mate_b5'][$index]);
        $mate_b6 = floatval($_POST['mate_b6'][$index]);
        $ingles_b1 = floatval($_POST['ingles_b1'][$index]);
        $ingles_b2 = floatval($_POST['ingles_b2'][$index]);
        $ingles_b3 = floatval($_POST['ingles_b3'][$index]);
        $ingles_b4 = floatval($_POST['ingles_b4'][$index]);
        $ingles_b5 = floatval($_POST['ingles_b5'][$index]);
        $ingles_b6 = floatval($_POST['ingles_b6'][$index]);

        // Calcular promedios
        $promedio_bloque1 = ($espanol_b1 + $mate_b1 + $ingles_b1) / 3;
        $promedio_bloque2 = ($espanol_b2 + $mate_b2 + $ingles_b2) / 3;
        $promedio_bloque3 = ($espanol_b3 + $mate_b3 + $ingles_b3) / 3;
        $promedio_bloque4 = ($espanol_b4 + $mate_b4 + $ingles_b4) / 3;
        $promedio_bloque5 = ($espanol_b5 + $mate_b5 + $ingles_b5) / 3;
        $promedio_bloque6 = ($espanol_b6 + $mate_b6 + $ingles_b6) / 3;
        $promedio_general = ($promedio_bloque1 + $promedio_bloque2 + $promedio_bloque3 + $promedio_bloque4 + $promedio_bloque5 + $promedio_bloque6) / 6;

        // Guardar los promedios en el array
        $promedios[$index] = [
            'promedio_bloque1' => $promedio_bloque1,
            'promedio_bloque2' => $promedio_bloque2,
            'promedio_bloque3' => $promedio_bloque3,
            'promedio_bloque4' => $promedio_bloque4,
            'promedio_bloque5' => $promedio_bloque5,
            'promedio_bloque6' => $promedio_bloque6,
            'promedio_general' => $promedio_general
        ];

        // Insertar o actualizar los datos en la tabla
        $sql = "INSERT INTO `$table_name` (RICE, nombre, grado, seccion, espanol_b1, espanol_b2, espanol_b3, espanol_b4, espanol_b5, espanol_b6, mate_b1, mate_b2, mate_b3, mate_b4, mate_b5, mate_b6, ingles_b1, ingles_b2, ingles_b3, ingles_b4, ingles_b5, ingles_b6, promedio_bloque1, promedio_bloque2, promedio_bloque3, promedio_bloque4, promedio_bloque5, promedio_bloque6, promedio_general)
        VALUES ('$rice', 'nombre', 5, 'A', $espanol_b1, $espanol_b2, $espanol_b3, $espanol_b4, $espanol_b5, $espanol_b6, $mate_b1, $mate_b2, $mate_b3, $mate_b4, $mate_b5, $mate_b6, $ingles_b1, $ingles_b2, $ingles_b3, $ingles_b4, $ingles_b5, $ingles_b6, $promedio_bloque1, $promedio_bloque2, $promedio_bloque3, $promedio_bloque4, $promedio_bloque5, $promedio_bloque6, $promedio_general)
        ON DUPLICATE KEY UPDATE
        nombre='nombre', 
        grado=5, 
        seccion='A', 
        espanol_b1=$espanol_b1, 
        espanol_b2=$espanol_b2, 
        espanol_b3=$espanol_b3, 
        espanol_b4=$espanol_b4, 
        espanol_b5=$espanol_b5, 
        espanol_b6=$espanol_b6, 
        mate_b1=$mate_b1, 
        mate_b2=$mate_b2, 
        mate_b3=$mate_b3, 
        mate_b4=$mate_b4, 
        mate_b5=$mate_b5, 
        mate_b6=$mate_b6, 
        ingles_b1=$ingles_b1, 
        ingles_b2=$ingles_b2, 
        ingles_b3=$ingles_b3, 
        ingles_b4=$ingles_b4, 
        ingles_b5=$ingles_b5, 
        ingles_b6=$ingles_b6, 
        promedio_bloque1=$promedio_bloque1, 
        promedio_bloque2=$promedio_bloque2, 
        promedio_bloque3=$promedio_bloque3, 
        promedio_bloque4=$promedio_bloque4, 
        promedio_bloque5=$promedio_bloque5, 
        promedio_bloque6=$promedio_bloque6, 
        promedio_general=$promedio_general";

        if ($conn->query($sql) !== TRUE) {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Consulta para seleccionar solo los alumnos del grado 1A y sus calificaciones
$sql = "SELECT a.RICE, a.nombre, a.grado, a.seccion, p.espanol_b1, p.espanol_b2, p.espanol_b3, p.espanol_b4, p.espanol_b5, p.espanol_b6, p.mate_b1, p.mate_b2, p.mate_b3, p.mate_b4, p.mate_b5, p.mate_b6, p.ingles_b1, p.ingles_b2, p.ingles_b3, p.ingles_b4, p.ingles_b5, p.ingles_b6, p.promedio_bloque1, p.promedio_bloque2, p.promedio_bloque3, p.promedio_bloque4, p.promedio_bloque5, p.promedio_bloque6, p.promedio_general 
FROM alumnos a
LEFT JOIN `$table_name` p ON a.RICE = p.RICE
WHERE a.grado = 5 AND a.seccion = 'A'";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Captura de Calificaciones</title>
 
<style>
  
  body {
    font-family: Calibri, Arial, sans-serif;
    margin: 20px;
    background-color: #f5f5f5; /* Fondo gris claro */
    color: #333; /* Texto en gris oscuro */
    margin-left: 100px;
    margin-right: 100px;
}

/* Estilo para los campos de entrada */
input[type="text"] {
    width: 40px; /* Ancho ajustado */
    padding: 5px;
    text-align: center; /* Centrar el texto */
    font-size: 14px; /* Tamaño de fuente ajustado */
    border-radius: 5px; /* Bordes redondeados */
    border: 1px solid #ccc; /* Color del borde */
}

/* Estilo general para la tabla */
table {
    border-collapse: collapse; /* Quitar los espacios entre celdas */
    width: 100%;
}

/* Estilo para las celdas y bordes */
th, td {
    padding: 8px; /* Espaciado en las celdas */
    border: 1px solid #ddd; /* Bordes de las celdas */
}

/* Estilo para el botón */
button {
    background-color: #333;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
}

/* Colores para diferenciar las materias */
td.espanol {
    background-color: #e6f7ff; /* Azul claro */
}

td.mate {
    background-color: #e6ffe6; /* Verde claro */
}

td.ingles {
    background-color: #fff5e6; /* Naranja claro */
}

/* Colores para las cabeceras de cada materia */
th.espanol {
    background-color: #b3e0ff; /* Azul un poco más fuerte */
}

th.mate {
    background-color: #b3ffb3; /* Verde un poco más fuerte */
}

th.ingles {
    background-color: #ffd9b3; /* Naranja un poco más fuerte */
}

/* Estilo para la columna de nombre */
.nombre-columna {
    width: 250px; /* Ajusta el ancho según sea necesario */
    white-space: nowrap; /* Evita que el texto se ajuste a la siguiente línea */
    overflow: hidden; /* Oculta el desbordamiento */
    text-overflow: ellipsis; /* Muestra puntos suspensivos si el texto es demasiado largo */
}


</style>
</head>
<body>
<a href="index.php">
    <button>Regresar al inicio</button>
</a>
<br>
<br>

    <h2>Captura de Calificaciones - 5A</h2>
    <hr>

    <p><i>Este sistema permite capturar y gestionar las calificaciones de los alumnos. Facilita el ingreso de calificaciones en Español, Matemáticas e Inglés, y calcula los promedios por bloque y un promedio general. Para usarlo, ingrese las calificaciones en los campos correspondientes y haga clic en 'Guardar Calificaciones' para actualizar los registros.</i></p>

    <hr>
    <br>

    <form method="post">
    <table>
    <thead>
        <tr>
            <th>RICE</th>
            <th class="nombre-columna">Nombre</th>
            <th class="espanol">Español B1</th>
            <th class="espanol">Español B2</th>
            <th class="espanol">Español B3</th>
            <th class="espanol">Español B4</th>
            <th class="espanol">Español B5</th>
            <th class="espanol">Español B6</th>
            <th class="mate">Mate B1</th>
            <th class="mate">Mate B2</th>
            <th class="mate">Mate B3</th>
            <th class="mate">Mate B4</th>
            <th class="mate">Mate B5</th>
            <th class="mate">Mate B6</th>
            <th class="ingles">Inglés B1</th>
            <th class="ingles">Inglés B2</th>
            <th class="ingles">Inglés B3</th>
            <th class="ingles">Inglés B4</th>
            <th class="ingles">Inglés B5</th>
            <th class="ingles">Inglés B6</th>
            <th>Promedio B1</th>
            <th>Promedio B2</th>
            <th>Promedio B3</th>
            <th>Promedio B4</th>
            <th>Promedio B5</th>
            <th>Promedio B6</th>
            <th>Promedio General</th>
               
            <th>Promedio Español</th>
            <th>Promedio Mate</th>
            <th>Promedio Ingles</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td><input type='hidden' name='rice[]' value='" . $row['RICE'] . "'>" . $row['RICE'] . "</td>";
                echo "<td class='nombre-columna'>" . $row['nombre'] . "</td>";
                echo "<td class='espanol'><input type='text' name='espanol_b1[]' value='" . $row['espanol_b1'] . "'></td>";
                echo "<td class='espanol'><input type='text' name='espanol_b2[]' value='" . $row['espanol_b2'] . "'></td>";
                echo "<td class='espanol'><input type='text' name='espanol_b3[]' value='" . $row['espanol_b3'] . "'></td>";
                echo "<td class='espanol'><input type='text' name='espanol_b4[]' value='" . $row['espanol_b4'] . "'></td>";
                echo "<td class='espanol'><input type='text' name='espanol_b5[]' value='" . $row['espanol_b5'] . "'></td>";
                echo "<td class='espanol'><input type='text' name='espanol_b6[]' value='" . $row['espanol_b6'] . "'></td>";
                echo "<td class='mate'><input type='text' name='mate_b1[]' value='" . $row['mate_b1'] . "'></td>";
                echo "<td class='mate'><input type='text' name='mate_b2[]' value='" . $row['mate_b2'] . "'></td>";
                echo "<td class='mate'><input type='text' name='mate_b3[]' value='" . $row['mate_b3'] . "'></td>";
                echo "<td class='mate'><input type='text' name='mate_b4[]' value='" . $row['mate_b4'] . "'></td>";
                echo "<td class='mate'><input type='text' name='mate_b5[]' value='" . $row['mate_b5'] . "'></td>";
                echo "<td class='mate'><input type='text' name='mate_b6[]' value='" . $row['mate_b6'] . "'></td>";
                echo "<td class='ingles'><input type='text' name='ingles_b1[]' value='" . $row['ingles_b1'] . "'></td>";
                echo "<td class='ingles'><input type='text' name='ingles_b2[]' value='" . $row['ingles_b2'] . "'></td>";
                echo "<td class='ingles'><input type='text' name='ingles_b3[]' value='" . $row['ingles_b3'] . "'></td>";
                echo "<td class='ingles'><input type='text' name='ingles_b4[]' value='" . $row['ingles_b4'] . "'></td>";
                echo "<td class='ingles'><input type='text' name='ingles_b5[]' value='" . $row['ingles_b5'] . "'></td>";
                echo "<td class='ingles'><input type='text' name='ingles_b6[]' value='" . $row['ingles_b6'] . "'></td>";


                   // Mostrar promedios calculados
                   echo "<td>" . round($row['promedio_bloque1'], 2) . "</td>";
                   echo "<td>" . round($row['promedio_bloque2'], 2) . "</td>";
                   echo "<td>" . round($row['promedio_bloque3'], 2) . "</td>";
                   echo "<td>" . round($row['promedio_bloque4'], 2) . "</td>";
                   echo "<td>" . round($row['promedio_bloque5'], 2) . "</td>";
                   echo "<td>" . round($row['promedio_bloque6'], 2) . "</td>";
                   echo "<td>" . round($row['promedio_general'], 2) . "</td>";
   
                   // Calcular los promedios
                   $espanol_avg = ($row['espanol_b1'] + $row['espanol_b2'] + $row['espanol_b3'] + $row['espanol_b4'] + $row['espanol_b5'] + $row['espanol_b6']) / 6;
                   $mate_avg = ($row['mate_b1'] + $row['mate_b2'] + $row['mate_b3'] + $row['mate_b4'] + $row['mate_b5'] + $row['mate_b6']) / 6;
                   $ingles_avg = ($row['ingles_b1'] + $row['ingles_b2'] + $row['ingles_b3'] + $row['ingles_b4'] + $row['ingles_b5'] + $row['ingles_b6']) / 6;
   
                   echo "<td>" . round($espanol_avg, 2) . "</td>";
                   echo "<td>" . round($mate_avg, 2) . "</td>";
                   echo "<td>" . round($ingles_avg, 2) . "</td>";
                   echo "</tr>";
            }
        }
        ?>
    </tbody>
</table>

        <br>
        <button type="submit">Guardar Calificaciones</button>
    </form>
</body>
</html>
