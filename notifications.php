<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'], $_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

function e($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

// Handle mark as read request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_read_id'])) {
        $note_id = (int)$_POST['mark_read_id'];
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $note_id, $user_id);
        $stmt->execute();
        $stmt->close();
        exit('success');
    }
    if (isset($_POST['delete_id'])) {
        $note_id = (int)$_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $note_id, $user_id);
        $stmt->execute();
        $stmt->close();
        exit('success');
    }
}

// Fetch notifications (latest 20, not expired)
$now = date('Y-m-d H:i:s');
$stmt = $conn->prepare("SELECT id, content, link, type, is_read, priority, created_at, expires_at 
                        FROM notifications 
                        WHERE user_id = ? AND (expires_at IS NULL OR expires_at > ?) 
                        ORDER BY priority ASC, created_at DESC 
                        LIMIT 20");
$stmt->bind_param("is", $user_id, $now);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while ($row = $result->fetch_assoc()) $notifications[] = $row;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Notifications - <?php echo e($username); ?></title>
<link rel="stylesheet" href="/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
<style>
body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; margin:0; padding:0; }
.container { max-width: 900px; margin:50px auto; }
.btn-back { display:inline-block; background:#38d39f; color:#fff; padding:8px 14px; border-radius:8px; font-weight:600; text-decoration:none; transition:0.3s; }
.btn-back:hover { background:#2bb58f; text-decoration:none; }
.card { background:#fff; border-radius:10px; box-shadow:0 3px 15px rgba(0,0,0,0.08); padding:20px; }
.card-header { display:flex; justify-content:space-between; align-items:center; }
.card-header h4 { margin:0; color:#333; }
.notification-item { padding:12px 15px; border-bottom:1px solid #eee; cursor:pointer; transition:background 0.2s; border-radius:6px; position:relative; }
.notification-item:hover { background:#f1fff8; }
.notification-unread { font-weight:700; background:#d8fce9; }
.notification-link { text-decoration:none; color:inherit; display:block; }
.notification-date { font-size:0.85rem; color:#777; }
.btn-clear, .btn-delete { background:#38d39f; color:#fff; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-weight:600; transition:0.3s; }
.btn-clear:hover, .btn-delete:hover { background:#2bb58f; }
.btn-delete { position:absolute; top:10px; right:10px; font-size:0.85rem; }
</style>
</head>
<body>
<div class="container">
    <div style="margin-bottom:20px;">
        <a href="dashboard.php" class="btn-back">← Retour au tableau de bord</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Notifications récentes</h4>
            <button id="markAllRead" class="btn-clear">Tout marquer lu</button>
        </div>
        <div class="card-content">
            <div id="notificationList">
                <?php if(empty($notifications)): ?>
                    <p class="text-center" style="margin:20px 0;">Aucune notification.</p>
                <?php else: ?>
                    <?php foreach($notifications as $note): ?>
                        <div class="notification-item <?php echo ($note['is_read']==0?'notification-unread':''); ?>" data-id="<?php echo e($note['id']); ?>">
                            <a href="<?php echo e($note['link']?:'#'); ?>" class="notification-link">
                                <?php echo e($note['content']); ?>
                                <div class="notification-date"><?php echo e(date('d/m/Y H:i', strtotime($note['created_at']))); ?></div>
                            </a>
                            <button class="btn-delete" data-id="<?php echo e($note['id']); ?>">Supprimer</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="/js/jquery-3.3.1.min.js"></script>
<script>
$(document).ready(function(){
    $(".notification-item").on("click", function(e){
        if($(e.target).hasClass('btn-delete')) return;
        var id = $(this).data("id");
        var el = $(this);
        if(el.hasClass("notification-unread")){
            $.post("notifications.php", { mark_read_id:id }, function(resp){
                if(resp==='success') el.removeClass("notification-unread");
            });
        }
    });

    $("#markAllRead").on("click", function(){
        $(".notification-unread").each(function(){
            var id = $(this).data("id");
            var el = $(this);
            $.post("notifications.php", { mark_read_id:id }, function(resp){
                if(resp==='success') el.removeClass("notification-unread");
            });
        });
    });

    $(".btn-delete").on("click", function(){
        var id = $(this).data("id");
        var el = $(this).closest(".notification-item");
        $.post("notifications.php", { delete_id:id }, function(resp){
            if(resp==='success') el.remove();
        });
    });
});
</script>
</body>
</html>
