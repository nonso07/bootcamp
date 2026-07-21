<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';

$pageTitle = 'Settings';
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
                <h2 class="h4 mb-1">Settings</h2>
                <p class="mb-0 opacity-75">Adjust school branding and pricing rules.</p>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Organization Name</label><input class="form-control" value="Habatech Digital Solutions"></div>
                            <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" value="info@habatech.com"></div>
                            <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" value="+237 670 000 000"></div>
                            <div class="col-md-6"><label class="form-label">Currency</label><input class="form-control" value="CFA"></div>
                            <div class="col-md-6"><label class="form-label">Early Bird Fee</label><input class="form-control" value="30000"></div>
                            <div class="col-md-6"><label class="form-label">Regular Fee</label><input class="form-control" value="35000"></div>
                        </div>
                        <button class="btn btn-primary mt-4">Save Settings</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
