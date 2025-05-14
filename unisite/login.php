<?php
// Inclure le fichier de configuration
require_once 'config.php';

// Variable pour stocker les messages d'erreur
$message_erreur = '';

// Si l'utilisateur est déjà connecté, le rediriger
if (est_connecte()) {
    // Rediriger selon le rôle
    if ($_SESSION["role"] === "etudiant") {
        header("location: etudiant/index.php");
    } else if ($_SESSION["role"] === "admin") {
        header("location: admin/index.php");
    } else if ($_SESSION["role"] === "agent") {
        header("location: agent/index.php");
    } else {
        header("location: index.php");
    }
    exit;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $email = nettoyer($_POST["email"]);
    $mot_de_passe = $_POST["password"]; // Pas besoin de nettoyer le mot de passe
    
    // Vérifier si les champs sont vides
    if (empty($email) || empty($mot_de_passe)) {
        $message_erreur = "Veuillez remplir tous les champs.";
    } else {
        // Vérifier si l'utilisateur existe
        $sql = "SELECT * FROM utilisateur WHERE email = '$email'";
        $resultat = mysqli_query($conn, $sql);
        
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
                $_SESSION["email"] = $utilisateur["email"];
                
                // Rediriger selon le rôle
                if ($_SESSION["role"] === "etudiant") {
                    header("location: etudiant/index.php");
                } else if ($_SESSION["role"] === "admin") {
                    header("location: admin/index.php");
                } else if ($_SESSION["role"] === "agent") {
                    header("location: agent/index.php");
                } else {
                    header("location: index.php");
                }
                exit;
            } else {
                $message_erreur = "Mot de passe incorrect.";
            }
        } else {
            $message_erreur = "Aucun compte trouvé avec cet email.";
        }
    }
}

// Définir le titre de la page
$page_title = "Connexion - Université Gustave Eiffel";
$page = "login";
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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Barre de navigation simple -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.png" alt="Université Gustave Eiffel">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="login.php">Connexion</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="signup.php">Inscription</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="auth-container">
            <div class="auth-logo">
                <img src="images/logo.png" alt="Université Gustave Eiffel">
            </div>
            <h2 class="text-center mb-4">Connexion</h2>
            
            <!-- Afficher les messages d'erreur -->
            <?php if (!empty($message_erreur)): ?>
                <div class="alert alert-danger"><?php echo $message_erreur; ?></div>
            <?php endif; ?>
            
            <!-- Formulaire de connexion -->
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Mot de passe:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-sign-in-alt"></i> Se connecter</button>
                </div>
                <p class="text-center">Pas encore de compte? <a href="signup.php">S'inscrire</a></p>
            </form>
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
