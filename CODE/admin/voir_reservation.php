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

// Vérifier si l'ID de la réservation est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: reservations.php");
    exit;
}

$reservation_id = intval($_GET['id']);

// Traitement des actions (approuver ou refuser une réservation)
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $commentaire = isset($_POST['commentaire_admin']) ? nettoyer_donnees($_POST['commentaire_admin']) : "";
    
    // Approuver une réservation
    if ($action == 'approuver') {
        $sql = "UPDATE reservation SET statut = 'approuvee', commentaire_admin = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $commentaire, $reservation_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = alerte("La réservation a été approuvée avec succès.", "success");
            
            // Ajouter une notification pour l'utilisateur
            $sql_user = "SELECT utilisateur_id FROM reservation WHERE id = ?";
            $stmt_user = mysqli_prepare($conn, $sql_user);
            mysqli_stmt_bind_param($stmt_user, "i", $reservation_id);
            mysqli_stmt_execute($stmt_user);
            $resultat_user = mysqli_stmt_get_result($stmt_user);
            $utilisateur = mysqli_fetch_assoc($resultat_user);
            
            $titre = "Réservation approuvée";
            $message_notif = "Votre réservation #$reservation_id a été approuvée.";
            
            $sql_notif = "INSERT INTO notification (utilisateur_id, titre, message) VALUES (?, ?, ?)";
            $stmt_notif = mysqli_prepare($conn, $sql_notif);
            mysqli_stmt_bind_param($stmt_notif, "iss", $utilisateur['utilisateur_id'], $titre, $message_notif);
            mysqli_stmt_execute($stmt_notif);
        } else {
            $message = alerte("Erreur lors de l'approbation: " . mysqli_error($conn), "danger");
        }
    }
    
    // Refuser une réservation
    if ($action == 'refuser') {
        $sql = "UPDATE reservation SET statut = 'refusee', commentaire_admin = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $commentaire, $reservation_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = alerte("La réservation a été refusée.", "success");
            
            // Ajouter une notification pour l'utilisateur
            $sql_user = "SELECT utilisateur_id FROM reservation WHERE id = ?";
            $stmt_user = mysqli_prepare($conn, $sql_user);
            mysqli_stmt_bind_param($stmt_user, "i", $reservation_id);
            mysqli_stmt_execute($stmt_user);
            $resultat_user = mysqli_stmt_get_result($stmt_user);
            $utilisateur = mysqli_fetch_assoc($resultat_user);
            
            $titre = "Réservation refusée";
            $message_notif = "Votre réservation #$reservation_id a été refusée. Veuillez consulter les commentaires pour plus d'informations.";
            
            $sql_notif = "INSERT INTO notification (utilisateur_id, titre, message) VALUES (?, ?, ?)";
            $stmt_notif = mysqli_prepare($conn, $sql_notif);
            mysqli_stmt_bind_param($stmt_notif, "iss", $utilisateur['utilisateur_id'], $titre, $message_notif);
            mysqli_stmt_execute($stmt_notif);
        } else {
            $message = alerte("Erreur lors du refus: " . mysqli_error($conn), "danger");
        }
    }
}

// Récupérer les détails de la réservation
$sql = "SELECT r.*, 
               u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, u.email as utilisateur_email, u.role as utilisateur_role,
               m.nom as materiel_nom, s.nom as salle_nom 
        FROM reservation r 
        JOIN utilisateur u ON r.utilisateur_id = u.id 
        LEFT JOIN materiel m ON r.materiel_id = m.id 
        LEFT JOIN salle s ON r.salle_id = s.id 
        WHERE r.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $reservation_id);
mysqli_stmt_execute($stmt);
$resultat = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultat) == 0) {
    header("location: reservations.php");
    exit;
}

$reservation = mysqli_fetch_assoc($resultat);

// Récupérer les commentaires pour cette réservation
$sql = "SELECT c.*, u.nom, u.prenom, u.role
        FROM commentaire c
        JOIN utilisateur u ON c.utilisateur_id = u.id
        WHERE c.reservation_id = ?
        ORDER BY c.date_creation DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $reservation_id);
mysqli_stmt_execute($stmt);
$commentaires = mysqli_stmt_get_result($stmt);

// Définir le titre de la page
$page_title = "Détails de la Réservation - Université Gustave Eiffel";
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
            <a class="navbar-brand" href="index.php">
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
                            <a href="salles.php">
                                <i class="fas fa-door-open"></i> Salles
                            </a>
                        </li>
                        <li>
                            <a href="reservations.php" class="active">
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
                <h2 class="mb-4"><i class="fas fa-calendar-alt"></i> Détails de la Réservation #<?php echo $reservation_id; ?></h2>
                
                <!-- Afficher les messages -->
                <?php echo $message; ?>
                
                <!-- Détails de la réservation -->
                <div class="section-card mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Informations générales</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th>ID:</th>
                                    <td><?php echo $reservation["id"]; ?></td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td>
                                        <?php if ($reservation["materiel_id"]): ?>
                                            <span class="badge badge-primary">Matériel</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Salle</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Objet:</th>
                                    <td>
                                        <?php 
                                        if ($reservation["materiel_id"]) {
                                            echo $reservation["materiel_nom"];
                                        } else {
                                            echo $reservation["salle_nom"];
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date début:</th>
                                    <td><?php echo date("d/m/Y H:i", strtotime($reservation["date_debut"])); ?></td>
                                </tr>
                                <tr>
                                    <th>Date fin:</th>
                                    <td><?php echo date("d/m/Y H:i", strtotime($reservation["date_fin"])); ?></td>
                                </tr>
                                <tr>
                                    <th>Statut:</th>
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
                                </tr>
                                <tr>
                                    <th>Date de création:</th>
                                    <td><?php echo date("d/m/Y H:i", strtotime($reservation["date_creation"])); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Informations sur l'utilisateur</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th>Nom:</th>
                                    <td><?php echo $reservation["utilisateur_prenom"] . " " . $reservation["utilisateur_nom"]; ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?php echo $reservation["utilisateur_email"]; ?></td>
                                </tr>
                                <tr>
                                    <th>Rôle:</th>
                                    <td><?php echo $reservation["utilisateur_role"]; ?></td>
                                </tr>
                            </table>
                            
                            <h5>Motif de la réservation</h5>
                            <p><?php echo $reservation["motif"]; ?></p>
                            
                            <?php if (!empty($reservation["commentaire_admin"])): ?>
                                <h5>Commentaire administratif</h5>
                                <p><?php echo $reservation["commentaire_admin"]; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Actions pour la réservation -->
                    <?php if ($reservation["statut"] == "en_attente"): ?>
                        <hr>
                        <h5>Actions</h5>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $reservation_id; ?>">
                            <div class="form-group">
                                <label for="commentaire_admin">Commentaire (optionnel):</label>
                                <textarea class="form-control" id="commentaire_admin" name="commentaire_admin" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <button type="submit" name="action" value="approuver" class="btn btn-success">
                                    <i class="fas fa-check"></i> Approuver
                                </button>
                                <button type="submit" name="action" value="refuser" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Refuser
                                </button>
                                <a href="reservations.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Retour à la liste
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <hr>
                        <div class="form-group">
                            <a href="reservations.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour à la liste
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Commentaires -->
                <h4 class="mb-3">Commentaires</h4>
                <div class="section-card">
                    <?php if (mysqli_num_rows($commentaires) > 0): ?>
                        <?php while ($commentaire = mysqli_fetch_assoc($commentaires)): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong><?php echo $commentaire["prenom"] . " " . $commentaire["nom"]; ?></strong>
                                            <small class="text-muted ml-2"><?php echo $commentaire["role"]; ?></small>
                                        </div>
                                        <small class="text-muted"><?php echo date("d/m/Y H:i", strtotime($commentaire["date_creation"])); ?></small>
                                    </div>
                                    <div class="mb-2">
                                        <?php 
                                        $note = $commentaire["note"];
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $note) {
                                                echo '<i class="fas fa-star text-warning"></i>';
                                            } else {
                                                echo '<i class="far fa-star text-warning"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <p class="card-text"><?php echo $commentaire["contenu"]; ?></p>
                                    <a href="commentaires.php?action=supprimer&id=<?php echo $commentaire["id"]; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire?');">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Aucun commentaire pour cette réservation.
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