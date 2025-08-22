<?php
session_start();
require_once "db.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$message_type = ""; // success or error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES['translation_file']) && $_FILES['translation_file']['error'] === UPLOAD_ERR_OK) {

        $user_id = $_SESSION['user_id'];
        $uploadDir = "uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmpPath = $_FILES['translation_file']['tmp_name'];
        $fileName = basename($_FILES['translation_file']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ["txt", "doc", "docx", "pdf"];
        if (!in_array($fileExtension, $allowedExtensions)) {
            $message = "❌ Seuls les fichiers TXT, DOC, DOCX et PDF sont autorisés.";
            $message_type = "error";
        } else {
            $newFileName = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $fileName);
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $stmt = $conn->prepare("INSERT INTO translations (user_id, file_path, status, upload_date) VALUES (?, ?, ?, NOW())");
                $status = "Pending";
                $stmt->bind_param("iss", $user_id, $destPath, $status);
                if ($stmt->execute()) {
                    $message = "✅ Fichier téléchargé avec succès !";
                    $message_type = "success";
                } else {
                    $message = "❌ Erreur base de données : " . $conn->error;
                    $message_type = "error";
                }
                $stmt->close();
            } else {
                $message = "❌ Erreur lors du déplacement du fichier.";
                $message_type = "error";
            }
        }
    } else {
        $message = "❌ Aucun fichier téléchargé ou erreur détectée.";
        $message_type = "error";
    }
}

// Fetch uploaded files for user
$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM translations WHERE user_id = $user_id ORDER BY upload_date DESC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Déposer un fichier de traduction</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/custom.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #38d39f;
            margin-bottom: 25px;
            font-weight: 700;
        }
        .message {
            margin-bottom: 20px;
            padding: 12px 15px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
        }
        .message.success {
            background-color: #38d39f33;
            color: #2d7a5e;
            border: 1.5px solid #38d39f;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1.5px solid #f5c6cb;
        }
        form input[type="file"] {
            display: block;
            margin: 0 auto 20px auto;
        }
        form button {
            display: block;
            margin: 0 auto;
            background-color: #38d39f;
            color: white;
            font-weight: 700;
            padding: 10px 30px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        form button:hover {
            background-color: #2baf85;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            font-weight: 500;
        }
        th {
            background-color: #e6f4f1;
            color: #2d7a5e;
        }
        a.btn-view, a.btn-download {
            padding: 6px 14px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            margin-right: 8px;
            transition: background-color 0.3s ease;
        }
        a.btn-view {
            background-color: #38d39f;
        }
        a.btn-view:hover {
            background-color: #2baf85;
        }
        a.btn-download {
            background-color: #219150;
        }
        a.btn-download:hover {
            background-color: #196838;
        }
        .back-btn {
            display: block;
            margin: 30px auto 0 auto;
            text-align: center;
            font-weight: 600;
            color: #38d39f;
            text-decoration: none;
            font-size: 16px;
            border: 2px solid #38d39f;
            border-radius: 30px;
            padding: 8px 40px;
            width: fit-content;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .back-btn:hover {
            background-color: #38d39f;
            color: white;
        }
    </style>
</head>
<body class="dashboard-body">

<div class="container">
    <h2>Déposer un fichier de traduction</h2>

    <?php if ($message): ?>
        <div class="message <?= $message_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="dashboard-form">
        <input type="file" name="translation_file" required>
        <button type="submit">📤 Télécharger</button>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Nom du fichier</th>
                    <th>Date d'envoi</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars(basename($row['file_path'])) ?></td>
                        <td><?= htmlspecialchars($row['upload_date']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td>
                            <a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="btn-view">Voir</a>
                            <a href="<?= htmlspecialchars($row['file_path']) ?>" download class="btn-download">Télécharger</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align:center; margin-top:30px; font-weight:600; color:#555;">
            Aucun fichier téléchargé pour l'instant.
        </p>
    <?php endif; ?>

    <a href="dashboard.php" class="back-btn">⬅ Retour au tableau de bord</a>
</div>

</body>
</html>

