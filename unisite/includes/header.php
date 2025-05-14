<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Système de Réservation - Université Gustave Eiffel'; ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Styles personnalisés -->
    <link rel="stylesheet" href="<?php echo isset($base_url) ? $base_url : ''; ?>/css/style.css">
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="<?php echo isset($base_url) ? $base_url : ''; ?>/index.php">
                <img src="<?php echo isset($base_url) ? $base_url : ''; ?>/images/logo.png" alt="Université Gustave Eiffel">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (isset($_SESSION["connecte"]) && $_SESSION["connecte"] === true): ?>
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'accueil' ? 'active' : ''; ?>" href="<?php echo isset($base_url) ? $base_url : ''; ?>/index.php">Accueil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'salles' ? 'active' : ''; ?>" href="<?php echo isset($base_url) ? $base_url : ''; ?>/<?php echo $_SESSION["role"]; ?>/salles.php">Réserver une salle</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'materiel' ? 'active' : ''; ?>" href="<?php echo isset($base_url) ? $base_url : ''; ?>/<?php echo $_SESSION["role"]; ?>/materiel.php">Réserver un matériel</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo isset($base_url) ? $base_url : ''; ?>/<?php echo $_SESSION["role"]; ?>/profil.php">
                                <i class="fas fa-user"></i> <?php echo $_SESSION["prenom"]; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo isset($base_url) ? $base_url : ''; ?>/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                <?php else: ?>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'login' ? 'active' : ''; ?>" href="<?php echo isset($base_url) ? $base_url : ''; ?>/login.php">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'signup' ? 'active' : ''; ?>" href="<?php echo isset($base_url) ? $base_url : ''; ?>/signup.php">Inscription</a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>
