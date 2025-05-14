<?php
session_start();
require_once '../config.php';
require_once '../database.php';

// Vérifier si l'utilisateur est connecté et est un agent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header('Location: ../login.php');
    exit();
}

$message = '';
$error = '';

// Traitement du formulaire d'ajout de salle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $nom = trim($_POST['nom']);
    $capacite = (int)$_POST['capacite'];
    $description = trim($_POST['description']);
    
    // Validation des données
    if (empty($nom)) {
        $error = "Le nom de la salle est obligatoire.";
    } else {
        // Traitement de l'image
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/salles/';
            
            // Créer le répertoire s'il n'existe pas
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $upload_dir . $file_name;
            $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Vérifier si le fichier est une image
            $valid_extensions = array("jpg", "jpeg", "png", "gif");
            if (in_array($image_file_type, $valid_extensions)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = $target_file;
                } else {
                    $error = "Erreur lors du téléchargement de l'image.";
                }
            } else {
                $error = "Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
            }
        }
        
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO salles (nom, capacite, description, image) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nom, $capacite, $description, $image_path]);
                
                $message = "La salle a été ajoutée avec succès.";
                
                // Rediriger vers la liste des salles
                header('Location: salles.php');
                exit();
            } catch (PDOException $e) {
                $error = "Erreur lors de l'ajout de la salle: " . $e->getMessage();
            }
        }
    }
}

// Titre de la page
$page_title = "Ajouter une salle";
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <?php include '../includes/agent_sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <div class="content-wrapper">
                <div class="content-header">
                    <h2><i class="fas fa-door-open"></i> Ajouter une salle</h2>
                </div>
                
                <div class="content">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3>Nouvelle salle</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="nom">Nom de la salle *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="capacite">Capacité</label>
                                    <input type="number" class="form-control" id="capacite" name="capacite" min="0" value="0">
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="image">Image de la salle</label>
                                    <input type="file" class="form-control-file" id="image" name="image">
                                    <small class="form-text text-muted">Formats acceptés: JPG, JPEG, PNG, GIF</small>
                                </div>
                                
                                <div class="form-group">
                                    <a href="salles.php" class="btn btn-secondary">Annuler</a>
                                    <button type="submit" class="btn btn-primary">Ajouter la salle</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
