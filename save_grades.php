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
                echo "Calificaciones guardadas para RICE $rice, bloque $bloque.<br>";
            } else {
                echo "Error: " . $conn->error . "<br>";
            }
        }
    }
}
?>
