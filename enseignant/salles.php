<?php
// Inclure le fichier de configuration
require_once '../config.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    header("location: ../login.php");
    exit;
}

// Vérifier si l'utilisateur est un étudiant
if (!est_role("enseignant")) {
    header("location: ../login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$id = $_SESSION["id"];
$nom = $_SESSION["nom"];
$prenom = $_SESSION["prenom"];

// Récupérer la liste des salles disponibles
$sql = "SELECT * FROM salle WHERE disponible = 1 ORDER BY batiment, nom";
$resultat = mysqli_query($conn, $sql);

// Filtrer par bâtiment si demandé
$batiment_filter = "";
if (isset($_GET['batiment']) && !empty($_GET['batiment'])) {
    $batiment_filter = nettoyer($_GET['batiment']);
    $sql = "SELECT * FROM salle WHERE disponible = 1 AND batiment = '$batiment_filter' ORDER BY nom";
    $resultat = mysqli_query($conn, $sql);
}

// Récupérer tous les bâtiments pour le filtre
$sql_batiments = "SELECT DISTINCT batiment FROM salle ORDER BY batiment";
$resultat_batiments = mysqli_query($conn, $sql_batiments);

// Traitement de la réservation
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reserver'])) {
    $salle_id = $_POST['salle_id'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $motif = nettoyer($_POST['motif']);
    
    // Vérifier si les dates sont valides
    if (strtotime($date_debut) >= strtotime($date_fin)) {
        $message = message_alerte("La date de fin doit être après la date de début.", "danger");
    } else {
        // Insérer la réservation
        $sql = "INSERT INTO reservation (utilisateur_id, salle_id, date_debut, date_fin, motif, statut) 
                VALUES ($id, $salle_id, '$date_debut', '$date_fin', '$motif', 'en_attente')";
        
        if (mysqli_query($conn, $sql)) {
            $message = message_alerte("Votre demande de réservation a été enregistrée et est en attente d'approbation.", "success");
        } else {
            $message = message_alerte("Erreur: " . mysqli_error($conn), "danger");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salles Disponibles - Système de Réservation MMI</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/pageadmin.css">
    <style>
        .salle-card {
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .salle-card:hover {
            transform: translateY(-5px);
        }
        .salle-img {
            height: 150px;
            object-fit: cover;
        }
    </style>
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
            <h2><i class="fas fa-door-open"></i> Salles Disponibles</h2>
            <p>Consultez et réservez les salles disponibles.</p>
        </div>
        
        <!-- Afficher les messages -->
        <?php echo $message; ?>
        
        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Filtrer par bâtiment</h5>
                <div class="d-flex flex-wrap">
                    <a href="salles.php" class="btn <?php echo empty($batiment_filter) ? 'btn-success' : 'btn-outline-success'; ?> m-1">Tous</a>
                    <?php while ($batiment = mysqli_fetch_assoc($resultat_batiments)): ?>
                        <a href="salles.php?batiment=<?php echo urlencode($batiment['batiment']); ?>" class="btn <?php echo $batiment_filter == $batiment['batiment'] ? 'btn-success' : 'btn-outline-success'; ?> m-1">
                            <?php echo $batiment['batiment']; ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        
        <!-- Liste des salles -->
        <div class="row">
            <?php if (mysqli_num_rows($resultat) > 0): ?>
                <?php while ($salle = mysqli_fetch_assoc($resultat)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card salle-card">
                            <img src="../images/salles/<?php echo !empty($salle['image']) ? $salle['image'] : 'default.jpg'; ?>" class="card-img-top salle-img" alt="<?php echo $salle['nom']; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $salle['nom']; ?></h5>
                                <p class="card-text"><?php echo $salle['description']; ?></p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <strong>Bâtiment:</strong> <?php echo $salle['batiment']; ?>, <strong>Étage:</strong> <?php echo $salle['etage']; ?><br>
                                        <strong>Capacité:</strong> <?php echo $salle['capacite']; ?> personnes<br>
                                        <strong>Équipements:</strong> <?php echo $salle['equipements']; ?>
                                    </small>
                                </p>
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#reservationModal<?php echo $salle['id']; ?>">
                                    Réserver
                                </button>
                            </div>
                        </div>
                        
                        <!-- Modal de réservation -->
                        <div class="modal fade" id="reservationModal<?php echo $salle['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="reservationModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="reservationModalLabel">Réserver <?php echo $salle['nom']; ?></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                        <div class="modal-body">
                                            <input type="hidden" name="salle_id" value="<?php echo $salle['id']; ?>">
                                            
                                            <div class="form-group">
                                                <label>Date et heure de début:</label>
                                                <input type="datetime-local" name="date_debut" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Date et heure de fin:</label>
                                                <input type="datetime-local" name="date_fin" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Motif de la réservation:</label>
                                                <textarea name="motif" class="form-control" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                            <button type="submit" name="reserver" class="btn btn-success">Réserver</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Aucune salle disponible dans ce bâtiment pour le moment.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p>© <?php echo date("Y"); ?> Système de Réservation</p>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
