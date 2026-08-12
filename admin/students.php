<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';

$pageTitle = 'Students';
$message = $_SESSION['flash_message'] ?? null;
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_message'], $_SESSION['flash_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_student') {
    $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
    $parentName = trim((string) ($_POST['parent_name'] ?? ''));
    $parentEmail = trim((string) ($_POST['parent_email'] ?? ''));
    $parentPhone = trim((string) ($_POST['parent_phone'] ?? ''));
    $gender = trim((string) ($_POST['gender'] ?? ''));
    $classLevel = trim((string) ($_POST['class_level'] ?? ''));
    $registrationStatus = trim((string) ($_POST['registration_status'] ?? ''));

    try {
        if (!$studentId) {
            throw new Exception('Invalid student selected.');
        }

        $stmt = $pdo->prepare('SELECT parent_id FROM students WHERE id = ? LIMIT 1');
        $stmt->execute([$studentId]);
        $studentRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$studentRow) {
            throw new Exception('Student not found.');
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare('UPDATE students SET gender = ?, class_level = ?, registration_status = ? WHERE id = ?');
        $stmt->execute([$gender, $classLevel, $registrationStatus, $studentId]);

        if (!empty($studentRow['parent_id'])) {
            $stmt = $pdo->prepare('UPDATE parents SET fullname = ?, email = ?, phone = ? WHERE id = ?');
            $stmt->execute([$parentName, $parentEmail, $parentPhone, $studentRow['parent_id']]);
        }

        $pdo->commit();
        $_SESSION['flash_message'] = 'Student record updated successfully.';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['flash_error'] = 'Unable to update student record. Please try again.';
    }

    header('Location: students.php');
    exit;
}

$rows = [];
try {
    $stmt = $pdo->prepare(
        'SELECT
            s.id,
            CONCAT_WS(" ", s.first_name, s.last_name) AS student_name,
            s.registration_no,
            s.gender,
            s.class_level,
            s.registration_status,
            s.photo,
            sc.school_name,
            p.fullname AS parent_name,
            p.email AS parent_email,
            p.phone AS parent_phone,
            r.course_id,
            cl.course_name,
            r.registration_date
        FROM students s
        LEFT JOIN parents p ON p.id = s.parent_id
        LEFT JOIN registrations r ON r.id = (
            SELECT MAX(id) FROM registrations WHERE student_id = s.id
        )
        LEFT JOIN classes cl ON cl.id = r.course_id
        LEFT JOIN schools sc ON sc.id = s.school_id
        ORDER BY s.created_at DESC'
    );
    $stmt->execute();
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
                <h2 class="h4 mb-1">Students</h2>
                <p class="mb-0 opacity-75">Manage all registered learners from one place.</p>
            </div>
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Student Directory</h5>
                    <button class="btn btn-sm btn-outline-secondary">Export</button>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle admin-datatable" id="studentsTable">
                        <thead>
                            <tr>
                                <th>Profile</th>
                                <th>Student Name</th>
                                <th>Parent</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Gender</th>
                                <th>School</th>
                                <th>Class</th>
                                <th>Course</th>
                                <th>Registration No.</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr data-student-id="<?php echo e((int) $row['id']); ?>">
                                    <td class="align-middle" style="white-space:nowrap;">
                                        <?php if (!empty($row['photo'])): ?>
                                            <img src="<?php echo e('../uploads/students/' . $row['photo']); ?>" alt="Student Photo" style="width:42px;height:42px;object-fit:cover;border-radius:50%;">
                                        <?php else: ?>
                                            <div style="width:42px;height:42px;border-radius:50%;background:#e2e8f0;color:#0f172a;font-weight:700;display:inline-flex;align-items:center;justify-content:center;font-size:0.95rem;"><?php echo e(substr($row['student_name'], 0, 1)); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($row['student_name'] ?: 'Unknown'); ?></td>
                                    <td><span class="editable-cell" data-field="parent_name"><?php echo e($row['parent_name'] ?: 'Unknown'); ?></span></td>
                                    <td><span class="editable-cell" data-field="parent_email"><?php echo e($row['parent_email'] ?: '-'); ?></span></td>
                                    <td><span class="editable-cell" data-field="parent_phone"><?php echo e($row['parent_phone'] ?: '-'); ?></span></td>
                                    <td><span class="editable-cell" data-field="gender"><?php echo e($row['gender'] ?: '-'); ?></span></td>
                                    <td><?php echo e($row['school_name'] ?: '-'); ?></td>
                                    <td><span class="editable-cell" data-field="class_level"><?php echo e($row['class_level'] ?: '-'); ?></span></td>
                                    <td><?php echo e($row['course_name'] ?: '-'); ?></td>
                                    <td><?php echo e($row['registration_no'] ?: '-'); ?></td>
                                    <td><span class="editable-cell" data-field="registration_status"><?php echo e($row['registration_status'] ?: 'Unknown'); ?></span></td>
                                    <td><?php echo e(substr($row['registration_date'] ?? '', 0, 10)); ?></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-primary edit-student-btn">Edit</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
(function () {
    const table = document.getElementById('studentsTable');
    const editableFields = {
        parent_name: 'text',
        parent_email: 'email',
        parent_phone: 'text',
        gender: 'text',
        class_level: 'text',
        registration_status: 'text'
    };

    function createInput(value, type) {
        const input = document.createElement('input');
        input.type = type;
        input.className = 'form-control form-control-sm';
        input.value = value === '-' ? '' : value;
        return input;
    }

    function createSelect(value, options) {
        const select = document.createElement('select');
        select.className = 'form-select form-select-sm';

        options.forEach(optionValue => {
            const option = document.createElement('option');
            option.value = optionValue;
            option.textContent = optionValue;
            if (optionValue === value) {
                option.selected = true;
            }
            select.appendChild(option);
        });

        return select;
    }

    function saveRow(row, studentId, updates) {
        const formData = new FormData();
        formData.append('action', 'update_student');
        formData.append('student_id', studentId);

        Object.entries(updates).forEach(([field, value]) => {
            formData.append(field, value);
        });

        fetch('students.php', {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) {
                throw new Error('Update failed');
            }
            return response.text();
        }).then(() => {
            window.location.reload();
        }).catch(() => {
            alert('Unable to save changes. Please try again.');
        });
    }

    function enterEditMode(row) {
        row.querySelectorAll('.editable-cell').forEach(cell => {
            const field = cell.getAttribute('data-field');
            if (!field || !editableFields[field]) return;

            const currentValue = cell.textContent.trim();
            let input;

            if (field === 'gender') {
                input = createSelect(currentValue === '-' ? '' : currentValue, ['Male', 'Female']);
            } else if (field === 'registration_status') {
                input = createSelect(currentValue === '-' ? '' : currentValue, ['draft', 'registered', 'awaiting_payment', 'payment_pending_validation', 'enrolled', 'completed', 'cancelled']);
            } else {
                const type = editableFields[field];
                input = createInput(currentValue, type);
            }

            cell.innerHTML = '';
            cell.appendChild(input);
        });

        const editButton = row.querySelector('.edit-student-btn');
        editButton.textContent = 'Save';
        editButton.classList.remove('btn-outline-primary');
        editButton.classList.add('btn-success');
        editButton.dataset.mode = 'save';
    }

    function exitEditMode(row) {
        row.querySelectorAll('.editable-cell').forEach(cell => {
            const input = cell.querySelector('input');
            if (!input) return;
            cell.textContent = input.value.trim() || '-';
        });

        const editButton = row.querySelector('.edit-student-btn');
        editButton.textContent = 'Edit';
        editButton.classList.remove('btn-success');
        editButton.classList.add('btn-outline-primary');
        editButton.dataset.mode = 'edit';
    }

    table.addEventListener('click', function (event) {
        const button = event.target.closest('.edit-student-btn');
        if (!button) return;

        const row = button.closest('tr');
        const studentId = row.getAttribute('data-student-id');
        const mode = button.dataset.mode || 'edit';

        if (mode === 'edit') {
            enterEditMode(row);
            return;
        }

        const updates = {};
        row.querySelectorAll('.editable-cell').forEach(cell => {
            const field = cell.getAttribute('data-field');
            const input = cell.querySelector('input');
            if (!field || !input) return;
            updates[field] = input.value.trim();
        });

        saveRow(row, studentId, updates);
    });
})();
</script>
<?php include 'includes/footer.php'; ?>
