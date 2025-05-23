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

// Récupérer quelques statistiques pour le tableau de bord
// Nombre total d'utilisateurs
$sql = "SELECT COUNT(*) as total FROM utilisateur";
$resultat = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($resultat);
$total_utilisateurs = $row["total"];

// Nombre total de réservations
$sql = "SELECT COUNT(*) as total FROM reservation";
$resultat = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($resultat);
$total_reservations = $row["total"];

// Nombre total de matériel
$sql = "SELECT COUNT(*) as total FROM materiel";
$resultat = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($resultat);
$total_materiel = $row["total"];

// Nombre total de salles
$sql = "SELECT COUNT(*) as total FROM salle";
$resultat = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($resultat);
$total_salles = $row["total"];

// Récupérer les 5 dernières réservations
$sql = "SELECT r.id, r.date_debut, r.date_fin, r.statut, u.nom, u.prenom 
        FROM reservation r 
        JOIN utilisateur u ON r.utilisateur_id = u.id 
        ORDER BY r.id DESC LIMIT 5";
$dernieres_reservations = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Administration - Système de Réservation MMI</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    
    <div class="container mt-4">
        <div class="welcome-card">
            <h2>Bienvenue, <?php echo $prenom . ' ' . $nom; ?> !</h2>
            <p>Vous êtes connecté en tant qu'administrateur. Vous avez accès à toutes les fonctionnalités du système.</p>
        </div>
        
        <!-- Statistiques -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card-1 text-dark">
                    <h3><?php echo $total_utilisateurs; ?></h3>
                    <p>Utilisateurs</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-2 text-dark">
                    <h3><?php echo $total_reservations; ?></h3>
                    <p>Réservations</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-3 text-dark">
                    <h3><?php echo $total_materiel; ?></h3>
                    <p>Matériel</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-4 text-dark">
                    <h3><?php echo $total_salles; ?></h3>
                    <p>Salles</p>
                </div>
            </div>
        </div>
        
        <!-- Actions rapides -->
        <div class="card mb-4">
            <div class="card-header text-white">
                <h5 class="mb-0">Actions rapides</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="utilisateurs.php" class="btn gradient-border-btn btn-block mb-2">Gérer les utilisateurs</a>
                    </div>
                    <div class="col-md-3">
                        <a href="materiel.php" class="btn gradient-border-btn btn-block mb-2">Gérer le matériel</a>
                    </div>
                    <div class="col-md-3">
                        <a href="salles.php" class="btn gradient-border-btn btn-block mb-2">Gérer les salles</a>
                    </div>
                    <div class="col-md-3">
                        <a href="reservations.php" class="btn gradient-border-btn btn-block mb-2">Gérer les réservations</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Dernières réservations -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Dernières réservations</h5>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($dernieres_reservations) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Utilisateur</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reservation = mysqli_fetch_assoc($dernieres_reservations)): ?>
                                    <tr>
                                        <td><?php echo $reservation["id"]; ?></td>
                                        <td><?php echo $reservation["prenom"] . " " . $reservation["nom"]; ?></td>
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
                                            <a href="voir_reservation.php?id=<?php echo $reservation["id"]; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($reservation["statut"] == "en_attente"): ?>
                                                <a href="reservations.php?action=approuver&id=<?php echo $reservation["id"]; ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="reservations.php?action=refuser&id=<?php echo $reservation["id"]; ?>" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Aucune réservation trouvée.
                    </div>
                <?php endif; ?>
                <a href="reservations.php" class="btn btn-primary">Voir toutes les réservations</a>
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
