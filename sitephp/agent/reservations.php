<?php
// Inclure le fichier de configuration
require_once '../config.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    header("location: ../login.php");
    exit;
}

// Vérifier si l'utilisateur est un agent
if (!a_role("agent")) {
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
    $statut_filter = nettoyer_donnees($_GET['statut']);
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Consultation des Réservations - Espace Agent</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Inclure Bootstrap pour un style simple mais efficace -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .sidebar {
            height: 100vh;
            background-color: #6c757d;
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
            background-color: #6c757d;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .content {
            margin-left: 225px;
            padding: 20px;
        }
        .page-header {
            background-color: #6c757d;
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Barre latérale -->
            <div class="col-md-2 sidebar">
                <div class="p-4">
                    <h4>Espace Agent</h4>
                    <p>Système de Réservation</p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="utilisateurs.php">
                            <i class="fas fa-users"></i> Utilisateurs
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
                        <a class="nav-link active" href="reservations.php">
                            <i class="fas fa-calendar-alt"></i> Réservations
                        </a>
                    </li>
                    <li class="nav-item mt-5">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Contenu principal -->
            <div class="col-md-10 content">
                <div class="page-header">
                    <h2><i class="fas fa-calendar-alt"></i> Consultation des Réservations</h2>
                    <p>Consultez la liste des réservations des utilisateurs.</p>
                </div>
                
                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Filtrer par statut</h5>
                        <div class="d-flex flex-wrap">
                            <a href="reservations.php" class="btn <?php echo empty($statut_filter) ? 'btn-secondary' : 'btn-outline-secondary'; ?> m-1">Tous</a>
                            <a href="reservations.php?statut=en_attente" class="btn <?php echo $statut_filter == 'en_attente' ? 'btn-secondary' : 'btn-outline-secondary'; ?> m-1">En attente</a>
                            <a href="reservations.php?statut=approuvee" class="btn <?php echo $statut_filter == 'approuvee' ? 'btn-secondary' : 'btn-outline-secondary'; ?> m-1">Approuvée</a>
                            <a href="reservations.php?statut=refusee" class="btn <?php echo $statut_filter == 'refusee' ? 'btn-secondary' : 'btn-outline-secondary'; ?> m-1">Refusée</a>
                            <a href="reservations.php?statut=terminee" class="btn <?php echo $statut_filter == 'terminee' ? 'btn-secondary' : 'btn-outline-secondary'; ?> m-1">Terminée</a>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des réservations -->
                <div class="card">
                    <div class="card-body">
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
                                                <td><?php echo formater_date($reservation["date_debut"]); ?></td>
                                                <td><?php echo formater_date($reservation["date_fin"]); ?></td>
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
                
                <!-- Note informative -->
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle"></i> En tant qu'agent, vous pouvez consulter les réservations mais vous ne pouvez pas les approuver ou les refuser. Veuillez contacter un administrateur pour ces actions.
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts JavaScript de Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
