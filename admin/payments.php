<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';

$pageTitle = 'Payments';
$rows = [];
$registrations = [];
$message = $_SESSION['payment_message'] ?? null;
$error = $_SESSION['payment_error'] ?? null;
unset($_SESSION['payment_message'], $_SESSION['payment_error']);

$formValues = [
    'invoice_no' => '',
    'amount' => '',
    'reference' => '',
    'payment_date' => date('Y-m-d'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formValues['invoice_no'] = trim((string) ($_POST['invoice_no'] ?? ''));
    $formValues['amount'] = trim((string) ($_POST['amount'] ?? ''));
    $formValues['reference'] = trim((string) ($_POST['reference'] ?? ''));
    $formValues['payment_date'] = trim((string) ($_POST['payment_date'] ?? date('Y-m-d')));

    $amount = filter_var($formValues['amount'], FILTER_VALIDATE_FLOAT);

    if ($formValues['invoice_no'] === '') {
        $error = 'Please select an invoice.';
    } elseif ($amount === false || $amount <= 0) {
        $error = 'Please enter a valid payment amount.';
    } elseif ($formValues['payment_date'] === '') {
        $error = 'Please select a payment date.';
    } else {
        try {
            $invoiceStmt = $pdo->prepare('SELECT registration_id, total FROM invoices WHERE invoice_no = ? LIMIT 1');
            $invoiceStmt->execute([$formValues['invoice_no']]);
            $invoice = $invoiceStmt->fetch(PDO::FETCH_ASSOC);

            if (!$invoice) {
                throw new Exception('Selected invoice was not found.');
            }

            $registrationId = (int) $invoice['registration_id'];
            $invoiceTotal = (float) $invoice['total'];

            $paymentStmt = $pdo->prepare('SELECT id, amount FROM payments WHERE registration_id = ? LIMIT 1');
            $paymentStmt->execute([$registrationId]);
            $existingPayment = $paymentStmt->fetch(PDO::FETCH_ASSOC);

            $existingAmount = $existingPayment['amount'] ?? 0.0;
            $newTotal = $existingAmount + $amount;

            if ($newTotal > $invoiceTotal) {
                $error = 'Payment amount exceeds invoice total. Please enter a smaller amount.';
            } else {
                $status = $newTotal === $invoiceTotal ? 'Success' : 'Part';
                $registrationStatus = $status === 'Success' ? 'Paid' : 'Part';
                $invoiceStatus = $status === 'Success' ? 'paid' : 'part';

                $pdo->beginTransaction();

                if ($existingPayment) {
                    $updateStmt = $pdo->prepare('UPDATE payments SET amount = ?, status = ?, transaction_reference = ?, paid_at = ? WHERE registration_id = ?');
                    $updateStmt->execute([
                        $newTotal,
                        $status,
                        $formValues['reference'] !== '' ? $formValues['reference'] : null,
                        $formValues['payment_date'] . ' 00:00:00',
                        $registrationId,
                    ]);
                } else {
                    $insertStmt = $pdo->prepare('INSERT INTO payments (registration_id, amount, currency, payment_method, payment_gateway, transaction_reference, status, gateway_response, paid_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
                    $insertStmt->execute([
                        $registrationId,
                        $newTotal,
                        'XOF',
                        'offline',
                        'Manual',
                        $formValues['reference'] !== '' ? $formValues['reference'] : null,
                        $status,
                        null,
                        $formValues['payment_date'] . ' 00:00:00',
                    ]);
                }

                $regUpdateStmt = $pdo->prepare('UPDATE registrations SET payment_status = ? WHERE id = ?');
                $regUpdateStmt->execute([$registrationStatus, $registrationId]);

                $invoiceUpdateStmt = $pdo->prepare('UPDATE invoices SET payment_status = ? WHERE registration_id = ?');
                $invoiceUpdateStmt->execute([$invoiceStatus, $registrationId]);

                $pdo->commit();

                $_SESSION['payment_message'] = 'Payment recorded successfully.';
                header('Location: payments.php');
                exit;
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Payment save error: ' . $e->getMessage());
            $error = 'Unable to save the payment right now.';
        }
    }
}

try {
    $stmt = $pdo->query('SELECT p.id, r.invoice_no, COALESCE(CONCAT_WS(" ", s.first_name, s.last_name), "Unknown") AS student_name, p.amount, p.transaction_reference AS reference, p.status, COALESCE(p.paid_at, p.created_at) AS payment_date, i.total AS invoice_total, (i.total - p.amount) AS balance FROM payments p LEFT JOIN registrations r ON r.id = p.registration_id LEFT JOIN students s ON s.id = r.student_id LEFT JOIN invoices i ON i.registration_id = r.id ORDER BY p.paid_at DESC, p.created_at DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $registrationsStmt = $pdo->query('SELECT r.invoice_no, r.id AS registration_id, COALESCE(CONCAT_WS(" ", s.first_name, s.last_name), "Unknown") AS student_name, COALESCE(c.course_name, "-") AS course_name, COALESCE(i.total, 0) AS total, COALESCE(p.amount, 0) AS amount_paid, COALESCE(i.total - p.amount, COALESCE(i.total, 0)) AS balance FROM registrations r LEFT JOIN students s ON s.id = r.student_id LEFT JOIN courses c ON c.course_id = r.course_id LEFT JOIN invoices i ON i.registration_id = r.id LEFT JOIN payments p ON p.registration_id = r.id WHERE r.invoice_no IS NOT NULL AND r.invoice_no != "" ORDER BY r.registration_date DESC');
    $registrations = $registrationsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Database Error: ' . $e->getMessage());

    $rows = [];
    $registrations = [];
}

include 'includes/header.php';
?>
<div class="d-flex">
    <div class="sidebar-wrapper">
        <div id="sidebarMenu" class="collapse d-lg-block">
            <?php include 'includes/sidebar.php'; ?>
        </div>
    </div>
    <div class="content-area">
        <?php include 'includes/navbar.php'; ?>
        <main class="p-4">
            <div class="page-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h4 mb-1">Payments</h2>
                        <p class="mb-0 opacity-75">Review payments and record new confirmations quickly.</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmPaymentModal">Confirm Payment</button>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo e($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Payment Ledger</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle admin-datatable">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Student</th>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <?php $status = strtolower((string) ($row['status'] ?? 'pending')); ?>
                                <?php $displayStatus = $status === 'success' ? 'Full' : ($status === 'part' ? 'Part' : ucfirst($status)); ?>
                                <?php $badgeClass = $status === 'success' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?>
                                <tr>
                                    <td><?php echo e($row['invoice_no'] ?? 'N/A'); ?></td>
                                    <td><?php echo e($row['student_name'] ?: 'Unknown'); ?></td>
                                    <td><?php echo e($row['reference'] ?: 'N/A'); ?></td>
                                    <td>CFA <?php echo e(number_format((float) ($row['amount'] ?? 0), 0)); ?></td>
                                    <td><span class="badge <?php echo e($badgeClass); ?>"><?php echo e($displayStatus); ?></span></td>
                                    <td><?php echo e(!empty($row['payment_date']) ? substr($row['payment_date'], 0, 10) : 'N/A'); ?></td>
                                    <td>
                                        <?php if ($status === 'part'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary add-payment-button" data-invoice-no="<?php echo e($row['invoice_no']); ?>" data-bs-toggle="modal" data-bs-target="#confirmPaymentModal">Add Payment</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="confirmPaymentModal" tabindex="-1" aria-labelledby="confirmPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="payments.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmPaymentModalLabel">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Invoice</label>
                            <select class="form-select" id="invoiceSelect" name="invoice_no" required>
                                <option value="">Select invoice</option>
                                <?php foreach ($registrations as $registration): ?>
                                    <option value="<?php echo e($registration['invoice_no']); ?>"
                                        data-student-name="<?php echo e($registration['student_name']); ?>"
                                        data-course-name="<?php echo e($registration['course_name']); ?>"
                                        data-total="<?php echo e($registration['total']); ?>"
                                        data-amount-paid="<?php echo e($registration['amount_paid']); ?>"
                                        data-balance="<?php echo e($registration['balance']); ?>"
                                        <?php echo ($formValues['invoice_no'] === $registration['invoice_no']) ? 'selected' : ''; ?>
                                    >
                                        <?php echo e($registration['invoice_no']); ?> — <?php echo e($registration['student_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference</label>
                            <input type="text" class="form-control" name="reference" value="<?php echo e($formValues['reference']); ?>" placeholder="e.g. INV-1001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Student Name</label>
                            <input type="text" class="form-control" id="selectedStudentName" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Course</label>
                            <input type="text" class="form-control" id="selectedCourseName" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Invoice Total</label>
                            <input type="text" class="form-control" id="selectedTotal" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount Paid</label>
                            <input type="text" class="form-control" id="selectedPaid" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Remaining Balance</label>
                            <input type="text" class="form-control" id="selectedBalance" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-control" name="amount" min="0" step="0.01" value="<?php echo e($formValues['amount']); ?>" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Date</label>
                            <input type="date" class="form-control" name="payment_date" value="<?php echo e($formValues['payment_date']); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const invoiceSelect = document.getElementById('invoiceSelect');
    const studentNameInput = document.getElementById('selectedStudentName');
    const courseNameInput = document.getElementById('selectedCourseName');
    const totalInput = document.getElementById('selectedTotal');
    const paidInput = document.getElementById('selectedPaid');
    const balanceInput = document.getElementById('selectedBalance');

    function updatePaymentDetails() {
        const selected = invoiceSelect.options[invoiceSelect.selectedIndex];
        const studentName = selected?.getAttribute('data-student-name') || '';
        const courseName = selected?.getAttribute('data-course-name') || '';
        const total = selected?.getAttribute('data-total') || '0';
        const paid = selected?.getAttribute('data-amount-paid') || '0';
        const balance = selected?.getAttribute('data-balance') || '0';

        studentNameInput.value = studentName;
        courseNameInput.value = courseName;
        totalInput.value = `₣${Number(total).toLocaleString()}`;
        paidInput.value = `₣${Number(paid).toLocaleString()}`;
        balanceInput.value = `₣${Number(balance).toLocaleString()}`;
    }

    invoiceSelect.addEventListener('change', updatePaymentDetails);
    updatePaymentDetails();

    document.querySelectorAll('.add-payment-button').forEach(button => {
        button.addEventListener('click', function () {
            const invoiceNo = this.getAttribute('data-invoice-no');
            if (!invoiceNo) return;
            invoiceSelect.value = invoiceNo;
            updatePaymentDetails();
        });
    });
});
</script>
<?php include 'includes/footer.php'; ?>
