<?php
// Inclure le fichier de configuration
require_once 'config.php';

// Détruire toutes les variables de session
$_SESSION = array();

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
header("location: login.php");
exit;
?>
