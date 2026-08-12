<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';
require 'includes/functions.php';
require '../services/RegistrationService.php';

$pageTitle = 'Registrations';
$message = $_SESSION['registration_message'] ?? null;
$error = $_SESSION['registration_error'] ?? null;
unset($_SESSION['registration_message'], $_SESSION['registration_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = [
        'first_name' => trim((string) ($_POST['first_name'] ?? '')),
        'last_name' => trim((string) ($_POST['last_name'] ?? '')),
        'gender' => trim((string) ($_POST['gender'] ?? '')),
        'dob' => trim((string) ($_POST['dob'] ?? '')),
        'parent_name' => trim((string) ($_POST['parent_name'] ?? '')),
        'phone' => trim((string) ($_POST['phone'] ?? '')),
        'course_id' => (int) ($_POST['course_id'] ?? 0),
        'session' => trim((string) ($_POST['session'] ?? '')),
        'tshirt_size' => trim((string) ($_POST['tshirt_size'] ?? '')),
    ];

    $result = (new RegistrationService(Database::getInstance()))->register($input);

    if (!empty($result['success'])) {
        $_SESSION['registration_message'] = 'Registration created successfully.';
    } else {
        $_SESSION['registration_error'] = $result['message'] ?? 'Registration failed.';
    }

    header('Location: registrations.php');
    exit;
}

$courses = [];
try {
    $coursesStmt = $pdo->query('SELECT course_id, course_name FROM courses ORDER BY course_name ASC');
    $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $courses = [];
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
                <p class="mb-0 opacity-75">Create a new student registration quickly.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRegistrationModal">Add New Registration</button>
            </div>

            <div class="modal fade" id="addRegistrationModal" tabindex="-1" aria-labelledby="addRegistrationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="post" action="registrations.php">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addRegistrationModalLabel">Add New Registration</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Gender</label>
                                        <select class="form-select" name="gender" required>
                                            <option value="">Select gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" name="dob" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Parent / Guardian Name</label>
                                        <input type="text" name="parent_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Parent Phone</label>
                                        <input type="text" name="phone" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Course</label>
                                        <select class="form-select" name="course_id" required>
                                            <option value="">Select course</option>
                                            <?php foreach ($courses as $course): ?>
                                                <option value="<?php echo e($course['course_id']); ?>"><?php echo e($course['course_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Session</label>
                                        <select class="form-select" name="session" required>
                                            <option value="">Select session</option>
                                            <option value="Morning">Morning</option>
                                            <option value="Afternoon">Afternoon</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">T-shirt Size</label>
                                        <select class="form-select" name="tshirt_size" required>
                                            <option value="">Select size</option>
                                            <option value="XS">XS</option>
                                            <option value="S">S</option>
                                            <option value="M">M</option>
                                            <option value="L">L</option>
                                            <option value="XL">XL</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Submit Registration</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
