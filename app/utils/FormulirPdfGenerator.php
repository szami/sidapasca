<?php

namespace App\Utils;

use Dompdf\Dompdf;
use Dompdf\Options;

class FormulirPdfGenerator
{
    public static function generate($participant)
    {
        // Use existing registration_form view
        ob_start();
        $p = $participant;
        include dirname(__DIR__) . '/views/pdf/registration_form.php';
        $html = ob_get_clean();

        // Remove print button and no-print elements for PDF
        $html = preg_replace('/<div class="no-print">.*?<\/div>/s', '', $html);
        $html = preg_replace('/<button.*?<\/button>/s', '', $html);
        $html = preg_replace('/<script>.*?<\/script>/s', '', $html);

        // Generate PDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Save to temp
        $filename = 'formulir_' . ($participant['nomor_peserta'] ?? $participant['id']) . '_' . time() . '.pdf';
        $tempPath = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($tempPath, $dompdf->output());

        return $tempPath;
    }
}
