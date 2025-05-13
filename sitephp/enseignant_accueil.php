<?php
// Inclure le fichier de configuration
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["connecte"]) || $_SESSION["connecte"] !== true) {
    header("location: login.php");
    exit;
}

// Vérifier si l'utilisateur est un enseignant
if ($_SESSION["role"] !== "enseignant") {
    header("location: login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$id = $_SESSION["id"];
$nom = $_SESSION["nom"];
$prenom = $_SESSION["prenom"];

// Récupérer les informations spécifiques à l'enseignant
$sql = "SELECT departement, matiere, bureau FROM utilisateur WHERE id = $id";
$resultat = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($resultat);

$departement = $row["departement"];
$matiere = $row["matiere"];
$bureau = $row["bureau"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Accueil Enseignant - Système de Réservation</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Inclure Bootstrap pour un style simple mais efficace -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .bienvenue {
            background-color: #28a745;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .carte {
            margin-bottom: 20px;
        }
        .info-enseignant {
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
                        <a class="nav-link" href="enseignant_accueil.php">Accueil</a>
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
            <p>Vous êtes connecté en tant qu'enseignant.</p>
        </div>
        
        <!-- Informations de l'enseignant -->
        <div class="info-enseignant">
            <h4>Vos informations</h4>
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Département:</strong> <?php echo $departement ? $departement : "Non renseigné"; ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Matière enseignée:</strong> <?php echo $matiere ? $matiere : "Non renseignée"; ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Bureau:</strong> <?php echo $bureau ? $bureau : "Non renseigné"; ?></p>
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
                        <p class="card-text">Consultez la liste du matériel disponible pour vos cours.</p>
                        <a href="#" class="btn btn-primary">Voir le Matériel</a>
                    </div>
                </div>
            </div>
            
            <!-- Carte pour les salles -->
            <div class="col-md-4">
                <div class="card carte">
                    <div class="card-body">
                        <h5 class="card-title">Réserver une Salle</h5>
                        <p class="card-text">Consultez les salles disponibles pour vos cours et examens.</p>
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
                    <li>En tant qu'enseignant, vous avez la priorité pour les réservations de salles.</li>
                    <li>Vous pouvez réserver du matériel pour vos cours jusqu'à une semaine à l'avance.</li>
                    <li>Les réservations de salles pour les examens doivent être effectuées au moins 2 semaines à l'avance.</li>
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
