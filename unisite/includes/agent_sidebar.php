<div class="sidebar">
    <div class="sidebar-title">
        Espace Agent
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/agent/index.php" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt mr-2"></i> Tableau de bord
            </a>
        </li>
        <li>
            <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/agent/utilisateurs.php" class="<?php echo $page == 'utilisateurs' ? 'active' : ''; ?>">
                <i class="fas fa-users mr-2"></i> Voir les utilisateurs
            </a>
        </li>
        <li>
            <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/agent/materiel.php" class="<?php echo $page == 'materiel' ? 'active' : ''; ?>">
                <i class="fas fa-laptop mr-2"></i> Voir le matériel
            </a>
        </li>
        <li>
            <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/agent/salles.php" class="<?php echo $page == 'salles' ? 'active' : ''; ?>">
                <i class="fas fa-door-open mr-2"></i> Voir les salles
            </a>
        </li>
        <li>
            <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/agent/reservations.php" class="<?php echo $page == 'reservations' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt mr-2"></i> Voir les réservations
            </a>
        </li>
        <li class="logout">
            <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/logout.php">
                <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
            </a>
        </li>
    </ul>
</div>
