<?php
session_start();
require_once '../config.php';
require_once '../database.php';

// Vérifier si l'utilisateur est connecté et est un étudiant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') {
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

// Récupérer les détails de la réservation
$stmt = $pdo->prepare("
    SELECT r.*, s.nom as salle_nom, s.image as salle_image, s.description as salle_description,
           m.nom as materiel_nom, m.description as materiel_description,
           u.nom as utilisateur_nom, u.prenom as utilisateur_prenom
    FROM reservations r
    LEFT JOIN salles s ON r.salle_id = s.id
    LEFT JOIN materiel m ON r.materiel_id = m.id
    LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$reservation_id]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les commentaires de la réservation
$stmt = $pdo->prepare("
    SELECT c.*, u.nom, u.prenom, u.role
    FROM commentaires c
    JOIN utilisateurs u ON c.utilisateur_id = u.id
    WHERE c.reservation_id = ?
    ORDER BY c.date_creation DESC
");
$stmt->execute([$reservation_id]);
$commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Titre de la page
$page_title = "Détails de la réservation";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-9">
            <div class="content-wrapper">
                <div class="content-header">
                    <h2><i class="fas fa-calendar-alt"></i> Détails de la réservation</h2>
                </div>
                
                <div class="content">
                    <div class="card">
                        <div class="card-header">
                            <h3>Réservation #<?php echo $reservation_id; ?></h3>
                            <span class="badge <?php 
                                if ($reservation['statut'] === 'approuvee') echo 'badge-success';
                                elseif ($reservation['statut'] === 'rejetee') echo 'badge-danger';
                                else echo 'badge-warning';
                            ?>">
                                <?php 
                                    if ($reservation['statut'] === 'approuvee') echo 'Approuvée';
                                    elseif ($reservation['statut'] === 'rejetee') echo 'Rejetée';
                                    else echo 'En attente';
                                ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Date de début:</strong> <?php echo date('d/m/Y H:i', strtotime($reservation['date_debut'])); ?></p>
                                    <p><strong>Date de fin:</strong> <?php echo date('d/m/Y H:i', strtotime($reservation['date_fin'])); ?></p>
                                    <p><strong>Motif:</strong> <?php echo $reservation['motif']; ?></p>
                                    <p><strong>Statut:</strong> 
                                        <?php 
                                            if ($reservation['statut'] === 'approuvee') echo '<span class="text-success">Approuvée</span>';
                                            elseif ($reservation['statut'] === 'rejetee') echo '<span class="text-danger">Rejetée</span>';
                                            else echo '<span class="text-warning">En attente</span>';
                                        ?>
                                    </p>
                                    <?php if ($reservation['statut'] === 'rejetee' && !empty($reservation['raison_rejet'])): ?>
                                        <p><strong>Raison du rejet:</strong> <?php echo $reservation['raison_rejet']; ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <?php if (!empty($reservation['salle_id'])): ?>
                                        <h4>Salle réservée</h4>
                                        <p><strong>Nom:</strong> <?php echo $reservation['salle_nom']; ?></p>
                                        <?php if (!empty($reservation['salle_image'])): ?>
                                            <img src="<?php echo $reservation['salle_image']; ?>" alt="<?php echo $reservation['salle_nom']; ?>" class="img-thumbnail" style="max-width: 200px;">
                                        <?php endif; ?>
                                        <?php if (!empty($reservation['salle_description'])): ?>
                                            <p><strong>Description:</strong> <?php echo $reservation['salle_description']; ?></p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($reservation['materiel_id'])): ?>
                                        <h4>Matériel réservé</h4>
                                        <p><strong>Nom:</strong> <?php echo $reservation['materiel_nom']; ?></p>
                                        <?php if (!empty($reservation['materiel_description'])): ?>
                                            <p><strong>Description:</strong> <?php echo $reservation['materiel_description']; ?></p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h4>Actions</h4>
                                <a href="reservations.php" class="btn btn-secondary">Retour à la liste</a>
                                <?php if ($reservation['statut'] === 'en_attente'): ?>
                                    <a href="modifier_reservation.php?id=<?php echo $reservation_id; ?>" class="btn btn-primary">Modifier</a>
                                    <a href="annuler_reservation.php?id=<?php echo $reservation_id; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation?');">Annuler</a>
                                <?php endif; ?>
                                <a href="ajouter_commentaire.php?id=<?php echo $reservation_id; ?>" class="btn btn-info">Ajouter un commentaire</a>
                            </div>
                            
                            <!-- Section des commentaires -->
                            <div class="mt-4">
                                <h4>Commentaires</h4>
                                <?php if (empty($commentaires)): ?>
                                    <p>Aucun commentaire pour cette réservation.</p>
                                <?php else: ?>
                                    <div class="commentaires">
                                        <?php foreach ($commentaires as $commentaire): ?>
                                            <div class="card mb-2">
                                                <div class="card-header">
                                                    <strong><?php echo $commentaire['prenom'] . ' ' . $commentaire['nom']; ?></strong> 
                                                    <span class="badge badge-info"><?php echo ucfirst($commentaire['role']); ?></span>
                                                    <small class="text-muted float-right"><?php echo date('d/m/Y H:i', strtotime($commentaire['date_creation'])); ?></small>
                                                </div>
                                                <div class="card-body">
                                                    <p><?php echo nl2br(htmlspecialchars($commentaire['commentaire'])); ?></p>
                                                </div>
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
</div>

