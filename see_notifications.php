<?php
session_start();
include 'db.php';

if (!isset($_GET['user_id'])) exit;

$user_id = intval($_GET['user_id']);
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

while (true) {
    $stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id=? AND is_read=0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $count = ($res->num_rows > 0) ? $res->fetch_assoc()['unread_count'] : 0;
    $stmt->close();

    echo "data: ".json_encode(['unread_count'=>$count])."\n\n";
    ob_flush();
    flush();
    sleep(5); // adjust refresh interval
}
