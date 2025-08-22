<?php
// admin_dashboard.php
session_start();

// Vérifier session admin
if (!isset($_SESSION['admin_id']) && !(isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
    header("Location: login.php");
    exit;
}

// Connexion DB
include __DIR__ . '/db.php';

// Helper pour sécuriser l'output
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/* Stats */
$stats = [
    'users' => 0,
    'translations_total' => 0,
    'translations_waiting' => 0,
    'appointments' => 0,
    'news' => 0,
];

// Comptes
$res = $conn->query("SELECT COUNT(*) AS cnt FROM users");
if ($res) { $row = $res->fetch_assoc(); $stats['users'] = (int)$row['cnt']; $res->free(); }

$res = $conn->query("SELECT COUNT(*) AS cnt FROM translations");
if ($res) { $row = $res->fetch_assoc(); $stats['translations_total'] = (int)$row['cnt']; $res->free(); }

// Traductions en attente
$pending_translations = [];
$res = $conn->query("SELECT id, file_path FROM translations WHERE status = 'En attente'");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $pending_translations[] = $row;
    }
    $stats['translations_waiting'] = count($pending_translations);
    $res->free();
} else {
    $stats['translations_waiting'] = 0;
}

// Rendez-vous
$res = $conn->query("SELECT COUNT(*) AS cnt FROM appointments");
if ($res) { $row = $res->fetch_assoc(); $stats['appointments'] = (int)$row['cnt']; $res->free(); }

// Actualités
$res = $conn->query("SELECT COUNT(*) AS cnt FROM news");
if ($res) { $row = $res->fetch_assoc(); $stats['news'] = (int)$row['cnt']; $res->free(); }

// 7 traductions récentes
$recent_translations = [];
$sql = "SELECT t.id, t.user_id, t.file_path, t.status, t.upload_date, u.username
        FROM translations t
        LEFT JOIN users u ON u.id = t.user_id
        ORDER BY t.upload_date DESC
        LIMIT 7";
if ($res = $conn->query($sql)) {
    while ($r = $res->fetch_assoc()) { $recent_translations[] = $r; }
    $res->free();
}

// 7 rendez-vous récents
$recent_appointments = [];
$sql = "SELECT a.id, a.user_id, a.appointment_date, a.appointment_time, a.status, u.username
        FROM appointments a
        LEFT JOIN users u ON u.id = a.user_id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
        LIMIT 7";
if ($res = $conn->query($sql)) {
    while ($r = $res->fetch_assoc()) { $recent_appointments[] = $r; }
    $res->free();
}

// Traductions statut français
$status_fr = [
    "Pending" => "En attente",
    "Confirmed" => "Confirmé",
    "Completed" => "Terminé",
    "Cancelled" => "Annulé"
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/css/custom.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet" />
</head>
<body>
<div class="wrapper">
    <div class="body-overlay"></div>

    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <img src="/img/logo2.png" class="img-fluid" alt="Traditaliano Logo" style="max-height:60px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1);" />
        </div>
        <ul class="list-unstyled components">
            <li class="active"><a href="admin_dashboard.php"><i class="material-icons">dashboard</i> Dashboard</a></li>
            <li><a href="manage_users.php"><i class="material-icons">person</i> Clients</a></li>
            <li><a href="manage_translations.php"><i class="material-icons">file_upload</i> Fichiers</a></li>
            <li><a href="manage_appointments.php"><i class="material-icons">event</i> Rendez-vous</a></li>
            <li><a href="manage_news.php"><i class="material-icons">campaign</i> Actualités / Promos</a></li>
            <li><a href="manage_notifications.php"><i class="material-icons">notifications</i> Notifications</a></li>
        </ul>
        <a href="logout.php" class="btn btn-danger mt-3" style="margin: 15px;">Déconnexion</a>
    </nav>

    <!-- Page Content -->
    <div id="content">
        <div class="top-navbar">
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse"><span class="material-icons">arrow_back_ios</span></button>
                    <a class="navbar-brand d-flex align-items-center" href="#">
                        <img src="/img/logo2.png" alt="Traditaliano Logo" style="height:36px; margin-right:8px; border-radius:6px;" />
                        <span>Traditaliano Admin</span>
                    </a>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="nav navbar-nav ml-auto">
                            <li class="dropdown nav-item active">
                                <a href="#" class="nav-link" data-toggle="dropdown">
                                    <span class="material-icons">notifications</span>
                                    <span class="badge" style="background-color: #38d39f; color: #fff;">
                                        <?= e("Vous avez {$stats['translations_waiting']} fichier(s) en attente") ?>
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <?php if ($stats['translations_waiting'] > 0): ?>
                                        <?php foreach ($pending_translations as $t): ?>
                                            <li>
                                                <a class="dropdown-item" href="manage_translations.php#translation_<?= e($t['id']) ?>">
                                                    <?= e(basename($t['file_path'])) ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li><a class="dropdown-item">Aucune traduction en attente</a></li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="manage_news.php"><span class="material-icons">campaign</span></a></li>
                            <li class="nav-item"><a class="nav-link" href="manage_users.php"><span class="material-icons">person</span></a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php"><span class="material-icons">settings</span></a></li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content container-fluid">
            <div class="row">
                <!-- Stats cards -->
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-header"><div class="icon icon-warning"><span class="material-icons">person</span></div></div>
                        <div class="card-content"><p class="category"><strong>Clients</strong></p><h3 class="card-title"><?= e($stats['users']) ?></h3></div>
                        <div class="card-footer"><div class="stats"><i class="material-icons text-info">info</i><a href="manage_users.php">Voir les clients</a></div></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-header"><div class="icon icon-rose"><span class="material-icons">file_upload</span></div></div>
                        <div class="card-content"><p class="category"><strong>Fichiers soumis</strong></p><h3 class="card-title"><?= e($stats['translations_total']) ?></h3></div>
                        <div class="card-footer"><div class="stats"><i class="material-icons">local_offer</i><a href="manage_translations.php">Gérer les fichiers</a></div></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-header"><div class="icon icon-success"><span class="material-icons">event</span></div></div>
                        <div class="card-content"><p class="category"><strong>Rendez-vous</strong></p><h3 class="card-title"><?= e($stats['appointments']) ?></h3></div>
                        <div class="card-footer"><div class="stats"><i class="material-icons">date_range</i><a href="manage_appointments.php">Voir les rendez-vous</a></div></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-header"><div class="icon icon-info"><span class="material-icons">campaign</span></div></div>
                        <div class="card-content"><p class="category"><strong>Actualités</strong></p><h3 class="card-title"><?= e($stats['news']) ?></h3></div>
                        <div class="card-footer"><div class="stats"><i class="material-icons">update</i><a href="manage_news.php">Gérer les promos</a></div></div>
                    </div>
                </div>
            </div>

            <!-- Recent translations -->
            <div class="row">
                <div class="col-lg-7 col-md-12">
                    <div class="card" style="min-height: 485px;">
                        <div class="card-header card-header-text"><h4 class="card-title">Fichiers récents</h4><p class="category">Les traductions déposées récemment</p></div>
                        <div class="card-content table-responsive">
                            <table class="table table-hover">
                                <thead class="text-primary"><tr><th>ID</th><th>Client</th><th>Fichier</th><th>Status</th><th>Upload</th></tr></thead>
                                <tbody>
                                    <?php if (count($recent_translations) === 0): ?>
                                        <tr><td colspan="5">Aucun fichier trouvé</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_translations as $t): ?>
                                            <tr>
                                                <td><?= e($t['id']) ?></td>
                                                <td><?= e($t['username'] ?? 'Utilisateur supprimé') ?></td>
                                                <td><?= e(basename($t['file_path'])) ?><?php if (!empty($t['file_path'])): ?> <a href="<?= e($t['file_path']) ?>" target="_blank">Voir</a><?php endif; ?></td>
                                                <td><?= e($t['status']) ?></td>
                                                <td><?= e($t['upload_date']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent appointments -->
                <div class="col-lg-5 col-md-12">
                    <div class="card" style="min-height: 485px;">
                        <div class="card-header card-header-text"><h4 class="card-title">Rendez-vous récents</h4><p class="category">Prochains rendez-vous</p></div>
                        <div class="card-content">
                            <div class="streamline">
                                <?php if (count($recent_appointments) === 0): ?>
                                    <p>Aucun rendez-vous trouvé</p>
                                <?php else: ?>
                                    <?php foreach ($recent_appointments as $a): ?>
                                        <div class="sl-item">
                                            <div class="sl-content">
                                                <small class="text-muted"><?= e($a['appointment_date'] . ' ' . $a['appointment_time']) ?></small>
                                                <p><?= e($a['username'] ?? 'Utilisateur supprimé') ?> - <?= e($status_fr[$a['status']] ?? $a['status']) ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6"><nav class="d-flex"><ul class="m-0 p-0" style="list-style:none; display:flex; gap:15px;"><li><a href="#"></a></li></ul></nav></div>
                        <div class="col-md-6 text-right"><p class="copyright">&copy; <?= date('Y') ?> Traditaliano</p></div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</div>

<!-- JS -->
<script src="/js/jquery-3.3.1.min.js"></script>
<script src="/js/popper.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script>
$(document).ready(function () {
    $("#sidebarCollapse").on("click", function () {
        $("#sidebar").toggleClass("active");
        $("#content").toggleClass("active");
    });
    $(".more-button,.body-overlay").on("click", function () {
        $("#sidebar,.body-overlay").toggleClass("show-nav");
    });
});
</script>
</body>
</html>
