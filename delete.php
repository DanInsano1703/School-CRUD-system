<?php
include 'db.php';

$id = $_GET['id'];

function deleteIfExists($conn, $table, $id) {
    // Verifica si la tabla existe
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 1) {
        // Si la tabla existe, elimina los registros correspondientes
        $sql = "DELETE FROM $table WHERE alumno_id=$id";
        $conn->query($sql);
    }
}

// Eliminar registros relacionados en las tablas si existen
deleteIfExists($conn, 'asistencia', $id);

deleteIfExists($conn, 'reportes_2024', $id);
deleteIfExists($conn, 'reportes_2025', $id);
deleteIfExists($conn, 'reportes_2026', $id);
deleteIfExists($conn, 'reportes_2027', $id);
deleteIfExists($conn, 'reportes_2028', $id);
deleteIfExists($conn, 'reportes_2029', $id);
deleteIfExists($conn, 'reportes_2030', $id);

deleteIfExists($conn, 'asistencia_2024_bloque1', $id);
deleteIfExists($conn, 'asistencia_2024_bloque2', $id);
deleteIfExists($conn, 'asistencia_2024_bloque3', $id);
deleteIfExists($conn, 'asistencia_2024_bloque4', $id);
deleteIfExists($conn, 'asistencia_2024_bloque5', $id);

deleteIfExists($conn, 'asistencia_2025_bloque1', $id);
deleteIfExists($conn, 'asistencia_2025_bloque2', $id);
deleteIfExists($conn, 'asistencia_2025_bloque3', $id);
deleteIfExists($conn, 'asistencia_2025_bloque4', $id);
deleteIfExists($conn, 'asistencia_2025_bloque5', $id);

deleteIfExists($conn, 'asistencia_2026_bloque1', $id);
deleteIfExists($conn, 'asistencia_2026_bloque2', $id);
deleteIfExists($conn, 'asistencia_2026_bloque3', $id);
deleteIfExists($conn, 'asistencia_2026_bloque4', $id);
deleteIfExists($conn, 'asistencia_2026_bloque5', $id);

deleteIfExists($conn, 'niveles_lectura2024', $id);
deleteIfExists($conn, 'niveles_lectura2025', $id);
deleteIfExists($conn, 'niveles_lectura2026', $id);
deleteIfExists($conn, 'niveles_lectura2027', $id);
deleteIfExists($conn, 'niveles_lectura2028', $id);
deleteIfExists($conn, 'niveles_lectura2029', $id);
deleteIfExists($conn, 'niveles_lectura2030', $id);

// Finalmente, eliminar el alumno
$sql_alumno = "DELETE FROM alumnos WHERE id=$id";

if ($conn->query($sql_alumno) === TRUE) {
    echo "<span style='color: green; font-style: italic;'>Alumno eliminado exitosamente.</span>";
} else {
    echo "Error al eliminar el alumno: " . $conn->error;
}

$conn->close();
header("Location: index.php");
exit();
?>
