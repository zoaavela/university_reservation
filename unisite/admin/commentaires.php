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

// Supprimer un commentaire si demandé
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $commentaire_id = intval($_GET['id']);
    
    $sql = "DELETE FROM commentaire WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $commentaire_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = alerte("Le commentaire a été supprimé avec succès.", "success");
    } else {
        $message = alerte("Erreur lors de la suppression du commentaire: " . mysqli_error($conn), "danger");
    }
}

// Récupérer tous les commentaires
$sql = "SELECT c.*, r.id as reservation_id, u.nom, u.prenom, u.role, 
               m.nom as materiel_nom, s.nom as salle_nom
        FROM commentaire c
        JOIN reservation r ON c.reservation_id = r.id
        JOIN utilisateur u ON c.utilisateur_id = u.id
        LEFT JOIN materiel m ON r.materiel_id = m.id
        LEFT JOIN salle s ON r.salle_id = s.id
        ORDER BY c.date_creation DESC";
$resultat = mysqli_query($conn, $sql);

// Définir le titre de la page
$page_title = "Gestion des Commentaires - Université Gustave Eiffel";
$page = "commentaires";
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
                            <a href="salles.php">
                                <i class="fas fa-door-open"></i> Salles
                            </a>
                        </li>
                        <li>
                            <a href="reservations.php">
                                <i class="fas fa-calendar-alt"></i> Réservations
                            </a>
                        </li>
                        <li>
                            <a href="commentaires.php" class="active">
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
                <h2 class="mb-4"><i class="fas fa-comments"></i> Gestion des Commentaires</h2>
                
                <!-- Afficher les messages -->
                <?php echo $message; ?>
                
                <!-- Liste des commentaires -->
                <div class="section-card">
                    <?php if (mysqli_num_rows($resultat) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Utilisateur</th>
                                        <th>Réservation</th>
                                        <th>Note</th>
                                        <th>Commentaire</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($commentaire = mysqli_fetch_assoc($resultat)): ?>
                                        <tr>
                                            <td><?php echo $commentaire["id"]; ?></td>
                                            <td>
                                                <?php echo $commentaire["prenom"] . " " . $commentaire["nom"]; ?>
                                                <br>
                                                <small class="text-muted"><?php echo $commentaire["role"]; ?></small>
                                            </td>
                                            <td>
                                                <?php if ($commentaire["materiel_nom"]): ?>
                                                    <span class="badge badge-primary">Matériel</span> <?php echo $commentaire["materiel_nom"]; ?>
                                                <?php else: ?>
                                                    <span class="badge badge-success">Salle</span> <?php echo $commentaire["salle_nom"]; ?>
                                                <?php endif; ?>
                                                <br>
                                                <small>
                                                    <a href="voir_reservation.php?id=<?php echo $commentaire["reservation_id"]; ?>">
                                                        Voir la réservation
                                                    </a>
                                                </small>
                                            </td>
                                            <td>
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
                                            </td>
                                            <td><?php echo $commentaire["contenu"]; ?></td>
                                            <td><?php echo date("d/m/Y H:i", strtotime($commentaire["date_creation"])); ?></td>
                                            <td>
                                                <a href="commentaires.php?action=supprimer&id=<?php echo $commentaire["id"]; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Aucun commentaire trouvé.
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
