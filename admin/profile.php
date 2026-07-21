<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';

$pageTitle = 'Profile';
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
                <h2 class="h4 mb-1">Profile</h2>
                <p class="mb-0 opacity-75">Manage your identity and password.</p>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4 text-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px; font-size: 2.5rem;"><i class="fa-solid fa-user"></i></div>
                            <h5 class="mt-3 mb-1"><?php echo e($_SESSION['admin_name']); ?></h5>
                            <p class="text-muted"><?php echo e($_SESSION['role']); ?></p>
                        </div>
                        <div class="col-md-8">
                            <form>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Full Name</label><input class="form-control" value="<?php echo e($_SESSION['admin_name']); ?>"></div>
                                    <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" value="admin@example.com"></div>
                                    <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" value="+237 670 000 000"></div>
                                    <div class="col-md-6"><label class="form-label">Role</label><input class="form-control" value="<?php echo e($_SESSION['role']); ?>"></div>
                                </div>
                                <button class="btn btn-primary mt-4">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
