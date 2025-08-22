<?php
session_start();

// Check admin session
if (!isset($_SESSION['admin_id']) && !(isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
    header("Location: login.php");
    exit;
}

include __DIR__ . '/db.php';

function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$message = "";

// Handle status/date/time update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status_date'], $_POST['appointment_id'])) {
        $appointment_id = (int)$_POST['appointment_id'];
        $status = $conn->real_escape_string($_POST['status']);
        $date = $_POST['appointment_date'];
        $time = $_POST['appointment_time'];

        $allowed_status = ['Pending', 'Confirmed', 'Cancelled'];
        if (!in_array($status, $allowed_status)) {
            $message = "❌ Statut invalide.";
        } else {
            $stmt = $conn->prepare("UPDATE appointments SET status = ?, appointment_date = ?, appointment_time = ? WHERE id = ?");
            $stmt->bind_param('sssi', $status, $date, $time, $appointment_id);
            if ($stmt->execute()) {
                $message = "✅ Rendez-vous mis à jour avec succès.";
            } else {
                $message = "❌ Erreur lors de la mise à jour : " . $conn->error;
            }
            $stmt->close();
        }
    }

    if (isset($_POST['delete_appointment'], $_POST['appointment_id'])) {
        $appointment_id = (int)$_POST['appointment_id'];
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->bind_param('i', $appointment_id);
        if ($stmt->execute()) {
            $message = "✅ Rendez-vous supprimé avec succès.";
        } else {
            $message = "❌ Erreur lors de la suppression : " . $conn->error;
        }
        $stmt->close();
    }
}

$sql = "SELECT a.id, a.user_id, a.appointment_date, a.appointment_time, a.status, u.username
        FROM appointments a
        LEFT JOIN users u ON u.id = a.user_id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>Gérer les rendez-vous - Admin</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css" />
    <style>
        :root {
            --primary-color: #38d39f;
            --primary-dark: #2eb389;
            --dashboard-bg: #f4f6f8;
        }
        html, body { height: 100%; margin: 0; background: var(--dashboard-bg); font-family: "Segoe UI", sans-serif; }
        body { display: flex; flex-direction: column; min-height: 100vh; }
        .top-navbar { padding: 20px 40px; background: white; box-shadow: 0 2px 4px rgb(0 0 0 / 0.05); margin-bottom: 30px; }
        .navbar-brand { font-weight: 700; color: var(--primary-color); font-size: 1.6rem; }
        .main-content { flex: 1; padding: 30px 40px; max-width: 1200px; margin: auto; background: white; box-shadow: 0 2px 8px rgb(0 0 0 / 0.1); border-radius: 8px; }
        table th { background: var(--primary-color); color: white; padding: 12px; }
        table td { padding: 10px; border-bottom: 1px solid #ddd; vertical-align: middle; }
        table tbody tr:hover { background-color: #e8f5e9; }
        .btn-status { padding: 6px 12px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-save { background: var(--primary-color); color: white; border: none; border-radius: 6px; padding: 6px 12px; font-weight: 600; cursor: pointer; }
        .btn-save:hover { background: var(--primary-dark); }
        .btn-danger { background-color: #e57373 !important; border: none; border-radius: 6px; padding: 6px 12px; font-weight: 600; cursor: pointer; }
        .btn-danger:hover { background-color: #c94f4f !important; }
        .message { padding: 12px; border-radius: 8px; font-weight: 600; text-align: center; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; border: 1px solid var(--primary-color); }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #e57373; }
    </style>
</head>
<body>
    <div class="top-navbar">
        <nav><a class="navbar-brand" href="#">Gestion des rendez-vous</a></nav>
    </div>

    <div class="main-content">
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo e($message); ?>
            </div>
        <?php endif; ?>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th><th>Client</th><th>Date</th><th>Heure</th><th>Statut</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <form method="POST">
                                <td><?php echo e($row['id']); ?></td>
                                <td><?php echo e($row['username'] ?? 'Utilisateur supprimé'); ?></td>
                                <td><input type="date" name="appointment_date" value="<?php echo e($row['appointment_date']); ?>" required></td>
                                <td><input type="time" name="appointment_time" value="<?php echo e($row['appointment_time']); ?>" required></td>
                                <td>
                                    <select name="status" class="btn-status">
                                        <option value="Pending" <?php if($row['status']=='Pending') echo 'selected'; ?>>En attente</option>
                                        <option value="Confirmed" <?php if($row['status']=='Confirmed') echo 'selected'; ?>>Confirmé</option>
                                        <option value="Cancelled" <?php if($row['status']=='Cancelled') echo 'selected'; ?>>Annulé</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="appointment_id" value="<?php echo e($row['id']); ?>">
                                    <button type="submit" name="update_status_date" class="btn-save">💾 Sauvegarder</button>
                                    <button type="submit" name="delete_appointment" class="btn-danger" onclick="return confirm('Supprimer ce rendez-vous ?');">🗑 Supprimer</button>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">Aucun rendez-vous trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="admin_dashboard.php" class="btn-save" style="margin-top:20px; display:inline-block;">← Retour au dashboard</a>
    </div>

    <footer style="background: white; padding: 15px; text-align: center; font-weight: 600; color: var(--primary-color);">
        &copy; <?php echo date('Y'); ?> Votre Projet
    </footer>
</body>
</html>
