<?php
session_start();
require_once '../config.php';
require_once '../database.php';

// Vérifier si l'utilisateur est connecté et est un étudiant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'etudiant') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Marquer une notification comme lue
if (isset($_GET['marquer_lu']) && !empty($_GET['marquer_lu'])) {
    $notification_id = $_GET['marquer_lu'];
    
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET lu = TRUE WHERE id = ? AND utilisateur_id = ?");
        $stmt->execute([$notification_id, $user_id]);
        
        header('Location: notifications.php');
        exit();
    } catch (PDOException $e) {
        $error = "Erreur lors de la mise à jour de la notification: " . $e->getMessage();
    }
}

// Marquer toutes les notifications comme lues
if (isset($_GET['marquer_tout_lu'])) {
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET lu = TRUE WHERE utilisateur_id = ?");
        $stmt->execute([$user_id]);
        
        header('Location: notifications.php');
        exit();
    } catch (PDOException $e) {
        $error = "Erreur lors de la mise à jour des notifications: " . $e->getMessage();
    }
}

// Récupérer les notifications de l'utilisateur
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE utilisateur_id = ? 
    ORDER BY date_creation DESC
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compter les notifications non lues
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE utilisateur_id = ? AND lu = FALSE");
$stmt->execute([$user_id]);
$count_non_lues = $stmt->fetchColumn();

// Titre de la page
$page_title = "Notifications";
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <?php include '../includes/etudiant_sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <div class="content-wrapper">
                <div class="content-header">
                    <h2><i class="fas fa-bell"></i> Notifications</h2>
                </div>
                
                <div class="content">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3>Vos notifications <span class="badge badge-primary"><?php echo count($notifications); ?></span></h3>
                                <?php if ($count_non_lues > 0): ?>
                                    <a href="?marquer_tout_lu=1" class="btn btn-sm btn-outline-primary">Marquer tout comme lu</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($notifications)): ?>
                                <p>Vous n'avez aucune notification.</p>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($notifications as $notification): ?>
                                        <div class="list-group-item list-group-item-action <?php echo $notification['lu'] ? '' : 'list-group-item-primary'; ?>">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h5 class="mb-1">
                                                    <?php if (!$notification['lu']): ?>
                                                        <span class="badge badge-primary">Nouveau</span>
                                                    <?php endif; ?>
                                                    <?php echo $notification['message']; ?>
                                                </h5>
                                                <small><?php echo date('d/m/Y H:i', strtotime($notification['date_creation'])); ?></small>
                                            </div>
                                            <?php if (!$notification['lu']): ?>
                                                <a href="?marquer_lu=<?php echo $notification['id']; ?>" class="btn btn-sm btn-outline-primary mt-2">Marquer comme lu</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
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
