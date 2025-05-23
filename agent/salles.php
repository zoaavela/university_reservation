<?php
session_start();
require_once '../config.php';
require_once '../database.php';

// Vérifier si l'utilisateur est connecté et est un agent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header('Location: ../login.php');
    exit();
}

// Supprimer une salle
if (isset($_GET['supprimer']) && !empty($_GET['supprimer'])) {
    $salle_id = $_GET['supprimer'];
    
    try {
        // Vérifier si la salle existe
        $stmt = $pdo->prepare("SELECT * FROM salles WHERE id = ?");
        $stmt->execute([$salle_id]);
        $salle = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($salle) {
            // Supprimer l'image si elle existe
            if (!empty($salle['image']) && file_exists($salle['image'])) {
                unlink($salle['image']);
            }
            
            // Supprimer la salle
            $stmt = $pdo->prepare("DELETE FROM salles WHERE id = ?");
            $stmt->execute([$salle_id]);
            
            $message = "La salle a été supprimée avec succès.";
        } else {
            $error = "La salle n'existe pas.";
        }
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression de la salle: " . $e->getMessage();
    }
}

// Récupérer toutes les salles
$stmt = $pdo->query("SELECT * FROM salles ORDER BY nom");
$salles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Titre de la page
$page_title = "Gestion des salles";
$base_url = "http://localhost/unisite/agent/";
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
                    <div class="d-flex justify-content-between align-items-center">
                        <h2><i class="fas fa-door-open"></i> Gestion des salles</h2>
                        <a href="ajouter_salle.php" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter une salle</a>
                    </div>
                </div>
                
                <div class="content">
                    <?php if (isset($message)): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3>Liste des salles</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($salles)): ?>
                                <p>Aucune salle n'a été ajoutée.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nom</th>
                                                <th>Capacité</th>
                                                <th>Image</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($salles as $salle): ?>
                                                <tr>
                                                    <td><?php echo $salle['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($salle['nom']); ?></td>
                                                    <td><?php echo $salle['capacite']; ?> personnes</td>
                                                    <td>
                                                        <?php if (!empty($salle['image'])): ?>
                                                            <img src="<?php echo $salle['image']; ?>" alt="<?php echo htmlspecialchars($salle['nom']); ?>" class="img-thumbnail" style="max-width: 100px;">
                                                        <?php else: ?>
                                                            <span class="text-muted">Aucune image</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="voir_salle.php?id=<?php echo $salle['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                                        <a href="modifier_salle.php?id=<?php echo $salle['id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                                        <a href="?supprimer=<?php echo $salle['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette salle?');"><i class="fas fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
