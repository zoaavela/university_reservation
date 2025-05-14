<?php
// Vérifier si l'utilisateur est connecté et est un étudiant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'etudiant') {
    header('Location: ../login.php');
    exit();
}
?>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="../images/logo.png" alt="Logo" class="sidebar-logo">
        <h3>Espace Étudiant</h3>
    </div>
    <ul class="sidebar-menu">
        <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
        <li><a href="reservations.php"><i class="fas fa-calendar-alt"></i> Mes Réservations</a></li>
        <li><a href="salles.php"><i class="fas fa-door-open"></i> Salles</a></li>
        <li><a href="materiel.php"><i class="fas fa-laptop"></i> Matériel</a></li>
        <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="profil.php"><i class="fas fa-user"></i> Mon Profil</a></li>
        <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
    </ul>
</div>
