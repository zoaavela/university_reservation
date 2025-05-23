-- Mise à jour de la base de données pour ajouter les tables nécessaires

-- Table pour les commentaires
CREATE TABLE IF NOT EXISTS commentaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    commentaire TEXT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Table pour les notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    message TEXT NOT NULL,
    lu BOOLEAN DEFAULT FALSE,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Ajout de colonnes supplémentaires à la table salles
ALTER TABLE salles ADD COLUMN IF NOT EXISTS image VARCHAR(255) DEFAULT NULL;
ALTER TABLE salles ADD COLUMN IF NOT EXISTS capacite INT DEFAULT 0;
ALTER TABLE salles ADD COLUMN IF NOT EXISTS description TEXT DEFAULT NULL;

-- Ajout de colonnes supplémentaires à la table reservations
ALTER TABLE reservations ADD COLUMN IF NOT EXISTS statut ENUM('en_attente', 'approuvee', 'rejetee') DEFAULT 'en_attente';
ALTER TABLE reservations ADD COLUMN IF NOT EXISTS raison_rejet TEXT DEFAULT NULL;
