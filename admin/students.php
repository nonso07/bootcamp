<?php
require '../config/config.php';
require '../config/database.php';
require 'includes/auth.php';

$pageTitle = 'Students';
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
                    <table class="table align-middle admin-datatable">
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td class="align-middle" style="white-space:nowrap;">
                                        <?php if (!empty($row['photo'])): ?>
                                            <img src="<?php echo e('../uploads/students/' . $row['photo']); ?>" alt="Student Photo" style="width:42px;height:42px;object-fit:cover;border-radius:50%;">
                                        <?php else: ?>
                                            <div style="width:42px;height:42px;border-radius:50%;background:#e2e8f0;color:#0f172a;font-weight:700;display:inline-flex;align-items:center;justify-content:center;font-size:0.95rem;"><?php echo e(substr($row['student_name'], 0, 1)); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($row['student_name'] ?: 'Unknown'); ?></td>
                                    <td><?php echo e($row['parent_name'] ?: 'Unknown'); ?></td>
                                    <td><?php echo e($row['parent_email'] ?: '-'); ?></td>
                                    <td><?php echo e($row['parent_phone'] ?: '-'); ?></td>
                                    <td><?php echo e($row['gender'] ?: '-'); ?></td>
                                    <td><?php echo e($row['school_name'] ?: '-'); ?></td>
                                    <td><?php echo e($row['class_level'] ?: '-'); ?></td>
                                    <td><?php echo e($row['course_name'] ?: '-'); ?></td>
                                    <td><?php echo e($row['registration_no'] ?: '-'); ?></td>
                                    <td><span class="badge bg-success-subtle text-success"><?php echo e($row['registration_status'] ?: 'Unknown'); ?></span></td>
                                    <td><?php echo e(substr($row['registration_date'] ?? '', 0, 10)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
