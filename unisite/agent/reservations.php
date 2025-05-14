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

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <?php include '../includes/agent_sidebar.php'; ?>
            </div>
            
            <!-- Contenu principal -->
            <div class="col-md-9">
                <h2 class="mb-4"><i class="fas fa-calendar-alt"></i> Gestion des Réservations</h2>
                
                <!-- Filtres -->
                <div class="section-card mb-4">
                    <h5 class="card-title">Filtrer par statut</h5>
                    <div class="d-flex flex-wrap">
                        <a href="reservations.php" class="btn <?php echo empty($statut_filter) ? 'btn-primary' : 'btn-outline-primary'; ?> m-1">Tous</a>
                        <a href="reservations.php?statut=en_attente" class="btn <?php echo $statut_filter == 'en_attente' ? 'btn-primary' : 'btn-outline-primary'; ?> m-1">En attente</a>
                        <a href="reservations.php?statut=approuvee" class="btn <?php echo $statut_filter == 'approuvee' ? 'btn-primary' : 'btn-outline-primary'; ?> m-1">Approuvée</a>
                        <a href="reservations.php?statut=refusee" class="btn <?php echo $statut_filter == 'refusee' ? 'btn-primary' : 'btn-outline-primary'; ?> m-1">Refusée</a>
                        <a href="reservations.php?statut=terminee" class="btn <?php echo $statut_filter == 'terminee' ? 'btn-primary' : 'btn-outline-primary'; ?> m-1">Terminée</a>
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
                
                <!-- Note informative -->
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle"></i> En tant qu'agent, vous pouvez consulter les réservations mais vous ne pouvez pas les approuver ou les refuser. Veuillez contacter un administrateur pour ces actions.
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
