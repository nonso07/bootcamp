<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';

$pageTitle = 'Reports';
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
                <h2 class="h4 mb-1">Reports</h2>
                <p class="mb-0 opacity-75">Export registration, payment, and course insights.</p>
            </div>
            <div class="row g-4">
                <div class="col-12 col-md-6 col-xl-3"><div class="card p-3 shadow-sm"><h6>Registration Report</h6><p class="text-muted small">Download current onboarding activity.</p><button class="btn btn-sm btn-primary">Export PDF</button></div></div>
                <div class="col-12 col-md-6 col-xl-3"><div class="card p-3 shadow-sm"><h6>Revenue Report</h6><p class="text-muted small">Summaries for paid and pending payments.</p><button class="btn btn-sm btn-success">Export Excel</button></div></div>
                <div class="col-12 col-md-6 col-xl-3"><div class="card p-3 shadow-sm"><h6>Payment Report</h6><p class="text-muted small">Review status by payment gateway.</p><button class="btn btn-sm btn-warning">Export CSV</button></div></div>
                <div class="col-12 col-md-6 col-xl-3"><div class="card p-3 shadow-sm"><h6>Course Report</h6><p class="text-muted small">See fill rates and open spots.</p><button class="btn btn-sm btn-info">Export</button></div></div>
            </div>
        </main>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
