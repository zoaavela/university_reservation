<?php
// Ce fichier contient les informations de connexion à la base de données
// Il est inclus dans tous les autres fichiers qui ont besoin d'accéder à la base de données

// Informations de connexion
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "university_reservation";

// Créer la connexion
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Vérifier la connexion
if (!$conn) {
    die("Connexion échouée: " . mysqli_connect_error());
}

// Démarrer la session pour gérer la connexion des utilisateurs
session_start();

// Fonction simple pour nettoyer les données saisies par l'utilisateur
function nettoyer_donnees($donnee) {
    $donnee = trim($donnee);               // Supprimer les espaces au début et à la fin
    $donnee = stripslashes($donnee);       // Supprimer les antislashs
    $donnee = htmlspecialchars($donnee);   // Convertir les caractères spéciaux en entités HTML
    return $donnee;
}
?>
