<?php
require '../config/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
header('Location: dashboard.php');
exit;
