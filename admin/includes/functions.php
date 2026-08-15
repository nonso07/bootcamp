<?php
function getDashboardStats($pdo) {
    $stats = [
        'registrations' => 0,
        'paid_students' => 0,
        'pending_payments' => 0,
        'revenue' => 0,
        'courses' => 0,
        'schools' => 0,
    ];

    try {
        $stats['registrations'] = (int) $pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
        $stats['paid_students'] = (int) $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'paid'")->fetchColumn();
        $stats['pending_payments'] = (int) $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'")->fetchColumn();
        $stats['revenue'] = (float) $pdo->query('SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = "paid"')->fetchColumn();
        $stats['courses'] = (int) $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();
        $stats['schools'] = (int) $pdo->query('SELECT COUNT(*) FROM parents')->fetchColumn();
    } catch (Exception $e) {
        $stats = [
            'registrations' => 0,
            'paid_students' => 0,
            'pending_payments' => 0,
            'revenue' => 0,
            'courses' => 0,
            'schools' => 0,
        ];
    }

    return $stats;
}

function getRecentRegistrations($pdo, $limit = 8) {
    try {
        $stmt = $pdo->prepare('SELECT r.*, CONCAT_WS(" ", s.first_name, s.last_name) AS student_name, cl.course_name FROM registrations r LEFT JOIN students s ON s.id = r.student_id LEFT JOIN classes cl ON cl.id = r.course_id ORDER BY r.created_at DESC LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function getRecentTransactions($pdo, $limit = 8) {
    try {
        $stmt = $pdo->prepare('SELECT t.*, CONCAT_WS(" ", s.first_name, s.last_name) AS student_name FROM transaction_logs t LEFT JOIN students s ON s.id = t.student_id ORDER BY t.created_at DESC LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function getActivityFeed($pdo, $limit = 8) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}
