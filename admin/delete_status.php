<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$data = json_decode(file_get_contents('php://input'), true);
$status_id = $data['status_id'] ?? null;

if ($status_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM order_statuses WHERE id = ?");
        $stmt->execute([$status_id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid status ID']);
}