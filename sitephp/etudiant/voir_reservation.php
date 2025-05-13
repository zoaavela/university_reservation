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

// Vérifier si l'ID de réservation est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: reservations.php");
    exit;
}

$reservation_id = $_GET['id'];

// Récupérer les détails de la réservation
$sql = "SELECT r.*, m.nom as materiel_nom, m.description as materiel_description, m.categorie as materiel_categorie,
        s.nom as salle_nom, s.description as salle_description, s.batiment, s.etage, s.capacite, s.equipements
        FROM reservation r 
        LEFT JOIN materiel m ON r.materiel_id = m.id 
        LEFT JOIN salle s ON r.salle_id = s.id 
        WHERE r.id = $reservation_id AND r.utilisateur_id = $id";
$resultat = mysqli_query($conn, $sql);

// Vérifier si la réservation existe et appartient à l'utilisateur
if (mysqli_num_rows($resultat) == 0) {
    header("location: reservations.php");
    exit;
}

$reservation = mysqli_fetch_assoc($resultat);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Réservation - Système de Réservation MMI</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .page-header {
            background-color: #6c757d;
            color: white;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .detail-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">MMI Réservation</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="materiel.php">Matériel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="salles.php">Salles</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="reservations.php">Mes Réservations</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="notifications.php">Notifications</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="profil.php">Mon Profil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">Déconnexion</a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="page-header">
            <h2><i class="fas fa-info-circle"></i> Détails de la Réservation #<?php echo $reservation_id; ?></h2>
            <p>Consultez les détails complets de votre réservation.</p>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card detail-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Informations de la réservation</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Type:</strong> 
                            <?php if ($reservation["materiel_id"]): ?>
                                <span class="badge badge-primary">Matériel</span>
                            <?php else: ?>
                                <span class="badge badge-success">Salle</span>
                            <?php endif; ?>
                        </p>
                        <p><strong>Date de début:</strong> <?php echo date("d/m/Y H:i", strtotime($reservation["date_debut"])); ?></p>
                        <p><strong>Date de fin:</strong> <?php echo date("d/m/Y H:i", strtotime($reservation["date_fin"])); ?></p>
                        <p><strong>Statut:</strong> 
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
                        </p>
                        <p><strong>Motif:</strong> <?php echo $reservation["motif"]; ?></p>
                        <p><strong>Date de demande:</strong> <?php echo date("d/m/Y H:i", strtotime($reservation["date_creation"])); ?></p>
                    </div>
                </div>
                
                <?php if ($reservation["commentaire"]): ?>
                <div class="card detail-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Commentaire de l'administrateur</h5>
                    </div>
                    <div class="card-body">
                        <p><?php echo $reservation["commentaire"]; ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-6">
                <?php if ($reservation["materiel_id"]): ?>
                <!-- Détails du matériel -->
                <div class="card detail-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Détails du matériel</h5>
                    </div>
                    <div class="card-body">
                        <h5><?php echo $reservation["materiel_nom"]; ?></h5>
                        <p><?php echo $reservation["materiel_description"]; ?></p>
                        <p><strong>Catégorie:</strong> <?php echo $reservation["materiel_categorie"]; ?></p>
                    </div>
                </div>
                <?php else: ?>
                <!-- Détails de la salle -->
                <div class="card detail-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Détails de la salle</h5>
                    </div>
                    <div class="card-body">
                        <h5><?php echo $reservation["salle_nom"]; ?></h5>
                        <p><?php echo $reservation["salle_description"]; ?></p>
                        <p><strong>Bâtiment:</strong> <?php echo $reservation["batiment"]; ?>, <strong>Étage:</strong> <?php echo $reservation["etage"]; ?></p>
                        <p><strong>Capacité:</strong> <?php echo $reservation["capacite"]; ?> personnes</p>
                        <p><strong>Équipements:</strong> <?php echo $reservation["equipements"]; ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="card detail-card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="reservations.php" class="btn btn-secondary btn-block">
                            <i class="fas fa-arrow-left"></i> Retour à la liste des réservations
                        </a>
                        
                        <?php if ($reservation["statut"] == "en_attente"): ?>
                        <a href="reservations.php?action=annuler&id=<?php echo $reservation_id; ?>" class="btn btn-danger btn-block mt-2" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation?');">
                            <i class="fas fa-times"></i> Annuler cette réservation
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p>© <?php echo date("Y"); ?> Système de Réservation MMI</p>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
