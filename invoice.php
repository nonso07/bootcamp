<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/helpers.php';
require_once __DIR__ . '/helpers/pdf_helper.php';

$invoiceId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$invoiceId) {
    http_response_code(404);
    $error = 'Invoice not found. Invalid invoice ID.';
}

$pdo = Database::getInstance();
$invoiceData = $invoiceId ? fetchInvoiceData($pdo, $invoiceId) : null;
if ($invoiceId && !$invoiceData) {
    http_response_code(404);
    $error = 'Invoice not found. The requested invoice does not exist.';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice - Habatech STEM Bootcamp</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/invoice.css">
  <style>
    body { background: #f1f5f9; }
    .status-chip { padding: 0.5rem 0.9rem; border-radius: 999px; font-weight: 700; }
    .status-pending { background: #e0f2fe; color: #0c4a6e; }
    .status-paid { background: #dcfce7; color: #166534; }
  </style>
</head>
<body>
  <div class="container py-5">
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger">
        <h4 class="alert-heading">Invoice not found</h4>
        <p><?php echo htmlspecialchars($error); ?></p>
        <hr>
        <a href="index.html" class="btn btn-primary">Return Home</a>
      </div>
    <?php else: ?>
      <div class="card p-4 shadow-sm">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
          <div>
            <h1 class="h3 mb-1">Registration Successful</h1>
            <p class="text-muted mb-0">Your invoice has been generated and is ready for download.</p>
          </div>
          <div class="text-end">
            <span class="status-chip <?php echo strtolower($invoiceData['invoice_payment_status']) === 'paid' ? 'status-paid' : 'status-pending'; ?>">
              <?php echo htmlspecialchars(ucfirst($invoiceData['invoice_payment_status'])); ?>
            </span>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-md-4 mb-3">
            <div class="p-3 rounded-3 bg-white border">
              <p class="text-muted mb-1">Registration Number</p>
              <h5><?php echo htmlspecialchars($invoiceData['student_registration_no']); ?></h5>
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <div class="p-3 rounded-3 bg-white border">
              <p class="text-muted mb-1">Invoice Number</p>
              <h5><?php echo htmlspecialchars($invoiceData['invoice_no']); ?></h5>
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <div class="p-3 rounded-3 bg-white border">
              <p class="text-muted mb-1">Payment Status</p>
              <h5><?php echo htmlspecialchars(ucfirst($invoiceData['invoice_payment_status'])); ?></h5>
            </div>
          </div>
        </div>

        <div class="mb-4">
          <iframe id="invoicePreview" src="generate_invoice_pdf.php?invoice_id=<?php echo $invoiceData['invoice_id']; ?>&preview=1" width="100%" height="900" style="border:1px solid #e2e8f0;border-radius:1rem;"></iframe>
        </div>

        <div class="d-flex flex-column flex-md-row gap-3">
          <a id="downloadInvoiceLink" href="generate_invoice_pdf.php?invoice_id=<?php echo $invoiceData['invoice_id']; ?>&download=1" class="btn btn-primary">Download Invoice</a>
          <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('invoicePreview').contentWindow.print();">Print Invoice</button>
          <a href="index.html" class="btn btn-light">Return Home</a>
        </div>
        <script>
          window.addEventListener('DOMContentLoaded', function() {
            var downloadLink = document.getElementById('downloadInvoiceLink');
            if (downloadLink) {
              var autoDownload = document.createElement('iframe');
              autoDownload.style.display = 'none';
              autoDownload.src = downloadLink.href;
              document.body.appendChild(autoDownload);
            }
          });
        </script>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
