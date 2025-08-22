<?php
session_start();

// Determine user role safely
$user_role = $_SESSION['role'] ?? 'guest'; // 'admin', 'user', or undefined
$dashboard_link = '';
if ($user_role === 'admin') {
    $dashboard_link = 'admin_dashboard.php';
} elseif ($user_role === 'user') {
    $user_role = 'client'; // for display
    $dashboard_link = 'dashboard.php';
} else {
    $user_role = 'guest';
}

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$alert = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = htmlspecialchars(trim($_POST['name']));
    $email   = htmlspecialchars(trim($_POST['email']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = htmlspecialchars(trim($_POST['message']));

    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'houssembelhabib04@gmail.com';
            $mail->Password   = 'iwaxguwttjmypfdi';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('houssembelhabib04@gmail.com', 'Bureau de traduction Contact');
            $mail->addReplyTo($email, $name);
            $mail->addAddress('hsss4@gmail.com', 'Admin');

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = "
                <h3>Nouveau message de contact</h3>
                <p><strong>Nom:</strong> $name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Sujet:</strong> $subject</p>
                <p><strong>Message:</strong><br>$message</p>
            ";

            $mail->send();
            $alert = '<div class="alert success">✅ Merci! Votre message a été envoyé avec succès.</div>';
        } catch (Exception $e) {
            $alert = '<div class="alert error">❌ Message non envoyé. Erreur: ' . $mail->ErrorInfo . '</div>';
        }
    } else {
        $alert = '<div class="alert error">⚠️ Veuillez remplir tous les champs.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8" />
<title>Contact - Traditaliano</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">
<style>
:root{--primary:#38d39f;--primary-dark:#2eb389;--bg-light:#f4fff9;}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:var(--bg-light);color:#222;}
nav{position:fixed;top:0;left:0;width:100%;background:rgba(56,211,159,0.9);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:space-between;padding:12px 40px;z-index:1000;}
nav .logo{display:flex;align-items:center;gap:10px;}
.nav-logo{max-width:60px;max-height:60px;display:block;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.1);}
nav a{color:white;text-decoration:none;font-weight:600;margin-left:20px;padding:8px 18px;border-radius:30px;transition:0.3s;}
nav a:hover{background:white;color:var(--primary);}
main{max-width:1100px;margin:100px auto;padding:20px;}
.contact-container{display:grid;grid-template-columns:1fr 1fr;gap:40px;}
.contact-form{background:white;padding:30px;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);}
.contact-form h2{color: var(--primary-dark);margin-bottom:20px;}
.contact-form label{font-weight:600;display:block;margin:12px 0 6px;}
.contact-form input, .contact-form textarea{width:100%;padding:10px;border:2px solid var(--primary);border-radius:10px;font-size:1rem;outline:none;transition:border 0.3s;}
.contact-form input:focus, .contact-form textarea:focus{border-color: var(--primary-dark);}
.contact-form button{background: var(--primary); color:white;border:none;padding:12px 20px;border-radius:30px;font-size:1rem;font-weight:600;cursor:pointer;margin-top:12px;transition:background 0.3s;}
.contact-form button:hover{background: var(--primary-dark);}
.alert{padding:12px;border-radius:8px;margin-bottom:20px;font-weight:600;}
.alert.success{background:#d4f8e8;color:#1b8a5a;}
.alert.error{background:#ffe0e0;color:#c0392b;}
.map-container iframe{width:100%;height:100%;min-height:350px;border-radius:16px;border:none;}
@media(max-width:900px){.contact-container{grid-template-columns:1fr;}}
</style>
</head>
<body>

<nav>
    <div class="logo">
        <img src="/img/logo2.png" alt="Logo" class="nav-logo">
        <span style="color:white;font-weight:900;font-size:1.3rem;">Traditaliano</span>
    </div>
    <div>
        <a href="index.php">Accueil</a>
        <a href="about.php">À propos</a>
        <a href="contact.php">Contact</a>
        <a href="localisation.php">Localisation</a>

        <?php if($user_role === 'admin'): ?>
            <a href="admin_dashboard.php">Admin Dashboard</a>
            <a href="logout.php">Déconnexion</a>
        <?php elseif($user_role === 'client'): ?>
            <a href="dashboard.php">Client Dashboard</a>
            <a href="logout.php">Déconnexion</a>
        <?php else: ?>
            <a href="signup.php">Inscription</a>
            <a href="login.php">Connexion</a>
        <?php endif; ?>
    </div>
</nav>

<main>
    <?= $alert ?>
    <div class="contact-container">
        <div class="contact-form">
            <h2>Envoyez-nous un message</h2>
            <form method="POST">
                <label>Nom :</label>
                <input type="text" name="name" required>
                <label>Email :</label>
                <input type="email" name="email" required>
                <label>Sujet :</label>
                <input type="text" name="subject" required>
                <label>Message :</label>
                <textarea name="message" rows="5" required></textarea>
                <button type="submit">📩 Envoyer</button>
            </form>
        </div>

        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!..." allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</main>

</body>
</html>

