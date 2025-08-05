<?php
// Conexión a la base de datos
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ponys2');

$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar la conexión
if ($mysqli === false) {
    die("ERROR: No se pudo conectar. " . $mysqli->connect_error);
}

// Obtener el RICE del parámetro de la solicitud
$rice = isset($_GET['rice']) ? $_GET['rice'] : '';

// Verificar si el RICE no está vacío
if ($rice) {
    $query = "SELECT RICE, CURP, nombre, grado, seccion FROM alumnos WHERE RICE = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $rice);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $alumno = $result->fetch_assoc();
        echo json_encode($alumno);
    } else {
        echo json_encode(array('error' => 'No se encontró un alumno con el RICE proporcionado.'));
    }
    $stmt->close();
}

$mysqli->close();
?>
