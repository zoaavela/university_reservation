<?php
// Inclure le fichier de configuration
require_once '../config.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    header("location: ../login.php");
    exit;
}

// Vérifier si l'utilisateur est un étudiant
if (!est_role("etudiant")) {
    header("location: ../login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$id = $_SESSION["id"];
$nom = $_SESSION["nom"];
$prenom = $_SESSION["prenom"];

// Récupérer les notifications de l'utilisateur
$sql = "SELECT * FROM notification WHERE utilisateur_id = $id ORDER BY date_creation DESC";
$notifications = mysqli_query($conn, $sql);

// Marquer toutes les notifications comme lues
$sql = "UPDATE notification SET lue = 1 WHERE utilisateur_id = $id AND lue = 0";
mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Notifications - Système de Réservation MMI</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .page-header {
            background-color: #dc3545;
            color: white;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .notification-item {
            border-left: 4px solid #007bff;
            margin-bottom: 10px;
            transition: transform 0.2s;
        }
        .notification-item:hover {
            transform: translateX(5px);
        }
        .notification-item.unread {
            border-left-color: #dc3545;
            background-color: rgba(220, 53, 69, 0.05);
        }
    </style>
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">MMI Réservation</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="materiel.php">Matériel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="salles.php">Salles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reservations.php">Mes Réservations</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="notifications.php">Notifications</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="profil.php">Mon Profil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">Déconnexion</a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="page-header">
            <h2><i class="fas fa-bell"></i> Mes Notifications</h2>
            <p>Consultez toutes vos notifications concernant vos réservations et autres informations importantes.</p>
        </div>
        
        <!-- Liste des notifications -->
        <div class="card">
            <div class="card-body">
                <?php if (mysqli_num_rows($notifications) > 0): ?>
                    <?php while ($notification = mysqli_fetch_assoc($notifications)): ?>
                        <div class="notification-item p-3 <?php echo $notification['lue'] == 0 ? 'unread' : ''; ?>">
                            <h5><?php echo $notification['titre']; ?></h5>
                            <p><?php echo $notification['message']; ?></p>
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> <?php echo date("d/m/Y H:i", strtotime($notification['date_creation'])); ?>
                            </small>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        Vous n'avez pas de notifications.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p>© <?php echo date("Y"); ?> Système de Réservation MMI</p>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
