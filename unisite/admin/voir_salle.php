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

// Vérifier si l'ID de la salle est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: salles.php");
    exit;
}

$salle_id = intval($_GET['id']);

// Récupérer les informations de la salle
$sql = "SELECT * FROM salle WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $salle_id);
mysqli_stmt_execute($stmt);
$resultat = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultat) == 0) {
    header("location: salles.php");
    exit;
}

$salle = mysqli_fetch_assoc($resultat);

// Récupérer les réservations pour cette salle
$sql = "SELECT r.id, r.date_debut, r.date_fin, r.statut, r.motif, 
               u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, u.role as utilisateur_role
        FROM reservation r 
        JOIN utilisateur u ON r.utilisateur_id = u.id 
        WHERE r.salle_id = ? 
        ORDER BY r.date_debut DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $salle_id);
mysqli_stmt_execute($stmt);
$reservations = mysqli_stmt_get_result($stmt);

// Définir le titre de la page
$page_title = "Détails de la Salle - Université Gustave Eiffel";
$page = "salles";
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
                <div class="sidebar">
                    <div class="sidebar-title">
                        Espace Administration
                    </div>
                    <ul class="sidebar-menu">
                        <li>
                            <a href="index.php">
                                <i class="fas fa-tachometer-alt"></i> Tableau de bord
                            </a>
                        </li>
                        <li>
                            <a href="utilisateurs.php">
                                <i class="fas fa-users"></i> Utilisateurs
                            </a>
                        </li>
                        <li>
                            <a href="materiel.php">
                                <i class="fas fa-laptop"></i> Matériel
                            </a>
                        </li>
                        <li>
                            <a href="salles.php" class="active">
                                <i class="fas fa-door-open"></i> Salles
                            </a>
                        </li>
                        <li>
                            <a href="reservations.php">
                                <i class="fas fa-calendar-alt"></i> Réservations
                            </a>
                        </li>
                        <li>
                            <a href="commentaires.php">
                                <i class="fas fa-comments"></i> Commentaires
                            </a>
                        </li>
                        <li class="logout">
                            <a href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Contenu principal -->
            <div class="col-md-9">
                <h2 class="mb-4"><i class="fas fa-door-open"></i> Détails de la Salle</h2>
                
                <!-- Détails de la salle -->
                <div class="section-card mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="../images/salles/<?php echo !empty($salle['image']) ? $salle['image'] : 'default.jpg'; ?>" alt="<?php echo $salle['nom']; ?>" class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                            <h3><?php echo $salle['nom']; ?></h3>
                            <p class="text-muted">
                                <strong>Bâtiment:</strong> <?php echo $salle['batiment']; ?> | 
                                <strong>Étage:</strong> <?php echo $salle['etage']; ?> | 
                                <strong>Capacité:</strong> <?php echo $salle['capacite']; ?> personnes
                            </p>
                            <p>
                                <strong>Statut:</strong> 
                                <?php if ($salle['disponible']): ?>
                                    <span class="badge badge-success">Disponible</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Non disponible</span>
                                <?php endif; ?>
                            </p>
                            <h5>Description:</h5>
                            <p><?php echo $salle['description']; ?></p>
                            <h5>Équipements:</h5>
                            <p><?php echo $salle['equipements']; ?></p>
                            <div class="mt-3">
                                <a href="modifier_salle.php?id=<?php echo $salle['id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="salles.php?action=supprimer&id=<?php echo $salle['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette salle?');">
                                    <i class="fas fa-trash"></i> Supprimer
                                </a>
                                <a href="salles.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Retour à la liste
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Réservations de la salle -->
                <h4 class="mb-3">Réservations de cette salle</h4>
                <div class="section-card">
                    <?php if (mysqli_num_rows($reservations) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Utilisateur</th>
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
                                            <td><?php echo $reservation["id"]; ?></td>
                                            <td>
                                                <?php echo $reservation["utilisateur_prenom"] . " " . $reservation["utilisateur_nom"]; ?>
                                                <br>
                                                <small class="text-muted"><?php echo $reservation["utilisateur_role"]; ?></small>
                                            </td>
                                            <td><?php echo date("d/m/Y H:i", strtotime($reservation["date_debut"])); ?></td>
                                            <td><?php echo date("d/m/Y H:i", strtotime($reservation["date_fin"])); ?></td>
                                            <td><?php echo substr($reservation["motif"], 0, 30) . (strlen($reservation["motif"]) > 30 ? '...' : ''); ?></td>
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
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Aucune réservation trouvée pour cette salle.
                        </div>
                    <?php endif; ?>
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
