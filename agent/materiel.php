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

// Récupérer la liste du matériel
$sql = "SELECT * FROM materiel ORDER BY categorie, nom";
$resultat = mysqli_query($conn, $sql);

// Filtrer par catégorie si demandé
$categorie_filter = "";
if (isset($_GET['categorie']) && !empty($_GET['categorie'])) {
    $categorie_filter = nettoyer_donnees($_GET['categorie']);
    $sql = "SELECT * FROM materiel WHERE categorie = ? ORDER BY nom";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $categorie_filter);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
}

// Récupérer toutes les catégories pour le filtre
$sql_categories = "SELECT DISTINCT categorie FROM materiel ORDER BY categorie";
$resultat_categories = mysqli_query($conn, $sql_categories);


$base_url = "http://localhost/unisite/agent/";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Consultation du Matériel - Espace Agent</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Inclure Bootstrap pour un style simple mais efficace -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>index.php" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt mr-2"></i> Tableau de bord
                </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>utilisateurs.php" class="<?php echo $page == 'utilisateurs' ? 'active' : ''; ?>">
                    <i class="fas fa-users mr-2"></i> Voir les utilisateurs
                </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>materiel.php" class="<?php echo $page == 'materiel' ? 'active' : ''; ?>">
                    <i class="fas fa-laptop mr-2"></i> Voir le matériel
                </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>salles.php" class="<?php echo $page == 'salles' ? 'active' : ''; ?>">
                    <i class="fas fa-door-open mr-2"></i> Voir les salles
                </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>reservations.php" class="<?php echo $page == 'reservations' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt mr-2"></i> Voir les réservations
                </a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                 <li class="logout">
                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>../logout.php">
                    <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                </a>
            </li>
            </ul>
        </div>
    </nav>
            
            <!-- Contenu principal -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col col-md-12">
                <div class="page-header mt-5">
                    <h2><i class="fas fa-laptop"></i> Consultation du Matériel</h2>
                    <p>Consultez la liste du matériel disponible.</p>
                </div>
                
                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Filtrer par catégorie</h5>
                        <div class="d-flex flex-wrap">
                            <a href="materiel.php" class="btn <?php echo empty($categorie_filter) ? 'btn-secondary' : 'btn-outline-secondary'; ?> m-1">Tous</a>
                            <?php 
                            mysqli_data_seek($resultat_categories, 0);
                            while ($categorie = mysqli_fetch_assoc($resultat_categories)): 
                            ?>
                                <a href="materiel.php?categorie=<?php echo urlencode($categorie['categorie']); ?>" class="btn <?php echo $categorie_filter == $categorie['categorie'] ? 'btn-secondary' : 'btn-outline-secondary'; ?> m-1">
                                    <?php echo $categorie['categorie']; ?>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Liste du matériel -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Nom</th>
                                        <th>Description</th>
                                        <th>Catégorie</th>
                                        <th>Quantité</th>
                                        <th>Disponible</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($resultat) > 0): ?>
                                        <?php while ($materiel = mysqli_fetch_assoc($resultat)): ?>
                                            <tr>
                                                <td><?php echo $materiel["id"]; ?></td>
                                                <td>
                                                    <img src="../images/materiel/<?php echo !empty($materiel['image']) ? $materiel['image'] : 'default.jpg'; ?>" class="materiel-img" alt="<?php echo $materiel['nom']; ?>">
                                                </td>
                                                <td><?php echo $materiel["nom"]; ?></td>
                                                <td><?php echo substr($materiel["description"], 0, 50) . (strlen($materiel["description"]) > 50 ? '...' : ''); ?></td>
                                                <td><?php echo $materiel["categorie"]; ?></td>
                                                <td><?php echo $materiel["quantite"]; ?></td>
                                                <td>
                                                    <?php if ($materiel["disponible"]): ?>
                                                        <span class="badge badge-success">Oui</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Non</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="voir_materiel.php?id=<?php echo $materiel["id"]; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Aucun matériel trouvé</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                
                <!-- Note informative -->
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle"></i> En tant qu'agent, vous pouvez consulter le matériel mais vous ne pouvez pas le modifier. Veuillez contacter un administrateur pour toute modification.
                </div>
            </div>
        </div>
    </div>

       <!-- Footer -->
    <footer class="footer text-dark text-center py-3 mt-5">
        <p>© <?php echo date("Y"); ?> Système de Réservation MMI</p>
    </footer>
    
    <!-- Scripts JavaScript de Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
