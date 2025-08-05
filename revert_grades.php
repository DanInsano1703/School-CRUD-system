<?php
// revert_grades.php

// Verificar la contraseña
$password = $_POST['password']; // Asegúrate de que se envíe la contraseña mediante POST
if ($password !== "1703") {
    die("Contraseña incorrecta.");
}

// Conexión a la base de datos (asegúrate de tener esto configurado correctamente)
include 'db.php';

// Lógica para revertir los datos de los grados de los alumnos
// Restar -1 a todos los grados
$sql_revert = "UPDATE alumnos SET grado = grado - 1";
if ($conn->query($sql_revert) === TRUE) {
    echo "Todos los grados han sido revertidos.<br>";
} else {
    echo "Error al revertir los grados: " . $conn->error . "<br>";
}

// Cerrar la conexión a la base de datos
$conn->close();

// Redirigir de vuelta a la página principal
header('Location: index.php');
exit();
?>
