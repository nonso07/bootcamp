<?php

if (!function_exists('renderInvoiceTemplate')) {
    function renderInvoiceTemplate(array $data, bool $forPdf = false): string
    {
        $logoLeft = '<div class="brand-logo">Habatech Logo</div>';
        $logoRight = '<div class="brand-logo">FMI School Logo</div>';
        $habatechLogo = __DIR__ . '/assets/images/habatech-logo.png';
        $fountainLogo = __DIR__ . '/assets/images/fountain-logo.png';

        if (file_exists($habatechLogo)) {
            $habatechLogoData = base64_encode(file_get_contents($habatechLogo));
            $logoLeft = '<img src="data:image/png;base64,' . $habatechLogoData . '" alt="Habatech Digital Solutions" class="logo-img">';
        }

        if (file_exists($fountainLogo)) {
            $fountainLogoData = base64_encode(file_get_contents($fountainLogo));
            $logoRight = '<img src="data:image/png;base64,' . $fountainLogoData . '" alt="Fountain Mission International School" class="logo-img">';
        }

        $invoiceDate = date('d F Y', strtotime($data['invoice_date']));
        $dueDate = date('d F Y', strtotime($data['due_date']));
        $dob = $data['dob'] ? date('d F Y', strtotime($data['dob'])) : 'Not Provided';

        $itemsHtml = '';
        foreach ($data['items'] as $item) {
            $itemsHtml .= sprintf(
                '<tr><td>%s</td><td class="text-center">%s</td><td class="text-right">₣%s</td></tr>',
                htmlspecialchars($item['description']),
                htmlspecialchars($item['quantity']),
                number_format((float) $item['unit_price'], 2)
            );
        }

        $statusBadge = '<span class="badge badge-pending">' . htmlspecialchars(ucfirst($data['invoice_payment_status'])) . '</span>';
        if (strtolower($data['invoice_payment_status']) === 'paid') {
            $statusBadge = '<span class="badge badge-paid">Paid</span>';
        }

        $mainHtml = '';
        if ($forPdf) {
            $css = file_exists(__DIR__ . '/assets/css/invoice.css') ? file_get_contents(__DIR__ . '/assets/css/invoice.css') : '';
            $mainHtml .= '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>' . htmlspecialchars($data['invoice_no']) . '</title><style>' . $css . '</style></head><body>';
        }

        $mainHtml .= '<div class="invoice-page">';
        $mainHtml .= '<header class="invoice-header">';
        $mainHtml .= '<div class="brand-section">';
        $mainHtml .= '<div class="brand-block">';
        $mainHtml .= '<h1 class="brand-title">Habatech Digital Solutions</h1>';
        $mainHtml .= '<p class="brand-subtitle">Building the Future Today!</p>';
        $mainHtml .= '</div>';
        $mainHtml .= '<div class="brand-block">';
        $mainHtml .= '<h2 class="partnership-heading">IN PARTNERSHIP WITH</h2>';
        $mainHtml .= '<p class="partnership-name">Fountain Mission International School, Abidjan</p>';
        $mainHtml .= '</div>';
        $mainHtml .= '</div>';
        $mainHtml .= '<div class="logo-row">' . $logoLeft . $logoRight . '</div>';
        $mainHtml .= '<h2 class="invoice-title">STEM BOOTCAMP REGISTRATION INVOICE</h2>';
        $mainHtml .= '</header>';

        $mainHtml .= '<section class="invoice-meta">';
        $mainHtml .= '<div><span>Invoice Number</span><strong>' . htmlspecialchars($data['invoice_no'] ?? 'N/A') . '</strong></div>';
        $mainHtml .= '<div><span>Registration Number</span><strong>' . htmlspecialchars($data['student_registration_no'] ?? 'N/A') . '</strong></div>';
        $mainHtml .= '<div><span>Invoice Date</span><strong>' . $invoiceDate . '</strong></div>';
        $mainHtml .= '<div><span>Payment Status</span><strong>' . $statusBadge . '</strong></div>';
        $mainHtml .= '<div><span>Payment Method</span><strong>' . htmlspecialchars(ucfirst($data['payment_method'] ?? 'Offline')) . '</strong></div>';
        $mainHtml .= '</section>';

        $mainHtml .= '<section class="data-grid">';
        $mainHtml .= '<div class="data-card">';
        $mainHtml .= '<h3>Student Information</h3>';
        $mainHtml .= '<div class="data-row"><span>Student Name</span><strong>' . htmlspecialchars($data['student_name']) . '</strong></div>';
        $mainHtml .= '<div class="data-row"><span>Gender</span><strong>' . htmlspecialchars($data['gender']) . '</strong></div>';
        $mainHtml .= '<div class="data-row"><span>Date of Birth</span><strong>' . $dob . '</strong></div>';
        $mainHtml .= '<div class="data-row"><span>School</span><strong>' . htmlspecialchars($data['school_name']) . '</strong></div>';
        $mainHtml .= '<div class="data-row"><span>Current Class</span><strong>' . htmlspecialchars($data['class_level']) . '</strong></div>';
        $mainHtml .= '<div class="data-row"><span>Nationality</span><strong>' . htmlspecialchars($data['nationality']) . '</strong></div>';
        $mainHtml .= '<div class="data-row"><span>Course Registered</span><strong>' . htmlspecialchars($data['course_name'] ?? 'Not Provided') . '</strong></div>';
        $mainHtml .= '<div class="data-row"><span>Session</span><strong>' . htmlspecialchars($data['session'] ?? 'Not Provided') . '</strong></div>';
        $mainHtml .= '<div class="data-row"><span>T-Shirt Size</span><strong>' . htmlspecialchars($data['tshirt_size'] ?? 'Not Provided') . '</strong></div>';
        $mainHtml .= '</div>';

        $mainHtml .= '<div class="data-card">';
        $mainHtml .= '<h3>Parent / Guardian</h3>';
        $mainHtml .= '<div class="data-row"><span>Parent Name</span><strong>' . htmlspecialchars($data['parent_name']) . '</strong></div>';
        $mainHtml .= '<div class="data-row"><span>Relationship</span><strong>' . htmlspecialchars($data['relationship']) . '</strong></div>';
        $mainHtml .= '<div class="data-row"><span>Phone Number</span><strong>' . htmlspecialchars($data['parent_phone']) . '</strong></div>';
        $mainHtml .= '<div class="data-row"><span>WhatsApp</span><strong>' . htmlspecialchars($data['parent_whatsapp']) . '</strong></div>';
        $mainHtml .= '<div class="data-row"><span>Email Address</span><strong>' . htmlspecialchars($data['parent_email']) . '</strong></div>';
        $mainHtml .= '<div class="data-row"><span>Emergency Contact</span><strong>' . htmlspecialchars($data['parent_emergency_contact']) . '</strong></div>';
        $mainHtml .= '</div>';
        $mainHtml .= '</section>';

        $mainHtml .= '<section class="payment-section">';
        $mainHtml .= '<h3>Payment Details</h3>';
        $mainHtml .= '<table class="payment-table"><thead><tr><th>Description</th><th>Quantity</th><th class="text-right">Amount</th></tr></thead><tbody>';
        $mainHtml .= $itemsHtml;
        $mainHtml .= '</tbody></table>';
        $mainHtml .= '<div class="summary-grid">';
        $mainHtml .= '<div><span>Subtotal</span><strong>₣' . number_format((float) $data['subtotal'], 2) . '</strong></div>';
        $mainHtml .= '<div><span>Discount</span><strong>₣' . number_format((float) $data['discount'], 2) . '</strong></div>';
        $mainHtml .= '<div class="total-row"><span>Total</span><strong>₣' . number_format((float) $data['total'], 2) . '</strong></div>';
        $mainHtml .= '</div>';
        $mainHtml .= '</section>';

        $mainHtml .= '<section class="instructions">';
        $mainHtml .= '<h3>Payment Instructions</h3>';
        $mainHtml .= '<p>Please present this invoice when making payment.</p>';
        $mainHtml .= '<p>Payment can be made at:</p>';
        $mainHtml .= '<ul><li>Habatech Digital Solutions</li><li>Fountain Mission International School, Abidjan</li></ul>';
        $mainHtml .= '<p>After payment, your registration will be validated.</p>';
        $mainHtml .= '<div class="contact-block">';
        $mainHtml .= '<p><strong>Contact:</strong></p>';
        $mainHtml .= '<p>Habatech Digital Solutions</p>';
        $mainHtml .= '<p>Phone: __________________</p>';
        $mainHtml .= '<p>WhatsApp: __________________</p>';
        $mainHtml .= '<p>Email: __________________</p>';
        $mainHtml .= '</div>';
        $mainHtml .= '</section>';

        $mainHtml .= '<section class="footer-section">';
        $mainHtml .= '<p>Thank you for registering for the Habatech STEM Bootcamp 2026.</p>';
        $mainHtml .= '<p>Organized by Habatech Digital Solutions in Partnership with Fountain Mission International School, Abidjan</p>';
        $mainHtml .= '<p class="footer-tagline">Building the Future Today!</p>';
        $mainHtml .= '</section>';

        $mainHtml .= '<div class="qr-block">';
        $mainHtml .= '<div class="qr-code"><img src="' . htmlspecialchars($data['qr_code_data_uri']) . '" alt="Invoice QR Code" style="width:100%;height:auto;border-radius:12px;"></div>';
        $mainHtml .= '<div><p style="font-size:0.95rem;line-height:1.5;margin:0;">Scan this QR code for verification.</p><p style="font-size:0.85rem;color:#475569;margin:0.75rem 0 0;">Registration: ' . htmlspecialchars($data['student_registration_no']) . '<br>Invoice: ' . htmlspecialchars($data['invoice_no']) . '<br>Student: ' . htmlspecialchars($data['student_name']) . '</p></div>';
        $mainHtml .= '</div>';

        $mainHtml .= '</div>';

        if ($forPdf) {
            $mainHtml .= '</body></html>';
        }

        return $mainHtml;
    }
}
