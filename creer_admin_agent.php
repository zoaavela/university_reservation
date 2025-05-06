<?php
// Ce script permet de créer des comptes administrateur et agent directement dans la base de données
// Puisqu'on ne peut pas s'inscrire avec ces rôles via le formulaire public

// Inclure le fichier de configuration
require_once 'config.php';

// Message pour afficher les résultats
$message = '';

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer et nettoyer les données du formulaire
    $nom = nettoyer_donnees($_POST["nom"]);
    $prenom = nettoyer_donnees($_POST["prenom"]);
    $email = nettoyer_donnees($_POST["email"]);
    $mot_de_passe = $_POST["password"];
    $role = nettoyer_donnees($_POST["role"]);
    
    // Vérifier si tous les champs sont remplis
    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe) || empty($role)) {
        $message = "<div class='alert alert-danger'>Veuillez remplir tous les champs.</div>";
    } 
    // Vérifier si le rôle est valide (admin ou agent uniquement)
    else if ($role != "admin" && $role != "agent") {
        $message = "<div class='alert alert-danger'>Rôle invalide. Seuls les administrateurs et les agents peuvent être créés ici.</div>";
    }
    else {
        // Vérifier si l'email existe déjà
        $sql = "SELECT id FROM utilisateur WHERE email = '$email'";
        $resultat = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($resultat) > 0) {
            $message = "<div class='alert alert-danger'>Cet email est déjà utilisé.</div>";
        } else {
            // Hacher le mot de passe
            $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            
            // Insérer l'utilisateur dans la base de données
            $sql = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) 
                    VALUES ('$nom', '$prenom', '$email', '$mot_de_passe_hache', '$role')";
            
            if (mysqli_query($conn, $sql)) {
                $message = "<div class='alert alert-success'>Compte $role créé avec succès!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Erreur: " . mysqli_error($conn) . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Créer Admin/Agent - Système de Réservation</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Inclure Bootstrap pour un style simple mais efficace -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .formulaire-creation {
            max-width: 500px;
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
        .alerte {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="formulaire-creation">
            <h2 class="titre">Créer un compte Admin/Agent</h2>
            <div class="alerte">
                <strong>Attention!</strong> Cette page est réservée à la création de comptes administrateur et agent.
            </div>
            
            <!-- Afficher les messages -->
            <?php echo $message; ?>
            
            <!-- Formulaire de création -->
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Nom:</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Prénom:</label>
                    <input type="text" name="prenom" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Mot de passe:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Rôle:</label>
                    <select name="role" class="form-control" required>
                        <option value="">Sélectionnez un rôle</option>
                        <option value="admin">Administrateur</option>
                        <option value="agent">Agent</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Créer le compte</button>
                </div>
            </form>
            
            <p class="text-center mt-3">
                <a href="login.php">Retour à la page de connexion</a>
            </p>
        </div>
    </div>
</body>
</html>
