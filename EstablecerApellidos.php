<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ponys2";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Procesar los apellidos enviados por el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['nombres_apellidos'] as $id => $nombre_apellido) {
        // Limpiar y guardar los apellidos editados
        $nombre_apellido = $conn->real_escape_string($nombre_apellido);

        // Actualizar los apellidos en la base de datos
        $sql = "UPDATE alumnos SET apellidos = '$nombre_apellido' WHERE id = $id";
        $conn->query($sql);
    }
    echo "<p>Apellidos guardados correctamente.</p>";
}

// Consulta para obtener los nombres completos
$sql = "SELECT id, nombre FROM alumnos";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Separar Nombres y Apellidos</title>
    <style>
        input[type="text"] {
            width: 400px; /* Puedes ajustar este valor según tus necesidades */
        }
    </style>
</head>
<body>
    <h2>Separar Nombres y Apellidos</h2>
    <form method="POST">
        <table border="1">
            <tr>
                <th>Nombre Completo</th>
                <th>Editar Nombre/Apellidos</th>
            </tr>

            <?php
            if ($result->num_rows > 0) {
                // Mostrar datos de cada alumno
                while ($row = $result->fetch_assoc()) {
                    $nombreCompleto = $row['nombre'];
                    echo "<tr>";
                    echo "<td>" . $nombreCompleto . "</td>";
                    echo "<td><input type='text' name='nombres_apellidos[" . $row['id'] . "]' value='" . $nombreCompleto . "' style='width: 400px;'></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2'>No hay alumnos registrados</td></tr>";
            }
            ?>
        </table>
        <br>
        <button type="submit">Guardar Apellidos</button>
    </form>
</body>
</html>

<?php
$conn->close();
?>
