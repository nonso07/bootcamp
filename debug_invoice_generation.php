<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/helpers.php';
require_once __DIR__ . '/helpers/pdf_helper.php';
require_once __DIR__ . '/invoice_template.php';

$pdo = Database::getInstance();
$stmt = $pdo->query('SELECT id FROM invoices ORDER BY id DESC LIMIT 1');
$invoiceId = $stmt->fetchColumn();
if (!$invoiceId) {
    echo "NO_INVOICE\n";
    exit(1);
}

$invoiceData = fetchInvoiceData($pdo, $invoiceId);
if (!$invoiceData) {
    echo "NO_DATA\n";
    exit(1);
}

echo "Invoice ID: $invoiceId\n";
echo "Invoice No: {$invoiceData['invoice_no']}\n";
echo "Student Name: {$invoiceData['student_name']}\n";
echo "ITEMS: " . count($invoiceData['items']) . "\n";

$html = renderInvoiceTemplate($invoiceData, true);
if (!$html) {
    echo "HTML_RENDER_FAIL\n";
    exit(1);
}

echo "HTML rendered successfully. Length: " . strlen($html) . "\n";

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

echo "PDF rendered. Canvas: " . $dompdf->getCanvas()->get_width() . "x" . $dompdf->getCanvas()->get_height() . "\n";
$buffer = $dompdf->output();
if (strlen($buffer) > 100) {
    file_put_contents(__DIR__ . '/debug_invoice_output.pdf', $buffer);
    echo "PDF file saved at debug_invoice_output.pdf\n";
} else {
    echo "PDF output too small\n";
}
