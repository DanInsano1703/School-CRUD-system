<?php
require('fpdf/fpdf.php');
include('db.php'); // Asegúrate de que este archivo contiene la conexión a la base de datos

// Verificar si se ha enviado la solicitud para generar el PDF
if (isset($_POST['generar_pdf'])) {
    // Obtener el año del formulario
    $año = isset($_POST['año']) && !empty($_POST['año']) ? $_POST['año'] : date('Y');

    // Llamar a la función para generar el PDF
    generarPDF($año);
    exit(); // Detener la ejecución aquí para evitar que se genere salida adicional
}

function generarPDF($año) {
    global $conn; // Usar la conexión global definida en db.php

    // Definir el nombre de la tabla basada en el año seleccionado
    $tabla = "niveles_lectura" . $año;

    // Crear la consulta para obtener los datos, incluyendo JOIN con la tabla de alumnos
    $sql = "
        SELECT n.id, n.alumno_id, a.nombre, a.RICE, a.CURP, a.grado, a.seccion, n.evaluacion1, n.evaluacion2, n.evaluacion3
        FROM $tabla n
        JOIN alumnos a ON n.alumno_id = a.id
        ORDER BY a.grado, a.seccion, a.nombre
    ";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die('Error en la consulta: ' . mysqli_error($conn));
    }

    // Crear el PDF
    $pdf = new FPDF('P', 'mm', [275, 6970]); // P para orientación vertical, tamaño personalizado (210 mm ancho x 2970 mm alto)
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);

    // Variables para conteo de letras generales y por grupo
    $conteoLetrasGeneral = [];
    $conteoLetrasGrupo = [];

    // Recolectar los datos para el conteo general
    while ($row = mysqli_fetch_assoc($result)) {
        // Conteo de letras para el conteo general
        for ($i = 1; $i <= 3; $i++) {
            $letra = $row["evaluacion$i"];
            if (!empty($letra)) {
                if (!isset($conteoLetrasGeneral[$letra])) {
                    $conteoLetrasGeneral[$letra] = 0;
                }
                $conteoLetrasGeneral[$letra]++;
            }
        }
    }

    // Ordenar letras alfabéticamente para el conteo general
    ksort($conteoLetrasGeneral);

    // Imprimir conteo general de letras al inicio
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, "Conteo General de Letras", 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    foreach ($conteoLetrasGeneral as $letra => $cantidad) {
        $pdf->Cell(0, 10, "Letra $letra: $cantidad repeticiones", 0, 1, 'L');
    }
    $pdf->Ln(10); // Espacio antes de los grupos

    // Volver a ejecutar la consulta para imprimir los datos de los grupos
    mysqli_data_seek($result, 0);

    // Definir las variables antes de usarlas
    $currentGrado = '';
    $currentSeccion = '';

    // Datos de la tabla con celdas ajustadas
    $pdf->SetFont('Arial', '', 10);
    while ($row = mysqli_fetch_assoc($result)) {
        // Verificar si el grado o sección ha cambiado
        if ($currentGrado !== $row['grado'] || $currentSeccion !== $row['seccion']) {
            // Si no es la primera iteración, imprimir el conteo de letras del grupo anterior
            if ($currentGrado !== '' || $currentSeccion !== '') {
                imprimirConteoLetras($pdf, $conteoLetrasGrupo);
                $pdf->Ln(10); // Espacio antes de cambiar de grupo
            }

            // Actualizar los valores actuales
            $currentGrado = $row['grado'];
            $currentSeccion = $row['seccion'];

            // Imprimir encabezado de grado y sección
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, "Nivel lectura $año ($currentGrado$currentSeccion)", 0, 1, 'L');
            $pdf->Ln(5); // Espacio

            // Reimprimir encabezados de tabla
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(10, 10, 'ID', 1);
            $pdf->Cell(100, 10, 'Nombre', 1); // Ancho ajustado
            $pdf->Cell(25, 10, 'RICE', 1);
            $pdf->Cell(45, 10, 'CURP', 1); // Ancho ajustado
            $pdf->Cell(25, 10, 'Evaluacion 1', 1);
            $pdf->Cell(25, 10, 'Evaluacion 2', 1);
            $pdf->Cell(25, 10, 'Evaluacion 3', 1);
            $pdf->Ln();

            // Reiniciar el conteo de letras para el nuevo grupo
            $conteoLetrasGrupo = [];
        }

        // Imprimir datos de la tabla
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(10, 10, $row['id'], 1);
        $pdf->Cell(100, 10, $row['nombre'], 1); // Ancho ajustado
        $pdf->Cell(25, 10, $row['RICE'], 1);
        $pdf->Cell(45, 10, $row['CURP'], 1); // Ancho ajustado
        $pdf->Cell(25, 10, $row['evaluacion1'], 1);
        $pdf->Cell(25, 10, $row['evaluacion2'], 1);
        $pdf->Cell(25, 10, $row['evaluacion3'], 1);
        $pdf->Ln();

        // Conteo de letras por grupo (sumar la evaluación a las letras del grupo actual)
        for ($i = 1; $i <= 3; $i++) {
            $letra = $row["evaluacion$i"];
            if (!empty($letra)) {
                if (!isset($conteoLetrasGrupo[$letra])) {
                    $conteoLetrasGrupo[$letra] = 0;
                }
                $conteoLetrasGrupo[$letra]++;
            }
        }
    }

    // Imprimir el conteo de letras del último grupo
    imprimirConteoLetras($pdf, $conteoLetrasGrupo);

    // Salida del PDF
    $pdf->Output('I', 'niveles_lectura_' . $año . '.pdf'); // Se mostrará en el navegador
}

function imprimirConteoLetras($pdf, $conteoLetrasGrupo) {
    $pdf->Ln(5); // Espacio antes del conteo
    $pdf->SetFont('Arial', '', 10);

    foreach ($conteoLetrasGrupo as $letra => $cantidad) {
        $pdf->Cell(0, 10, "Letra $letra: $cantidad repeticiones", 0, 1, 'L');
    }
}
?>
