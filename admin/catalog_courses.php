<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';

$pageTitle = 'Catalog Courses';
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
            $pdo->prepare('DELETE FROM courses WHERE course_id = ?')->execute([$id]);
            $_SESSION['flash_message'] = 'Course deleted successfully.';
            header('Location: catalog_courses.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Unable to delete course.';
            header('Location: catalog_courses.php');
            exit;
        }
    }

    $courseName = trim((string) ($_POST['course_name'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $credits = filter_input(INPUT_POST, 'credits', FILTER_VALIDATE_INT);
    $coursePrice = filter_input(INPUT_POST, 'course_price', FILTER_VALIDATE_FLOAT);

    if ($courseName === '' || $credits === false || $coursePrice === false) {
        $_SESSION['flash_error'] = 'Please complete all required course fields.';
        header('Location: catalog_courses.php');
        exit;
    }

    try {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE courses SET course_name = ?, description = ?, credits = ?, course_price = ? WHERE course_id = ?');
            $stmt->execute([$courseName, $description, $credits, $coursePrice, $id]);
            $_SESSION['flash_message'] = 'Course updated successfully.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO courses (course_name, description, credits, course_price) VALUES (?, ?, ?, ?)');
            $stmt->execute([$courseName, $description, $credits, $coursePrice]);
            $_SESSION['flash_message'] = 'Course added successfully.';
        }

        header('Location: catalog_courses.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Unable to save course right now.';
        header('Location: catalog_courses.php');
        exit;
    }
}

if ($editId) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM courses WHERE course_id = ? LIMIT 1');
        $stmt->execute([$editId]);
        $editing = $stmt->fetch(PDO::FETCH_ASSOC);
        $showModal = true;
    } catch (Exception $e) {
        $editing = null;
    }
}

try {
    $stmt = $pdo->query('SELECT * FROM courses ORDER BY course_id DESC');
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
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h2 class="h4 mb-1">Courses Catalog</h2>
                        <p class="mb-0 opacity-75">Add the courses that are available for bootcamp classes.</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal">Add Course</button>
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
                                <th>Course Name</th>
                                <th>Description</th>
                                <th>Credits</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?php echo e($row['course_name'] ?: 'Untitled'); ?></td>
                                    <td><?php echo e($row['description'] ?: '-'); ?></td>
                                    <td><?php echo e($row['credits'] ?? 0); ?></td>
                                    <td><?php echo e(number_format((float) ($row['course_price'] ?? 0), 2)); ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="catalog_courses.php?edit=<?php echo (int) $row['course_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <form method="post" action="catalog_courses.php" onsubmit="return confirm('Delete this course?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo (int) $row['course_id']; ?>">
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

<div class="modal fade" id="courseModal" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="catalog_courses.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="courseModalLabel"><?php echo $editing ? 'Edit Course' : 'Add New Course'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?php echo e($editing['course_id'] ?? ''); ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Course Name</label>
                            <input type="text" class="form-control" name="course_name" value="<?php echo e($editing['course_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"><?php echo e($editing['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Credits</label>
                            <input type="number" class="form-control" name="credits" min="0" value="<?php echo e($editing['credits'] ?? 0); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" name="course_price" step="0.01" min="0" value="<?php echo e($editing['course_price'] ?? 0); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Save Changes' : 'Add Course'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php if ($showModal): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = new bootstrap.Modal(document.getElementById('courseModal'));
    modal.show();
});
</script>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
