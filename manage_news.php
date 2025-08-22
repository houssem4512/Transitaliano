<?php
session_start();
include __DIR__ . '/db.php';

if (!isset($_SESSION['admin_id']) && !(isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
    header("Location: login.php");
    exit;
}

function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$message = "";

// Handle adding news
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_news'])) {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $published = isset($_POST['published']) ? 1 : 0;

        if ($title === '' || $content === '') {
            $message = "❌ Veuillez remplir tous les champs obligatoires.";
        } else {
            $stmt = $conn->prepare("INSERT INTO news (title, content, published, published_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param('ssi', $title, $content, $published);
            if ($stmt->execute()) {
                $message = "✅ Actualité ajoutée avec succès.";
            } else {
                $message = "❌ Erreur lors de l'ajout : " . $conn->error;
            }
            $stmt->close();
        }
    }

    // Handle editing news
    if (isset($_POST['edit_news'], $_POST['news_id'])) {
        $news_id = (int)$_POST['news_id'];
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $published = isset($_POST['published']) ? 1 : 0;

        if ($title === '' || $content === '') {
            $message = "❌ Veuillez remplir tous les champs obligatoires.";
        } else {
            $stmt = $conn->prepare("UPDATE news SET title = ?, content = ?, published = ?, published_at = NOW() WHERE id = ?");
            $stmt->bind_param('ssii', $title, $content, $published, $news_id);
            if ($stmt->execute()) {
                $message = "✅ Actualité mise à jour avec succès.";
            } else {
                $message = "❌ Erreur lors de la mise à jour : " . $conn->error;
            }
            $stmt->close();
        }
    }

    // Handle deleting news
    if (isset($_POST['delete_news'], $_POST['news_id'])) {
        $news_id = (int)$_POST['news_id'];
        $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
        $stmt->bind_param('i', $news_id);
        if ($stmt->execute()) {
            $message = "✅ Actualité supprimée avec succès.";
        } else {
            $message = "❌ Erreur lors de la suppression : " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch all news
$sql = "SELECT id, title, content, published, published_at FROM news ORDER BY published_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <title>Gestion des actualités - Admin</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css" />
    <style>
        :root {
            --primary-color: #38d39f;
            --primary-dark: #2eb389;
            --dashboard-bg: #f4f6f8;
        }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--dashboard-bg);
            color: #2e4a1f;
        }
        .dashboard-header {
            background: var(--primary-color);
            padding: 15px 20px;
            color: white;
            user-select: none;
        }
        .dashboard-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 1.8rem;
        }
        .dashboard-main {
            padding: 20px;
            max-width: 1000px;
            margin: auto;
        }
        .message {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
            font-size: 1rem;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1.5px solid var(--primary-color);
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1.5px solid #e57373;
        }
        .table-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 8px;
        }
        .dashboard-table th {
            background: var(--primary-color);
            color: white;
            text-align: left;
            padding: 12px;
        }
        .dashboard-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }
        .dashboard-table tr:hover {
            background-color: #f9fafc;
        }
        .btn-small {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }
        .btn-small:hover {
            background: var(--primary-dark);
        }
        .btn-primary {
            background: var(--primary-color);
            color: white;
            padding: 10px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 700;
            display: inline-block;
            transition: background 0.3s ease;
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            text-decoration: none;
            color: white;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 8px 12px;
            margin: 6px 0 14px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-family: inherit;
            font-size: 1rem;
            resize: vertical;
        }
        textarea {
            min-height: 80px;
        }
        label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
            color: var(--primary-dark);
        }
        form.add-news {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-checkbox {
            margin-bottom: 18px;
            font-weight: 600;
            color: var(--primary-dark);
        }
        .form-checkbox input[type="checkbox"] {
            transform: scale(1.2);
            margin-right: 8px;
            vertical-align: middle;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <header class="dashboard-header">
        <h1>📰 Gestion des actualités - Admin</h1>
    </header>

    <main class="dashboard-main">
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo e($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="add-news" autocomplete="off">
            <h2>Ajouter une actualité</h2>
            <label for="title">Titre *</label>
            <input type="text" id="title" name="title" required maxlength="255">

            <label for="content">Contenu *</label>
            <textarea id="content" name="content" required></textarea>

            <label class="form-checkbox">
                <input type="checkbox" name="published">
                Publier maintenant
            </label>

            <button type="submit" name="add_news" class="btn-small">Ajouter</button>
        </form>

        <div class="table-container">
            <table class="dashboard-table" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Contenu</th>
                        <th>Publié</th>
                        <th>Date de publication</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= e($row['title']) ?></td>
                                <td style="max-width: 300px; white-space: pre-wrap;"><?= e($row['content']) ?></td>
                                <td><?= $row['published'] ? 'Oui' : 'Non' ?></td>
                                <td><?= e(date('d/m/Y H:i', strtotime($row['published_at']))) ?></td>
                                <td>
                                    <!-- Edit form toggled inline -->
                                    <details>
                                        <summary style="cursor:pointer; color: var(--primary-color); font-weight:600;">Modifier</summary>
                                        <form method="post" style="margin-top:10px;" autocomplete="off">
                                            <input type="hidden" name="news_id" value="<?= $row['id'] ?>">
                                            
                                            <label for="title-<?= $row['id'] ?>">Titre *</label>
                                            <input type="text" id="title-<?= $row['id'] ?>" name="title" required maxlength="255" value="<?= e($row['title']) ?>">
                                            
                                            <label for="content-<?= $row['id'] ?>">Contenu *</label>
                                            <textarea id="content-<?= $row['id'] ?>" name="content" required><?= e($row['content']) ?></textarea>

                                            <label class="form-checkbox">
                                                <input type="checkbox" name="published" <?= $row['published'] ? 'checked' : '' ?>>
                                                Publier maintenant
                                            </label>

                                            <button type="submit" name="edit_news" class="btn-small">Mettre à jour</button>
                                        </form>
                                    </details>

                                    <form method="post" style="margin-top:8px;" onsubmit="return confirm('Supprimer cette actualité ?');">
                                        <input type="hidden" name="news_id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="delete_news" class="btn-small" style="background:#e57373;">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;">Aucune actualité trouvée.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="text-align:right; margin-top: 20px;">
            <a href="admin_dashboard.php" class="btn-primary">⬅ Retour au tableau de bord</a>
        </div>
    </main>
</body>

</html>
