<?php
session_start();
require_once '../config.php';
require_once '../database.php';

// Vérifier si l'utilisateur est connecté et est un agent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
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

// Récupérer les détails de la réservation
$stmt = $pdo->prepare("
    SELECT r.*, s.nom as salle_nom, s.image as salle_image, s.description as salle_description,
           m.nom as materiel_nom, m.description as materiel_description,
           u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, u.email as utilisateur_email
    FROM reservations r
    LEFT JOIN salles s ON r.salle_id = s.id
    LEFT JOIN materiel m ON r.materiel_id = m.id
    LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$reservation_id]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    header('Location: reservations.php');
    exit();
}

$message = '';
$error = '';

// Traitement des actions sur la réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'approuver') {
            try {
                $stmt = $pdo->prepare("UPDATE reservations SET statut = 'approuvee' WHERE id = ?");
                $stmt->execute([$reservation_id]);
                
                // Créer une notification pour l'utilisateur
                $notification_message = "Votre réservation #" . $reservation_id . " a été approuvée.";
                $stmt = $pdo->prepare("INSERT INTO notifications (utilisateur_id, message) VALUES (?, ?)");
                $stmt->execute([$reservation['utilisateur_id'], $notification_message]);
                
                $message = "La réservation a été approuvée avec succès.";
                
                // Mettre à jour les informations de la réservation
                $stmt = $pdo->prepare("
                    SELECT r.*, s.nom as salle_nom, s.image as salle_image, s.description as salle_description,
                           m.nom as materiel_nom, m.description as materiel_description,
                           u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, u.email as utilisateur_email
                    FROM reservations r
                    LEFT JOIN salles s ON r.salle_id = s.id
                    LEFT JOIN materiel m ON r.materiel_id = m.id
                    LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id
                    WHERE r.id = ?
                ");
                $stmt->execute([$reservation_id]);
                $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $error = "Erreur lors de l'approbation de la réservation: " . $e->getMessage();
            }
        } elseif ($action === 'rejeter') {
            $raison_rejet = isset($_POST['raison_rejet']) ? trim($_POST['raison_rejet']) : '';
            
            try {
                $stmt = $pdo->prepare("UPDATE reservations SET statut = 'rejetee', raison_rejet = ? WHERE id = ?");
                $stmt->execute([$raison_rejet, $reservation_id]);
                
                // Créer une notification pour l'utilisateur
                $notification_message = "Votre réservation #" . $reservation_id . " a été rejetée.";
                $stmt = $pdo->prepare("INSERT INTO notifications (utilisateur_id, message) VALUES (?, ?)");
                $stmt->execute([$reservation['utilisateur_id'], $notification_message]);
                
                $message = "La réservation a été rejetée avec succès.";
                
                // Mettre à jour les informations de la réservation
                $stmt = $pdo->prepare("
                    SELECT r.*, s.nom as salle_nom, s.image as salle_image, s.description as salle_description,
                           m.nom as materiel_nom, m.description as materiel_description,
                           u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, u.email as utilisateur_email
                    FROM reservations r
                    LEFT JOIN salles s ON r.salle_id = s.id
                    LEFT JOIN materiel m ON r.materiel_id = m.id
                    LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id
                    WHERE r.id = ?
                ");
                $stmt->execute([$reservation_id]);
                $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $error = "Erreur lors du rejet de la réservation: " . $e->getMessage();
            }
        } elseif ($action === 'ajouter_commentaire') {
            $commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : '';
            
            if (!empty($commentaire)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO commentaires (reservation_id, utilisateur_id, commentaire) VALUES (?, ?, ?)");
                    $stmt->execute([$reservation_id, $user_id, $commentaire]);
                    
                    // Créer une notification pour l'utilisateur
                    $notification_message = "Nouveau commentaire sur votre réservation #" . $reservation_id;
                    $stmt = $pdo->prepare("INSERT INTO notifications (utilisateur_id, message) VALUES (?, ?)");
                    $stmt->execute([$reservation['utilisateur_id'], $notification_message]);
                    
                    $message = "Votre commentaire a été ajouté avec succès.";
                } catch (PDOException $e) {
                    $error = "Erreur lors de l'ajout du commentaire: " . $e->getMessage();
                }
            } else {
                $error = "Veuillez saisir un commentaire.";
            }
        }
    }
}

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

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <?php include '../includes/agent_sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <div class="content-wrapper">
                <div class="content-header">
                    <h2><i class="fas fa-calendar-alt"></i> Détails de la réservation</h2>
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
                                    <h4>Informations de la réservation</h4>
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
                                    
                                    <h4 class="mt-4">Informations de l'utilisateur</h4>
                                    <p><strong>Nom:</strong> <?php echo $reservation['utilisateur_prenom'] . ' ' . $reservation['utilisateur_nom']; ?></p>
                                    <p><strong>Email:</strong> <?php echo $reservation['utilisateur_email']; ?></p>
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
                                        <h4 class="mt-4">Matériel réservé</h4>
                                        <p><strong>Nom:</strong> <?php echo $reservation['materiel_nom']; ?></p>
                                        <?php if (!empty($reservation['materiel_description'])): ?>
                                            <p><strong>Description:</strong> <?php echo $reservation['materiel_description']; ?></p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if ($reservation['statut'] === 'en_attente'): ?>
                                <div class="mt-4">
                                    <h4>Actions</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <form method="post" action="">
                                                <input type="hidden" name="action" value="approuver">
                                                <button type="submit" class="btn btn-success btn
