<?php
require_once 'db.php';

header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'logged_in' => true,
        'user_id' => $_SESSION['user_id'],
        'user_name' => $_SESSION['user_name'],
        'user_email' => $_SESSION['user_email'],
        'user_role' => $_SESSION['user_role']
    ]);
} else {
    echo json_encode([
        'logged_in' => false
    ]);
}
?>
