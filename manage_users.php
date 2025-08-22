<?php
// manage_users.php
session_start();

// Vérification de la session admin
if (!isset($_SESSION['admin_id']) && !(isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
    header("Location: login.php");
    exit;
}

include 'db.php'; // Ajuste le chemin si besoin

// Fonction pour échapper les sorties HTML
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Gestion du formulaire d'ajout utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = trim($_POST['role']);

    if ($username && $email && $password) {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $username, $email, $password, $role);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_users.php");
        exit;
    }
}

// Récupérer les utilisateurs
$users = [];
$res = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY id DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $users[] = $row;
    }
    $res->free();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Gérer les utilisateurs - Admin</title>
<link rel="stylesheet" href="/css/bootstrap.min.css" />
<link rel="stylesheet" href="/css/custom.css" />
<link rel="stylesheet" href="/css/style.css" />
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet" />
<style>
.card { margin-bottom: 20px; }
.dashboard-form input, .dashboard-form select { margin-bottom: 10px; }
</style>
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
        <li><a href="admin_dashboard.php"><i class="material-icons">dashboard</i> Dashboard</a></li>
        <li class="active"><a href="manage_users.php"><i class="material-icons">person</i> Clients</a></li>
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
<button type="button" id="sidebarCollapse" class="d-xl-block d-lg-block d-md-none d-none">
    <span class="material-icons">arrow_back_ios</span>
</button>
<a class="navbar-brand d-flex align-items-center" href="#">
    <img src="/img/logo2.png" alt="Traditaliano Logo" style="height:36px; margin-right:8px; border-radius:6px;" />
    <span>Gérer les utilisateurs</span>
</a>
</div>
</nav>
</div>

<div class="main-content container-fluid">
    <!-- Formulaire Ajouter Utilisateur -->
    <div class="card">
        <div class="card-header"><h4>Ajouter un utilisateur</h4></div>
        <div class="card-content">
            <form method="post" class="dashboard-form">
                <input type="hidden" name="add_user" value="1">
                <input type="text" name="username" placeholder="Nom d'utilisateur" required class="form-control">
                <input type="email" name="email" placeholder="Email" required class="form-control">
                <input type="password" name="password" placeholder="Mot de passe" required class="form-control">
                <select name="role" class="form-control" required>
                    <option value="client">Client</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit" class="btn btn-success mt-2">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- Liste des utilisateurs -->
    <div class="card">
        <div class="card-header card-header-text">
            <h4 class="card-title">Liste des utilisateurs</h4>
            <p class="category">Gestion des clients enregistrés</p>
        </div>
        <div class="card-content table-responsive">
            <table class="table table-hover">
                <thead class="text-primary">
                    <tr>
                        <th>ID</th>
                        <th>Nom d'utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Inscrit le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="6" class="text-center">Aucun utilisateur trouvé.</td></tr>
                    <?php else: foreach ($users as $user): ?>
                        <tr>
                            <td><?= e($user['id']) ?></td>
                            <td><?= e($user['username']) ?></td>
                            <td><?= e($user['email']) ?></td>
                            <td><?= e($user['role']) ?></td>
                            <td><?= e($user['created_at']) ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= e($user['id']) ?>" class="btn btn-sm btn-primary">Modifier</a>
                                <a href="delete_user.php?id=<?= e($user['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
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
