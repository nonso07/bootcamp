<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';

$pageTitle = 'Classes';
$message = $_SESSION['flash_message'] ?? null;
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_message'], $_SESSION['flash_error']);

$editing = null;
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$showModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($action === 'delete' && $id) {
        try {
            $pdo->prepare('DELETE FROM classes WHERE id = ?')->execute([$id]);
            $_SESSION['flash_message'] = 'Class deleted successfully.';
            header('Location: classes.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Unable to delete class.';
            header('Location: classes.php');
            exit;
        }
    }

    $bootcampId = filter_input(INPUT_POST, 'bootcamp_id', FILTER_VALIDATE_INT);
    $courseName = trim((string) ($_POST['course_name'] ?? ''));
    $level = trim((string) ($_POST['level'] ?? ''));
    $duration = trim((string) ($_POST['duration'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_VALIDATE_INT);
    $status = $status !== false ? $status : 1;

    if ($courseName === '' || !$bootcampId) {
        $_SESSION['flash_error'] = 'Please select a bootcamp and a course.';
        header('Location: classes.php');
        exit;
    }

    try {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE classes SET bootcamp_id = ?, course_name = ?, level = ?, duration = ?, description = ?, capacity = ?, status = ? WHERE id = ?');
            $stmt->execute([$bootcampId, $courseName, $level, $duration, $description, $capacity ?: 100, $status, $id]);
            $_SESSION['flash_message'] = 'Class updated successfully.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO classes (bootcamp_id, course_name, level, duration, description, capacity, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$bootcampId, $courseName, $level, $duration, $description, $capacity ?: 100, $status]);
            $_SESSION['flash_message'] = 'Class added successfully.';
        }

        header('Location: classes.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Unable to save class right now.';
        header('Location: classes.php');
        exit;
    }
}

if ($editId) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM classes WHERE id = ? LIMIT 1');
        $stmt->execute([$editId]);
        $editing = $stmt->fetch(PDO::FETCH_ASSOC);
        $showModal = true;
    } catch (Exception $e) {
        $editing = null;
    }
}

try {
    $stmt = $pdo->query('SELECT cl.*, b.title AS bootcamp_title, s.school_name FROM classes cl LEFT JOIN bootcamps b ON b.id = cl.bootcamp_id LEFT JOIN schools s ON s.id = b.school_id ORDER BY cl.id DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $bootcampsStmt = $pdo->query('SELECT b.id, b.title, s.school_name FROM bootcamps b LEFT JOIN schools s ON s.id = b.school_id ORDER BY s.school_name, b.title');
    $bootcamps = $bootcampsStmt->fetchAll(PDO::FETCH_ASSOC);

    $coursesStmt = $pdo->query('SELECT course_id, course_name FROM courses ORDER BY course_name');
    $catalogCourses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $rows = [];
    $bootcamps = [];
    $catalogCourses = [];
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
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h2 class="h4 mb-1">Classes</h2>
                        <p class="mb-0 opacity-75">Manage class offerings for each bootcamp.</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#classModal">Add Class</button>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo e($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <div class="table-card">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>School</th>
                                <th>Bootcamp</th>
                                <th>Course</th>
                                <th>Level</th>
                                <th>Capacity</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?php echo e($row['school_name'] ?: '-'); ?></td>
                                    <td><?php echo e($row['bootcamp_title'] ?: '-'); ?></td>
                                    <td><?php echo e($row['course_name'] ?: 'Untitled'); ?></td>
                                    <td><?php echo e($row['level'] ?: '-'); ?></td>
                                    <td><?php echo e($row['capacity'] ?? 100); ?></td>
                                    <td>
                                        <span class="badge <?php echo (int) ($row['status'] ?? 1) ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'; ?>">
                                            <?php echo (int) ($row['status'] ?? 1) ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="classes.php?edit=<?php echo (int) $row['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <form method="post" action="classes.php" onsubmit="return confirm('Delete this class?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="classModal" tabindex="-1" aria-labelledby="classModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="classes.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="classModalLabel"><?php echo $editing ? 'Edit Class' : 'Add New Class'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?php echo e($editing['id'] ?? ''); ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bootcamp</label>
                            <select class="form-select" name="bootcamp_id" required>
                                <option value="">Select a bootcamp</option>
                                <?php foreach ($bootcamps as $bootcamp): ?>
                                    <option value="<?php echo e($bootcamp['id']); ?>" <?php echo (($editing['bootcamp_id'] ?? '') == $bootcamp['id']) ? 'selected' : ''; ?>><?php echo e($bootcamp['school_name'] . ' - ' . $bootcamp['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Course Name</label>
                            <select class="form-select" name="course_name" required>
                                <option value="">Select a course</option>
                                <?php foreach ($catalogCourses as $course): ?>
                                    <option value="<?php echo e($course['course_name']); ?>" <?php echo (($editing['course_name'] ?? '') === $course['course_name']) ? 'selected' : ''; ?>><?php echo e($course['course_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Level</label>
                            <select class="form-select" name="level">
                                <option value="">Select level</option>
                                <?php foreach (['Upper Primary','JSS1','JSS2','JSS3','SS1','SS2','SS3'] as $levelOption): ?>
                                    <option value="<?php echo e($levelOption); ?>" <?php echo (($editing['level'] ?? '') === $levelOption) ? 'selected' : ''; ?>><?php echo e($levelOption); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Duration</label>
                            <input type="text" class="form-control" name="duration" value="<?php echo e($editing['duration'] ?? ''); ?>" placeholder="e.g. 6 Weeks">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Capacity</label>
                            <input type="number" class="form-control" name="capacity" min="1" value="<?php echo e($editing['capacity'] ?? 100); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="1" <?php echo (($editing['status'] ?? 1) == 1) ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo (($editing['status'] ?? 1) == 0) ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"><?php echo e($editing['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Save Changes' : 'Add Class'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php if ($showModal): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = new bootstrap.Modal(document.getElementById('classModal'));
    modal.show();
});
</script>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
