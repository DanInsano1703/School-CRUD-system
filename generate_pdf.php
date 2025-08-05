<?php
require(__DIR__ . '/fpdf/fpdf.php');
include 'db.php';

$id = $_GET['id'];
$sql = "SELECT * FROM alumnos WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $alumno = $result->fetch_assoc();
} else {
    echo "No se encontró el alumno.";
    exit();
}

class PDF extends FPDF
{
    function Header()
    {
        // Logo centrado y más grande
        $this->Image('media/Logito.png', 50, 10, 110); // Ajusta el ancho según sea necesario
        $this->Ln(40); // Espacio después del logo
    }

    function Footer()
    {
        $this->SetY(-70); // Ajusta la posición vertical del pie de página
        $this->SetFont('Arial', '', 11);

        // Mensaje final centrado
        $this->Cell(0, 10, 'Atentamente', 0, 1, 'C');
        $this->Cell(0, 10, '"Educamos para trascender"', 0, 1, 'C');
        $this->Ln(5); // Espacio para la firma

        // Línea para la firma centrada
        $this->Cell(0, 10, '_______________________________', 0, 1, 'C');

        // Nombre de la Directora centrado
        $this->Ln(5); // Espacio entre la línea de firma y el nombre
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 10, utf8_decode('Mtra. María Guadalupe Ibarra Hernández'), 0, 1, 'C');
        $this->Cell(0, 10, utf8_decode('Directora Académica.'), 0, 0, 'C');
    }

    function MultiCellUTF8($w, $h, $txt, $border = 0, $align = 'J', $fill = false)
    {
        $this->SetFont('Arial', '', 11); // Usando Arial a tamaño 11
        $txt = utf8_decode($txt); // Convertir texto a Windows-1252
        parent::MultiCell($w, $h, $txt, $border, $align, $fill);
    }

    function ConstanciaDeEstudios($alumno)
    {
        $this->SetFont('Arial', '', 11);
        $this->MultiCellUTF8(0, 10, "Por medio del presente, como Directora de Primaria del Instituto Eduardo Tricio Gómez, certifico que la constancia de estudios presentada por:\n\n");

        $this->SetFont('Arial', 'B', 11);
        $this->MultiCellUTF8(0, 10, 'Nombre: ' . $alumno['nombre'] . "\n");
        $this->MultiCellUTF8(0, 10, 'RICE: ' . $alumno['RICE'] . "\n");
        $this->MultiCellUTF8(0, 10, 'Grado: ' . $alumno['grado'] . "\n");
        $this->MultiCellUTF8(0, 10, 'Sección: ' . $alumno['seccion'] . "\n");
        $this->Ln(5);

        $this->SetFont('Arial', '', 11);
        $this->MultiCellUTF8(0, 10, "La presente constancia confirma que " . $alumno['nombre'] . " está actualmente inscrito(a) en nuestra institución y está cursando el " . $alumno['grado'] . "°, cumpliendo con los requisitos académicos establecidos.\n\n");

        $this->MultiCellUTF8(0, 10, "Para cualquier consulta adicional o verificación, no dude en ponerse en contacto con nuestra institución.\n\n");

        $fecha_emision = date("d/m/Y");
        $this->MultiCellUTF8(0, 10, "Fecha de emisión: " . $fecha_emision . "\n");
    }

    function ConductaAcademica($alumno)
    {
        $this->SetFont('Arial', '', 11);
        $this->MultiCellUTF8(0, 10, "Por medio del presente, como Directora de Primaria del Instituto Eduardo Tricio Gómez, se informa que:\n\n");

        $this->SetFont('Arial', 'B', 11);
        $this->MultiCellUTF8(0, 10, 'Nombre: ' . $alumno['nombre'] . "\n");
        $this->MultiCellUTF8(0, 10, 'Grado: ' . $alumno['grado'] . "\n");
        $this->MultiCellUTF8(0, 10, 'Sección: ' . $alumno['seccion'] . "\n");
        $this->Ln(5);

        $this->SetFont('Arial', '', 11);
        $this->MultiCellUTF8(0, 10, "Se ha evaluado la conducta académica de " . $alumno['nombre'] . " y se confirma su buena conducta durante el período.\n\n");

        $this->MultiCellUTF8(0, 10, "Para cualquier consulta adicional o verificación, no dude en ponerse en contacto con nuestra institución.\n\n");

        $fecha_emision = date("d/m/Y");
        $this->MultiCellUTF8(0, 10, "Fecha de emisión: " . $fecha_emision . "\n");
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

if ($_GET['report'] == 'constancia') {
    $pdf->ConstanciaDeEstudios($alumno);
} elseif ($_GET['report'] == 'conducta') {
    $pdf->ConductaAcademica($alumno);
}

$pdf->Output();
$conn->close();
?>
