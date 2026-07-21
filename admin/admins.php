<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';

$pageTitle = 'Administrators';
$rows = [];
try {
    $stmt = $pdo->query('SELECT * FROM admins ORDER BY id DESC');
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
                <h2 class="h4 mb-1">Administrators</h2>
                <p class="mb-0 opacity-75">Manage access for school admins and super users.</p>
            </div>
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Admin Accounts</h5>
                    <button class="btn btn-sm btn-primary">Create Admin</button>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle admin-datatable">
                        <thead>
                            <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?php echo e($row['fullname'] ?: 'Unknown'); ?></td>
                                    <td><?php echo e($row['email'] ?: ''); ?></td>
                                    <td><?php echo e($row['role'] ?: 'Admin'); ?></td>
                                    <td><span class="badge bg-success-subtle text-success">Active</span></td>
                                    <td><?php echo e($row['last_login'] ?: '-'); ?></td>
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
