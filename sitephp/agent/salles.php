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

// Récupérer la liste des salles
$sql = "SELECT * FROM salle ORDER BY batiment, nom";
$resultat = mysqli_query($conn, $sql);

// Filtrer par bâtiment si demandé
$batiment_filter = "";
if (isset($_GET['batiment']) && !empty($_GET['batiment'])) {
    $batiment_filter = nettoyer_donnees($_GET['batiment']);
    $sql = "SELECT * FROM salle WHERE batiment = ? ORDER BY nom";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $batiment_filter);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
}

// Récupérer tous les bâtiments pour le filtre
$sql_batiments = "SELECT DISTINCT batiment FROM salle ORDER BY batiment";
$resultat_batiments = mysqli_query($conn, $sql_batiments);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Consultation des Salles - Espace Agent</title>
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
        .salle-img {
            height: 100px;
            object-fit: cover;
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
                        <a class="nav-link active" href="salles.php">
                            <i class="fas fa-door-open"></i> Salles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reservations.php">
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
                    <h2><i class="fas fa-door-open"></i> Consultation des Salles</h2>
                    <p>Consultez la liste des salles disponibles.</p>
                </div>
                
                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Filtrer par bâtiment</h5>
                        <div class="d-flex flex-wrap">
                            <a href="salles.php" class="btn <?php echo empty($batiment_filter) ? 'btn-secondary' : 'btn-outline-secondary'; ?> m-1">Tous</a>
                            <?php 
                            mysqli_data_seek($resultat_batiments, 0);
                            while ($batiment = mysqli_fetch_assoc($resultat_batiments)): 
                            ?>
                                <a href="salles.php?batiment=<?php echo urlencode($batiment['batiment']); ?>" class="btn <?php echo $batiment_filter == $batiment['batiment'] ? 'btn-secondary' : 'btn-outline-secondary'; ?> m-1">
                                    <?php echo $batiment['batiment']; ?>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des salles -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Nom</th>
                                        <th>Bâtiment</th>
                                        <th>Étage</th>
                                        <th>Capacité</th>
                                        <th>Disponible</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($resultat) > 0): ?>
                                        <?php while ($salle = mysqli_fetch_assoc($resultat)): ?>
                                            <tr>
                                                <td><?php echo $salle["id"]; ?></td>
                                                <td>
                                                    <img src="../images/salles/<?php echo !empty($salle['image']) ? $salle['image'] : 'default.jpg'; ?>" class="salle-img" alt="<?php echo $salle['nom']; ?>">
                                                </td>
                                                <td><?php echo $salle["nom"]; ?></td>
                                                <td><?php echo $salle["batiment"]; ?></td>
                                                <td><?php echo $salle["etage"]; ?></td>
                                                <td><?php echo $salle["capacite"]; ?> personnes</td>
                                                <td>
                                                    <?php if ($salle["disponible"]): ?>
                                                        <span class="badge badge-success">Oui</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Non</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="voir_salle.php?id=<?php echo $salle["id"]; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Aucune salle trouvée</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Note informative -->
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle"></i> En tant qu'agent, vous pouvez consulter les salles mais vous ne pouvez pas les modifier. Veuillez contacter un administrateur pour toute modification.
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
