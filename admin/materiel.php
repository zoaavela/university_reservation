<?php
// Inclure le fichier de configuration
require_once '../config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["connecte"]) || $_SESSION["connecte"] !== true) {
    header("location: ../login.php");
    exit;
}

// Vérifier si l'utilisateur est un administrateur
if ($_SESSION["role"] !== "admin") {
    header("location: ../login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$id = $_SESSION["id"];
$nom = $_SESSION["nom"];
$prenom = $_SESSION["prenom"];

// Variable pour stocker les messages
$message = "";

// Traitement de la suppression d'un matériel
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $id_materiel = $_GET['id'];
    
    // Supprimer le matériel
    $sql = "DELETE FROM materiel WHERE id = $id_materiel";
    if (mysqli_query($conn, $sql)) {
        $message = '<div class="alert alert-success">Le matériel a été supprimé avec succès.</div>';
    } else {
        $message = '<div class="alert alert-danger">Erreur lors de la suppression du matériel: ' . mysqli_error($conn) . '</div>';
    }
}

// Traitement de l'ajout d'un matériel
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $nom_materiel = $_POST['nom'];
    $description = $_POST['description'];
    $quantite = $_POST['quantite'];
    $categorie = $_POST['categorie'];
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    
    // Par défaut, utiliser l'image par défaut
    $image = "default.jpg";
    
    // Vérifier si une image a été téléchargée
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $filename = $_FILES['image']['name'];
        
        // Télécharger l'image
        if (move_uploaded_file($_FILES['image']['tmp_name'], "../images/materiel/" . $filename)) {
            $image = $filename;
        } else {
            $message = '<div class="alert alert-danger">Erreur lors du téléchargement de l\'image.</div>';
        }
    }
    
    // Insérer le matériel dans la base de données
    $sql = "INSERT INTO materiel (nom, description, quantite, disponible, image, categorie) 
            VALUES ('$nom_materiel', '$description', $quantite, $disponible, '$image', '$categorie')";
    
    if (mysqli_query($conn, $sql)) {
        $message = '<div class="alert alert-success">Le matériel a été ajouté avec succès.</div>';
    } else {
        $message = '<div class="alert alert-danger">Erreur lors de l\'ajout du matériel: ' . mysqli_error($conn) . '</div>';
    }
}

// Récupérer la liste du matériel
$sql = "SELECT * FROM materiel ORDER BY categorie, nom";
$resultat = mysqli_query($conn, $sql);

// Filtrer par catégorie si demandé
if (isset($_GET['categorie']) && !empty($_GET['categorie'])) {
    $categorie_filter = $_GET['categorie'];
    $sql = "SELECT * FROM materiel WHERE categorie = '$categorie_filter' ORDER BY nom";
    $resultat = mysqli_query($conn, $sql);
} else {
    $categorie_filter = "";
}

// Récupérer toutes les catégories pour le filtre
$sql_categories = "SELECT DISTINCT categorie FROM materiel ORDER BY categorie";
$resultat_categories = mysqli_query($conn, $sql_categories);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion du Matériel - Administration</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Inclure Bootstrap pour le style -->
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
            
            <!-- Contenu principal -->
            <div class="col-md-10 content mx-auto">
                <div class="page-header">
                    <h2><i class="fas fa-laptop"></i> Gestion du Matériel</h2>
                    <p>Consultez, ajoutez, modifiez et supprimez le matériel disponible.</p>
                </div>
                
                <!-- Afficher les messages -->
                <?php echo $message; ?>
                
                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Filtrer par catégorie</h5>
                        <div class="d-flex flex-wrap">
                            <a href="materiel.php" class="btn <?php echo empty($categorie_filter) ? 'btn-dark' : 'btn-outline-dark'; ?> m-1">Tous</a>
                            <?php 
                            // Réinitialiser le pointeur du résultat
                            mysqli_data_seek($resultat_categories, 0);
                            while ($categorie = mysqli_fetch_assoc($resultat_categories)): 
                            ?>
                                <a href="materiel.php?categorie=<?php echo urlencode($categorie['categorie']); ?>" class="btn <?php echo $categorie_filter == $categorie['categorie'] ? 'btn-dark' : 'btn-outline-dark'; ?> m-1">
                                    <?php echo $categorie['categorie']; ?>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Formulaire d'ajout de matériel -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Ajouter un nouveau matériel</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nom">Nom du matériel:</label>
                                        <input type="text" class="form-control" id="nom" name="nom" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="description">Description:</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="categorie">Catégorie:</label>
                                        <input type="text" class="form-control" id="categorie" name="categorie" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="quantite">Quantité:</label>
                                        <input type="number" class="form-control" id="quantite" name="quantite" min="1" value="1" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="image">Image:</label>
                                        <input type="file" class="form-control-file" id="image" name="image">
                                        <small class="form-text text-muted">Format: JPG, JPEG, PNG, GIF. Taille max: 5 MB.</small>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="disponible" name="disponible" checked>
                                            <label class="custom-control-label" for="disponible">Disponible</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">Ajouter le matériel</button>
                        </form>
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
                                <tbody class="fond">
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
                                                    <a href="materiel.php?action=supprimer&id=<?php echo $materiel["id"]; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce matériel?');">
                                                        <i class="fas fa-trash"></i>
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
