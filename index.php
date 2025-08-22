<?php
session_start();
include __DIR__ . '/db.php';

function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Exemple de session après login :
// $_SESSION['user_id']
// $_SESSION['username']
// $_SESSION['role'] // 'admin' ou 'user'

// Récupération actualités
$sql = "SELECT id, title, content, published_at FROM news WHERE published = 1 ORDER BY published_at DESC LIMIT 6";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8" />
<title>Accueil - Actualités Dynamiques</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #38d39f;
    --primary-dark: #2eb389;
    --bg-light: #f4fff9;
    --text-dark: #222;
    --card-shadow: rgba(0,0,0,0.08);
}
* {margin:0;padding:0;box-sizing:border-box;}
body {
    font-family: 'Inter', sans-serif;
    background: var(--bg-light);
    color: var(--text-dark);
    overflow-x: hidden;
}

/* Navbar */
nav {
    position: fixed;
    top:0; left:0; width:100%;
    background: rgba(56,211,159,0.95);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 50px;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
nav .logo {
    display:flex;
    align-items:center;
    gap:10px;
}
.nav-logo {
    max-width: 60px;
    max-height: 60px;
    display: block;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
nav a {
    color:white;
    text-decoration:none;
    font-weight:600;
    margin-left:18px;
    padding:8px 20px;
    border-radius:30px;
    transition:0.3s;
}
nav a:hover {
    background:white;
    color:var(--primary);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Hero */
.hero {
    height: 90vh;
    background: linear-gradient(-45deg, #38d39f, #2eb389, #40e0d0, #2bc48d);
    background-size: 400% 400%;
    animation: gradientBG 15s ease infinite;
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    color:white;
    text-align:center;
    padding-top:70px;
}
@keyframes gradientBG {
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}
.hero h1 { font-size:3.5rem; font-weight:900; margin-bottom:16px; text-shadow: 0 2px 8px rgba(0,0,0,0.2); }
.hero p { font-size:1.4rem; opacity:0.9; }
.hero .location { margin-top:24px; font-size:1.1rem; background: rgba(255,255,255,0.2); padding:6px 12px; border-radius:20px; }
.hero .dashboard-btn {
    margin-top:30px;
    padding:12px 28px;
    background:white;
    color: var(--primary-dark);
    font-weight:700;
    border:none;
    border-radius:30px;
    cursor:pointer;
    transition:0.3s;
    text-decoration:none;
}
.hero .dashboard-btn:hover { background: var(--primary-dark); color:white; }

/* Actualités Section */
section.news {
    max-width:1200px;
    margin: -80px auto 60px;
    padding: 50px 30px;
    background:white;
    border-radius:20px;
    box-shadow: 0 12px 40px var(--card-shadow);
    position: relative;
    z-index:1;
}
section.news h2 {
    font-size:2.2rem;
    font-weight:900;
    color: var(--primary-dark);
    margin-bottom:40px;
    text-align:center;
}
section.news h2::after {
    content:'';
    display:block;
    width:80px;
    height:4px;
    background: var(--primary);
    margin:8px auto 0;
    border-radius:2px;
}

/* News Grid */
.news-grid {
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap:28px;
}
.news-item {
    background: var(--bg-light);
    border-radius:20px;
    padding:25px 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display:flex; flex-direction:column;
}
.news-item:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 36px rgba(0,0,0,0.12);
}
.news-title { font-size:1.4rem; font-weight:800; margin-bottom:10px; color: var(--primary-dark); }
.news-date { font-size:0.9rem; color:#666; margin-bottom:16px; }
.news-content { font-size:1rem; color:#333; flex-grow:1; }

/* Footer */
footer { background: #fff; padding: 25px; text-align:center; color: var(--primary-dark); font-weight:600; box-shadow: 0 -4px 12px rgba(0,0,0,0.05); }

/* Animations for fade-in effect */
.news-item { opacity:0; transform: translateY(20px); animation: fadeInUp 0.8s forwards; }
.news-item:nth-child(1) { animation-delay: 0.1s; }
.news-item:nth-child(2) { animation-delay: 0.2s; }
.news-item:nth-child(3) { animation-delay: 0.3s; }
.news-item:nth-child(4) { animation-delay: 0.4s; }
.news-item:nth-child(5) { animation-delay: 0.5s; }
.news-item:nth-child(6) { animation-delay: 0.6s; }
@keyframes fadeInUp { to { opacity:1; transform: translateY(0); } }
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

        <?php if(isset($_SESSION['role'])): ?>
            <?php if($_SESSION['role'] === 'admin'): ?>
                <a href="admin_dashboard.php">Admin Dashboard</a>
            <?php elseif($_SESSION['role'] === 'user'): ?>
                <a href="dashboard.php">Client Dashboard</a>
            <?php endif; ?>
            <a href="logout.php">Déconnexion</a>
        <?php else: ?>
            <a href="signup.php">Inscription</a>
            <a href="login.php">Connexion</a>
        <?php endif; ?>
    </div>
</nav>

<header class="hero">
    <h1>Bienvenue sur notre plateforme Traditaliano</h1>   
    <p>Les dernières actualités à portée de main</p>
    <div class="location" id="user-location">Détection de votre localisation...</div>

    <?php if(isset($_SESSION['role'])): ?>
        <?php if($_SESSION['role'] === 'admin'): ?>
            <a href="admin_dashboard.php" class="dashboard-btn">Aller au Dashboard Admin</a>
        <?php elseif($_SESSION['role'] === 'user'): ?>
            <a href="dashboard.php" class="dashboard-btn">Aller à mon espace client</a>
        <?php endif; ?>
    <?php endif; ?>
</header>

<section class="news">
    <h2>📰 Actualités</h2>
    <div class="news-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="news-item">
                    <div class="news-title"><?php echo e($row['title']); ?></div>
                    <div class="news-date"><?php echo e(date('d/m/Y', strtotime($row['published_at']))); ?></div>
                    <div class="news-content"><?php echo nl2br(e($row['content'])); ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;color:var(--primary-dark);">Aucune actualité disponible.</p>
        <?php endif; ?>
    </div>
</section>

<footer>
    &copy; <?php echo date('Y'); ?> Traditaliano
</footer>

<script>
// Geolocation
document.addEventListener("DOMContentLoaded", () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const { latitude, longitude } = pos.coords;
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latitude}&lon=${longitude}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById("user-location").innerText = ` Vous êtes à ${data.address.city || data.address.town || data.address.village || 'votre région'}`;
                })
                .catch(() => {
                    document.getElementById("user-location").innerText = " Localisation non disponible";
                });
        }, () => {
            document.getElementById("user-location").innerText = " Localisation refusée";
        });
    } else {
        document.getElementById("user-location").innerText = " Localisation non supportée";
    }
});
</script>

</body>
</html>
