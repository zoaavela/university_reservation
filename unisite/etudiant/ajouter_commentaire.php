<?php
session_start();
require_once '../config.php';
require_once '../database.php';

// Vérifier si l'utilisateur est connecté et est un étudiant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'etudiant') {
    header('Location: ../login.php');
    exit();
}

// Vérifier si l'ID de réservation est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: reservations.php');
    exit();
}

$reservation_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Vérifier si la réservation appartient à l'étudiant
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? AND utilisateur_id = ?");
$stmt->execute([$reservation_id, $user_id]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    header('Location: reservations.php');
    exit();
}

$message = '';
$error = '';

// Traitement du formulaire d'ajout de commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['commentaire']) && !empty($_POST['commentaire'])) {
        $commentaire = trim($_POST['commentaire']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO commentaires (reservation_id, utilisateur_id, commentaire) VALUES (?, ?, ?)");
            $stmt->execute([$reservation_id, $user_id, $commentaire]);
            
            // Créer une notification pour l'agent
            $notification_message = "Nouveau commentaire sur la réservation #" . $reservation_id;
            $stmt = $pdo->prepare("INSERT INTO notifications (utilisateur_id, message) SELECT id, ? FROM utilisateurs WHERE role = 'agent'");
            $stmt->execute([$notification_message]);
            
            $message = "Votre commentaire a été ajouté avec succès.";
            
            // Rediriger vers la page de détails de la réservation
            header('Location: voir_reservation.php?id=' . $reservation_id);
            exit();
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout du commentaire: " . $e->getMessage();
        }
    } else {
        $error = "Veuillez saisir un commentaire.";
    }
}

// Récupérer les détails de la réservation
$stmt = $pdo->prepare("
    SELECT r.*, s.nom as salle_nom, m.nom as materiel_nom 
    FROM reservations r
    LEFT JOIN salles s ON r.salle_id = s.id
    LEFT JOIN materiel m ON r.materiel_id = m.id
    WHERE r.id = ?
");
$stmt->execute([$reservation_id]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

// Titre de la page
$page_title = "Ajouter un commentaire";
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
                    <h2><i class="fas fa-comment"></i> Ajouter un commentaire</h2>
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
                            <h3>Réservation #<?php echo $reservation_id; ?></h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Date de début:</strong> <?php echo date('d/m/Y H:i', strtotime($reservation['date_debut'])); ?></p>
                                    <p><strong>Date de fin:</strong> <?php echo date('d/m/Y H:i', strtotime($reservation['date_fin'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Salle:</strong> <?php echo $reservation['salle_nom'] ?: 'Aucune'; ?></p>
                                    <p><strong>Matériel:</strong> <?php echo $reservation['materiel_nom'] ?: 'Aucun'; ?></p>
                                </div>
                            </div>
                            
                            <form method="post" action="">
                                <div class="form-group">
                                    <label for="commentaire">Votre commentaire:</label>
                                    <textarea class="form-control" id="commentaire" name="commentaire" rows="5" required></textarea>
                                </div>
                                <div class="form-group">
                                    <a href="voir_reservation.php?id=<?php echo $reservation_id; ?>" class="btn btn-secondary">Annuler</a>
                                    <button type="submit" class="btn btn-primary">Ajouter le commentaire</button>
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
