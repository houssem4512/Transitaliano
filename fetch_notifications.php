<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, content, link, type, is_read, priority, created_at 
                        FROM notifications 
                        WHERE user_id = ? 
                        ORDER BY priority ASC, created_at DESC 
                        LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$notifications = [];
while ($row = $res->fetch_assoc()) {
    $notifications[] = $row;
}

$stmt->close();
echo json_encode($notifications);
