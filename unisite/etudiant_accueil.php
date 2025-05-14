<?php
// Inclure le fichier de configuration
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["connecte"]) || $_SESSION["connecte"] !== true) {
    header("location: login.php");
    exit;
}

// Vérifier si l'utilisateur est un étudiant
if ($_SESSION["role"] !== "etudiant") {
    header("location: login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$id = $_SESSION["id"];
$nom = $_SESSION["nom"];
$prenom = $_SESSION["prenom"];

// Récupérer les informations spécifiques à l'étudiant
$sql = "SELECT numero_etudiant, filiere, annee_etude FROM utilisateur WHERE id = $id";
$resultat = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($resultat);

$numero_etudiant = $row["numero_etudiant"];
$filiere = $row["filiere"];
$annee_etude = $row["annee_etude"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Accueil Étudiant - Système de Réservation</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Inclure Bootstrap pour un style simple mais efficace -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .bienvenue {
            background-color: #007bff;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .carte {
            margin-bottom: 20px;
        }
        .info-etudiant {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Barre de navigation simple -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
            <a class="navbar-brand" href="#">Système de Réservation</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <a class="nav-link" href="etudiant_accueil.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Matériel</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Salles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Mes Réservations</a>
                    </li>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Déconnexion</a>
                    </li>
                </ul>
            </div>
        </nav>
        
        <!-- Message de bienvenue -->
        <div class="bienvenue">
            <h2>Bienvenue, <?php echo $prenom . ' ' . $nom; ?> !</h2>
            <p>Vous êtes connecté en tant qu'étudiant.</p>
        </div>
        
        <!-- Informations de l'étudiant -->
        <div class="info-etudiant">
            <h4>Vos informations</h4>
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Numéro étudiant:</strong> <?php echo $numero_etudiant ? $numero_etudiant : "Non renseigné"; ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Filière:</strong> <?php echo $filiere ? $filiere : "Non renseignée"; ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Année d'étude:</strong> <?php echo $annee_etude ? $annee_etude : "Non renseignée"; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Cartes d'options -->
        <div class="row">
            <!-- Carte pour le matériel -->
            <div class="col-md-4">
                <div class="card carte">
                    <div class="card-body">
                        <h5 class="card-title">Réserver du Matériel</h5>
                        <p class="card-text">Consultez la liste du matériel disponible et faites une réservation.</p>
                        <a href="#" class="btn btn-primary">Voir le Matériel</a>
                    </div>
                </div>
            </div>
            
            <!-- Carte pour les salles -->
            <div class="col-md-4">
                <div class="card carte">
                    <div class="card-body">
                        <h5 class="card-title">Réserver une Salle</h5>
                        <p class="card-text">Consultez les salles disponibles et réservez pour vos études.</p>
                        <a href="#" class="btn btn-success">Voir les Salles</a>
                    </div>
                </div>
            </div>
            
            <!-- Carte pour les réservations -->
            <div class="col-md-4">
                <div class="card carte">
                    <div class="card-body">
                        <h5 class="card-title">Mes Réservations</h5>
                        <p class="card-text">Consultez l'historique et le statut de vos réservations.</p>
                        <a href="#" class="btn btn-warning">Voir mes Réservations</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Informations importantes -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5>Informations Importantes</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>Les réservations de matériel doivent être effectuées au moins 24 heures à l'avance.</li>
                    <li>Les salles peuvent être réservées pour une durée maximale de 4 heures.</li>
                    <li>Tout matériel endommagé doit être signalé immédiatement.</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Scripts JavaScript de Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
