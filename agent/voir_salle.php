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

// Récupérer les réservations pour cette salle
$stmt = $pdo->prepare("
    SELECT r.*, u.nom as utilisateur_nom, u.prenom as utilisateur_prenom
    FROM reservations r
    JOIN utilisateurs u ON r.utilisateur_id = u.id
    WHERE r.salle_id = ?
    ORDER BY r.date_debut DESC
");
$stmt->execute([$salle_id]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Titre de la page
$page_title = "Détails de la salle";
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
                    <h2><i class="fas fa-door-open"></i> Détails de la salle</h2>
                </div>
                
                <div class="content">
                    <div class="card">
                        <div class="card-header">
                            <h3><?php echo htmlspecialchars($salle['nom']); ?></h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>ID:</strong> <?php echo $salle['id']; ?></p>
                                    <p><strong>Capacité:</strong> <?php echo $salle['capacite']; ?> personnes</p>
                                    <?php if (!empty($salle['description'])): ?>
                                        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($salle['description'])); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <?php if (!empty($salle['image'])): ?>
                                        <div class="text-center">
                                            <img src="<?php echo $salle['image']; ?>" alt="<?php echo htmlspecialchars($salle['nom']); ?>" class="img-fluid img-thumbnail" style="max-height: 300px;">
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">Aucune image disponible pour cette salle.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h4>Actions</h4>
                                <a href="salles.php" class="btn btn-secondary">Retour à la liste</a>
                                <a href="modifier_salle.php?id=<?php echo $salle_id; ?>" class="btn btn-primary">Modifier</a>
                                <a href="salles.php?supprimer=<?php echo $salle_id; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette salle?');">Supprimer</a>
                            </div>
                            
                            <div class="mt-4">
                                <h4>Réservations de cette salle</h4>
                                <?php if (empty($reservations)): ?>
                                    <p>Aucune réservation pour cette salle.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Utilisateur</th>
                                                    <th>Date de début</th>
                                                    <th>Date de fin</th>
                                                    <th>Statut</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($reservations as $reservation): ?>
                                                    <tr>
                                                        <td><?php echo $reservation['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($reservation['utilisateur_prenom'] . ' ' . $reservation['utilisateur_nom']); ?></td>
                                                        <td><?php echo date('d/m/Y H:i', strtotime($reservation['date_debut'])); ?></td>
                                                        <td><?php echo date('d/m/Y H:i', strtotime($reservation['date_fin'])); ?></td>
                                                        <td>
                                                            <?php 
                                                                if ($reservation['statut'] === 'approuvee') echo '<span class="badge badge-success">Approuvée</span>';
                                                                elseif ($reservation['statut'] === 'rejetee') echo '<span class="badge badge-danger">Rejetée</span>';
                                                                else echo '<span class="badge badge-warning">En attente</span>';
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <a href="voir_reservation.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
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
</div>

<?php include '../includes/footer.php'; ?>
