<?php

require_once '../dompdf/autoload.inc.php';

use Dompdf\Dompdf;

class AlumnoController
{
    public function generarConstanciaEstudios($alumno)
    {
        $html = file_get_contents('../views/constancia_estudios.php');
        // Reemplaza las variables en el HTML con los datos del alumno
        $html = str_replace('{{ nombre_alumno }}', $alumno->nombre, $html);
        // Genera el PDF
        $this->generarPDF($html, 'constancia_estudios.pdf');
    }

    // Métodos para generar los otros tipos de reportes (salida de excursión, beca, excelencia académica)

    protected function generarPDF($html, $filename)
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($filename);
    }
}
