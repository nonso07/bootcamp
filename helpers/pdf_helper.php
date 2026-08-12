<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

require_once __DIR__ . '/helpers.php';

function fetchInvoiceData(PDO $pdo, int $invoiceId): ?array
{
    $sql = <<<SQL
SELECT
    i.id AS invoice_id,
    i.invoice_no,
    i.invoice_date,
    i.due_date,
    i.subtotal,
    i.discount,
    i.total,
    i.payment_status AS invoice_payment_status,
    r.id AS registration_id,
    r.registration_date,
    r.tshirt_size,
    r.session,
    r.amount AS registration_amount,
    r.payment_status AS registration_payment_status,
    s.id AS student_id,
    s.registration_no AS student_registration_no,
    s.first_name,
    s.last_name,
    s.gender,
    s.dob,
    s.class_level,
    s.nationality,
    s.address AS student_address,
    sc.school_name,
    p.fullname AS parent_name,
    p.relationship,
    p.phone AS parent_phone,
    p.whatsapp AS parent_whatsapp,
    p.email AS parent_email,
    p.emergency_contact AS parent_emergency_contact,
    c.course_name,
    pay.payment_method,
    pay.payment_gateway
FROM invoices i
JOIN registrations r ON r.id = i.registration_id
JOIN students s ON s.id = r.student_id
LEFT JOIN schools sc ON sc.id = s.school_id
LEFT JOIN parents p ON p.id = s.parent_id
LEFT JOIN courses c ON c.course_id = r.course_id
LEFT JOIN payments pay ON pay.registration_id = r.id
WHERE i.id = ?
ORDER BY pay.created_at DESC
LIMIT 1
SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$invoiceId]);
    $invoice = $stmt->fetch();
    if (!$invoice) {
        return null;
    }

    $invoice['student_name'] = trim($invoice['first_name'] . ' ' . $invoice['last_name']);
    $invoice['school_name'] = $invoice['school_name'] ?: 'Not Provided';
    $invoice['payment_method'] = $invoice['payment_method'] ?: 'Offline';
    $invoice['payment_gateway'] = $invoice['payment_gateway'] ?: 'Manual';
    $invoice['invoice_date'] = $invoice['invoice_date'] ?: date('Y-m-d');
    $invoice['due_date'] = $invoice['due_date'] ?: date('Y-m-d');

    $invoice['items'] = fetchInvoiceItems($pdo, $invoiceId);
    $invoice['qr_code_data_uri'] = generateInvoiceQrCodeDataUri([
        'registration_number' => $invoice['student_registration_no'],
        'invoice_number' => $invoice['invoice_no'],
        'student_name' => $invoice['student_name'],
    ]);

    return $invoice;
}

function generateInvoiceQrCodeDataUri(array $payload): string
{
    $text = sprintf(
        'Registration: %s | Invoice: %s | Student: %s',
        $payload['registration_number'] ?? '',
        $payload['invoice_number'] ?? '',
        $payload['student_name'] ?? ''
    );

    $writer = new PngWriter();
    $result = (new Builder())->build(
        $writer,
        null,
        false,
        $text,
        null,
        null,
        240,
        10
    );

    return 'data:image/png;base64,' . base64_encode($result->getString());
}

function fetchInvoiceItems(PDO $pdo, int $invoiceId): array
{
    $stmt = $pdo->prepare('SELECT description, quantity, unit_price, total FROM invoice_items WHERE invoice_id = ?');
    $stmt->execute([$invoiceId]);

    return $stmt->fetchAll();
}

function createInvoiceFilename(string $invoiceNumber): string
{
    $safeNumber = preg_replace('/[^A-Za-z0-9_-]/', '-', $invoiceNumber);
    return sprintf('%s.pdf', $safeNumber);
}
