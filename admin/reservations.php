<?php
// Inclure le fichier de configuration
require_once '../config.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    header("location: ../login.php");
    exit;
}

// Vérifier si l'utilisateur est un administrateur
if (!est_role("admin")) {
    header("location: ../login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$id = $_SESSION["id"];
$nom = $_SESSION["nom"];
$prenom = $_SESSION["prenom"];

// Message pour les actions
$message = "";

// Traitement des actions (approuver ou refuser une réservation)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $reservation_id = $_GET['id'];
    $action = $_GET['action'];
    
    // Approuver une réservation
    if ($action == 'approuver') {
        $sql = "UPDATE reservation SET statut = 'approuvee' WHERE id = $reservation_id";
        if (mysqli_query($conn, $sql)) {
            $message = message_alerte("La réservation a été approuvée avec succès.", "success");
        } else {
            $message = message_alerte("Erreur lors de l'approbation: " . mysqli_error($conn), "danger");
        }
    }
    
    // Refuser une réservation
    if ($action == 'refuser') {
        $sql = "UPDATE reservation SET statut = 'refusee' WHERE id = $reservation_id";
        if (mysqli_query($conn, $sql)) {
            $message = message_alerte("La réservation a été refusée.", "success");
        } else {
            $message = message_alerte("Erreur lors du refus: " . mysqli_error($conn), "danger");
        }
    }
}

// Récupérer la liste des réservations
$sql = "SELECT r.id, r.date_debut, r.date_fin, r.statut, r.motif, 
               u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, 
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
                   u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, 
                   m.nom as materiel_nom, s.nom as salle_nom 
            FROM reservation r 
            JOIN utilisateur u ON r.utilisateur_id = u.id 
            LEFT JOIN materiel m ON r.materiel_id = m.id 
            LEFT JOIN salle s ON r.salle_id = s.id 
            WHERE r.statut = '$statut_filter'
            ORDER BY r.date_creation DESC";
    $resultat = mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Réservations - Administration</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/pageadmin.css">
</head>
<body class="min-vh-100">
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="index.php"><img src="../images/logopng.png" alt=""></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt icon-align"></i>Tableau de bord</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="utilisateurs.php"><i class="fas fa-users"></i>Utilisateurs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="materiel.php"><i class="fas fa-laptop"></i>Matériel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="salles.php"><i class="fas fa-door-open"></i>Salles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reservations.php"><i class="fas fa-calendar-alt"></i>Réservations</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                </li>
            </ul>
        </div>
    </nav>
    
    <main>
    <div class="container mt-4">
        <div class="page-header">
            <h2><i class="fas fa-calendar-alt"></i> Gestion des Réservations</h2>
            <p>Consultez, approuvez et refusez les réservations des utilisateurs.</p>
        </div>
        
        <!-- Afficher les messages -->
        <?php echo $message; ?>
        
        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Filtrer par statut</h5>
                <div class="d-flex flex-wrap">
                    <a href="reservations.php" class="btn <?php echo empty($statut_filter) ? 'btn-dark' : 'btn-outline-dark'; ?> m-1">Tous</a>
                    <a href="reservations.php?statut=en_attente" class="btn <?php echo $statut_filter == 'en_attente' ? 'btn-dark' : 'btn-outline-dark'; ?> m-1">En attente</a>
                    <a href="reservations.php?statut=approuvee" class="btn <?php echo $statut_filter == 'approuvee' ? 'btn-dark' : 'btn-outline-dark'; ?> m-1">Approuvée</a>
                    <a href="reservations.php?statut=refusee" class="btn <?php echo $statut_filter == 'refusee' ? 'btn-dark' : 'btn-outline-dark'; ?> m-1">Refusée</a>
                </div>
            </div>
        </div>
        
        <!-- Liste des réservations -->
        <div class="card">
            <div class="card-body">
                <?php if (mysqli_num_rows($resultat) > 0): ?>
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
                                <?php while ($reservation = mysqli_fetch_assoc($resultat)): ?>
                                    <tr>
                                        <td><?php echo $reservation["id"]; ?></td>
                                        <td><?php echo $reservation["utilisateur_prenom"] . " " . $reservation["utilisateur_nom"]; ?></td>
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
                                            
                                            if ($statut == "en_attente") {
                                                $classe = "badge badge-warning";
                                            } elseif ($statut == "approuvee") {
                                                $classe = "badge badge-success";
                                            } elseif ($statut == "refusee") {
                                                $classe = "badge badge-danger";
                                            } else {
                                                $classe = "badge badge-secondary";
                                            }
                                            ?>
                                            <span class="<?php echo $classe; ?>"><?php echo $statut; ?></span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#detailModal<?php echo $reservation["id"]; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($reservation["statut"] == "en_attente"): ?>
                                                <a href="reservations.php?action=approuver&id=<?php echo $reservation["id"]; ?>" class="btn btn-sm btn-success" onclick="return confirm('Êtes-vous sûr de vouloir approuver cette réservation?');">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="reservations.php?action=refuser&id=<?php echo $reservation["id"]; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir refuser cette réservation?');">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal de détail -->
                                    <div class="modal fade" id="detailModal<?php echo $reservation["id"]; ?>" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="detailModalLabel">Détails de la réservation #<?php echo $reservation["id"]; ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Utilisateur:</strong> <?php echo $reservation["utilisateur_prenom"] . " " . $reservation["utilisateur_nom"]; ?></p>
                                                    <p><strong>Type:</strong> <?php echo $reservation["materiel_nom"] ? "Matériel" : "Salle"; ?></p>
                                                    <p><strong>Objet:</strong> <?php echo $reservation["materiel_nom"] ? $reservation["materiel_nom"] : $reservation["salle_nom"]; ?></p>
                                                    <p><strong>Date début:</strong> <?php echo date("d/m/Y H:i", strtotime($reservation["date_debut"])); ?></p>
                                                    <p><strong>Date fin:</strong> <?php echo date("d/m/Y H:i", strtotime($reservation["date_fin"])); ?></p>
                                                    <p><strong>Statut:</strong> <?php echo $reservation["statut"]; ?></p>
                                                    <p><strong>Motif:</strong> <?php echo $reservation["motif"]; ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                    <?php if ($reservation["statut"] == "en_attente"): ?>
                                                        <a href="reservations.php?action=approuver&id=<?php echo $reservation["id"]; ?>" class="btn btn-success">Approuver</a>
                                                        <a href="reservations.php?action=refuser&id=<?php echo $reservation["id"]; ?>" class="btn btn-danger">Refuser</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Aucune réservation trouvée.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
        <!-- Footer -->
        <footer class="footer text-dark text-center py-3 mt-auto">
    <p>@ <?php echo date("Y"); ?> Système de Réservation MMI</p>
</footer>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
