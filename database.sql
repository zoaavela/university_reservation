-- Base de données: `mmi_reservation`
--
CREATE DATABASE IF NOT EXISTS `mmi_reservation` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `mmi_reservation`;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('etudiant','enseignant','admin','agent') NOT NULL,
  `numero_etudiant` varchar(20) DEFAULT NULL,
  `filiere` varchar(50) DEFAULT NULL,
  `annee_etude` varchar(20) DEFAULT NULL,
  `groupe_tp` varchar(10) DEFAULT NULL,
  `groupe_td` varchar(10) DEFAULT NULL,
  `departement` varchar(50) DEFAULT NULL,
  `matiere` varchar(100) DEFAULT NULL,
  `bureau` varchar(20) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Structure de la table `materiel`
--

CREATE TABLE `materiel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `categorie` varchar(50) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `disponible` tinyint(1) NOT NULL DEFAULT 1,
  `image` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Structure de la table `salle`
--

CREATE TABLE `salle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `batiment` varchar(50) NOT NULL,
  `etage` varchar(10) NOT NULL,
  `capacite` int(11) NOT NULL,
  `equipements` text DEFAULT NULL,
  `disponible` tinyint(1) NOT NULL DEFAULT 1,
  `image` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Structure de la table `reservation`
--

CREATE TABLE `reservation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `materiel_id` int(11) DEFAULT NULL,
  `salle_id` int(11) DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `motif` text NOT NULL,
  `statut` enum('en_attente','approuvee','refusee','terminee') NOT NULL DEFAULT 'en_attente',
  `commentaire` text DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `materiel_id` (`materiel_id`),
  KEY `salle_id` (`salle_id`),
  CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`materiel_id`) REFERENCES `materiel` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservation_ibfk_3` FOREIGN KEY (`salle_id`) REFERENCES `salle` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Structure de la table `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `titre` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `lue` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Données de test
--

-- Utilisateur admin (mot de passe: admin123)
INSERT INTO `utilisateur` (`nom`, `prenom`, `email`, `mot_de_passe`, `role`) VALUES
('Admin', 'Système', 'admin@mmi.fr', '$2y$10$8MNE.3xQlBYAKX3uveZKh.1/aGWLNAJUGJHaKQQWnvNxFAqUEkfZW', 'admin');

-- Quelques matériels
INSERT INTO `materiel` (`nom`, `description`, `categorie`, `quantite`, `disponible`, `image`) VALUES
('Appareil photo Canon EOS 80D', 'Appareil photo reflex numérique avec objectif 18-55mm', 'Photo', 5, 1, 'canon_eos.jpg'),
('Caméra Sony HXR-NX100', 'Caméra professionnelle Full HD', 'Vidéo', 3, 1, 'sony_hxr.jpg'),
('Microphone Rode NTG-3', 'Microphone directionnel pour enregistrement audio', 'Audio', 4, 1, 'rode_ntg3.jpg'),
('Trépied Manfrotto', 'Trépied professionnel pour appareil photo et caméra', 'Photo', 8, 1, 'trepied.jpg'),
('Tablette graphique Wacom Intuos Pro', 'Tablette graphique pour designers', 'Design', 6, 1, 'wacom.jpg'),
('Enregistreur Zoom H4n', 'Enregistreur audio portable 4 pistes', 'Audio', 5, 1, 'zoom_h4n.jpg'),
('MacBook Pro 16"', 'Ordinateur portable Apple pour montage vidéo et design', 'Informatique', 10, 1, 'macbook.jpg'),
('Steadicam', 'Stabilisateur pour caméra', 'Vidéo', 2, 1, 'steadicam.jpg');

-- Quelques salles
INSERT INTO `salle` (`nom`, `description`, `batiment`, `etage`, `capacite`, `equipements`, `disponible`, `image`) VALUES
('Studio Photo', 'Studio photo équipé pour les shootings professionnels', 'Bâtiment A', '1', 15, 'Fond vert, éclairages, réflecteurs', 1, 'studio_photo.jpg'),
('Salle Informatique 101', 'Salle équipée de 20 postes Mac pour le design et le développement', 'Bâtiment B', '1', 20, '20 iMac, vidéoprojecteur, tableau blanc', 1, 'salle_info.jpg'),
('Studio Son', 'Studio d\'enregistrement audio professionnel', 'Bâtiment A', '2', 8, 'Console de mixage, microphones, cabine d\'enregistrement', 1, 'studio_son.jpg'),
('Salle de Montage', 'Salle dédiée au montage vidéo et audio', 'Bâtiment C', '1', 12, '12 postes de montage, logiciels Adobe', 1, 'salle_montage.jpg'),
('Amphithéâtre MMI', 'Grand amphithéâtre pour les cours magistraux', 'Bâtiment Principal', '0', 120, 'Système de sonorisation, vidéoprojecteur, écran géant', 1, 'amphi.jpg'),
('Salle de Réunion', 'Salle pour les réunions et travaux de groupe', 'Bâtiment B', '2', 10, 'Table de conférence, écran, tableau blanc', 1, 'salle_reunion.jpg');
