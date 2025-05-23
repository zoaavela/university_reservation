<?php
session_start();
require_once '../config.php';
require_once '../database.php';

// Vérifier si l'utilisateur est connecté et est un agent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header('Location: ../login.php');
    exit();
}

// Vérifier si l'ID de la salle est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: salles.php');
    exit();
}

$salle_id = $_GET['id'];

// Récupérer les informations de la salle
$stmt = $pdo->prepare("SELECT * FROM salles WHERE id = ?");
$stmt->execute([$salle_id]);
$salle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$salle) {
    header('Location: salles.php');
    exit();
}

$message = '';
$error = '';

// Traitement du formulaire de modification de salle
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
        $image_path = $salle['image']; // Conserver l'image existante par défaut
        
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
                    // Supprimer l'ancienne image si elle existe
                    if (!empty($salle['image']) && file_exists($salle['image'])) {
                        unlink($salle['image']);
                    }
                    $image_path = $target_file;
                } else {
                    $error = "Erreur lors du téléchargement de l'image.";
                }
            } else {
                $error = "Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
            }
        }
        
        // Supprimer l'image si demandé
        if (isset($_POST['supprimer_image']) && $_POST['supprimer_image'] === '1') {
            if (!empty($salle['image']) && file_exists($salle['image'])) {
                unlink($salle['image']);
            }
            $image_path = null;
        }
        
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("UPDATE salles SET nom = ?, capacite = ?, description = ?, image = ? WHERE id = ?");
                $stmt->execute([$nom, $capacite, $description, $image_path, $salle_id]);
                
                $message = "La salle a été modifiée avec succès.";
                
                // Mettre à jour les informations de la salle
                $stmt = $pdo->prepare("SELECT * FROM salles WHERE id = ?");
                $stmt->execute([$salle_id]);
                $salle = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $error = "Erreur lors de la modification de la salle: " . $e->getMessage();
            }
        }
    }
}

// Titre de la page
$page_title = "Modifier une salle";
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
                    <h2><i class="fas fa-door-open"></i> Modifier une salle</h2>
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
                            <h3>Modifier la salle: <?php echo htmlspecialchars($salle['nom']); ?></h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="nom">Nom de la salle *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($salle['nom']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="capacite">Capacité</label>
                                    <input type="number" class="form-control" id="capacite" name="capacite" min="0" value="<?php echo $salle['capacite']; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($salle['description']); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>Image actuelle</label>
                                    <?php if (!empty($salle['image'])): ?>
                                        <div>
                                            <img src="<?php echo $salle['image']; ?>" alt="<?php echo htmlspecialchars($salle['nom']); ?>" class="img-thumbnail" style="max-width: 200px;">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="supprimer_image" name="supprimer_image" value="1">
                                                <label class="form-check-label" for="supprimer_image">
                                                    Supprimer cette image
                                                </label>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p>Aucune image</p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="image">Nouvelle image</label>
                                    <input type="file" class="form-control-file" id="image" name="image">
                                    <small class="form-text text-muted">Formats acceptés: JPG, JPEG, PNG, GIF</small>
                                </div>
                                
                                <div class="form-group">
                                    <a href="salles.php" class="btn btn-secondary">Annuler</a>
                                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
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
