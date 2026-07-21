<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';

$pageTitle = 'Payments';
$rows = [];
try {
    $stmt = $pdo->query('SELECT p.*, CONCAT_WS(" ", s.first_name, s.last_name) AS student_name FROM payments p LEFT JOIN students s ON s.id = p.student_id ORDER BY p.payment_date DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $rows = [];
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
                <h2 class="h4 mb-1">Payments</h2>
                <p class="mb-0 opacity-75">Review payments and mark statuses quickly.</p>
            </div>
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Payment Ledger</h5>
                    <button class="btn btn-sm btn-outline-primary">Filter</button>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle admin-datatable">
                        <thead>
                            <tr><th>Student</th><th>Reference</th><th>Amount</th><th>Status</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?php echo e($row['student_name'] ?: 'Unknown'); ?></td>
                                    <td><?php echo e($row['reference'] ?: 'N/A'); ?></td>
                                    <td>CFA <?php echo e(number_format((float) ($row['amount'] ?? 0), 0)); ?></td>
                                    <td><span class="badge bg-success-subtle text-success"><?php echo e($row['status'] ?: 'Pending'); ?></span></td>
                                    <td><?php echo e(substr($row['payment_date'], 0, 10)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
