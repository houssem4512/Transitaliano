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

// Fetch user info
$user = null;
$stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) $user = $result->fetch_assoc();
$stmt->close();

// Fetch translations
$translations = [];
$stmt = $conn->prepare("SELECT id, file_path, status, upload_date FROM translations WHERE user_id=? ORDER BY upload_date DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $translations[] = $row;
$stmt->close();

// Fetch appointments
$appointments = [];
$stmt = $conn->prepare("SELECT id, appointment_date, status FROM appointments WHERE user_id=? ORDER BY appointment_date DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $appointments[] = $row;
$stmt->close();

// Fetch latest 3 notifications
$notifications = [];
$stmt = $conn->prepare("SELECT id, content, link, type, is_read, priority, created_at 
    FROM notifications 
    WHERE user_id=? 
    ORDER BY priority ASC, created_at DESC 
    LIMIT 3");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $notifications[] = $row;
$stmt->close();

// Count unread notifications
$stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id=? AND is_read=0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$unread_count = ($res->num_rows>0)? $res->fetch_assoc()['unread_count'] : 0;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Tableau de bord - Client</title>
<link rel="stylesheet" href="/css/bootstrap.min.css" />
<link rel="stylesheet" href="/css/custom.css" />
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet" />
<style>
/* Sidebar logo */
.sidebar-header {
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
}
.sidebar-header img {
    max-width: 150px;
    max-height: 60px;
    display: block;
}

/* Notification badge */
.notification-badge { position:absolute; top:5px; right:5px; font-size:12px; background:red; color:white; padding:2px 6px; border-radius:50%; }
.notification-icon { position: relative; }
.notification-item.font-weight-bold { font-weight:bold; }
#toast-container { position: fixed; top: 70px; right: 20px; z-index: 1050; }

/* Remove blue highlight on sidebar links */
#sidebar ul li a, #sidebar ul li.active a {
    background: none !important;
    color: inherit !important;
    box-shadow: none !important;
}
#sidebar ul li a:hover, #sidebar ul li a:focus {
    background: none !important;
    color: inherit !important;
    box-shadow: none !important;
}
</style>
</head>
<body>
<div class="wrapper">
<div class="body-overlay"></div>

<!-- Sidebar -->
<nav id="sidebar">
    <div class="sidebar-header">
        <img src="/img/logo2.png" alt="logo" />
    </div>
    <ul class="list-unstyled components">
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php"><i class="material-icons">dashboard</i><span>Tableau de bord</span></a></li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>"><a href="profile.php"><i class="material-icons">person</i><span>Profil</span></a></li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'upload_translation.php' ? 'active' : '' ?>"><a href="upload_translation.php"><i class="material-icons">file_upload</i><span>Déposer un fichier</span></a></li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : '' ?>"><a href="appointments.php"><i class="material-icons">event</i><span>Rendez-vous</span></a></li>
        <li class="notification-icon <?= basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : '' ?>">
            <a href="notifications.php">
                <i class="material-icons">notifications</i>
                <span id="notif-count" class="notification-badge"><?php echo ($unread_count>0?$unread_count:''); ?></span>
                <span>Notifications</span>
            </a>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'translated_list.php' ? 'active' : '' ?>"><a href="translated_list.php"><i class="material-icons">description</i><span>Fichiers traduits</span></a></li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'paiment.php' ? 'active' : '' ?>"><a href="paiment.php"><i class="material-icons">payment</i><span>Paiement</span></a></li>
    </ul>
    <a href="logout.php" class="btn btn-danger mt-3 mx-3">Déconnexion</a>
</nav>

<!-- Page content -->
<div id="content">
<div class="top-navbar">
<nav class="navbar navbar-expand-lg">
<div class="container-fluid">
<button type="button" id="sidebarCollapse" class="d-xl-block d-lg-block d-md-none d-none"><span class="material-icons">arrow_back_ios</span></button>
<a class="navbar-brand" href="#">Tableau de bord client</a>
<button class="d-inline-block d-lg-none ml-auto more-button" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"><span class="material-icons">more_vert</span></button>

<div class="collapse navbar-collapse d-lg-block d-xl-block d-sm-none d-md-none d-none" id="navbarSupportedContent">
<ul class="nav navbar-nav ml-auto">
    <li class="nav-item notification-icon">
        <a class="nav-link" href="notifications.php">
            <i class="material-icons">notifications</i>
            <span id="notif-count-top" class="notification-badge"><?php echo ($unread_count>0?$unread_count:''); ?></span>
        </a>
    </li>
    <li class="nav-item"><a class="nav-link" href="profile.php"><span class="material-icons">person</span></a></li>
    <li class="nav-item"><a class="nav-link" href="index.php"><span class="material-icons">settings</span></a></li>
</ul>
</div>
</div>
</nav>
</div>

<div class="main-content container-fluid">
<h3>Bienvenue, <?php echo e($username); ?>!</h3>
<div class="row">

<!-- Translations -->
<div class="col-lg-7 col-md-12">
    <div class="card" style="min-height: 400px;">
        <div class="card-header card-header-text">
            <h4 class="card-title">Mes fichiers soumis</h4>
            <p class="category">Suivez vos traductions</p>
        </div>
        <div class="card-content table-responsive">
            <table class="table table-hover" id="translations-table">
                <thead class="text-primary">
                    <tr><th>ID</th><th>Fichier</th><th>Statut</th><th>Date d'envoi</th><th>Voir</th></tr>
                </thead>
                <tbody>
                    <?php if(count($translations)===0): ?>
                        <tr><td colspan="5" class="text-center">Aucun fichier soumis.</td></tr>
                    <?php else: foreach($translations as $t): ?>
                        <tr>
                            <td><?php echo e($t['id']); ?></td>
                            <td><?php echo e(basename($t['file_path'])); ?></td>
                            <td>
                                <?php 
                                $statusClass = '';
                                if($t['status']=='Pending') $statusClass='badge badge-warning';
                                elseif($t['status']=='Completed') $statusClass='badge badge-success';
                                elseif($t['status']=='Rejected') $statusClass='badge badge-danger';
                                ?>
                                <span class="<?php echo $statusClass; ?>"><?php echo e($t['status']); ?></span>
                            </td>
                            <td><?php echo e($t['upload_date']); ?></td>
                            <td><?php if(!empty($t['file_path'])): ?><a href="<?php echo e($t['file_path']); ?>" target="_blank" class="btn btn-sm btn-info">Voir</a><?php else: ?>N/A<?php endif; ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Appointments + Notifications -->
<div class="col-lg-5 col-md-12">
    <div class="card" style="min-height: 400px;">
        <div class="card-header card-header-text">
            <h4 class="card-title">Mes rendez-vous</h4>
            <p class="category">Prochains rendez-vous</p>
        </div>
        <div class="card-content">
            <div class="streamline">
                <?php
                $status_fr = [
                    "Pending"   => "En attente",
                    "Confirmed" => "Confirmé",
                    "Cancelled" => "Annulé",
                    "Upcoming"  => "À venir",
                    "Completed" => "Terminé"
                ];
                ?>

                <?php if(count($appointments)===0): ?>
                    <p>Aucun rendez-vous programmé.</p>
                <?php else: foreach($appointments as $a): 
                    $appClass='';
                    if($a['status']=='Pending') $appClass='text-warning';
                    elseif($a['status']=='Confirmed') $appClass='text-info';
                    elseif($a['status']=='Cancelled') $appClass='text-danger';
                    elseif($a['status']=='Completed') $appClass='text-primary';
                    elseif($a['status']=='Upcoming') $appClass='text-success';
                ?>
                    <div class="sl-item">
                        <div class="sl-content">
                            <small class="text-muted"><?php echo e($a['appointment_date']); ?></small>
                            <p class="<?php echo $appClass; ?>">
                                <?php echo e($status_fr[$a['status']] ?? $a['status']); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>

        <!-- Notifications -->
        <div class="card mt-3">
            <div class="card-header card-header-text"><h4 class="card-title">Notifications récentes</h4><p class="category">Vos dernières notifications</p></div>
            <div class="card-content">
                <ul class="list-group">
                    <?php if(count($notifications)===0): ?>
                        <li class="list-group-item text-center">Aucune notification.</li>
                    <?php else: foreach($notifications as $n): ?>
                        <li class="list-group-item <?php echo ($n['is_read']==0?'font-weight-bold':''); ?>">
                            <a href="<?php echo e($n['link'] ?: '#'); ?>"><?php echo e($n['content']); ?></a>
                            <br/><small class="text-muted"><?php echo e($n['created_at']); ?></small>
                        </li>
                    <?php endforeach; endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<footer class="footer mt-4">
<div class="container-fluid">
<div class="row">
<div class="col-md-6">
<nav class="d-flex">
<ul class="m-0 p-0" style="list-style:none; display:flex; gap:15px;">
<li><a href="#"></a></li>
<li><a href="#"></a></li>
<li><a href="#"></a></li>
<li><a href="#"></a></li>
</ul>
</nav>
</div>
<div class="col-md-6 text-right"><p class="copyright">&copy; <?php echo date('Y'); ?> Traditaliano</p></div>
</div>
</div>
</footer>
</div>
</div>

<div id="toast-container"></div>

<script src="assets/js/jquery-3.3.1.min.js"></script>
<script src="assets/js/popper.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script>
$(document).ready(function(){
    $("#sidebarCollapse").on("click",function(){$("#sidebar,#content").toggleClass("active");});
    $(".more-button,.body-overlay").on("click",function(){$("#sidebar,.body-overlay").toggleClass("show-nav");});
});
</script>
</body>
</html>

