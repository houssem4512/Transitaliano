-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 22 août 2025 à 08:54
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `awesome_dashboard_project`
--

-- --------------------------------------------------------

--
-- Structure de la table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `appointment_date`, `appointment_time`, `reason`, `status`, `created_at`) VALUES
(1, 2, '2025-08-13', '04:05:00', 'SD', 'Cancelled', '2025-08-13 04:05:28'),
(2, 2, '2025-08-13', '22:20:00', 'qsdq', 'Cancelled', '2025-08-13 20:32:17'),
(3, 2, '2025-08-16', '04:46:00', '', 'Confirmed', '2025-08-16 21:49:47');

-- --------------------------------------------------------

--
-- Structure de la table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `published` tinyint(1) DEFAULT 0,
  `published_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `published`, `published_at`) VALUES
(1, 'qsdsqd', 'qsdqs', 1, '2025-08-16 08:44:02'),
(2, 'hhhh', 'hh', 1, '2025-08-17 18:19:23');

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `priority` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `content`, `link`, `type`, `is_read`, `priority`, `created_at`, `expires_at`) VALUES
(6, NULL, 'qsd', 'qsdqsd', '', 0, 1, '2025-08-13 20:28:31', NULL),
(7, NULL, 'qsdsq', 'qsd', '', 0, 1, '2025-08-13 20:30:10', NULL),
(8, NULL, 'qsdsq', 'qsd', '', 0, 1, '2025-08-13 20:30:10', NULL),
(9, NULL, 'qsd', 'qsdqsdqsd', '', 0, 1, '2025-08-13 20:30:58', NULL),
(15, NULL, 'aaa', 'aaaa', 'aaa', 0, 1, '2025-08-13 21:46:56', NULL),
(19, 2, 'Votre traduction #4 est terminée!', '#', 'info', 1, 1, '2025-08-16 09:24:10', NULL),
(20, 2, 'Votre traduction #4 est terminée!', '#', 'info', 1, 1, '2025-08-16 09:24:42', NULL),
(22, 2, 'Votre traduction \"1755338960_diagramme_de_C_v2.png\" est terminée!', '#', 'info', 1, 1, '2025-08-16 10:09:20', NULL),
(23, 2, 'Votre traduction \"1755341758_1755336250_1755030841_MemoSQL__1_.pdf\" est terminée!', '#', 'info', 1, 1, '2025-08-16 10:55:58', NULL),
(24, 2, 'Votre traduction \"1755381159_1755336282_1755334898_diagramme_de_C_v2.png\" est terminée!', '#', 'info', 1, 1, '2025-08-16 21:52:39', NULL),
(26, 2, 'Votre traduction \"1755454654_1755336282_1755334898_diagramme_de_C_v2.png\" est terminée!', '#', 'info', 1, 1, '2025-08-17 18:17:34', NULL),
(30, 2, 'Votre traduction \"1755667087_diagramme_de_sequence_actualit__s.png\" est terminée!', '#', 'info', 1, 1, '2025-08-20 05:18:07', NULL),
(31, 2, '00', NULL, 'info', 0, 1, '2025-08-22 06:14:16', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `translations`
--

CREATE TABLE `translations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `translated_file_path` varchar(255) DEFAULT NULL,
  `status` enum('En attente','En cours','Terminé') NOT NULL,
  `upload_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `translations`
--

INSERT INTO `translations` (`id`, `user_id`, `file_path`, `translated_file_path`, `status`, `upload_date`) VALUES
(1, 2, 'uploads/1755028071_Nouveau_document_texte.txt', NULL, 'En attente', '2025-08-12 19:47:51'),
(2, 2, 'uploads/1755029357_Nouveau_document_texte.txt', NULL, 'En attente', '2025-08-12 20:09:17'),
(3, 2, 'uploads/1755030841_MemoSQL.pdf', 'uploads/1755338960_diagramme_de_C_v2.png', 'Terminé', '2025-08-12 20:34:01'),
(4, 2, 'uploads/1755112176_Nouveau_document_texte.txt', 'uploads/1755336715_1755334898_diagramme_de_C_v2.png', 'Terminé', '2025-08-13 19:09:36'),
(5, 2, 'uploads/1755340292_1755112176_Nouveau_document_texte.txt', NULL, 'En attente', '2025-08-16 10:31:32'),
(6, 2, 'uploads/1755341146_1755112176_Nouveau_document_texte.txt', NULL, 'En cours', '2025-08-16 10:45:46'),
(7, 2, 'uploads/1755341727_1755112176_Nouveau_document_texte.txt', 'uploads/1755381173_1755336250_1755030841_MemoSQL__1_.pdf', 'Terminé', '2025-08-16 10:55:27'),
(8, 2, 'uploads/1755380959_1755112176_Nouveau_document_texte.txt', 'uploads/1755381159_1755336282_1755334898_diagramme_de_C_v2.png', 'Terminé', '2025-08-16 21:49:19'),
(9, 2, 'uploads/1755454074_1755112176_Nouveau_document_texte.txt', 'uploads/1755667087_diagramme_de_sequence_actualit__s.png', 'Terminé', '2025-08-17 18:07:54'),
(10, 2, 'uploads/1755454117_version.txt', 'uploads/1755454654_1755336282_1755334898_diagramme_de_C_v2.png', 'Terminé', '2025-08-17 18:08:37'),
(11, 5, 'uploads/1755660901_1755112176_Nouveau_document_texte.txt', NULL, 'Terminé', '2025-08-20 03:35:01');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `notify_email` tinyint(1) DEFAULT 0,
  `notify_dashboard` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `latitude`, `longitude`, `notify_email`, `notify_dashboard`) VALUES
(1, 'hoss', 'hoss@gmail.com', '$2y$10$T86RQ2aqexC7557FYM9/W.Pfxoh46scbUpncjRL.f6lCGsXF0suDy', 'admin', '2025-08-07 20:21:11', NULL, NULL, 0, 0),
(2, 'samer', 'samer@gmail.com', '$2y$10$NuuA4YKY5gdLw1M/BywTbOANTp6uaY6MRH5ehVtBqEiMKohzcGKhe', 'user', '2025-08-07 20:58:03', 999.9999999, 999.9999999, 1, 1),
(3, 'serine', 'serine@gmail.com', '$2y$10$2pbB/kKcEGoulLdrleftzepRqC.W/5eoElnt388CfxrdpajDPuEM.', 'user', '2025-08-08 16:39:47', NULL, NULL, 0, 0),
(4, 'sirine', 'sirine@gmail.com', '$2y$10$kfpFAkfMz7UIRFWnmGvnY.r8Y6/hju.RtXyTp9L0md.OnqZa24XXW', 'user', '2025-08-08 16:54:09', NULL, NULL, 0, 0),
(5, 'aa', 'aa@gmail.com', '$2y$10$33ewYKUjpPnPVEt1t4lZie1a2Fp/alu5CljoBzAaEyr7ZSwu5UnKC', 'user', '2025-08-20 03:31:17', NULL, NULL, 0, 0),
(6, 'houssembelhabib04@gmail.com', 'aaa@gmail.com', '$2y$10$vazk7T8Vh2z7uE1Iisv3du6u.VSDf/clvWBhOf7T.h8P.6IbFvZqa', '', '2025-08-22 06:07:00', NULL, NULL, 0, 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT pour la table `translations`
--
ALTER TABLE `translations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `translations`
--
ALTER TABLE `translations`
  ADD CONSTRAINT `translations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
