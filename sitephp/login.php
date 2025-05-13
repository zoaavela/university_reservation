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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Connexion - Système de Réservation MMI</title>
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
        .login-form {
            max-width: 400px;
            margin: 50px auto;
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
        <div class="login-form">
            <div class="logo">
                <i class="fas fa-university"></i>
            </div>
            <h2 class="text-center mb-4">Connexion</h2>
            <h5 class="text-center mb-4">Système de Réservation MMI</h5>
            
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
        <div class="text-center mt-3">
            <p class="text-muted">© <?php echo date("Y"); ?> Système de Réservation MMI</p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
