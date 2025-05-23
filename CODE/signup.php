<?php
// Inclure le fichier de configuration
require_once 'config.php';

// Variables pour stocker les messages
$message_erreur = '';
$message_succes = '';

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer et nettoyer les données du formulaire
    $nom = nettoyer_donnees($_POST["nom"]);
    $prenom = nettoyer_donnees($_POST["prenom"]);
    $email = nettoyer_donnees($_POST["email"]);
    $mot_de_passe = $_POST["password"];
    $confirmer_mot_de_passe = $_POST["confirm_password"];
    $role = nettoyer_donnees($_POST["role"]);
    
    // Vérifier si tous les champs obligatoires sont remplis
    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe) || empty($confirmer_mot_de_passe) || empty($role)) {
        $message_erreur = "Veuillez remplir tous les champs obligatoires.";
    } 
    // Vérifier si le rôle est valide
    else if ($role != "etudiant" && $role != "enseignant") {
        $message_erreur = "Rôle invalide. Seuls les étudiants et les enseignants peuvent s'inscrire.";
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
            // Hacher le mot de passe
            $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            
            // Récupérer les champs spécifiques selon le rôle
            if ($role == "etudiant") {
                $numero_etudiant = nettoyer_donnees($_POST["numero_etudiant"]);
                $filiere = nettoyer_donnees($_POST["filiere"]);
                $annee_etude = nettoyer_donnees($_POST["annee_etude"]);
                
                // Insérer l'étudiant dans la base de données
                $sql = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role, numero_etudiant, filiere, annee_etude) 
                        VALUES ('$nom', '$prenom', '$email', '$mot_de_passe_hache', '$role', '$numero_etudiant', '$filiere', '$annee_etude')";
            } else {
                // C'est un enseignant
                $departement = nettoyer_donnees($_POST["departement"]);
                $matiere = nettoyer_donnees($_POST["matiere"]);
                $bureau = nettoyer_donnees($_POST["bureau"]);
                
                // Insérer l'enseignant dans la base de données
                $sql = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role, departement, matiere, bureau) 
                        VALUES ('$nom', '$prenom', '$email', '$mot_de_passe_hache', '$role', '$departement', '$matiere', '$bureau')";
            }
            
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
    <title>Inscription - Système de Réservation</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Inclure Bootstrap pour un style simple mais efficace -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <!-- Font Awesome pour les icônes -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .formulaire-inscription {
            max-width: 500px;
            margin: 0 auto;
            margin-top: 30px;
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .titre {
            text-align: center;
            margin-bottom: 20px;
        }
        /* Cacher les champs spécifiques par défaut */
        #champs_etudiant, #champs_enseignant {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="formulaire-inscription">
            <h2 class="titre">Inscription</h2>
            
            <!-- Afficher les messages d'erreur ou de succès -->
            <?php if (!empty($message_erreur)): ?>
                <div class="alert alert-danger"><?php echo $message_erreur; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($message_succes)): ?>
                <div class="alert alert-success"><?php echo $message_succes; ?></div>
            <?php endif; ?>
            
            <!-- Formulaire d'inscription -->
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <!-- Champs communs à tous les utilisateurs -->
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
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Rôle:</label>
                    <select name="role" id="role" class="form-control" required onchange="afficherChamps()">
                        <option value="">Sélectionnez votre rôle</option>
                        <option value="etudiant">Étudiant</option>
                        <option value="enseignant">Enseignant</option>
                    </select>
                </div>
                
                <!-- Champs spécifiques aux étudiants -->
                <div id="champs_etudiant">
                    <h4 class="mt-4 mb-3">Informations étudiant</h4>
                    <div class="form-group">
                        <label>Numéro étudiant:</label>
                        <input type="text" name="numero_etudiant" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Filière:</label>
                        <select name="filiere" class="form-control">
                            <option value=""> Sélectionnez votre filière</option>
                            <option value="Informatique">Informatique</option>
                            <option value="Mathématiques">Mathématiques</option>
                            <option value="Physique">Physique</option>
                            <option value="Chimie">Chimie</option>
                            <option value="Biologie">Biologie</option>
                            <option value="Droit">Droit</option>
                            <option value="Économie">Économie</option>
                            <option value="Lettres">Lettres</option>
                            <option value="Histoire">Histoire</option>
                            <option value="Géographie">Géographie</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label> Année d'étude:</label>
                        <select name="annee_etude" class="form-control">
                            <option value="">Sélectionnez votre année</option>
                            <option value="L1">Licence 1</option>
                            <option value="L2">Licence 2</option>
                            <option value="L3">Licence 3</option>
                            <option value="M1">Master 1</option>
                            <option value="M2">Master 2</option>
                            <option value="Doctorat">Doctorat</option>
                        </select>
                    </div>
                </div>
                
                <!-- Champs spécifiques aux enseignants -->
                <div id="champs_enseignant">
                    <h4 class="mt-4 mb-3">Informations enseignant</h4>
                    <div class="form-group">
                        <label>Département:</label>
                        <select name="departement" class="form-control">
                            <option value="">Sélectionnez votre département</option>
                            <option value="Informatique">Informatique</option>
                            <option value="Mathématiques">Mathématiques</option>
                            <option value="Physique">Physique</option>
                            <option value="Chimie">Chimie</option>
                            <option value="Biologie">Biologie</option>
                            <option value="Droit">Droit</option>
                            <option value="Économie">Économie</option>
                            <option value="Lettres">Lettres</option>
                            <option value="Histoire">Histoire</option>
                            <option value="Géographie">Géographie</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Matière enseignée:</label>
                        <input type="text" name="matiere" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Bureau:</label>
                        <input type="text" name="bureau" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
                </div>
                <p class="text-center">Déjà un compte? <a href="login.php">Se connecter</a></p>
            </form>
        </div>
    </div>
    
    <!-- Script JavaScript pour afficher/masquer les champs selon le rôle -->
    <script>
        function afficherChamps() {
            var role = document.getElementById("role").value;
            var champsEtudiant = document.getElementById("champs_etudiant");
            var champsEnseignant = document.getElementById("champs_enseignant");
            
            // Cacher tous les champs spécifiques
            champsEtudiant.style.display = "none";
            champsEnseignant.style.display = "none";
            
            // Afficher les champs selon le rôle sélectionné
            if (role === "etudiant") {
                champsEtudiant.style.display = "block";
            } else if (role === "enseignant") {
                champsEnseignant.style.display = "block";
            }
        }
    </script>
    
    <!-- Scripts JavaScript de Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
