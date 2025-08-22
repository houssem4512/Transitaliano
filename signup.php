<?php
include 'db.php';

session_start();

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $role = 'user';  // rôle par défaut

    // Validation basique
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format d'email invalide.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Cet email est déjà enregistré.";
            $stmt->close();
        } else {
            $stmt->close();
            // Insérer le nouvel utilisateur
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: login.php");
                exit;
            } else {
                $error = "Erreur : " . $stmt->error;
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Inscription</title>

  <link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet"/>
  <script src="https://kit.fontawesome.com/a81368914c.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="assets/css/style.css" />
  <style>
    body.signup-page { overflow-y: auto; }
    @media screen and (max-width: 900px) {
      .login-content { padding-bottom: 3rem; }
    }
    .error-msg {
      color: red;
      margin: 10px 0;
      font-size: 0.9rem;
    }
  </style>
</head>
<body class="signup-page">
  <img src="assets/img/wave.png" class="wave" alt="background" />
  <div class="container">
    <div class="img">
      <img src="assets/img/bg.svg" alt="signup-illustration" />
    </div>

    <div class="login-content">
      <form action="" method="POST" novalidate>
        <img src="assets/img/avatar.svg" alt="Avatar" />
        <h2 class="title">Créer un compte</h2>

        <?php if (!empty($error)): ?>
          <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="input-div one">
          <div class="i"><i class="fas fa-user"></i></div>
          <div class="div">
            <h5>Nom d’utilisateur</h5>
            <input type="text" class="input" name="username" value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" required />
          </div>
        </div>

        <div class="input-div one">
          <div class="i"><i class="fas fa-envelope"></i></div>
          <div class="div">
            <h5>Adresse e-mail</h5>
            <input type="email" class="input" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required />
          </div>
        </div>

        <div class="input-div pass">
          <div class="i"><i class="fas fa-lock"></i></div>
          <div class="div">
            <h5>Mot de passe</h5>
            <input type="password" class="input" name="password" required />
          </div>
        </div>

        <div class="input-div pass">
          <div class="i"><i class="fas fa-lock"></i></div>
          <div class="div">
            <h5>Confirmer le mot de passe</h5>
            <input type="password" class="input" name="confirm_password" required />
          </div>
        </div>

        <input type="submit" class="btn" value="S'inscrire" />
        <p class="redirect">Vous avez déjà un compte ? <a href="login.php">Se connecter</a></p>
      </form>
    </div>
  </div>

  <script src="assets/js/main.js"></script>
</body>
</html>
