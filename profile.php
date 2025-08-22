<?php 
session_start();
require_once "db.php";

// Check if user logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Helper function for safe output
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$message = "";
$error = "";

// Handle profile update
if (isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email)) {
        $error = "Le nom d'utilisateur et l'email sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format d'email invalide.";
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?");
            $stmt->bind_param("sssi", $username, $email, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
            $stmt->bind_param("ssi", $username, $email, $user_id);
        }
        if ($stmt->execute()) {
            $message = "Profil mis à jour avec succès.";
            $_SESSION['username'] = $username;
        } else {
            $error = "Erreur lors de la mise à jour.";
        }
        $stmt->close();
    }
}

// Handle delete profile
if (isset($_POST['delete_profile'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        session_destroy();
        header("Location: index.php?deleted=1");
        exit();
    } else {
        $error = "Erreur lors de la suppression du compte.";
    }
    $stmt->close();
}

// Fetch current user info
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Profil utilisateur</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/custom.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script>
        function confirmDelete() {
            let confirm1 = confirm("⚠️ Êtes-vous sûr de vouloir supprimer votre profil ?");
            if (confirm1) {
                let confirm2 = confirm("❌ Cette action est irréversible. Supprimer définitivement ?");
                return confirm2;
            }
            return false;
        }
    </script>
</head>
<body class="dashboard-body">
    <header class="dashboard-header">
        <div class="header-content">
            <h1>👤 Mon Profil</h1>
        </div>
    </header>

    <main class="dashboard-main">
        <div class="form-container" style="max-width: 480px; margin: auto;">
            <?php if ($message): ?>
                <div class="alert alert-success" style="background-color:#38d39f; color:#fff; padding:10px; border-radius:5px; margin-bottom:20px;">
                    <?= e($message) ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error" style="background-color:#e74c3c; color:#fff; padding:10px; border-radius:5px; margin-bottom:20px;">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <!-- Update Profile Form -->
            <form method="post" class="dashboard-form">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" value="<?= e($user['username']) ?>" required>

                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" value="<?= e($user['email']) ?>" required>

                <label for="password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                <input type="password" id="password" name="password" placeholder="••••••">

                <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••">

                <button type="submit" name="update_profile" class="btn-primary">💾 Mettre à jour</button>
            </form>

            <!-- Delete Profile Button -->
            <form method="post" onsubmit="return confirmDelete();" style="margin-top:20px; text-align:center;">
                <button type="submit" name="delete_profile" style="background-color:#e74c3c; color:white; padding:12px 20px; border:none; border-radius:6px; font-weight:bold; cursor:pointer;">
                    🗑 Supprimer mon compte
                </button>
            </form>

            <div class="back-btn-container" style="margin-top: 30px; text-align: center;">
                <a href="dashboard.php" class="btn-primary">⬅ Retour au tableau de bord</a>
            </div>
        </div>
    </main>
</body>
</html>
