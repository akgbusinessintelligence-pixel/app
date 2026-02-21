<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;

echo "Testing Dompdf core functionality...\n";

try {
    $dompdf = new Dompdf();
    $html = '<h1>Dompdf Test</h1><p>If you see this, Dompdf is working correctly.</p><p>Time: ' . date('Y-m-d H:i:s') . '</p>';
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $output = $dompdf->output();
    $filename = 'dompdf_test.pdf';
    file_put_contents($filename, $output);

    echo "SUCCESS: PDF generated and saved as '{$filename}'.\n";
    echo "File Size: " . strlen($output) . " bytes\n";
}
catch (Throwable $e) {
    echo "FAILURE: Dompdf error.\n";
    echo "Error Message: " . $e->getMessage() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
}
