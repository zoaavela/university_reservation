<?php
// ====================================================================
// FICHIER DE CONFIGURATION
// Ce fichier contient les paramètres de connexion à la base de données
// et des fonctions utilitaires pour le site
// ====================================================================

// Informations de connexion à la base de données
$servername = "localhost"; // Adresse du serveur
$username = "root";        // Nom d'utilisateur MySQL
$password = "";            // Mot de passe MySQL (vide par défaut)
$dbname = "mmi_reservation"; // Nom de la base de données

// Créer la connexion à la base de données
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Vérifier si la connexion a échoué
if (!$conn) {
    die("Erreur de connexion: " . mysqli_connect_error());
}

// Définir l'encodage des caractères pour éviter les problèmes d'accents
mysqli_set_charset($conn, "utf8");

// Démarrer la session pour gérer la connexion des utilisateurs
session_start();

// ====================================================================
// FONCTIONS UTILITAIRES
// ====================================================================

/**
 * Nettoie les données saisies par l'utilisateur pour éviter les injections
 * 
 * @param string $donnee La donnée à nettoyer
 * @return string La donnée nettoyée
 */
function nettoyer_donnees($donnee) {
    global $conn; // Utiliser la connexion globale
    
    $donnee = trim($donnee);               // Supprimer les espaces au début et à la fin
    $donnee = stripslashes($donnee);       // Supprimer les antislashs
    $donnee = htmlspecialchars($donnee);   // Convertir les caractères spéciaux en entités HTML
    $donnee = mysqli_real_escape_string($conn, $donnee); // Échapper les caractères spéciaux pour SQL
    
    return $donnee;
}

// Fonction simple pour nettoyer les données
function nettoyer($donnee) {
    global $conn;
    $donnee = trim($donnee);
    $donnee = stripslashes($donnee);
    $donnee = htmlspecialchars($donnee);
    $donnee = mysqli_real_escape_string($conn, $donnee);
    return $donnee;
}

/**
 * Vérifie si l'utilisateur est connecté
 * 
 * @return bool Vrai si l'utilisateur est connecté, faux sinon
 */
function est_connecte() {
    return isset($_SESSION["connecte"]) && $_SESSION["connecte"] === true;
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 * 
 * @param string $role Le rôle à vérifier
 * @return bool Vrai si l'utilisateur a le rôle spécifié, faux sinon
 */
function a_role($role) {
    return est_connecte() && $_SESSION["role"] === $role;
}

// Fonction pour vérifier le rôle de l'utilisateur
function est_role($role) {
    return est_connecte() && $_SESSION["role"] === $role;
}

/**
 * Redirige l'utilisateur vers la page d'accueil correspondant à son rôle
 */
function rediriger_selon_role() {
    if (!est_connecte()) {
        header("location: login.php");
        exit;
    }
    
    // Rediriger selon le rôle de l'utilisateur
    switch ($_SESSION["role"]) {
        case "etudiant":
            header("location: etudiant/index.php");
            break;
        case "enseignant":
            header("location: enseignant/index.php");
            break;
        case "admin":
            header("location: admin/index.php");
            break;
        case "agent":
            header("location: agent/index.php");
            break;
        default:
            header("location: login.php");
    }
    exit;
}

/**
 * Formate une date pour l'affichage
 * 
 * @param string $date La date à formater (format MySQL)
 * @return string La date formatée (format français)
 */
function formater_date($date) {
    return date("d/m/Y H:i", strtotime($date));
}

/**
 * Génère un message d'alerte Bootstrap
 * 
 * @param string $message Le message à afficher
 * @param string $type Le type d'alerte (success, danger, warning, info)
 * @return string Le HTML de l'alerte
 */
function alerte($message, $type = "info") {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
}

// Fonction pour afficher un message d'alerte
function message_alerte($texte, $type = "info") {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . $texte . '
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
}

/**
 * Vérifie si une date est disponible pour une réservation
 * 
 * @param string $date_debut Date de début
 * @param string $date_fin Date de fin
 * @param int $salle_id ID de la salle (optionnel)
 * @param int $materiel_id ID du matériel (optionnel)
 * @return bool Vrai si la date est disponible, faux sinon
 */
function est_date_disponible($date_debut, $date_fin, $salle_id = null, $materiel_id = null) {
    global $conn;
    
    // Vérifier si on réserve une salle ou du matériel
    if ($salle_id) {
        // Vérifier si la salle est déjà réservée à cette date
        $sql = "SELECT id FROM reservation 
                WHERE salle_id = $salle_id 
                AND statut != 'refusee' 
                AND ((date_debut BETWEEN '$date_debut' AND '$date_fin') 
                OR (date_fin BETWEEN '$date_debut' AND '$date_fin') 
                OR ('$date_debut' BETWEEN date_debut AND date_fin))";
    } else {
        // Vérifier si le matériel est déjà réservé à cette date
        $sql = "SELECT id FROM reservation 
                WHERE materiel_id = $materiel_id 
                AND statut != 'refusee' 
                AND ((date_debut BETWEEN '$date_debut' AND '$date_fin') 
                OR (date_fin BETWEEN '$date_debut' AND '$date_fin') 
                OR ('$date_debut' BETWEEN date_debut AND date_fin))";
    }
    
    $resultat = mysqli_query($conn, $sql);
    
    // Si aucun résultat, la date est disponible
    return mysqli_num_rows($resultat) == 0;
}
?>
