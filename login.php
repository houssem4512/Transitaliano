<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $identifier = trim($_POST["identifier"]); // nom d’utilisateur ou email
    $password = $_POST["password"];

    // Requête : trouver utilisateur par username OU email
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $username, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Session unifiée
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role; // 'admin' ou 'user'

            // Redirection selon rôle
            if ($role === 'admin') {
                header("Location: admin_dashboard.php");
                exit;
            } else {
                header("Location: dashboard.php");
                exit;
            }
        } else {
            $error = "Mot de passe incorrect.";
        }
    } else {
        $error = "Nom d’utilisateur ou e-mail introuvable.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Connexion</title>

  <link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet"/>
  <script src="https://kit.fontawesome.com/a81368914c.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="assets/css/style.css" />
  <style>
    .error-msg {
        color: red;
        margin-bottom: 10px;
        text-align: center;
    }

    /* Bouton Home en haut à gauche */
    .btn-home-top {
        position: fixed;
        top: 20px;
        left: 20px;
        background: #38d39f;
        color: white;
        padding: 10px 20px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 600;
        transition: 0.3s;
        z-index: 1000;
    }
    .btn-home-top:hover {
        background: #2eb389;
    }
  </style>
</head>
<body>
  <!-- Bouton Accueil -->
  <a href="index.php" class="btn-home-top">Accueil</a>

  <img src="assets/img/wave.png" class="wave" alt="background" />
  <div class="container">
    <div class="img">
      <img src="assets/img/bg.svg" alt="illustration connexion" />
    </div>

    <div class="login-content glass">
      <form action="" method="POST">
        <img src="assets/img/avatar.svg" alt="Avatar" />
        <h2 class="title">Bon retour</h2>

        <?php if (!empty($error)): ?>
          <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="input-div one">
          <div class="i"><i class="fas fa-user"></i></div>
          <div class="div">
            <h5>Nom d’utilisateur ou e-mail</h5>
            <input type="text" class="input" name="identifier" required />
          </div>
        </div>

        <div class="input-div pass">
          <div class="i"><i class="fas fa-lock"></i></div>
          <div class="div">
            <h5>Mot de passe</h5>
            <input type="password" class="input" name="password" required />
          </div>
        </div>

        <a href="#">Mot de passe oublié ?</a>
        <input type="submit" class="btn" value="Se connecter" />

        <p class="redirect">Vous n’avez pas de compte ? <a href="signup.php">S’inscrire</a></p>
      </form>
    </div>
  </div>

  <script src="assets/js/main.js"></script>
</body>
</html>

