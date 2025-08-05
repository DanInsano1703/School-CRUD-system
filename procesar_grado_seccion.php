<?php
// Conexión a la base de datos
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ponys2');

$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar la conexión
if($mysqli === false){
    die("ERROR: No se pudo conectar. " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoge el input del formulario
    $riceInput = trim($_POST['rice']);
    $grado = intval($_POST['grado']);
    $seccion = $_POST['seccion'];

    // Separa los RICEs por líneas y elimina espacios en blanco adicionales
    $riceArray = array_filter(array_map('trim', explode("\n", $riceInput)));
    $riceNoExistentes = [];

    if (!empty($riceArray)) {
        // Actualiza el grado y la sección para cada RICE
        foreach ($riceArray as $rice) {
            $rice = $mysqli->real_escape_string($rice); // Escapa el RICE para evitar inyección SQL
            
            // Verifica si el RICE existe en la base de datos
            $sqlCheck = "SELECT COUNT(*) as count FROM alumnos WHERE RICE = '$rice'";
            $resultCheck = $mysqli->query($sqlCheck);
            $row = $resultCheck->fetch_assoc();
            
            if ($row['count'] > 0) {
                // Si el RICE existe, realiza la actualización
                $sql = "UPDATE alumnos SET grado = '$grado', seccion = '$seccion' WHERE RICE = '$rice'";
                if ($mysqli->query($sql) === TRUE) {
                    echo "Alumno con RICE $rice actualizado correctamente.<br>";
                } else {
                    echo "Error actualizando RICE $rice: " . $mysqli->error . "<br>";
                }
            } else {
                // Si el RICE no existe, agrégalo a la lista de no encontrados
                $riceNoExistentes[] = $rice;
            }
        }
        
        // Mostrar los RICE que no se encontraron
        if (!empty($riceNoExistentes)) {
            echo "<br><strong>RICEs que no se encontraron en la base de datos:</strong><br>";
            echo implode("<br>", $riceNoExistentes);
        } else {
            echo "<br>Todos los RICEs fueron actualizados correctamente.";
        }
    } else {
        echo "No se ingresaron RICEs válidos.";
    }

    // Botón para regresar a index.php
    echo "<br><br><button onclick=\"window.location.href='Cambio_avanzado.php'\">Regresar</button>";
    
    // Cierra la conexión
    $mysqli->close();
}
?>
