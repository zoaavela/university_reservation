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

// Récupérer la liste des réservations
$sql = "SELECT r.id, r.date_debut, r.date_fin, r.statut, r.motif, 
               u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, u.role as utilisateur_role,
               m.nom as materiel_nom, s.nom as salle_nom 
        FROM reservation r 
        JOIN utilisateur u ON r.utilisateur_id = u.id 
        LEFT JOIN materiel m ON r.materiel_id = m.id 
        LEFT JOIN salle s ON r.salle_id = s.id 
        ORDER BY r.date_creation DESC";
$resultat = mysqli_query($conn, $sql);

// Filtrer par statut si demandé
$statut_filter = "";
if (isset($_GET['statut']) && !empty($_GET['statut'])) {
    $statut_filter = nettoyer($_GET['statut']);
    $sql = "SELECT r.id, r.date_debut, r.date_fin, r.statut, r.motif, 
                   u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, u.role as utilisateur_role,
                   m.nom as materiel_nom, s.nom as salle_nom 
            FROM reservation r 
            JOIN utilisateur u ON r.utilisateur_id = u.id 
            LEFT JOIN materiel m ON r.materiel_id = m.id 
            LEFT JOIN salle s ON r.salle_id = s.id 
            WHERE r.statut = ?
            ORDER BY r.date_creation DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $statut_filter);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
}

// Définir le titre de la page
$page_title = "Gestion des Réservations - Université Gustave Eiffel";
$page = "reservations";
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
                 <div class="container mt-4">
        <div class="page-header">
            <h2><i class="fas fa-calendar-alt"></i> Gestion des réservations</h2>
        </div>
                
                <!-- Filtres -->
                <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Filtrer par statut</h5>
                <div class="d-flex flex-wrap">
                    <a href="reservations.php" class="btn <?php echo empty($statut_filter) ? 'btn-warning' : 'btn-outline-warning'; ?> m-1">Tous</a>
                    <a href="reservations.php?statut=en_attente" class="btn <?php echo $statut_filter == 'en_attente' ? 'btn-warning' : 'btn-outline-warning'; ?> m-1">En attente</a>
                    <a href="reservations.php?statut=approuvee" class="btn <?php echo $statut_filter == 'approuvee' ? 'btn-warning' : 'btn-outline-warning'; ?> m-1">Approuvée</a>
                    <a href="reservations.php?statut=refusee" class="btn <?php echo $statut_filter == 'refusee' ? 'btn-warning' : 'btn-outline-warning'; ?> m-1">Refusée</a>
                </div>
            </div>
        </div>
                
                <!-- Liste des réservations -->
                <div class="section-card">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Utilisateur</th>
                                    <th>Type</th>
                                    <th>Objet</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($resultat) > 0): ?>
                                    <?php while ($reservation = mysqli_fetch_assoc($resultat)): ?>
                                        <tr>
                                            <td><?php echo $reservation["id"]; ?></td>
                                            <td>
                                                <?php echo $reservation["utilisateur_prenom"] . " " . $reservation["utilisateur_nom"]; ?>
                                                <br>
                                                <small class="text-muted"><?php echo $reservation["utilisateur_role"]; ?></small>
                                            </td>
                                            <td>
                                                <?php if ($reservation["materiel_nom"]): ?>
                                                    <span class="badge badge-primary">Matériel</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">Salle</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo $reservation["materiel_nom"] ? $reservation["materiel_nom"] : $reservation["salle_nom"]; ?>
                                            </td>
                                            <td><?php echo date("d/m/Y H:i", strtotime($reservation["date_debut"])); ?></td>
                                            <td><?php echo date("d/m/Y H:i", strtotime($reservation["date_fin"])); ?></td>
                                            <td>
                                                <?php 
                                                $statut = $reservation["statut"];
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
                                            <td>
                                                <a href="voir_reservation.php?id=<?php echo $reservation["id"]; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Aucune réservation trouvée</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
                
                <!-- Note informative -->
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle"></i> En tant qu'agent, vous pouvez consulter les réservations mais vous ne pouvez pas les approuver ou les refuser. Veuillez contacter un administrateur pour ces actions.
                </div>
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
