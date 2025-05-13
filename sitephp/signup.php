<?php
// Inclure le fichier de configuration
require_once 'config.php';

// Variables pour stocker les messages
$message_erreur = '';
$message_succes = '';

// Si l'utilisateur est déjà connecté, le rediriger
if (est_connecte()) {
    header("location: index.php");
    exit;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $nom = nettoyer($_POST["nom"]);
    $prenom = nettoyer($_POST["prenom"]);
    $email = nettoyer($_POST["email"]);
    $mot_de_passe = $_POST["password"];
    $confirmer_mot_de_passe = $_POST["confirm_password"];
    $role = "etudiant"; // Par défaut, tous les utilisateurs sont des étudiants
    
    // Vérifier si tous les champs sont remplis
    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe) || empty($confirmer_mot_de_passe)) {
        $message_erreur = "Veuillez remplir tous les champs.";
    } 
    // Vérifier si les mots de passe correspondent
    else if ($mot_de_passe != $confirmer_mot_de_passe) {
        $message_erreur = "Les mots de passe ne correspondent pas.";
    }
    // Vérifier la longueur du mot de passe
    else if (strlen($mot_de_passe) < 6) {
        $message_erreur = "Le mot de passe doit contenir au moins 6 caractères.";
    }
    else {
        // Vérifier si l'email existe déjà
        $sql = "SELECT id FROM utilisateur WHERE email = '$email'";
        $resultat = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($resultat) > 0) {
            $message_erreur = "Cet email est déjà utilisé.";
        } else {
            // Récupérer les champs spécifiques aux étudiants
            $numero_etudiant = nettoyer($_POST["numero_etudiant"]);
            $filiere = nettoyer($_POST["filiere"]);
            $annee_etude = nettoyer($_POST["annee_etude"]);
            $groupe_tp = nettoyer($_POST["groupe_tp"]);
            $groupe_td = nettoyer($_POST["groupe_td"]);
            
            // Hacher le mot de passe
            $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            
            // Insérer l'utilisateur dans la base de données
            $sql = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role, numero_etudiant, filiere, annee_etude, groupe_tp, groupe_td) 
                    VALUES ('$nom', '$prenom', '$email', '$mot_de_passe_hache', '$role', '$numero_etudiant', '$filiere', '$annee_etude', '$groupe_tp', '$groupe_td')";
            
            if (mysqli_query($conn, $sql)) {
                $message_succes = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            } else {
                $message_erreur = "Erreur: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inscription - Système de Réservation MMI</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .signup-form {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo i {
            font-size: 50px;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="signup-form">
            <div class="logo">
                <i class="fas fa-university"></i>
            </div>
            <h2 class="text-center mb-4">Inscription</h2>
            <h5 class="text-center mb-4">Système de Réservation MMI</h5>
            
            <!-- Afficher les messages -->
            <?php if (!empty($message_erreur)): ?>
                <div class="alert alert-danger"><?php echo $message_erreur; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($message_succes)): ?>
                <div class="alert alert-success"><?php echo $message_succes; ?></div>
            <?php endif; ?>
            
            <!-- Formulaire d'inscription -->
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <!-- Informations personnelles -->
                <h4 class="mb-3">Informations personnelles</h4>
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nom:</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Prénom:</label>
                    <input type="text" name="prenom" class="form-control" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Mot de passe:</label>
                    <input type="password" name="password" class="form-control" required>
                    <small class="form-text text-muted">Le mot de passe doit contenir au moins 6 caractères.</small>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Confirmer le mot de passe:</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                
                <!-- Informations étudiant -->
                <h4 class="mt-4 mb-3">Informations étudiant MMI</h4>
                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> Numéro étudiant:</label>
                    <input type="text" name="numero_etudiant" class="form-control" placeholder="Ex: 22MMI1234">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-graduation-cap"></i> Filière:</label>
                    <select name="filiere" class="form-control">
                        <option value="">Sélectionnez votre filière</option>
                        <option value="MMI">MMI (Métiers du Multimédia et de l'Internet)</option>
                        <option value="MMI-Dev">MMI - Parcours Développement Web</option>
                        <option value="MMI-Com">MMI - Parcours Communication</option>
                        <option value="MMI-Design">MMI - Parcours Design</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user-graduate"></i> Année d'étude:</label>
                    <select name="annee_etude" class="form-control">
                        <option value="">Sélectionnez votre année</option>
                        <option value="BUT1">BUT 1ère année</option>
                        <option value="BUT2">BUT 2ème année</option>
                        <option value="BUT3">BUT 3ème année</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-users"></i> Groupe TP:</label>
                    <select name="groupe_tp" class="form-control">
                        <option value="">Sélectionnez votre groupe TP</option>
                        <option value="TP1">TP1</option>
                        <option value="TP2">TP2</option>
                        <option value="TP3">TP3</option>
                        <option value="TP4">TP4</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-users"></i> Groupe TD:</label>
                    <select name="groupe_td" class="form-control">
                        <option value="">Sélectionnez votre groupe TD</option>
                        <option value="TD1">TD1</option>
                        <option value="TD2">TD2</option>
                        <option value="TD3">TD3</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-user-plus"></i> S'inscrire</button>
                </div>
                <p class="text-center">Déjà un compte? <a href="login.php">Se connecter</a></p>
            </form>
        </div>
        <div class="text-center mt-3 mb-5">
            <p class="text-muted">© <?php echo date("Y"); ?> Système de Réservation MMI</p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
