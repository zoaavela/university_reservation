<?php
// Inclure le fichier de configuration
require_once 'config.php';

// Variable pour stocker les messages d'erreur
$message_erreur = '';

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer et nettoyer les données du formulaire
    $email = nettoyer_donnees($_POST["email"]);
    $mot_de_passe = $_POST["password"]; // Le mot de passe n'est pas nettoyé car il sera haché
    
    // Vérifier si les champs sont vides
    if (empty($email) || empty($mot_de_passe)) {
        $message_erreur = "Veuillez remplir tous les champs.";
    } else {
        // Requête pour vérifier si l'utilisateur existe
        $sql = "SELECT * FROM utilisateur WHERE email = '$email'";
        $resultat = mysqli_query($conn, $sql);
        
        // Vérifier si l'utilisateur existe
        if (mysqli_num_rows($resultat) == 1) {
            $utilisateur = mysqli_fetch_assoc($resultat);
            
            // Vérifier le mot de passe
            if (password_verify($mot_de_passe, $utilisateur["mot_de_passe"])) {
                // Créer les variables de session
                $_SESSION["connecte"] = true;
                $_SESSION["id"] = $utilisateur["id"];
                $_SESSION["nom"] = $utilisateur["nom"];
                $_SESSION["prenom"] = $utilisateur["prenom"];
                $_SESSION["role"] = $utilisateur["role"];
                
                // Rediriger vers la page d'accueil selon le rôle
                if ($utilisateur["role"] == "etudiant") {
                    header("location: etudiant_accueil.php");
                } else if ($utilisateur["role"] == "enseignant") {
                    header("location: enseignant_accueil.php");
                } else if ($utilisateur["role"] == "admin") {
                    header("location: admin_accueil.php");
                } else if ($utilisateur["role"] == "agent") {
                    header("location: agent_accueil.php");
                } else {
                    $message_erreur = "Rôle non reconnu.";
                }
                exit();
            } else {
                $message_erreur = "Mot de passe incorrect.";
            }
        } else {
            $message_erreur = "Aucun compte trouvé avec cet email.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Connexion - Système de Réservation</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Inclure Bootstrap pour un style simple mais efficace -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .formulaire-connexion {
            max-width: 400px;
            margin: 0 auto;
            margin-top: 50px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .titre {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="formulaire-connexion">
            <h2 class="titre">Connexion</h2>
            
            <!-- Afficher les messages d'erreur -->
            <?php if (!empty($message_erreur)): ?>
                <div class="alert alert-danger"><?php echo $message_erreur; ?></div>
            <?php endif; ?>
            
            <!-- Formulaire de connexion -->
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Mot de passe:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
                </div>
                <p class="text-center">Pas encore de compte? <a href="signup.php">S'inscrire</a></p>
            </form>
        </div>
    </div>
</body>
</html>
