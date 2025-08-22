<?php
session_start();

// Determine user role and dashboard link
$user_role = $_SESSION['role'] ?? 'guest';
$dashboard_link = '';

if ($user_role === 'admin') {
    $dashboard_link = 'admin_dashboard.php';
} elseif ($user_role === 'user') {
    $dashboard_link = 'dashboard.php';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8" />
<title>À propos - Traditaliano</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #38d39f;
    --primary-dark: #2eb389;
    --bg-light: #f4fff9;
}
* {margin:0;padding:0;box-sizing:border-box;}
body {
    font-family: 'Inter', sans-serif;
    background: var(--bg-light);
    color: #222;
}
/* Navbar */
nav {
    position: fixed;
    top:0;left:0;width:100%;
    background: rgba(56,211,159,0.9);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 40px;
    z-index: 1000;
}
.logo {
    display:flex;align-items:center;gap:10px;
}
.nav-logo {
    max-width: 60px;
    max-height: 60px;
    display:block;
    border-radius:12px;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}
nav a {
    color:white;
    text-decoration:none;
    font-weight:600;
    margin-left:20px;
    padding:8px 18px;
    border-radius:30px;
    transition:0.3s;
}
nav a:hover {
    background:white;
    color:var(--primary);
}
/* Hero */
.hero {
    height: 50vh;
    background: linear-gradient(-45deg, #38d39f, #2eb389, #40e0d0, #2bc48d);
    background-size: 400% 400%;
    animation: gradientBG 12s ease infinite;
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    color:white;
    text-align:center;
    padding-top:60px;
}
@keyframes gradientBG {
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}
.hero h1 {font-size:2.5rem;font-weight:900;margin-bottom:12px;}
.hero p {font-size:1.2rem;opacity:0.9;}
.hero .dashboard-btn {
    margin-top:20px;
    padding:10px 25px;
    background:white;
    color: var(--primary-dark);
    font-weight:700;
    border:none;
    border-radius:30px;
    cursor:pointer;
    text-decoration:none;
    transition:0.3s;
}
.hero .dashboard-btn:hover { background: var(--primary-dark); color:white; }
/* About Content */
section.about {max-width:1100px;margin: 60px auto;padding: 40px;background:white;border-radius:20px;box-shadow: 0 8px 40px rgba(0,0,0,0.08);}
.about-flex {display:flex;flex-wrap:wrap;gap:30px;align-items:center;}
.about-flex img {max-width:500px;width:100%;border-radius:20px;}
.about-text {flex:1;}
.about-text h2 {font-size:2rem;color:var(--primary-dark);margin-bottom:10px;}
.about-text p {line-height:1.6;color:#444;}
/* Mission/Vision/Values */
.cards {display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:40px;}
.card {background: var(--bg-light);padding:20px;border-radius:16px;text-align:center;box-shadow:0 4px 16px rgba(0,0,0,0.05);transition:transform 0.3s, box-shadow 0.3s;}
.card:hover {transform:translateY(-6px);box-shadow:0 8px 28px rgba(0,0,0,0.08);}
.card h3 {color:var(--primary-dark);margin-bottom:10px;}
.card p {color:#555;font-size:0.95rem;}
/* Team Section */
section.team {max-width:1100px;margin: 60px auto;padding: 20px;}
.team h2 {text-align:center;color:var(--primary-dark);margin-bottom:20px;}
.team-grid {display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;}
.team-member {background:white;padding:20px;border-radius:16px;text-align:center;box-shadow:0 4px 16px rgba(0,0,0,0.05);}
.team-member img {width:120px;height:120px;object-fit:cover;border-radius:50%;margin-bottom:10px;}
.team-member h4 {color:var(--primary-dark);}
.team-member p {font-size:0.9rem;color:#666;}
/* Footer */
footer {background:white;padding: 20px;text-align:center;color: var(--primary-dark);font-weight:600;box-shadow: 0 -2px 10px rgba(0,0,0,0.05);}
@media(max-width:768px){.about-flex{flex-direction:column;text-align:center;}}
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

        <?php if($user_role === 'guest'): ?>
            <a href="signup.php">Inscription</a>
            <a href="login.php">Connexion</a>
        <?php else: ?>
            <a href="<?= $dashboard_link ?>">
                <?= $user_role === 'admin' ? 'Admin Dashboard' : 'Client Dashboard' ?>
            </a>
            <a href="logout.php">Déconnexion</a>
        <?php endif; ?>
    </div>
</nav>

<header class="hero">
    <h1>À propos de notre bureau</h1>
    <p>Experts en traduction et localisation professionnelle</p>

    <?php if($user_role !== 'guest'): ?>
        <a href="<?= $dashboard_link ?>" class="dashboard-btn">
            <?= $user_role === 'admin' ? 'Admin Dashboard' : 'Client Dashboard' ?>
        </a>
    <?php endif; ?>
</header>

<section class="about">
    <div class="about-flex">
        <div class="about-text">
            <h2>Notre histoire</h2>
            <p>Fondé par des passionnés des langues et de la communication, notre bureau de traduction accompagne
            particuliers et entreprises dans la gestion multilingue de leurs documents depuis plusieurs années.</p>
            <p>Grâce à une équipe diversifiée de traducteurs experts et à l’utilisation d’outils technologiques modernes,
            nous garantissons des traductions précises, rapides et adaptées à chaque contexte.</p>
        </div>
    </div>
    <div class="cards">
        <div class="card"><h3>Notre Mission</h3><p>Faciliter la communication internationale en fournissant des traductions fiables, fidèles et de haute qualité.</p></div>
        <div class="card"><h3>Notre Vision</h3><p>Devenir le partenaire de référence pour les entreprises et particuliers cherchant à se développer à l’international.</p></div>
        <div class="card"><h3>Nos Valeurs</h3><p>Précision, confidentialité, respect culturel et excellence professionnelle sont au cœur de notre métier.</p></div>
    </div>
</section>

 <section class="team">
    </div>
</section>

<footer>
    &copy; <?= date('Y'); ?> Traditaliano
</footer>

</body>
</html>

