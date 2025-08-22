<?php
session_start();
require_once "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle new appointment
if (isset($_POST['create_appointment'])) {
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $reason = $_POST['reason'];

    $stmt = $conn->prepare("INSERT INTO appointments (user_id, appointment_date, appointment_time, reason) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $date, $time, $reason);
    $stmt->execute();
    $stmt->close();
}

// Handle cancel appointment
if (isset($_POST['cancel_appointment'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("UPDATE appointments SET status='Cancelled' WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch user's appointments
$stmt = $conn->prepare("SELECT * FROM appointments WHERE user_id=? ORDER BY appointment_date DESC, appointment_time DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Traduction des statuts
$status_fr = [
    "Pending"   => "En attente",
    "Confirmed" => "Confirmé",
    "Cancelled" => "Annulé",
    "Completed" => "Terminé",
    "Upcoming"  => "À venir"
];

// Couleur associée à chaque statut
$status_class = [
    "Pending"   => "text-warning",
    "Confirmed" => "text-info",
    "Cancelled" => "text-danger",
    "Completed" => "text-primary",
    "Upcoming"  => "text-success"
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes rendez-vous</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body class="dashboard-body">

<header class="dashboard-header">
    <div class="header-content">
        <h1>📅 Mes rendez-vous</h1>
    </div>
</header>

<main class="dashboard-main">

    <!-- Appointment Form -->
    <div class="form-container">
        <h2>Prendre un rendez-vous</h2>
        <form method="post" class="dashboard-form">
            <label for="appointment_date">Date :</label>
            <input type="date" id="appointment_date" name="appointment_date" required>

            <label for="appointment_time">Heure :</label>
            <input type="time" id="appointment_time" name="appointment_time" required>

            <label for="reason">Raison :</label>
            <textarea id="reason" name="reason" placeholder="Indiquez la raison du rendez-vous"></textarea>

            <button type="submit" name="create_appointment" class="btn-primary">✅ Prendre rendez-vous</button>
        </form>
    </div>

    <!-- Appointments List -->
    <div class="table-container">
        <h2>Historique des rendez-vous</h2>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Raison</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): 
                    $status_text = $status_fr[$row['status']] ?? $row['status'];
                    $class = $status_class[$row['status']] ?? '';
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                        <td><?= htmlspecialchars($row['appointment_time']) ?></td>
                        <td><?= htmlspecialchars($row['reason']) ?></td>
                        <td class="<?= $class ?>"><?= $status_text ?></td>
                        <td>
                            <?php if ($row['status'] == 'Pending' || $row['status'] == 'Confirmed'): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="cancel_appointment" class="btn-small btn-danger">❌ Annuler</button>
                                </form>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Back Button -->
    <div class="back-btn-container">
        <a href="dashboard.php" class="btn-primary">⬅ Retour au tableau de bord</a>
    </div>
</main>

</body>
</html>
