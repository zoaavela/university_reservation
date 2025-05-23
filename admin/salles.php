<?php
// Inclure le fichier de configuration
require_once '../config.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    header("location: ../login.php");
    exit;
}

// Vérifier si l'utilisateur est un administrateur
if (!a_role("admin")) {
    header("location: ../login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$id = $_SESSION["id"];
$nom = $_SESSION["nom"];
$prenom = $_SESSION["prenom"];

// Traitement des actions
$message = "";

// Suppression d'une salle
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $id_salle = intval($_GET['id']);
    
    // Vérifier si la salle existe
    $sql = "SELECT id FROM salle WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_salle);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($resultat) > 0) {
        // Supprimer la salle
        $sql = "DELETE FROM salle WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_salle);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = alerte("La salle a été supprimée avec succès.", "success");
        } else {
            $message = alerte("Erreur lors de la suppression de la salle: " . mysqli_error($conn), "danger");
        }
    } else {
        $message = alerte("Salle introuvable.", "danger");
    }
}

// Ajout d'une nouvelle salle
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'ajouter') {
    $nom_salle = nettoyer_donnees($_POST['nom']);
    $capacite = intval($_POST['capacite']);
    $description = nettoyer_donnees($_POST['description']);
    $equipements = nettoyer_donnees($_POST['equipements']);
    $batiment = nettoyer_donnees($_POST['batiment']);
    $etage = nettoyer_donnees($_POST['etage']);
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    
    // Traitement de l'image
    $image = "default.jpg"; // Image par défaut
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $filename = $_FILES['image']['name'];
        $filetype = $_FILES['image']['type'];
        $filesize = $_FILES['image']['size'];
        
        // Vérifier l'extension du fichier
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            $message = alerte("Erreur: Veuillez sélectionner un format de fichier valide.", "danger");
        }
        
        // Vérifier la taille du fichier - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            $message = alerte("Erreur: La taille du fichier est supérieure à la limite autorisée (5 MB).", "danger");
        }
        
        // Vérifier le type MIME du fichier
        if (in_array($filetype, $allowed)) {
            // Vérifier si le fichier existe avant de le télécharger.
            if (file_exists("../images/salles/" . $filename)) {
                $filename = time() . '_' . $filename;
            }
            
            // Télécharger le fichier
            if (move_uploaded_file($_FILES['image']['tmp_name'], "../images/salles/" . $filename)) {
                $image = $filename;
            } else {
                $message = alerte("Erreur lors du téléchargement de l'image.", "danger");
            }
        } else {
            $message = alerte("Erreur: Il y a eu un problème de téléchargement de votre fichier. Veuillez réessayer.", "danger");
        }
    }
    
    // Insérer la salle dans la base de données
    $sql = "INSERT INTO salle (nom, capacite, description, disponible, equipements, batiment, etage, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sisssss", $nom_salle, $capacite, $description, $disponible, $equipements, $batiment, $etage, $image);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = alerte("La salle a été ajoutée avec succès.", "success");
    } else {
        $message = alerte("Erreur lors de l'ajout de la salle: " . mysqli_error($conn), "danger");
    }
}

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
    <title>Gestion des Salles - Administration</title>
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
                    <h2><i class="fas fa-door-open"></i> Gestion des Salles</h2>
                    <p>Consultez, ajoutez, modifiez et supprimez les salles disponibles.</p>
                </div>
                
                <!-- Afficher les messages -->
                <?php echo $message; ?>
                
                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Filtrer par bâtiment</h5>
                        <div class="d-flex flex-wrap">
                            <a href="salles.php" class="btn <?php echo empty($batiment_filter) ? 'btn-dark' : 'btn-outline-dark'; ?> m-1">Tous</a>
                            <?php 
                            mysqli_data_seek($resultat_batiments, 0);
                            while ($batiment = mysqli_fetch_assoc($resultat_batiments)): 
                            ?>
                                <a href="salles.php?batiment=<?php echo urlencode($batiment['batiment']); ?>" class="btn <?php echo $batiment_filter == $batiment['batiment'] ? 'btn-dark' : 'btn-outline-dark'; ?> m-1">
                                    <?php echo $batiment['batiment']; ?>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Formulaire d'ajout de salle -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Ajouter une nouvelle salle</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="ajouter">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nom">Nom de la salle:</label>
                                        <input type="text" class="form-control" id="nom" name="nom" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="capacite">Capacité (nombre de personnes):</label>
                                        <input type="number" class="form-control" id="capacite" name="capacite" min="1" value="1" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="description">Description:</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="equipements">Équipements:</label>
                                        <textarea class="form-control" id="equipements" name="equipements" rows="2"></textarea>
                                        <small class="form-text text-muted">Exemple: Projecteur, Tableau blanc, Ordinateurs...</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="batiment">Bâtiment:</label>
                                        <input type="text" class="form-control" id="batiment" name="batiment" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="etage">Étage:</label>
                                        <input type="text" class="form-control" id="etage" name="etage" required>
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
                            <button type="submit" class="btn btn-success">Ajouter la salle</button>
                        </form>
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
                                <tbody class="fond">
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
                                                    <a href="modifier_salle.php?id=<?php echo $salle["id"]; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="salles.php?action=supprimer&id=<?php echo $salle["id"]; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette salle?');">
                                                        <i class="fas fa-trash"></i>
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
