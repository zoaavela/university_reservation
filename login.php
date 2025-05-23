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
    } else if ($_SESSION["role"] === "enseignant") {
        header("location: enseignant/index.php");
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
    <link rel="stylesheet" href="css/pageadmin.css">
</head>
<body class="d-flex flex-column min-vh-100">
   <!-- Barre de navigation -->
   <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="index.php"><img src="images/logopng.png" alt=""></a>
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
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col col-md-5">
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
    </div>
    
   <!-- Footer -->
   <footer class="footer text-dark text-center py-3 mt-auto">
    <p>@ <?php echo date("Y"); ?> Système de Réservation MMI</p>
</footer>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
