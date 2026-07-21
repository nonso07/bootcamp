<?php
require '../config/config.php';
require '../config/database.php';
require '../classes/Admin.php';
require 'includes/auth.php';
require 'includes/functions.php';

$pageTitle = 'Dashboard';
$stats = getDashboardStats($pdo);
$recentRegistrations = getRecentRegistrations($pdo, 8);
$recentTransactions = getRecentTransactions($pdo, 8);
$activityFeed = getActivityFeed($pdo, 8);

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
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h2 class="h4 mb-1">Welcome back, <?php echo e($_SESSION['admin_name']); ?></h2>
                        <p class="mb-0 opacity-75">A modern view of registrations, payments, and student activity.</p>
                    </div>
                    <a href="registrations.php" class="btn btn-light text-primary fw-semibold"><i class="fa-solid fa-plus me-2"></i>New Registration</a>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <?php
                $cards = [
                    ['title' => 'Total Registrations', 'value' => number_format($stats['registrations']), 'icon' => 'fa-user-graduate', 'bg' => 'primary'],
                    ['title' => 'Paid Students', 'value' => number_format($stats['paid_students']), 'icon' => 'fa-money-bill-wave', 'bg' => 'success'],
                    ['title' => 'Pending Payments', 'value' => number_format($stats['pending_payments']), 'icon' => 'fa-hourglass-half', 'bg' => 'warning'],
                    ['title' => 'Total Revenue', 'value' => 'CFA ' . number_format($stats['revenue'], 0), 'icon' => 'fa-chart-line', 'bg' => 'purple'],
                    ['title' => 'Courses', 'value' => number_format($stats['courses']), 'icon' => 'fa-book-open', 'bg' => 'info'],
                    ['title' => 'Partner Schools', 'value' => number_format($stats['schools']), 'icon' => 'fa-school', 'bg' => 'indigo'],
                ];
                foreach ($cards as $card) {
                    $colorClass = 'bg-' . $card['bg'];
                    echo '<div class="col-12 col-md-6 col-xl-4"><div class="card stat-card text-white border-0 ' . $colorClass . '"><div class="card-body d-flex justify-content-between align-items-center"><div><h6 class="mb-1 text-white-50">' . e($card['title']) . '</h6><h3 class="mb-0">' . e($card['value']) . '</h3><small class="opacity-75">+12% vs last week</small></div><div class="fs-2"><i class="fa-solid ' . e($card['icon']) . '"></i></div></div></div></div>';
                }
                ?>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-8">
                    <div class="table-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Registration Trend</h5>
                            <span class="badge bg-primary-subtle text-primary">Last 30 days</span>
                        </div>
                        <canvas id="trendChart" height="220"></canvas>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="table-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Payment Status</h5>
                            <span class="badge bg-success-subtle text-success">Live</span>
                        </div>
                        <canvas id="paymentChart" height="220"></canvas>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-7">
                    <div class="table-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Recent Registrations</h5>
                            <a href="registrations.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr><th>Student</th><th>Course</th><th>Status</th><th>Date</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentRegistrations as $row): ?>
                                        <tr>
                                            <td><?php echo e($row['student_name'] ?: 'Pending Student'); ?></td>
                                            <td><?php echo e($row['course_name'] ?: 'Unassigned'); ?></td>
                                            <td><span class="badge bg-success-subtle text-success"><?php echo e($row['status'] ?: 'Active'); ?></span></td>
                                            <td><?php echo e(substr($row['created_at'], 0, 10)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-5">
                    <div class="table-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Recent Transactions</h5>
                            <a href="payments.php" class="btn btn-sm btn-outline-secondary">Manage</a>
                        </div>
                        <div class="list-group">
                            <?php foreach ($recentTransactions as $tx): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold"><?php echo e($tx['student_name'] ?: 'Student'); ?></div>
                                        <small class="text-muted"><?php echo e($tx['reference'] ?: 'N/A'); ?></small>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary">CFA <?php echo e(number_format((float) ($tx['amount'] ?? 0), 0)); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-xl-7">
                    <div class="table-card">
                        <h5 class="mb-3">Quick Actions</h5>
                        <div class="row g-3">
                            <div class="col-6 col-md-3"><a href="registrations.php" class="btn btn-primary w-100 py-3"><i class="fa-solid fa-user-plus d-block mb-2 fs-4"></i>New Registration</a></div>
                            <div class="col-6 col-md-3"><a href="payments.php" class="btn btn-success w-100 py-3"><i class="fa-solid fa-credit-card d-block mb-2 fs-4"></i>Record Payment</a></div>
                            <div class="col-6 col-md-3"><a href="reports.php" class="btn btn-warning w-100 py-3"><i class="fa-solid fa-file-invoice d-block mb-2 fs-4"></i>Generate Receipt</a></div>
                            <div class="col-6 col-md-3"><a href="students.php" class="btn btn-info w-100 py-3"><i class="fa-solid fa-download d-block mb-2 fs-4"></i>Export Students</a></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-5">
                    <div class="table-card">
                        <h5 class="mb-3">Recent Activity</h5>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($activityFeed as $item): ?>
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between">
                                        <span><?php echo e($item['activity'] ?: 'Activity logged'); ?></span>
                                        <small class="text-muted"><?php echo e(substr($item['created_at'], 0, 10)); ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
