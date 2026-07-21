<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container-fluid">
        <button class="btn btn-outline-secondary d-lg-none me-3" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="d-flex align-items-center gap-3">
            <div class="input-group" style="width: 320px;">
                <span class="input-group-text bg-light border-0"><i class="fa-solid fa-search"></i></span>
                <input type="text" class="form-control border-0 bg-light" placeholder="Search...">
            </div>
        </div>
        <div class="ms-auto d-flex align-items-center gap-3">
            <button class="btn btn-outline-secondary position-relative"><i class="fa-solid fa-bell"></i><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span></button>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-user-circle"></i>
                    <span class="d-none d-md-inline"><?php echo e($_SESSION['admin_name']); ?></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="profile.php"><i class="fa-solid fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
