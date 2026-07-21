<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';

$pageTitle = 'Courses';
$rows = [];
try {
    $stmt = $pdo->query('SELECT * FROM courses ORDER BY id DESC');
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
                <h2 class="h4 mb-1">Courses</h2>
                <p class="mb-0 opacity-75">Manage available bootcamp tracks and their capacity.</p>
            </div>
            <div class="row g-4">
                <?php foreach ($rows as $row): ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="mb-0"><?php echo e($row['title'] ?: 'Course'); ?></h5>
                                    <span class="badge bg-primary-subtle text-primary">Open</span>
                                </div>
                                <p class="text-muted small mb-3">Capacity: <?php echo e($row['capacity'] ?? 'N/A'); ?></p>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Price</span>
                                    <strong>CFA <?php echo e(number_format((float) ($row['price'] ?? 0), 0)); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
