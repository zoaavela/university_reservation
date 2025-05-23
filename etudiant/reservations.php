<?php
// Inclure le fichier de configuration
require_once '../config.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    header("location: ../login.php");
    exit;
}

// Vérifier si l'utilisateur est un étudiant
if (!est_role("etudiant")) {
    header("location: ../login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$id = $_SESSION["id"];
$nom = $_SESSION["nom"];
$prenom = $_SESSION["prenom"];

// Récupérer toutes les réservations de l'étudiant
$sql = "SELECT r.id, r.date_debut, r.date_fin, r.statut, r.motif, m.nom as materiel_nom, s.nom as salle_nom 
        FROM reservation r 
        LEFT JOIN materiel m ON r.materiel_id = m.id 
        LEFT JOIN salle s ON r.salle_id = s.id 
        WHERE r.utilisateur_id = $id 
        ORDER BY r.date_debut DESC";
$reservations = mysqli_query($conn, $sql);

// Filtrer par statut si demandé
$statut_filter = "";
if (isset($_GET['statut']) && !empty($_GET['statut'])) {
    $statut_filter = nettoyer($_GET['statut']);
    $sql = "SELECT r.id, r.date_debut, r.date_fin, r.statut, r.motif, m.nom as materiel_nom, s.nom as salle_nom 
            FROM reservation r 
            LEFT JOIN materiel m ON r.materiel_id = m.id 
            LEFT JOIN salle s ON r.salle_id = s.id 
            WHERE r.utilisateur_id = $id AND r.statut = '$statut_filter'
            ORDER BY r.date_debut DESC";
    $reservations = mysqli_query($conn, $sql);
}

// Traitement de l'annulation d'une réservation
$message = "";
if (isset($_GET['action']) && $_GET['action'] == 'annuler' && isset($_GET['id'])) {
    $reservation_id = $_GET['id'];
    
    // Vérifier si la réservation appartient à l'utilisateur et est en attente
    $sql = "SELECT id FROM reservation WHERE id = $reservation_id AND utilisateur_id = $id AND statut = 'en_attente'";
    $resultat = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($resultat) > 0) {
        // Supprimer la réservation
        $sql = "DELETE FROM reservation WHERE id = $reservation_id";
        if (mysqli_query($conn, $sql)) {
            $message = message_alerte("La réservation a été annulée avec succès.", "success");
            // Rafraîchir la liste des réservations
            header("location: reservations.php");
            exit;
        } else {
            $message = message_alerte("Erreur lors de l'annulation de la réservation: " . mysqli_error($conn), "danger");
        }
    } else {
        $message = message_alerte("Vous ne pouvez pas annuler cette réservation.", "danger");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Réservations - Système de Réservation MMI</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
                <li class="nav-item">
                    <a class="nav-link" href="materiel.php"><i class="fas fa-laptop"></i>Matériel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="salles.php"><i class="fas fa-door-open"></i>Salles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reservations.php"><i class="fas fa-calendar-alt"></i>Mes Réservations</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="profil.php"><i class="fas fa-user"></i>Mon Profil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="page-header">
            <h2><i class="fas fa-calendar-alt"></i> Mes Réservations</h2>
            <p>Consultez et gérez toutes vos réservations de matériel et de salles.</p>
        </div>
        
        <!-- Afficher les messages -->
        <?php echo $message; ?>
        
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
        <div class="card">
            <div class="card-body">
                <?php if (mysqli_num_rows($reservations) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Nom</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Motif</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reservation = mysqli_fetch_assoc($reservations)): ?>
                                    <tr>
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
                                        <td><?php echo substr($reservation["motif"], 0, 30) . (strlen($reservation["motif"]) > 30 ? '...' : ''); ?></td>
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
                                            <a href="voir_reservation.php?id=<?php echo $reservation["id"]; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Détails
                                            </a>
                                            <?php if ($reservation["statut"] == "en_attente"): ?>
                                                <a href="reservations.php?action=annuler&id=<?php echo $reservation["id"]; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation?');">
                                                    <i class="fas fa-times"></i> Annuler
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary" disabled>
                                                    <i class="fas fa-lock"></i> Verrouillé
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Vous n'avez pas encore de réservations.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Informations -->
        <div class="card mt-4 mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Informations sur les réservations</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>Les réservations en attente peuvent être annulées.</li>
                    <li>Les réservations approuvées ne peuvent plus être annulées.</li>
                    <li>Pour toute question concernant une réservation refusée, veuillez contacter l'administration.</li>
                    <li>Les réservations sont soumises à l'approbation d'un administrateur.</li>
                </ul>
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
