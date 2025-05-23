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

// Traitement du formulaire d'ajout de salle
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    
    // Insérer la salle dans la base de données
    $sql = "INSERT INTO salle (nom, capacite, description, disponible, equipements, batiment, etage, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sissssss", $nom_salle, $capacite, $description, $disponible, $equipements, $batiment, $etage, $image);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = alerte("La salle a été ajoutée avec succès.", "success");
        // Rediriger vers la liste des salles
        header("location: salles.php");
        exit;
    } else {
        $message = alerte("Erreur lors de l'ajout de la salle: " . mysqli_error($conn), "danger");
    }
}

// Définir le titre de la page
$page_title = "Ajouter une Salle - Université Gustave Eiffel";
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
                <h2 class="mb-4"><i class="fas fa-plus-circle"></i> Ajouter une Salle</h2>
                
                <!-- Afficher les messages -->
                <?php echo $message; ?>
                
                <!-- Formulaire d'ajout de salle -->
                <div class="section-card">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
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
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Ajouter la salle
                            </button>
                            <a href="salles.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour à la liste
                            </a>
                        </div>
                    </form>
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
