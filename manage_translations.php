<?php
session_start();
require_once "db.php"; // Connect to DB

$messages = []; // Store success/error messages

// Handle status update
if (isset($_POST['update_status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE translations SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    if ($stmt->execute()) $messages[] = "Statut mis à jour avec succès pour la traduction #$id.";
    else $messages[] = "Erreur lors de la mise à jour du statut pour la traduction #$id.";
    $stmt->close();
}

// Handle translated file upload
if (isset($_POST['upload_translation']) && isset($_FILES['translated_file'])) {
    $id = intval($_POST['id']);
    $originalFileName = basename($_FILES["translated_file"]["name"]);
    $fileExt = pathinfo($originalFileName, PATHINFO_EXTENSION);
    $safeName = preg_replace("/[^a-zA-Z0-9_-]/", "_", pathinfo($originalFileName, PATHINFO_FILENAME));
    $fileName = time() . "_" . $safeName . "." . $fileExt;
    $targetFilePath = "uploads/" . $fileName;

    if (move_uploaded_file($_FILES["translated_file"]["tmp_name"], $targetFilePath)) {
        // Update DB with file path and mark status as 'Terminé'
        $stmt = $conn->prepare("UPDATE translations SET translated_file_path = ?, status = 'Terminé' WHERE id = ?");
        $stmt->bind_param("si", $targetFilePath, $id);
        if ($stmt->execute()) {
            $messages[] = "Traduction #$id téléchargée avec succès et marquée comme 'Terminé'.";

            // Notification with file name
            $fileNameOnly = basename($targetFilePath);
            $stmt2 = $conn->prepare("INSERT INTO notifications (user_id, content, link, type, priority, is_read, created_at) 
                SELECT user_id, CONCAT('Votre traduction \"', ?, '\" est terminée!'), '#', 'info', 1, 0, NOW() 
                FROM translations WHERE id=?");
            $stmt2->bind_param("si", $fileNameOnly, $id);
            $stmt2->execute();
            $stmt2->close();
        } else {
            $messages[] = "Erreur lors de la mise à jour du statut pour la traduction #$id.";
        }
        $stmt->close();
    } else {
        $messages[] = "Erreur lors du téléchargement du fichier pour la traduction #$id.";
    }
}

// Fetch all translations with username
$result = $conn->query("
    SELECT t.*, u.username 
    FROM translations t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.upload_date DESC
");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des traductions</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="custom.css">
<style>
:root { --primary-color: #38d39f; --primary-dark: #2eb389; --dashboard-bg: #f4f6f8; }
.dashboard-main { padding: 20px; background: var(--dashboard-bg); }
.table-container { background: white; border-radius: 8px; padding: 20px; box-shadow: 0px 2px 6px rgba(0,0,0,0.1); }
.dashboard-table { width: 100%; border-collapse: collapse; overflow: hidden; border-radius: 8px; }
.dashboard-table th { background: var(--primary-color); color: white; text-align: left; padding: 12px; }
.dashboard-table td { padding: 10px; border-bottom: 1px solid #ddd; }
.dashboard-table tr:hover { background-color: #f9fafc; }
.btn-small { background: var(--primary-color); color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
.btn-small:hover { background: var(--primary-dark); }
.status-select, .file-input { padding: 5px; border: 1px solid #ccc; border-radius: 4px; }
.btn-link { color: var(--primary-color); text-decoration: none; font-weight: bold; }
.btn-link:hover { text-decoration: underline; }
.btn-primary { background: var(--primary-color); color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; display: inline-block; }
.btn-primary:hover { background: var(--primary-dark); }
.back-btn-container { margin-top: 20px; text-align: right; }
.message { padding: 10px 15px; margin-bottom: 10px; border-radius: 6px; background: #e0f7f3; color: #04695c; }
</style>
</head>
<body class="dashboard-body">

<header class="dashboard-header" style="background: var(--primary-color); padding: 15px; color: white;">
    <div class="header-content">
        <h1>📄 Gestion des traductions - Admin</h1>
    </div>
</header>

<main class="dashboard-main">

<?php foreach($messages as $msg): ?>
    <div class="message"><?= htmlspecialchars($msg) ?></div>
<?php endforeach; ?>

<div class="table-container">
    <table class="dashboard-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Utilisateur</th>
                <th>Fichier original</th>
                <th>Statut</th>
                <th>Changer le statut</th>
                <th>Joindre le document traduit</th>
                <th>Télécharger document traduit</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><a href="<?= htmlspecialchars($row['file_path']) ?>" download class="btn-link">📥 Télécharger</a></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <select name="status" class="status-select">
                            <option value="En attente" <?= ($row['status']=="En attente")?"selected":"" ?>>En attente</option>
                            <option value="En cours" <?= ($row['status']=="En cours")?"selected":"" ?>>En cours</option>
                            <option value="Terminé" <?= ($row['status']=="Terminé")?"selected":"" ?>>Terminé</option>
                        </select>
                        <button type="submit" name="update_status" class="btn-small">✔</button>
                    </form>
                </td>
                <td>
                    <form method="post" enctype="multipart/form-data" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="file" name="translated_file" required class="file-input">
                        <button type="submit" name="upload_translation" class="btn-small">📤</button>
                    </form>
                </td>
                <td>
                    <?php if (!empty($row['translated_file_path'])): ?>
                        <a href="<?= htmlspecialchars($row['translated_file_path']) ?>" download class="btn-link">📥 Télécharger</a>
                    <?php else: ?>
                        <span>Non disponible</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="back-btn-container">
    <a href="admin_dashboard.php" class="btn-primary">⬅ Retour au tableau de bord</a>
</div>

</main>
</body>
</html>
