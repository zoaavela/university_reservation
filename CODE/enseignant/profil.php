<?php
// Inclure le fichier de configuration
require_once '../config.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    header("location: ../login.php");
    exit;
}

// Vérifier si l'utilisateur est un étudiant
if (!est_role("enseignant")) {
    header("location: ../login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$id = $_SESSION["id"];
$nom = $_SESSION["nom"];
$prenom = $_SESSION["prenom"];
$email = $_SESSION["email"];

// Récupérer les informations spécifiques à l'étudiant
$sql = "SELECT departement, matiere, bureau FROM utilisateur WHERE id = $id";
$resultat = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($resultat);

$departement = $row["departement"];
$matiere = $row["matiere"];
$bureau = $row["bureau"];

// Traitement du formulaire de mise à jour
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $nouveau_departement = nettoyer($_POST["departement"]);
    $nouvelle_matiere = nettoyer($_POST["matiere"]);
    $nouveau_bureau = nettoyer($_POST["bureau"]);
    
    // Mettre à jour les informations
    $sql = "UPDATE utilisateur SET 
            departement = '$nouveau_departement', 
            matiere = '$nouvelle_matiere', 
            bureau = '$nouveau_bureau',  
            WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        $message = message_alerte("Votre profil a été mis à jour avec succès.", "success");
        
        // Mettre à jour les variables locales
        $departement = $nouveau_departement;
        $matiere = $nouvelle_matiere;
        $bureau = $nouveau_bureau;
    } else {
        $message = message_alerte("Erreur lors de la mise à jour du profil: " . mysqli_error($conn), "danger");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Système de Réservation MMI</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome pour les icônes -->
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
                <li class="nav-item">
                    <a class="nav-link" href="materiel.php"><i class="fas fa-laptop"></i>Matériel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="salles.php"><i class="fas fa-door-open"></i>Salles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reservations.php"><i class="fas fa-calendar-alt"></i>Mes Réservations</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="profil.php"><i class="fas fa-user"></i>Mon Profil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="page-header">
            <h2><i class="fas fa-user"></i> Mon Profil</h2>
            <p>Consultez et modifiez vos informations personnelles.</p>
        </div>
        
        <!-- Afficher les messages -->
        <?php echo $message; ?>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Informations personnelles</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Nom:</strong> <?php echo $nom; ?></p>
                        <p><strong>Prénom:</strong> <?php echo $prenom; ?></p>
                        <p><strong>Email:</strong> <?php echo $email; ?></p>
                        <p><strong>Rôle:</strong> Enseignant</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Modifier mes informations</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="form-group">
                                <label><i class="fas fa-id-card"></i> Département:</label>
                                <input type="text" name="departement" class="form-control" value="<?php echo $departement; ?>">
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-graduation-cap"></i> Matière:</label>
                                <input type="text" name="matiere" class="form-control" value="<?php echo $matiere; ?>">
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-user-graduate"></i> Bureau:</label>
                                <input type="text" name="bureau" class="form-control" value="<?php echo $bureau; ?>">
                            </div>
                            <button type="submit" class="btn btn-info btn-block">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </form>
                    </div>
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
