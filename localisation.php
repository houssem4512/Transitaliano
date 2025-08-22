<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Localisation de notre magasin</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
        }
        #map {
            flex: 1;
        }
        .info-box {
            padding: 20px;
            text-align: center;
            background: #38d39f;
            color: white;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(56, 211, 159, 0.3);
        }
        .back-button {
            display: block;
            width: fit-content;
            margin: 10px auto;
            padding: 10px 20px;
            background-color: #38d39f;
            color: white;
            text-decoration: none;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(56, 211, 159, 0.3);
            transition: background 0.3s;
        }
        .back-button:hover {
            background-color: #2eb389;
        }
    </style>
</head>
<body>
    <div class="info-box">
        📍 notre bureau est  ici !
    </div>
    <div id="map"></div>
    <a href="index.php" class="back-button">← Retour à l'accueil</a>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Coordinates of your store
        const storeLat = 35.8280278 ;  // CHANGE THIS latitude
        const storeLng = 10.6413056 ;  // CHANGE THIS longitude

        const map = L.map('map').setView([storeLat, storeLng], 15);

        // OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Marker
        const marker = L.marker([storeLat, storeLng]).addTo(map);
        marker.bindPopup("<b>Notre bureau</b><br>Venez nous visiter !").openPopup();
    </script>
</body>
</html>
