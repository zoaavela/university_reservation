<?php
// Inclure le fichier de configuration
require_once '../config.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    header("location: ../login.php");
    exit;
}

// Vérifier si l'utilisateur est un agent
if (!est_role("agent")) {
    header("location: ../login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$id = $_SESSION["id"];
$nom = $_SESSION["nom"];
$prenom = $_SESSION["prenom"];

// Récupérer quelques statistiques pour le tableau de bord
// Nombre total de réservations
$sql_reservations = "SELECT COUNT(*) as total FROM reservation";
$resultat_reservations = mysqli_query($conn, $sql_reservations);
$row_reservations = mysqli_fetch_assoc($resultat_reservations);
$total_reservations = $row_reservations["total"];

// Nombre total de matériel
$sql_materiel = "SELECT COUNT(*) as total FROM materiel";
$resultat_materiel = mysqli_query($conn, $sql_materiel);
$row_materiel = mysqli_fetch_assoc($resultat_materiel);
$total_materiel = $row_materiel["total"];

// Nombre total de salles
$sql_salles = "SELECT COUNT(*) as total FROM salle";
$resultat_salles = mysqli_query($conn, $sql_salles);
$row_salles = mysqli_fetch_assoc($resultat_salles);
$total_salles = $row_salles["total"];

// Récupérer les 5 dernières réservations
$sql_dernieres_reservations = "SELECT r.id, u.nom, u.prenom, r.date_debut, r.date_fin, r.statut 
                              FROM reservation r 
                              JOIN utilisateur u ON r.utilisateur_id = u.id 
                              ORDER BY r.id DESC LIMIT 5";
$resultat_dernieres_reservations = mysqli_query($conn, $sql_dernieres_reservations);

// Définir le titre de la page
$page_title = "Tableau de bord Agent - Université Gustave Eiffel";
$page = "dashboard";
$base_url = "..";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Styles personnalisés -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <img src="../images/logo.png" alt="Université Gustave Eiffel">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Bannière -->
    <div class="banner" style="background-image: url('../images/agent-banner.jpg');">
        <div class="container">
            <div class="banner-content">
                <h1>Agent</h1>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <?php include '../includes/agent_sidebar.php'; ?>
            </div>
            
            <!-- Contenu principal -->
            <div class="col-md-9">
                <!-- Carte d'information -->
                <div class="info-card mb-4">
                    <p class="text-center mb-0">
                        <strong>Sur cette page</strong>, vous pouvez gérer les utilisateurs, les réservations et les consignes de réservations
                    </p>
                </div>
                
                <h2 class="mb-4">Bienvenue, agent <?php echo $prenom; ?> !</h2>
                
                <!-- Boutons d'action -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <a href="reservations.php" class="action-button d-block">
                            <i class="fas fa-calendar-alt fa-2x mb-2"></i><br>
                            Réservations
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="materiel.php" class="action-button d-block">
                            <i class="fas fa-laptop fa-2x mb-2"></i><br>
                            Matériel
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="salles.php" class="action-button d-block">
                            <i class="fas fa-door-open fa-2x mb-2"></i><br>
                            Salles
                        </a>
                    </div>
                </div>
                
                <!-- État des salles -->
                <div class="section-card">
                    <h3 class="section-title">État des salles <a href="salles.php" class="view-all-link">Voir toutes les salles</a></h3>
                    <p>En tant qu'agent, vous pouvez consulter l'état des salles et leur disponibilité.</p>
                </div>
                
                <!-- État du matériel -->
                <div class="section-card">
                    <h3 class="section-title">État du matériel <a href="materiel.php" class="view-all-link">Voir tout le matériel</a></h3>
                    <p>En tant qu'agent, vous pouvez consulter l'état du matériel disponible pour les réservations.</p>
                </div>
                
                <!-- Dernières réservations -->
                <div class="section-card">
                    <h3 class="section-title">Dernières réservations <a href="reservations.php" class="view-all-link">Voir toutes les réservations</a></h3>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Utilisateur</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($resultat_dernieres_reservations)): ?>
                                <tr>
                                    <td><?php echo $row["id"]; ?></td>
                                    <td><?php echo $row["prenom"] . " " . $row["nom"]; ?></td>
                                    <td><?php echo date("d/m/Y H:i", strtotime($row["date_debut"])); ?></td>
                                    <td><?php echo date("d/m/Y H:i", strtotime($row["date_fin"])); ?></td>
                                    <td>
                                        <?php 
                                        $statut = $row["statut"];
                                        $classe = "";
                                        
                                        switch ($statut) {
                                            case "en_attente":
                                                $classe = "badge badge-warning";
                                                break;
                                            case "approuvee":
                                                $classe = "badge badge-success";
                                                break;
                                            case "refusee":
                                                $classe = "badge badge-danger";
                                                break;
                                            case "terminee":
                                                $classe = "badge badge-secondary";
                                                break;
                                            default:
                                                $classe = "badge badge-info";
                                        }
                                        ?>
                                        <span class="<?php echo $classe; ?>"><?php echo $statut; ?></span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                
                                <?php if (mysqli_num_rows($resultat_dernieres_reservations) == 0): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Aucune réservation trouvée</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Université Gustave Eiffel</h5>
                    <p>Système de réservation de salles et de matériel</p>
                </div>
                <div class="col-md-4">
                    <h5>Liens utiles</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Aide</a></li>
                        <li><a href="#" class="text-white">Conditions d'utilisation</a></li>
                        <li><a href="#" class="text-white">Politique de confidentialité</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact</h5>
                    <address>
                        <p><i class="fas fa-map-marker-alt mr-2"></i> 5 Boulevard Descartes, 77420 Champs-sur-Marne</p>
                        <p><i class="fas fa-phone mr-2"></i> +33 1 60 95 75 00</p>
                        <p><i class="fas fa-envelope mr-2"></i> contact@univ-eiffel.fr</p>
                    </address>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p>© <?php echo date("Y"); ?> Université Gustave Eiffel - Tous droits réservés</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
