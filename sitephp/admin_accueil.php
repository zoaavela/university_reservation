<?php
// Inclure le fichier de configuration
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["connecte"]) || $_SESSION["connecte"] !== true) {
    header("location: login.php");
    exit;
}

// Vérifier si l'utilisateur est un administrateur
if ($_SESSION["role"] !== "admin") {
    header("location: login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$nom = $_SESSION["nom"];
$prenom = $_SESSION["prenom"];

// Récupérer quelques statistiques pour le tableau de bord
// Nombre total d'utilisateurs
$sql_utilisateurs = "SELECT COUNT(*) as total FROM utilisateur";
$resultat_utilisateurs = mysqli_query($conn, $sql_utilisateurs);
$row_utilisateurs = mysqli_fetch_assoc($resultat_utilisateurs);
$total_utilisateurs = $row_utilisateurs["total"];

// Nombre total de réservations
$sql_reservations = "SELECT COUNT(*) as total FROM reservation";
$resultat_reservations = mysqli_query($conn, $sql_reservations);
$row_reservations = mysqli_fetch_assoc($resultat_reservations);
$total_reservations = $row_reservations["total"];

// Nombre total de matériel
$sql_materiel = "SELECT COUNT(*) as total FROM materiel";
$resultat_materiel = mysqli_query($conn, $sql_materiel);
$row_materiel = mysqli_fetch_assoc($resultat_materiel);
$total_materiel = $row_materiel["total"];

// Nombre total de salles
$sql_salles = "SELECT COUNT(*) as total FROM salle";
$resultat_salles = mysqli_query($conn, $sql_salles);
$row_salles = mysqli_fetch_assoc($resultat_salles);
$total_salles = $row_salles["total"];

// Récupérer les 5 dernières réservations
$sql_dernieres_reservations = "SELECT r.id, u.nom, u.prenom, r.date_debut, r.date_fin, r.statut 
                              FROM reservation r 
                              JOIN utilisateur u ON r.utilisateur_id = u.id 
                              ORDER BY r.id DESC LIMIT 5";
$resultat_dernieres_reservations = mysqli_query($conn, $sql_dernieres_reservations);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Administration - Système de Réservation</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Inclure Bootstrap pour un style simple mais efficace -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .bienvenue {
            background-color: #343a40;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .carte {
            margin-bottom: 20px;
        }
        .statistique {
            text-align: center;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .statistique h3 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .bg-admin {
            background-color: #343a40;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Barre latérale -->
            <div class="col-md-2 bg-dark text-white p-0" style="min-height: 100vh;">
                <div class="p-4">
                    <h4>Administration</h4>
                    <p>Système de Réservation</p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white active" href="admin_accueil.php">
                            Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">
                            Utilisateurs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">
                            Matériel
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">
                            Salles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">
                            Réservations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">
                            Commentaires
                        </a>
                    </li>
                    <li class="nav-item mt-5">
                        <a class="nav-link text-white" href="logout.php">
                            Déconnexion
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Contenu principal -->
            <div class="col-md-10 p-4">
                <!-- Message de bienvenue -->
                <div class="bienvenue">
                    <h2>Bienvenue, <?php echo $prenom . ' ' . $nom; ?> !</h2>
                    <p>Vous êtes connecté en tant qu'administrateur. Vous avez accès à toutes les fonctionnalités du système.</p>
                </div>
                
                <!-- Statistiques -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="statistique bg-primary text-white">
                            <h3><?php echo $total_utilisateurs; ?></h3>
                            <p>Utilisateurs</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="statistique bg-success text-white">
                            <h3><?php echo $total_reservations; ?></h3>
                            <p>Réservations</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="statistique bg-warning text-dark">
                            <h3><?php echo $total_materiel; ?></h3>
                            <p>Matériel</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="statistique bg-info text-white">
                            <h3><?php echo $total_salles; ?></h3>
                            <p>Salles</p>
                        </div>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-admin">
                                <h5 class="mb-0">Actions rapides</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <a href="#" class="btn btn-outline-primary btn-block mb-2">Ajouter un utilisateur</a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="#" class="btn btn-outline-success btn-block mb-2">Ajouter du matériel</a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="#" class="btn btn-outline-warning btn-block mb-2">Ajouter une salle</a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="#" class="btn btn-outline-info btn-block mb-2">Gérer les réservations</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Dernières réservations -->
                <div class="card">
                    <div class="card-header bg-admin">
                        <h5 class="mb-0">Dernières réservations</h5>
                    </div>
                    <div class="card-body">
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
                                <?php while ($row = mysqli_fetch_assoc($resultat_dernieres_reservations)): ?>
                                <tr>
                                    <td><?php echo $row["id"]; ?></td>
                                    <td><?php echo $row["prenom"] . " " . $row["nom"]; ?></td>
                                    <td><?php echo $row["date_debut"]; ?></td>
                                    <td><?php echo $row["date_fin"]; ?></td>
                                    <td>
                                        <?php 
                                        $statut = $row["statut"];
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
                                        <a href="#" class="btn btn-sm btn-info">Voir</a>
                                        <a href="#" class="btn btn-sm btn-success">Approuver</a>
                                        <a href="#" class="btn btn-sm btn-danger">Refuser</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                
                                <?php if (mysqli_num_rows($resultat_dernieres_reservations) == 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucune réservation trouvée</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <a href="#" class="btn btn-primary">Voir toutes les réservations</a>
                    </div>
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
