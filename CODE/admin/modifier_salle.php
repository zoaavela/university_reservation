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

// Traitement du formulaire de modification
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_salle = nettoyer_donnees($_POST['nom']);
    $capacite = intval($_POST['capacite']);
    $description = nettoyer_donnees($_POST['description']);
    $equipements = nettoyer_donnees($_POST['equipements']);
    $batiment = nettoyer_donnees($_POST['batiment']);
    $etage = nettoyer_donnees($_POST['etage']);
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    
    // Traitement de l'image
    $image = $salle['image']; // Garder l'image existante par défaut
    
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
            
            // Créer le dossier s'il n'existe pas
            if (!file_exists("../images/salles")) {
                mkdir("../images/salles", 0777, true);
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
    
    // Mettre à jour la salle dans la base de données
    $sql = "UPDATE salle SET nom = ?, capacite = ?, description = ?, disponible = ?, equipements = ?, batiment = ?, etage = ?, image = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sisssssi", $nom_salle, $capacite, $description, $disponible, $equipements, $batiment, $etage, $image, $salle_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = alerte("La salle a été modifiée avec succès.", "success");
        // Mettre à jour les informations de la salle
        $salle['nom'] = $nom_salle;
        $salle['capacite'] = $capacite;
        $salle['description'] = $description;
        $salle['equipements'] = $equipements;
        $salle['batiment'] = $batiment;
        $salle['etage'] = $etage;
        $salle['disponible'] = $disponible;
        $salle['image'] = $image;
    } else {
        $message = alerte("Erreur lors de la modification de la salle: " . mysqli_error($conn), "danger");
    }
}

// Définir le titre de la page
$page_title = "Modifier une Salle - Université Gustave Eiffel";
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
                    <h2><i class="fas fa-edit"></i> Modifier une Salle</h2>
                </div>
                
                <!-- Afficher les messages -->
                <?php echo $message; ?>
                
                <!-- Formulaire de modification de salle -->
                <div class="section-card">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $salle_id; ?>" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nom">Nom de la salle:</label>
                                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $salle['nom']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="capacite">Capacité (nombre de personnes):</label>
                                    <input type="number" class="form-control" id="capacite" name="capacite" min="1" value="<?php echo $salle['capacite']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="description">Description:</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo $salle['description']; ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="equipements">Équipements:</label>
                                    <textarea class="form-control" id="equipements" name="equipements" rows="2"><?php echo $salle['equipements']; ?></textarea>
                                    <small class="form-text text-muted">Exemple: Projecteur, Tableau blanc, Ordinateurs...</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="batiment">Bâtiment:</label>
                                    <input type="text" class="form-control" id="batiment" name="batiment" value="<?php echo $salle['batiment']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="etage">Étage:</label>
                                    <input type="text" class="form-control" id="etage" name="etage" value="<?php echo $salle['etage']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Image actuelle:</label>
                                    <div class="mb-2">
                                        <img src="../images/salles/<?php echo !empty($salle['image']) ? $salle['image'] : 'default.jpg'; ?>" alt="<?php echo $salle['nom']; ?>" class="img-thumbnail" style="max-height: 150px;">
                                    </div>
                                    <label for="image">Nouvelle image (optionnel):</label>
                                    <input type="file" class="form-control-file" id="image" name="image">
                                    <small class="form-text text-muted">Format: JPG, JPEG, PNG, GIF. Taille max: 5 MB.</small>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="disponible" name="disponible" <?php echo $salle['disponible'] ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="disponible">Disponible</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card col-md-6">
                            <div class="d-flex">
                                <a href=""><button type="submit" class="btn btn-primary mr-5">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                                </a>
                                <a href="salles.php" class="btn btn-secondary ml-5">
                                    <i class="fas fa-arrow-left"></i> Retour à la liste
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
   <!-- Footer -->
    <footer class="footer text-dark text-center py-3 mt-5">
        <p>© <?php echo date("Y"); ?> Système de Réservation MMI</p>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>