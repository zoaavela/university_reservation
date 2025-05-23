<?php
// Ce script met à jour la base de données avec les nouvelles tables et champs

// Informations de connexion
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mmi_reservation";

// Connexion à MySQL
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Vérifier la connexion
if (!$conn) {
    die("Connexion échouée: " . mysqli_connect_error());
}
echo "Connexion réussie à MySQL<br>";

// Ajouter la table commentaire
$sql = "CREATE TABLE IF NOT EXISTS commentaire (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT(11) NOT NULL,
    utilisateur_id INT(11) NOT NULL,
    contenu TEXT NOT NULL,
    note INT(1) DEFAULT 5,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservation(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Table commentaire créée<br>";
} else {
    echo "Erreur: " . mysqli_error($conn);
}

// Ajouter la table notification
$sql = "CREATE TABLE IF NOT EXISTS notification (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT(11) NOT NULL,
    titre VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    lue TINYINT(1) DEFAULT 0,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Table notification créée<br>";
} else {
    echo "Erreur: " . mysqli_error($conn);
}

// Vérifier si la colonne commentaire existe dans la table reservation
$sql = "SHOW COLUMNS FROM reservation LIKE 'commentaire_admin'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) == 0) {
    // Ajouter la colonne commentaire_admin à la table reservation
    $sql = "ALTER TABLE reservation ADD COLUMN commentaire_admin TEXT AFTER motif";
    if (mysqli_query($conn, $sql)) {
        echo "Colonne commentaire_admin ajoutée à la table reservation<br>";
    } else {
        echo "Erreur: " . mysqli_error($conn);
    }
}

// Fermer la connexion
mysqli_close($conn);
echo "<br>Base de données mise à jour avec succès!";
?>
