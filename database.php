<?php
// Ce fichier crée la base de données et les tables nécessaires

// Informations de connexion à la base de données
$servername = "localhost"; // Serveur de base de données (généralement localhost)
$username = "root";        // Nom d'utilisateur MySQL (par défaut: root)
$password = "";            // Mot de passe MySQL (par défaut: vide)
$dbname = "university_reservation"; // Nom de la base de données

// Créer la connexion à MySQL
$conn = mysqli_connect($servername, $username, $password);

// Vérifier si la connexion a réussi
if (!$conn) {
    die("Connexion échouée: " . mysqli_connect_error());
}
echo "Connexion réussie à MySQL<br>";

// Créer la base de données si elle n'existe pas
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (mysqli_query($conn, $sql)) {
    echo "Base de données créée avec succès<br>";
} else {
    echo "Erreur lors de la création de la base de données: " . mysqli_error($conn);
}

// Sélectionner la base de données
mysqli_select_db($conn, $dbname);

// Créer la table utilisateur avec des champs pour les deux rôles
$sql = "CREATE TABLE IF NOT EXISTS utilisateur (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    
    /* Champs spécifiques aux étudiants */
    numero_etudiant VARCHAR(20),
    promotion VARCHAR(100),
    tp VARCHAR(20),
    td VARCHAR(20),
    
    /* Champs spécifiques aux enseignants */
    departement VARCHAR(100),
    matiere VARCHAR(100),
    bureau VARCHAR(50)
)";

if (mysqli_query($conn, $sql)) {
    echo "Table utilisateur créée avec succès<br>";
} else {
    echo "Erreur lors de la création de la table utilisateur: " . mysqli_error($conn);
}

// Créer la table materiel
$sql = "CREATE TABLE IF NOT EXISTS materiel (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    quantite INT(11) NOT NULL,
    disponible TINYINT(1) DEFAULT 1
)";

if (mysqli_query($conn, $sql)) {
    echo "Table materiel créée avec succès<br>";
} else {
    echo "Erreur lors de la création de la table materiel: " . mysqli_error($conn);
}

// Créer la table salle
$sql = "CREATE TABLE IF NOT EXISTS salle (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    capacite INT(11) NOT NULL,
    description TEXT,
    disponible TINYINT(1) DEFAULT 1
)";

if (mysqli_query($conn, $sql)) {
    echo "Table salle créée avec succès<br>";
} else {
    echo "Erreur lors de la création de la table salle: " . mysqli_error($conn);
}

// Créer la table reservation
$sql = "CREATE TABLE IF NOT EXISTS reservation (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT(11) NOT NULL,
    materiel_id INT(11),
    salle_id INT(11),
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    statut VARCHAR(20) DEFAULT 'en_attente'
)";

if (mysqli_query($conn, $sql)) {
    echo "Table reservation créée avec succès<br>";
} else {
    echo "Erreur lors de la création de la table reservation: " . mysqli_error($conn);
}

// Créer la table commentaire
$sql = "CREATE TABLE IF NOT EXISTS commentaire (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT(11) NOT NULL,
    materiel_id INT(11) NOT NULL,
    contenu TEXT NOT NULL,
    note INT(1) NOT NULL
)";

if (mysqli_query($conn, $sql)) {
    echo "Table commentaire créée avec succès<br>";
} else {
    echo "Erreur lors de la création de la table commentaire: " . mysqli_error($conn);
}

// Fermer la connexion
mysqli_close($conn);
echo "<br>Configuration de la base de données terminée.";
?>
