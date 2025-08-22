<?php
session_start();
include __DIR__ . '/db.php';

// Handle new notification submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $user_id = !empty($_POST['user_id']) ? intval($_POST['user_id']) : NULL;
    $content = trim($_POST['content']);
    $priority = intval($_POST['priority']);

    if (!empty($content)) {
        if ($user_id === NULL) {
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, content, priority) VALUES (NULL, ?, ?)");
            $stmt->bind_param("si", $content, $priority);
        } else {
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, content, priority) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $user_id, $content, $priority);
        }
        $stmt->execute();
        $stmt->close();
    }
    header("Location: manage_notifications.php");
    exit();
}

// Handle delete notification
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_notifications.php");
    exit();
}

// Fetch notifications
$sql = "SELECT n.id, u.username, n.content, n.priority, n.created_at 
        FROM notifications n 
        LEFT JOIN users u ON n.user_id = u.id
        ORDER BY n.created_at DESC";
$result = $conn->query($sql);

// Fetch users for dropdown
$users_res = $conn->query("SELECT id, username FROM users ORDER BY username ASC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gérer les notifications</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/custom.css">
<style>
:root {
    --primary-green: #38d39f;
    --primary-green-hover: #2eb389;
    --bg-light: #f0faf5;
    --card-shadow: rgba(56, 211, 159, 0.15);
}

body.dashboard-body {
    font-family: 'Segoe UI', sans-serif;
    background: var(--bg-light);
    color: #2e4a1f;
    padding-top: 90px;
    margin: 0;
}

.dashboard-header {
    background: var(--primary-green);
    color: white;
    padding: 18px 32px;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 999;
}
.dashboard-header h1 { margin: 0; font-size: 1.6rem; font-weight: 600; }
.dashboard-header a {
    color: white;
    font-weight: 600;
    text-decoration: none;
    background: rgba(255,255,255,0.25);
    padding: 8px 22px;
    border-radius: 20px;
    transition: 0.3s;
}
.dashboard-header a:hover {
    background: white;
    color: var(--primary-green);
}

.dashboard-main { max-width: 1050px; margin: 0 auto; padding: 30px 20px; }

.card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 6px 20px var(--card-shadow);
    padding: 28px;
    margin-bottom: 28px;
    transition: transform 0.2s;
}
.card:hover { transform: translateY(-2px); }
.card h4 { margin-bottom: 20px; font-size: 1.25rem; font-weight: 600; color: #2e4a1f; }

.dashboard-form label { display: block; margin-top: 14px; margin-bottom: 6px; font-weight: 600; color: #2e4a1f; }
.dashboard-form input, .dashboard-form textarea, .dashboard-form select {
    width: 100%; padding: 12px 14px; border-radius: 10px; border: 1px solid #c8e6c9; margin-bottom: 14px;
    font-size: 0.95rem; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
}
.dashboard-form input:focus, .dashboard-form textarea:focus, .dashboard-form select:focus {
    border-color: var(--primary-green); box-shadow: 0 0 8px rgba(56,211,159,0.25);
}

.btn-primary {
    background: var(--primary-green); color: white; font-weight: 600; border: none; padding: 12px 26px;
    border-radius: 10px; cursor: pointer; transition: 0.3s;
}
.btn-primary:hover { background: var(--primary-green-hover); }

.dashboard-table {
    width: 100%; border-collapse: collapse; font-size: 0.95rem;
}
.dashboard-table th, .dashboard-table td {
    padding: 14px 12px; text-align: left; border-bottom: 1px solid #e0e0e0;
}
.dashboard-table th {
    background: var(--primary-green); color: white; font-weight: 600; border-radius: 6px 6px 0 0;
}
.dashboard-table tbody tr:hover { background: rgba(56,211,159,0.08); }

.delete-btn {
    background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 6px;
    cursor: pointer; font-size: 0.85rem; transition: 0.2s;
}
.delete-btn:hover { background: #c0392b; }
</style>
</head>
<body class="dashboard-body">

<header class="dashboard-header">
    <h1>🔔 Gestion des notifications</h1>
    <a href="admin_dashboard.php">⬅ Retour au dashboard</a>
</header>

<main class="dashboard-main">

    <!-- Notification Form -->
    <div class="card">
        <h4>Ajouter une notification</h4>
        <form method="post" class="dashboard-form">
            <input type="hidden" name="action" value="add">

            <label for="user_id">Utilisateur</label>
            <select name="user_id" id="user_id" class="dashboard-select">
                <option value="">Tous les utilisateurs</option>
                <?php 
                $users_res->data_seek(0);
                while ($u = $users_res->fetch_assoc()): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="content">Contenu</label>
            <textarea name="content" id="content" required></textarea>

            <label for="priority">Priorité</label>
            <input type="number" name="priority" id="priority" value="1">

            <button type="submit" class="btn-primary">Envoyer</button>
        </form>
    </div>

    <!-- Notifications Table -->
    <div class="card">
        <h4>Historique des notifications</h4>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Utilisateur</th>
                    <th>Contenu</th>
                    <th>Priorité</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['username'] ? htmlspecialchars($row['username']) : "Tous" ?></td>
                        <td><?= htmlspecialchars($row['content']) ?></td>
                        <td><?= $row['priority'] ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <a href="?delete_id=<?= $row['id'] ?>" onclick="return confirm('Supprimer cette notification ?')" class="delete-btn">Supprimer</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</main>

</body>
</html>




