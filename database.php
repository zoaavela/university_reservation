<?php
// Ce script crée la base de données et les tables

// Informations de connexion
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mmi_reservation";

// Connexion à MySQL
$conn = mysqli_connect($servername, $username, $password);

// Vérifier la connexion
if (!$conn) {
    die("Connexion échouée: " . mysqli_connect_error());
}
echo "Connexion réussie à MySQL<br>";

// Créer la base de données
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (mysqli_query($conn, $sql)) {
    echo "Base de données créée avec succès<br>";
} else {
    echo "Erreur: " . mysqli_error($conn);
}

// Sélectionner la base de données
mysqli_select_db($conn, $dbname);

// Créer la table utilisateur
$sql = "CREATE TABLE IF NOT EXISTS utilisateur (
id INT(11) AUTO_INCREMENT PRIMARY KEY,
nom VARCHAR(50) NOT NULL,
prenom VARCHAR(50) NOT NULL,
email VARCHAR(100) NOT NULL UNIQUE,
mot_de_passe VARCHAR(255) NOT NULL,
role VARCHAR(20) NOT NULL,
date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
numero_etudiant VARCHAR(20),
filiere VARCHAR(100),
annee_etude VARCHAR(20),
groupe_tp VARCHAR(10),
groupe_td VARCHAR(10)
)";

if (mysqli_query($conn, $sql)) {
    echo "Table utilisateur créée<br>";
} else {
    echo "Erreur: " . mysqli_error($conn);
}

// Créer la table materiel
$sql = "CREATE TABLE IF NOT EXISTS materiel (
id INT(11) AUTO_INCREMENT PRIMARY KEY,
nom VARCHAR(100) NOT NULL,
description TEXT,
quantite INT(11) NOT NULL,
disponible TINYINT(1) DEFAULT 1,
image VARCHAR(255),
categorie VARCHAR(50)
)";

if (mysqli_query($conn, $sql)) {
    echo "Table materiel créée<br>";
} else {
    echo "Erreur: " . mysqli_error($conn);
}

// Créer la table salle
$sql = "CREATE TABLE IF NOT EXISTS salle (
id INT(11) AUTO_INCREMENT PRIMARY KEY,
nom VARCHAR(50) NOT NULL,
capacite INT(11) NOT NULL,
description TEXT,
disponible TINYINT(1) DEFAULT 1,
equipements TEXT,
batiment VARCHAR(50),
etage VARCHAR(10),
image VARCHAR(255)
)";

if (mysqli_query($conn, $sql)) {
    echo "Table salle créée<br>";
} else {
    echo "Erreur: " . mysqli_error($conn);
}

// Créer la table reservation
$sql = "CREATE TABLE IF NOT EXISTS reservation (
id INT(11) AUTO_INCREMENT PRIMARY KEY,
utilisateur_id INT(11) NOT NULL,
materiel_id INT(11),
salle_id INT(11),
date_debut DATETIME NOT NULL,
date_fin DATETIME NOT NULL,
motif TEXT,
statut VARCHAR(20) DEFAULT 'en_attente',
date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Table reservation créée<br>";
} else {
    echo "Erreur: " . mysqli_error($conn);
}

// Ajouter un administrateur par défaut
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);
$sql = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) 
        VALUES ('Admin', 'MMI', 'admin@mmi.fr', '$admin_password', 'admin')
        ON DUPLICATE KEY UPDATE id=id";

if (mysqli_query($conn, $sql)) {
    echo "Admin créé<br>";
} else {
    echo "Erreur: " . mysqli_error($conn);
}

// Ajouter quelques salles
$salles = [
    ["MMI101", 30, "Salle de cours MMI", 1, "Projecteur, Tableau", "Bâtiment MMI", "1", "salle1.jpg"],
    ["MMI102", 25, "Salle informatique", 1, "25 ordinateurs Mac", "Bâtiment MMI", "1", "salle2.jpg"],
    ["MMI201", 50, "Amphithéâtre", 1, "Projecteur, Audio", "Bâtiment MMI", "2", "salle3.jpg"]
];

foreach ($salles as $salle) {
    $sql = "INSERT INTO salle (nom, capacite, description, disponible, equipements, batiment, etage, image) 
            VALUES ('$salle[0]', $salle[1], '$salle[2]', $salle[3], '$salle[4]', '$salle[5]', '$salle[6]', '$salle[7]')
            ON DUPLICATE KEY UPDATE id=id";
    
    if (mysqli_query($conn, $sql)) {
        echo "Salle $salle[0] ajoutée<br>";
    } else {
        echo "Erreur: " . mysqli_error($conn);
    }
}

// Ajouter du matériel
$materiels = [
    ["Appareil photo Canon", "Appareil photo reflex", 5, 1, "canon.jpg", "Photo"],
    ["Caméra Sony", "Caméra HD", 3, 1, "camera.jpg", "Vidéo"],
    ["Micro Rode", "Microphone directionnel", 4, 1, "micro.jpg", "Audio"],
    ["Trépied", "Trépied pour appareil photo", 8, 1, "trepied.jpg", "Accessoires"]
];

foreach ($materiels as $materiel) {
    $sql = "INSERT INTO materiel (nom, description, quantite, disponible, image, categorie) 
            VALUES ('$materiel[0]', '$materiel[1]', $materiel[2], $materiel[3], '$materiel[4]', '$materiel[5]')
            ON DUPLICATE KEY UPDATE id=id";
    
    if (mysqli_query($conn, $sql)) {
        echo "Matériel $materiel[0] ajouté<br>";
    } else {
        echo "Erreur: " . mysqli_error($conn);
    }
}

// Fermer la connexion
mysqli_close($conn);
echo "<br>Base de données configurée!";
echo "<p>Admin: admin@mmi.fr / admin123</p>";
?>
