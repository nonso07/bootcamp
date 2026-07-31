<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';
require 'includes/functions.php';

$pageTitle = 'Registrations';
$rows = [];
try {
    $stmt = $pdo->query('SELECT r.*, CONCAT_WS(" ", s.first_name, s.last_name) AS student_name, cl.course_name FROM registrations r LEFT JOIN students s ON s.id = r.student_id LEFT JOIN classes cl ON cl.id = r.course_id ORDER BY r.created_at DESC');
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
                <h2 class="h4 mb-1">Registrations</h2>
                <p class="mb-0 opacity-75">Track each student registration and update their status.</p>
            </div>
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Registration List</h5>
                    <a href="registrations.php" class="btn btn-primary btn-sm">Add New</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle admin-datatable">
                        <thead>
                            <tr><th>Student</th><th>Course</th><th>Status</th><th>Created</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?php echo e($row['student_name'] ?: 'Unknown'); ?></td>
                                    <td><?php echo e($row['course_name'] ?: 'Pending'); ?></td>
                                    <td><span class="badge bg-success-subtle text-success"><?php echo e($row['status'] ?: 'Active'); ?></span></td>
                                    <td><?php echo e(substr($row['created_at'], 0, 10)); ?></td>
                                    <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
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
