<?php
session_start();
require 'db.php'; // your DB connection

// Function to check if a column exists
function columnExists($conn, $table, $column) {
    $table = mysqli_real_escape_string($conn, $table);
    $column = mysqli_real_escape_string($conn, $column);
    $sql = "SHOW COLUMNS FROM `$table` LIKE '$column'";
    $res = $conn->query($sql);
    return ($res && $res->num_rows > 0);
}

// Ensure required columns exist
$requiredColumns = [
    'latitude' => "DECIMAL(10,7) NULL",
    'longitude' => "DECIMAL(10,7) NULL",
    'notify_email' => "TINYINT(1) DEFAULT 0",
    'notify_dashboard' => "TINYINT(1) DEFAULT 0"
];

foreach ($requiredColumns as $col => $type) {
    if (!columnExists($conn, 'users', $col)) {
        $conn->query("ALTER TABLE users ADD $col $type");
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $notify_email = isset($_POST['notify_email']) ? 1 : 0;
    $notify_dashboard = isset($_POST['notify_dashboard']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE users SET latitude=?, longitude=?, notify_email=?, notify_dashboard=? WHERE id=?");
    $stmt->bind_param("ddiii", $latitude, $longitude, $notify_email, $notify_dashboard, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();

    $message = "Paramètres mis à jour avec succès.";
}

// Get current user settings
$stmt = $conn->prepare("SELECT latitude, longitude, notify_email, notify_dashboard FROM users WHERE id=?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($latitude, $longitude, $notify_email, $notify_dashboard);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin-top: 50px; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        h2 { color: #38d39f; }
        .form-check-label { margin-left: 8px; }
        .btn-primary { background-color: #38d39f; border: none; }
        .btn-primary:hover { background-color: #2eb389; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mb-4">Paramètres</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="latitude" class="form-label">Latitude</label>
            <input type="text" step="0.0000001" name="latitude" id="latitude" value="<?= htmlspecialchars($latitude ?? '') ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label for="longitude" class="form-label">Longitude</label>
            <input type="text" step="0.0000001" name="longitude" id="longitude" value="<?= htmlspecialchars($longitude ?? '') ?>" class="form-control">
        </div>

        <div class="form-check">
            <input type="checkbox" name="notify_email" id="notify_email" class="form-check-input" <?= ($notify_email ?? 0) ? 'checked' : '' ?>>
            <label for="notify_email" class="form-check-label">Recevoir des notifications par email</label>
        </div>
        <div class="form-check">
            <input type="checkbox" name="notify_dashboard" id="notify_dashboard" class="form-check-input" <?= ($notify_dashboard ?? 0) ? 'checked' : '' ?>>
            <label for="notify_dashboard" class="form-check-label">Recevoir des notifications sur le tableau de bord</label>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Enregistrer</button>
        <a href="index.php" class="btn btn-secondary mt-3">Retour</a>
    </form>
</div>

</body>
</html>
