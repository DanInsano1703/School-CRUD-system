<?php
include 'db.php';

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Agregar esta línea para verificar los datos recibidos
    var_dump($_POST);
    
    $RICE = $_POST['RICE'];
    $CURP = $_POST['CURP'];
    $nombre = $_POST['nombre'];
    $sexo = $_POST['sexo'];
    $tipo_sangre = $_POST['tipo_sangre'];
    $grado = $_POST['grado'];
    $seccion = $_POST['seccion'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $telefono_casa = $_POST['telefono_casa'];
    $telefono_tutor = $_POST['telefono_tutor'];
    $nombre_tutor = $_POST['nombre_tutor'];
    $ejido = $_POST['ejido'];
    $tiene_hermanos = $_POST['tiene_hermanos'];

    // Resto del código para la inserción en la base de datos...


    $sql = "INSERT INTO alumnos (RICE, CURP, nombre, sexo, tipo_sangre, grado, seccion, fecha_nacimiento, telefono_casa, telefono_tutor, nombre_tutor, ejido, tiene_hermanos)
            VALUES ('$RICE', '$CURP', '$nombre', '$sexo', '$tipo_sangre', '$grado', '$seccion', '$fecha_nacimiento', '$telefono_casa', '$telefono_tutor', '$nombre_tutor', '$ejido', '$tiene_hermanos')";

    if ($conn->query($sql) === TRUE) {
        echo "Nuevo alumno creado exitosamente";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
    header("Location: index.php");
    exit();
}
?>
