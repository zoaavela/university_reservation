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
$base_url = "http://localhost/unisite/agent/";
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
    <link rel="stylesheet" href="../css/pageadmin.css">
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="index.php"><img src="../images/logopng.png" alt=""></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>index.php" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt mr-2"></i> Tableau de bord
                </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>utilisateurs.php" class="<?php echo $page == 'utilisateurs' ? 'active' : ''; ?>">
                    <i class="fas fa-users mr-2"></i> Voir les utilisateurs
                </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>materiel.php" class="<?php echo $page == 'materiel' ? 'active' : ''; ?>">
                    <i class="fas fa-laptop mr-2"></i> Voir le matériel
                </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>salles.php" class="<?php echo $page == 'salles' ? 'active' : ''; ?>">
                    <i class="fas fa-door-open mr-2"></i> Voir les salles
                </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>reservations.php" class="<?php echo $page == 'reservations' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt mr-2"></i> Voir les réservations
                </a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                 <li class="logout">
                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>../logout.php">
                    <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                </a>
            </li>
            </ul>
        </div>
    </nav>

            
            <!-- Contenu principal -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col col-md-12">
                <div class="page-header mt-5">
                    <h2>Bienvenue, <?php echo $prenom . ' ' . $nom; ?> !</h2>
                    <p>Vous pouvez gérer les utilisateurs, les réservations et les consignes de réservations.</p>
                </div>
                
                <!-- Boutons d'action -->
                <div class="row mb-4 text-center">
                    <div class="col-md-4 card card-body btn">
                        <a href="reservations.php" class="btn d-block">
                            <i class="fas fa-calendar-alt text-dark"></i><br>
                            Réservations
                        </a>
                    </div>
                    <div class="col-md-4 card card-body text-center btn">
                        <a href="materiel.php" class="btn d-block">
                            <i class="fas fa-laptop text-dark"></i><br>
                            Matériel
                        </a>
                    </div>
                    <div class="col-md-4 card card-body text-center btn">
                        <a href="salles.php" class="btn d-block">
                            <i class="fas fa-door-open text-dark"></i><br>
                            Salles
                        </a>
                    </div>
                </div>
                
                <!-- État des salles -->
    <div class="card mt-4">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0">État des salles</h5>
        </div>
        <div class="card-body">
            <p>En tant qu'agent, vous pouvez consulter l'état des salles et leur disponibilité.</p>
            <a href="salles.php" class="btn btn-warning">Voir toutes les salles</a>
        </div>
    </div>

    <!-- État du matériel -->
    <div class="card mt-4">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0">État du matériel</h5>
        </div>
        <div class="card-body">
            <p>En tant qu'agent, vous pouvez consulter l'état du matériel disponible pour les réservations.</p>
            <a href="materiel.php" class="btn btn-warning">Voir tout le matériel</a>
        </div>
    </div>

    <!-- Dernières réservations -->
    <div class="card mt-4">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0">Dernières réservations</h5>
        </div>
        <div class="card-body">
            <p>En tant qu'agent, vous pouvez consulter les réservations.</p>
            <a href="reservations.php" class="btn btn-warning mb-3">Voir toutes les réservations</a>
            
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
    
  <!-- Footer -->
    <footer class="footer text-dark text-center py-3 mt-5">
        <p>© <?php echo date("Y"); ?> Système de Réservation MMI</p>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
