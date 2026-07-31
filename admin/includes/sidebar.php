<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar bg-dark text-white p-3">
    <div class="brand mb-4">
        <h4 class="fw-bold"><i class="fa-solid fa-graduation-cap me-2"></i>Habatech</h4>
        <p class="small text-white-50 mb-0">Admin Portal</p>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" href="index.php"><i class="fa-solid fa-gauge-high me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'registrations.php' ? 'active' : ''; ?>" href="registrations.php"><i class="fa-solid fa-clipboard-list me-2"></i>Registrations</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'students.php' ? 'active' : ''; ?>" href="students.php"><i class="fa-solid fa-user-graduate me-2"></i>Students</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'parents.php' ? 'active' : ''; ?>" href="parents.php"><i class="fa-solid fa-users me-2"></i>Parents</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'schools.php' ? 'active' : ''; ?>" href="schools.php"><i class="fa-solid fa-school me-2"></i>Schools</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'bootcamp.php' ? 'active' : ''; ?>" href="bootcamp.php"><i class="fa-solid fa-campground me-2"></i>Bootcamp</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'catalog_courses.php' ? 'active' : ''; ?>" href="catalog_courses.php"><i class="fa-solid fa-book-open me-2"></i>Courses</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'classes.php' ? 'active' : ''; ?>" href="classes.php"><i class="fa-solid fa-layer-group me-2"></i>Classes</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'payments.php' ? 'active' : ''; ?>" href="payments.php"><i class="fa-solid fa-credit-card me-2"></i>Payments</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'reports.php' ? 'active' : ''; ?>" href="reports.php"><i class="fa-solid fa-chart-line me-2"></i>Reports</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>" href="settings.php"><i class="fa-solid fa-sliders me-2"></i>Settings</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'admins.php' ? 'active' : ''; ?>" href="admins.php"><i class="fa-solid fa-user-shield me-2"></i>Administrators</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>" href="profile.php"><i class="fa-solid fa-id-card me-2"></i>Profile</a></li>
        <li class="nav-item mt-3"><a class="nav-link text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
    </ul>
</div>
