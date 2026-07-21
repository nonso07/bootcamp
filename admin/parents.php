<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';

$pageTitle = 'Parents';
$rows = [];
try {
    $stmt = $pdo->query('SELECT * FROM parents ORDER BY id DESC');
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
                <h2 class="h4 mb-1">Parents</h2>
                <p class="mb-0 opacity-75">Keep parent contacts and communications organized.</p>
            </div>
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table align-middle admin-datatable">
                        <thead>
                            <tr><th>Name</th><th>Email</th><th>Phone</th><th>Relationship</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?php echo e($row['fullname'] ?: 'Unknown'); ?></td>
                                    <td><?php echo e($row['email'] ?: ''); ?></td>
                                    <td><?php echo e($row['phone'] ?: ''); ?></td>
                                    <td><?php echo e($row['relationship'] ?: 'Parent'); ?></td>
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
