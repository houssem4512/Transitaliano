<?php
session_start();
include 'db.php';

// Vérification admin
if (!isset($_SESSION['admin_id']) && !(isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Supprimer l'utilisateur
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Retour à la liste des utilisateurs
header("Location: manage_users.php");
exit;
?>
