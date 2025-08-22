<?php
session_start();
include 'db.php'; // connexion à la BD

// Vérifier si l’utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les données utilisateur
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();

// Gestion de la mise à jour
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    if (!empty($new_username) && !empty($new_email)) {
        if ($new_password) {
            $update = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
            $update->bind_param("sssi", $new_username, $new_email, $new_password, $user_id);
        } else {
            $update = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $update->bind_param("ssi", $new_username, $new_email, $user_id);
        }

        if ($update->execute()) {
            $success = "Profil mis à jour avec succès !";
            $_SESSION['username'] = $new_username;
            $username = $new_username;
            $email = $new_email;
        } else {
            $error = "Une erreur est survenue. Veuillez réessayer.";
        }
        $update->close();
    } else {
        $error = "Tous les champs sont obligatoires !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier le profil</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f6f9;
      margin: 0;
      padding: 0;
    }
    .container {
      width: 400px;
      margin: 60px auto;
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      color: #333;
    }
    label {
      font-weight: bold;
      color: #555;
      display: block;
      margin-bottom: 6px;
    }
    input[type="text"], input[type="email"], input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 16px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
    }
    .btn {
      width: 100%;
      background: #38d39f;
      border: none;
      padding: 12px;
      border-radius: 8px;
      color: #fff;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
    }
    .btn:hover {
      background: #2bb880;
    }
    .message {
      text-align: center;
      margin-bottom: 12px;
      font-size: 14px;
    }
    .success {
      color: #38d39f;
      font-weight: bold;
    }
    .error {
      color: red;
      font-weight: bold;
    }
    .back-btn {
      display: block;
      text-align: center;
      margin-top: 15px;
      text-decoration: none;
      color: #38d39f;
      font-weight: bold;
    }
    .back-btn:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Modifier le profil</h2>

    <?php if ($success): ?>
      <p class="message success"><?= $success ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
      <p class="message error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="username">Nom d’utilisateur</label>
      <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required>

      <label for="email">Adresse e-mail</label>
      <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

      <label for="password">Nouveau mot de passe (laisser vide pour conserver l’actuel)</label>
      <input type="password" name="password" placeholder="Saisir un nouveau mot de passe">

      <button type="submit" class="btn">Mettre à jour le profil</button>
    </form>

    <a href="admin_dashboard.php" class="back-btn">← Retour au tableau de bord</a>
  </div>
</body>
</html>
