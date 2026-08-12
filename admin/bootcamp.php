<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';

$pageTitle = 'Bootcamp';
$message = $_SESSION['flash_message'] ?? null;
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_message'], $_SESSION['flash_error']);

$editing = null;
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($action === 'delete' && $id) {
        try {
            $pdo->prepare('DELETE FROM bootcamps WHERE id = ?')->execute([$id]);
            $_SESSION['flash_message'] = 'Bootcamp deleted successfully.';
            header('Location: bootcamp.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Unable to delete bootcamp.';
            header('Location: bootcamp.php');
            exit;
        }
    }

    if ($action === 'end' && $id) {
        try {
            $pdo->prepare('UPDATE bootcamps SET status = ? WHERE id = ?')->execute(['Completed', $id]);
            $_SESSION['flash_message'] = 'Bootcamp ended successfully. You may now add another bootcamp for the same school.';
            header('Location: bootcamp.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Unable to end bootcamp.';
            header('Location: bootcamp.php');
            exit;
        }
    }

    $schoolId = filter_input(INPUT_POST, 'school_id', FILTER_VALIDATE_INT);
    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $venue = trim((string) ($_POST['venue'] ?? ''));
    $startDate = trim((string) ($_POST['start_date'] ?? ''));
    $endDate = trim((string) ($_POST['end_date'] ?? ''));
    $earlyBirdFee = trim((string) ($_POST['early_bird_fee'] ?? ''));
    $regularFee = trim((string) ($_POST['regular_fee'] ?? ''));
    $earlyBirdDeadline = trim((string) ($_POST['early_bird_deadline'] ?? ''));
    $registrationDeadline = trim((string) ($_POST['registration_deadline'] ?? ''));
    $status = trim((string) ($_POST['status'] ?? 'Upcoming'));

    if ($title === '' || !$schoolId) {
        $_SESSION['flash_error'] = 'Please provide a bootcamp title and select a school.';
        header('Location: bootcamp.php');
        exit;
    }

    try {
        $existsQuery = 'SELECT id FROM bootcamps WHERE school_id = ? AND status != ?';
        $params = [$schoolId, 'Completed'];
        if ($id) {
            $existsQuery .= ' AND id != ?';
            $params[] = $id;
        }
        $existsQuery .= ' LIMIT 1';

        $existsStmt = $pdo->prepare($existsQuery);
        $existsStmt->execute($params);
        if ($existsStmt->fetchColumn()) {
            $_SESSION['flash_error'] = 'This school already has an active bootcamp assigned. End the existing bootcamp before adding another.';
            header('Location: bootcamp.php');
            exit;
        }

        if ($id) {
            $stmt = $pdo->prepare('UPDATE bootcamps SET school_id = ?, title = ?, description = ?, venue = ?, start_date = ?, end_date = ?, early_bird_fee = ?, regular_fee = ?, early_bird_deadline = ?, registration_deadline = ?, status = ? WHERE id = ?');
            $stmt->execute([$schoolId, $title, $description, $venue, $startDate ?: null, $endDate ?: null, $earlyBirdFee ?: null, $regularFee ?: null, $earlyBirdDeadline ?: null, $registrationDeadline ?: null, $status, $id]);
            $_SESSION['flash_message'] = 'Bootcamp updated successfully.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO bootcamps (school_id, title, description, venue, start_date, end_date, early_bird_fee, regular_fee, early_bird_deadline, registration_deadline, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$schoolId, $title, $description, $venue, $startDate ?: null, $endDate ?: null, $earlyBirdFee ?: null, $regularFee ?: null, $earlyBirdDeadline ?: null, $registrationDeadline ?: null, $status]);
            $_SESSION['flash_message'] = 'Bootcamp added successfully.';
        }

        header('Location: bootcamp.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Unable to save bootcamp right now.';
        header('Location: bootcamp.php');
        exit;
    }
}

if ($editId) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM bootcamps WHERE id = ? LIMIT 1');
        $stmt->execute([$editId]);
        $editing = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $editing = null;
    }
}

try {
    $stmt = $pdo->query('SELECT b.*, s.school_name FROM bootcamps b LEFT JOIN schools s ON s.id = b.school_id ORDER BY b.id DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $schoolsStmt = $pdo->query('SELECT id, school_name FROM schools WHERE status = 1 ORDER BY school_name');
    $schools = $schoolsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $rows = [];
    $schools = [];
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
                        <h2 class="h4 mb-1">Bootcamp</h2>
                        <p class="mb-0 opacity-75">Create and manage the bootcamp linked to a school.</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bootcampModal">Add Bootcamp</button>
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
                                <th>Venue</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?php echo e($row['school_name'] ?: '-'); ?></td>
                                    <td><?php echo e($row['title'] ?: 'Untitled'); ?></td>
                                    <td><?php echo e($row['venue'] ?: '-'); ?></td>
                                    <td>
                                        <?php if (trim((string) $row['status']) === 'Completed'): ?>
                                            <span class="badge bg-success-subtle text-success"><?php echo e($row['status']); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-primary-subtle text-primary"><?php echo e($row['status'] ?: 'Upcoming'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="bootcamp.php?edit=<?php echo (int) $row['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <?php if (trim((string) $row['status']) !== 'Completed'): ?>
                                            <form method="post" action="bootcamp.php" onsubmit="return confirm('End this bootcamp? This will allow adding another bootcamp for the same school.');">
                                                <input type="hidden" name="action" value="end">
                                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-warning">End</button>
                                            </form>
                                            <?php endif; ?>
                                            <form method="post" action="bootcamp.php" onsubmit="return confirm('Delete this bootcamp?');">
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

<div class="modal fade" id="bootcampModal" tabindex="-1" aria-labelledby="bootcampModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="bootcamp.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="bootcampModalLabel"><?php echo $editing ? 'Edit Bootcamp' : 'Add New Bootcamp'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?php echo e($editing['id'] ?? ''); ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">School</label>
                            <select class="form-select" name="school_id" required>
                                <option value="">Select a school</option>
                                <?php foreach ($schools as $school): ?>
                                    <option value="<?php echo e($school['id']); ?>" <?php echo (($editing['school_id'] ?? '') == $school['id']) ? 'selected' : ''; ?>><?php echo e($school['school_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" value="<?php echo e($editing['title'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Venue</label>
                            <input type="text" class="form-control" name="venue" value="<?php echo e($editing['venue'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="Upcoming" <?php echo (($editing['status'] ?? 'Upcoming') === 'Upcoming') ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="Open" <?php echo (($editing['status'] ?? 'Upcoming') === 'Open') ? 'selected' : ''; ?>>Open</option>
                                <option value="Closed" <?php echo (($editing['status'] ?? 'Upcoming') === 'Closed') ? 'selected' : ''; ?>>Closed</option>
                                <option value="Completed" <?php echo (($editing['status'] ?? 'Upcoming') === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" value="<?php echo e($editing['start_date'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" value="<?php echo e($editing['end_date'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Early Bird Fee</label>
                            <input type="number" class="form-control" name="early_bird_fee" step="0.01" value="<?php echo e($editing['early_bird_fee'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Regular Fee</label>
                            <input type="number" class="form-control" name="regular_fee" step="0.01" value="<?php echo e($editing['regular_fee'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Early Bird Deadline</label>
                            <input type="date" class="form-control" name="early_bird_deadline" value="<?php echo e($editing['early_bird_deadline'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Registration Deadline</label>
                            <input type="date" class="form-control" name="registration_deadline" value="<?php echo e($editing['registration_deadline'] ?? ''); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"><?php echo e($editing['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Save Changes' : 'Add Bootcamp'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php if ($editing): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = new bootstrap.Modal(document.getElementById('bootcampModal'));
    modal.show();
});
</script>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
