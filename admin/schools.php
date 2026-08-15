<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';

$pageTitle = 'Schools';
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
            $pdo->prepare('DELETE FROM schools WHERE id = ?')->execute([$id]);
            $_SESSION['flash_message'] = 'School deleted successfully.';
            header('Location: schools.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Unable to delete school.';
            header('Location: schools.php');
            exit;
        }
    }

    $schoolName = trim((string) ($_POST['school_name'] ?? ''));
    $contactPerson = trim((string) ($_POST['contact_person'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $address = trim((string) ($_POST['address'] ?? ''));
    $logo = trim((string) ($_POST['logo'] ?? ''));
    $status = (string) ($_POST['status'] ?? '1');
    $status = in_array($status, ['0', '1'], true) ? $status : '1';

    if ($schoolName === '') {
        $_SESSION['flash_error'] = 'School name is required.';
        header('Location: schools.php');
        exit;
    }

    try {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE schools SET school_name = ?, contact_person = ?, phone = ?, email = ?, address = ?, logo = ?, status = ? WHERE id = ?');
            $stmt->execute([$schoolName, $contactPerson, $phone, $email, $address, $logo, (int) $status, $id]);
            $_SESSION['flash_message'] = 'School updated successfully.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO schools (school_name, contact_person, phone, email, address, logo, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$schoolName, $contactPerson, $phone, $email, $address, $logo, (int) $status]);
            $_SESSION['flash_message'] = 'School added successfully.';
        }

        header('Location: schools.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Unable to save school right now.';
        header('Location: schools.php');
        exit;
    }
}

if ($editId) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM schools WHERE id = ? LIMIT 1');
        $stmt->execute([$editId]);
        $editing = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $editing = null;
    }
}

try {
    $stmt = $pdo->query('SELECT * FROM schools ORDER BY id DESC');
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
                        <h2 class="h4 mb-1">Schools</h2>
                        <p class="mb-0 opacity-75">Manage school records and their contact information.</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#schoolModal">Add School</button>
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
                                <th>Contact</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?php echo e($row['school_name'] ?: 'Unnamed'); ?></td>
                                    <td><?php echo e($row['contact_person'] ?: '-'); ?></td>
                                    <td><?php echo e($row['phone'] ?: '-'); ?></td>
                                    <td><?php echo e($row['email'] ?: '-'); ?></td>
                                    <td>
                                        <span class="badge <?php echo (int) ($row['status'] ?? 1) ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'; ?>">
                                            <?php echo (int) ($row['status'] ?? 1) ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="schools.php?edit=<?php echo (int) $row['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <form method="post" action="schools.php" onsubmit="return confirm('Delete this school?');">
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

<div class="modal fade" id="schoolModal" tabindex="-1" aria-labelledby="schoolModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="schools.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="schoolModalLabel"><?php echo $editing ? 'Edit School' : 'Add New School'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?php echo e($editing['id'] ?? ''); ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">School Name</label>
                            <input type="text" class="form-control" name="school_name" value="<?php echo e($editing['school_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Person</label>
                            <input type="text" class="form-control" name="contact_person" value="<?php echo e($editing['contact_person'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" value="<?php echo e($editing['phone'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo e($editing['email'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Logo</label>
                            <input type="text" class="form-control" name="logo" placeholder="logo.png or /uploads/logo.png" value="<?php echo e($editing['logo'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="1" <?php echo (($editing['status'] ?? 1) == 1) ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo (($editing['status'] ?? 1) == 0) ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="3"><?php echo e($editing['address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Save Changes' : 'Add School'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php if ($editing): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = new bootstrap.Modal(document.getElementById('schoolModal'));
    modal.show();
});
</script>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
