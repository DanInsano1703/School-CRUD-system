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

$table_name = $_GET['table'];
$reporte_id = $_GET['id'];

$sql = "DELETE FROM $table_name WHERE id = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $reporte_id);
    if ($stmt->execute()) {
        echo "<p>Reporte eliminado correctamente.</p>";
    } else {
        echo "ERROR: No se pudo ejecutar $sql. " . $mysqli->error;
    }
    $stmt->close();
}

$mysqli->close();

// Redirigir de vuelta a la página de gestión de reportes
header("Location: GestionReporte.php");
exit;
?>
