<?php
// Inclure le fichier de configuration
require_once '../config.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    header("location: ../login.php");
    exit;
}

// Vérifier si l'utilisateur est un étudiant
if (!est_role("etudiant")) {
    header("location: ../login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$id = $_SESSION["id"];
$nom = $_SESSION["nom"];
$prenom = $_SESSION["prenom"];
$email = $_SESSION["email"];

// Récupérer les informations spécifiques à l'étudiant
$sql = "SELECT numero_etudiant, filiere, annee_etude, groupe_tp, groupe_td FROM utilisateur WHERE id = $id";
$resultat = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($resultat);

$numero_etudiant = $row["numero_etudiant"];
$filiere = $row["filiere"];
$annee_etude = $row["annee_etude"];
$groupe_tp = $row["groupe_tp"];
$groupe_td = $row["groupe_td"];

// Traitement du formulaire de mise à jour
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $nouveau_numero = nettoyer($_POST["numero_etudiant"]);
    $nouvelle_filiere = nettoyer($_POST["filiere"]);
    $nouvelle_annee = nettoyer($_POST["annee_etude"]);
    $nouveau_tp = nettoyer($_POST["groupe_tp"]);
    $nouveau_td = nettoyer($_POST["groupe_td"]);
    
    // Mettre à jour les informations
    $sql = "UPDATE utilisateur SET 
            numero_etudiant = '$nouveau_numero', 
            filiere = '$nouvelle_filiere', 
            annee_etude = '$nouvelle_annee', 
            groupe_tp = '$nouveau_tp', 
            groupe_td = '$nouveau_td' 
            WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        $message = message_alerte("Votre profil a été mis à jour avec succès.", "success");
        
        // Mettre à jour les variables locales
        $numero_etudiant = $nouveau_numero;
        $filiere = $nouvelle_filiere;
        $annee_etude = $nouvelle_annee;
        $groupe_tp = $nouveau_tp;
        $groupe_td = $nouveau_td;
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
                        <p><strong>Rôle:</strong> Étudiant</p>
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
                                <label><i class="fas fa-id-card"></i> Numéro étudiant:</label>
                                <input type="text" name="numero_etudiant" class="form-control" value="<?php echo $numero_etudiant; ?>">
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-graduation-cap"></i> Filière:</label>
                                <select name="filiere" class="form-control">
                                    <option value="">Sélectionnez votre filière</option>
                                    <option value="MMI" <?php if ($filiere == "MMI") echo "selected"; ?>>MMI (Métiers du Multimédia et de l'Internet)</option>
                                    <option value="MMI-Dev" <?php if ($filiere == "MMI-Dev") echo "selected"; ?>>MMI - Parcours Développement Web</option>
                                    <option value="MMI-Com" <?php if ($filiere == "MMI-Com") echo "selected"; ?>>MMI - Parcours Communication</option>
                                    <option value="MMI-Design" <?php if ($filiere == "MMI-Design") echo "selected"; ?>>MMI - Parcours Design</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-user-graduate"></i> Année d'étude:</label>
                                <select name="annee_etude" class="form-control">
                                    <option value="">Sélectionnez votre année</option>
                                    <option value="BUT1" <?php if ($annee_etude == "BUT1") echo "selected"; ?>>BUT 1ère année</option>
                                    <option value="BUT2" <?php if ($annee_etude == "BUT2") echo "selected"; ?>>BUT 2ème année</option>
                                    <option value="BUT3" <?php if ($annee_etude == "BUT3") echo "selected"; ?>>BUT 3ème année</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-users"></i> Groupe TP:</label>
                                <select name="groupe_tp" class="form-control">
                                    <option value="">Sélectionnez votre groupe TP</option>
                                    <option value="TP1" <?php if ($groupe_tp == "TP1") echo "selected"; ?>>TP1</option>
                                    <option value="TP2" <?php if ($groupe_tp == "TP2") echo "selected"; ?>>TP2</option>
                                    <option value="TP3" <?php if ($groupe_tp == "TP3") echo "selected"; ?>>TP3</option>
                                    <option value="TP4" <?php if ($groupe_tp == "TP4") echo "selected"; ?>>TP4</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-users"></i> Groupe TD:</label>
                                <select name="groupe_td" class="form-control">
                                    <option value="">Sélectionnez votre groupe TD</option>
                                    <option value="TD1" <?php if ($groupe_td == "TD1") echo "selected"; ?>>TD1</option>
                                    <option value="TD2" <?php if ($groupe_td == "TD2") echo "selected"; ?>>TD2</option>
                                    <option value="TD3" <?php if ($groupe_td == "TD3") echo "selected"; ?>>TD3</option>
                                </select>
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
