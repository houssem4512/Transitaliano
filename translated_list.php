<?php
session_start();
require_once "db.php"; // Your database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'], $_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

function e($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

// Fetch user translations
$translations = [];
$stmt = $conn->prepare("SELECT id, file_path, translated_file_path, status, upload_date FROM translations WHERE user_id = ? ORDER BY upload_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $translations[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes traductions</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="custom.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0; }
        .container { max-width: 1000px; margin: 50px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        h2 { color: #38d39f; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #38d39f; color: #fff; }
        tr:hover { background-color: #f9fafc; }
        .btn-download { background-color: #38d39f; color: #fff; padding: 6px 12px; border-radius: 4px; text-decoration: none; }
        .btn-download:hover { background-color: #2eb389; }
        .status-badge { padding: 4px 8px; border-radius: 4px; color: #fff; font-size: 0.9em; }
        .status-pending { background-color: #f0ad4e; }
        .status-completed { background-color: #38d39f; }
        .status-rejected { background-color: #d9534f; }
        .back-link { display: inline-block; margin-top: 20px; color: #38d39f; text-decoration: none; font-weight: bold; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Mes traductions</h2>
        <?php if (count($translations) === 0): ?>
            <p>Aucun fichier traduit pour le moment.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom du fichier</th>
                        <th>Fichier original</th>
                        <th>Fichier traduit</th>
                        <th>Statut</th>
                        <th>Date d'envoi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($translations as $t): ?>
                        <tr>
                            <td><?= e($t['id']) ?></td>
                            <td><?= e(basename($t['file_path'])) ?></td>
                            <td>
                                <?php if (!empty($t['file_path'])): ?>
                                    <a href="<?= e($t['file_path']) ?>" download class="btn-download">Télécharger</a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($t['translated_file_path'])): ?>
                                    <a href="<?= e($t['translated_file_path']) ?>" download class="btn-download">Télécharger</a>
                                <?php else: ?>
                                    Non disponible
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusClass = '';
                                if ($t['status'] == 'En attente') $statusClass = 'status-pending';
                                elseif ($t['status'] == 'Terminé') $statusClass = 'status-completed';
                                elseif ($t['status'] == 'Rejeté') $statusClass = 'status-rejected';
                                ?>
                                <span class="status-badge <?= $statusClass ?>"><?= e($t['status']) ?></span>
                            </td>
                            <td><?= e($t['upload_date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="dashboard.php" class="back-link">⬅ Retour au tableau de bord</a>
    </div>
</body>
</html>
