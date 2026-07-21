<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/helpers.php';
require_once __DIR__ . '/helpers/pdf_helper.php';
require_once __DIR__ . '/invoice_template.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$invoiceId = filter_input(INPUT_GET, 'invoice_id', FILTER_VALIDATE_INT);
$previewMode = filter_input(INPUT_GET, 'preview', FILTER_VALIDATE_BOOLEAN);
$downloadMode = filter_input(INPUT_GET, 'download', FILTER_VALIDATE_BOOLEAN);
$printMode = filter_input(INPUT_GET, 'print', FILTER_VALIDATE_BOOLEAN);

if (!$invoiceId) {
    http_response_code(404);
    echo '<h1>Invoice not found.</h1><p>Invalid invoice identifier.</p>';
    exit;
}

try {
    $pdo = Database::getInstance();
    $invoiceData = fetchInvoiceData($pdo, $invoiceId);

    if (!$invoiceData) {
        http_response_code(404);
        echo '<h1>Invoice not found.</h1><p>The requested invoice does not exist.</p>';
        exit;
    }

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $html = renderInvoiceTemplate($invoiceData, true);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $filename = createInvoiceFilename($invoiceData['invoice_no']);
    $attachment = $downloadMode || (!$previewMode && !$printMode);
    $dompdf->stream($filename, ['Attachment' => $attachment ? 1 : 0]);
    exit;
} catch (Throwable $e) {
    logError('PDF generation failed: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
    http_response_code(500);
    echo '<h1>PDF generation failed.</h1><p>Please try again later.</p>';
    exit;
}
