<?php
require_once '../config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

// Vérifier si l'utilisateur est un étudiant
if ($_SESSION["role"] !== "etudiant") {
    header("location: ../login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$nom = $_SESSION["nom"];
$prenom = $_SESSION["prenom"];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil Étudiant - Système de Réservation Universitaire</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: white;
            position: fixed;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 10px;
        }
        .sidebar .nav-link:hover {
            color: white;
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: #007bff;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .content {
            margin-left: 225px;
            padding: 20px;
        }
        .welcome-card {
            background-color: #007bff;
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .card-icon {
            font-size: 2rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h4 class="text-center mb-4">Espace Étudiant</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home"></i> Accueil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reservations.php">
                            <i class="fas fa-calendar-alt"></i> Mes Réservations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="materiel.php">
                            <i class="fas fa-laptop"></i> Matériel
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="salles.php">
                            <i class="fas fa-door-open"></i> Salles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profil.php">
                            <i class="fas fa-user"></i> Mon Profil
                        </a>
                    </li>
                    <li class="nav-item mt-5">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Content -->
            <div class="col-md-10 content">
                <div class="welcome-card">
                    <h2>Bienvenue, <?php echo $prenom . ' ' . $nom; ?> !</h2>
                    <p>Vous êtes connecté en tant qu'étudiant. Utilisez le menu de gauche pour naviguer.</p>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card dashboard-card">
                            <div class="card-body text-center">
                                <i class="fas fa-laptop card-icon text-primary"></i>
                                <h5 class="card-title">Réserver du Matériel</h5>
                                <p class="card-text">Accédez à notre catalogue de matériel disponible pour réservation.</p>
                                <a href="materiel.php" class="btn btn-primary">Voir le Matériel</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card dashboard-card">
                            <div class="card-body text-center">
                                <i class="fas fa-door-open card-icon text-success"></i>
                                <h5 class="card-title">Réserver une Salle</h5>
                                <p class="card-text">Consultez les salles disponibles et effectuez une réservation.</p>
                                <a href="salles.php" class="btn btn-success">Voir les Salles</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card dashboard-card">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-alt card-icon text-warning"></i>
                                <h5 class="card-title">Mes Réservations</h5>
                                <p class="card-text">Consultez l'historique et le statut de vos réservations.</p>
                                <a href="reservations.php" class="btn btn-warning">Voir mes Réservations</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Informations Importantes</h5>
                    </div>
                    <div class="card-body">
                        <ul>
                            <li>Les réservations de matériel doivent être effectuées au moins 24 heures à l'avance.</li>
                            <li>Les salles peuvent être réservées pour une durée maximale de 4 heures consécutives.</li>
                            <li>Tout matériel endommagé devra être signalé immédiatement.</li>
                            <li>Pour toute question, veuillez contacter le service d'assistance au support@universite.fr</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
