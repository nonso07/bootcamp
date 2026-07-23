<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';

$pageTitle = 'Payments';
$rows = [];
$students = [];
$message = $_SESSION['payment_message'] ?? null;
$error = $_SESSION['payment_error'] ?? null;
unset($_SESSION['payment_message'], $_SESSION['payment_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
    $reference = trim((string) ($_POST['reference'] ?? ''));
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $status = strtolower(trim((string) ($_POST['status'] ?? '')));
    $paymentDate = trim((string) ($_POST['payment_date'] ?? ''));

    if (!in_array($status, ['part', 'full'], true)) {
        $status = 'part';
    }

    $dbStatus = $status === 'full' ? 'Success' : 'Pending';

    if ($studentId && $reference !== '' && $amount !== false && $amount > 0 && $paymentDate !== '') {
        try {
            $registrationStmt = $pdo->prepare('SELECT id FROM registrations WHERE student_id = ? ORDER BY id DESC LIMIT 1');
            $registrationStmt->execute([$studentId]);
            $registration = $registrationStmt->fetch(PDO::FETCH_ASSOC);

            if (!$registration) {
                throw new Exception('No registration found for the selected student.');
            }

            $stmt = $pdo->prepare('INSERT INTO payments (registration_id, amount, currency, payment_method, payment_gateway, transaction_reference, status, paid_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $registration['id'],
                $amount,
                'XOF',
                'offline',
                'Manual',
                $reference,
                $dbStatus,
                $paymentDate . ' 00:00:00',
            ]);
            $_SESSION['payment_message'] = 'Payment recorded successfully.';
            header('Location: payments.php');
            exit;
        } catch (Exception $e) {
            error_log('Payment save error: ' . $e->getMessage());
            $error = 'Unable to save the payment right now.';
        }
    } else {
        $error = 'Please complete all required fields.';
    }
}

try {
    $stmt = $pdo->query('SELECT p.id, p.registration_id, p.amount, p.transaction_reference AS reference, p.status, COALESCE(p.paid_at, p.created_at) AS payment_date, CONCAT_WS(" ", s.first_name, s.last_name) AS student_name FROM payments p LEFT JOIN registrations r ON r.id = p.registration_id LEFT JOIN students s ON s.id = r.student_id ORDER BY p.paid_at DESC, p.created_at DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $studentsStmt = $pdo->query('SELECT s.id, CONCAT_WS(" ", s.first_name, s.last_name) AS student_name FROM students s ORDER BY s.first_name, s.last_name');
    $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Database Error: ' . $e->getMessage());

    $rows = [];
    $students = [];
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
                            <tr><th>Student</th><th>Reference</th><th>Amount</th><th>Status</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <?php $status = strtolower((string) ($row['status'] ?? 'pending')); ?>
                                <?php $displayStatus = $status === 'success' ? 'Full' : ($status === 'pending' ? 'Part' : ucfirst($status)); ?>
                                <?php $badgeClass = $status === 'success' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?>
                                <tr>
                                    <td><?php echo e($row['student_name'] ?: 'Unknown'); ?></td>
                                    <td><?php echo e($row['reference'] ?: 'N/A'); ?></td>
                                    <td>CFA <?php echo e(number_format((float) ($row['amount'] ?? 0), 0)); ?></td>
                                    <td><span class="badge <?php echo e($badgeClass); ?>"><?php echo e($displayStatus); ?></span></td>
                                    <td><?php echo e(!empty($row['payment_date']) ? substr($row['payment_date'], 0, 10) : 'N/A'); ?></td>
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
                    <h5 class="modal-title" id="confirmPaymentModalLabel">Confirm New Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Student</label>
                            <select class="form-select" name="student_id" required>
                                <option value="">Select student</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo e($student['id']); ?>"><?php echo e($student['student_name'] ?: 'Student'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference</label>
                            <input type="text" class="form-control" name="reference" placeholder="e.g. INV-1001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-control" name="amount" min="0" step="0.01" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="part">Part</option>
                                <option value="full">Full</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Date</label>
                            <input type="date" class="form-control" name="payment_date" value="<?php echo e(date('Y-m-d')); ?>" required>
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
<?php include 'includes/footer.php'; ?>
