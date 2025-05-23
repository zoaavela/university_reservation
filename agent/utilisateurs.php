<?php
// Inclure le fichier de configuration
require_once '../config.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    header("location: ../login.php");
    exit;
}

// Vérifier si l'utilisateur est un agent
if (!a_role("agent")) {
    header("location: ../login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$id = $_SESSION["id"];
$nom = $_SESSION["nom"];
$prenom = $_SESSION["prenom"];

// Récupérer la liste des utilisateurs
$sql = "SELECT id, nom, prenom, email, role, date_creation FROM utilisateur ORDER BY role, nom, prenom";
$resultat = mysqli_query($conn, $sql);

// Filtrer par rôle si demandé
$role_filter = "";
if (isset($_GET['role']) && !empty($_GET['role'])) {
    $role_filter = nettoyer_donnees($_GET['role']);
    $sql = "SELECT id, nom, prenom, email, role, date_creation FROM utilisateur WHERE role = ? ORDER BY nom, prenom";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $role_filter);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
}

$base_url = "http://localhost/unisite/agent/";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Consultation des Utilisateurs - Espace Agent</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Inclure Bootstrap pour un style simple mais efficace -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/pageadmin.css">
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="index.php"><img src="../images/logopng.png" alt=""></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>index.php" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt mr-2"></i> Tableau de bord
                </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>utilisateurs.php" class="<?php echo $page == 'utilisateurs' ? 'active' : ''; ?>">
                    <i class="fas fa-users mr-2"></i> Voir les utilisateurs
                </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>materiel.php" class="<?php echo $page == 'materiel' ? 'active' : ''; ?>">
                    <i class="fas fa-laptop mr-2"></i> Voir le matériel
                </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>salles.php" class="<?php echo $page == 'salles' ? 'active' : ''; ?>">
                    <i class="fas fa-door-open mr-2"></i> Voir les salles
                </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo isset($base_url) ? $base_url : ''; ?>reservations.php" class="<?php echo $page == 'reservations' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt mr-2"></i> Voir les réservations
                </a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                 <li class="logout">
                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>../logout.php">
                    <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                </a>
            </li>
            </ul>
        </div>
    </nav>
            
            <!-- Contenu principal -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col col-md-12">
                <div class="page-header mt-5">
                    <h2><i class="fas fa-users"></i> Consultation des Utilisateurs</h2>
                    <p>Consultez la liste des utilisateurs du système.</p>
                </div>
                
                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Filtrer par rôle</h5>
                        <div class="d-flex flex-wrap">
                            <a href="utilisateurs.php" class="btn <?php echo empty($role_filter) ? 'btn-secondary' : 'btn-outline-secondary'; ?> m-1">Tous</a>
                            <a href="utilisateurs.php?role=etudiant" class="btn <?php echo $role_filter == 'etudiant' ? 'btn-secondary' : 'btn-outline-secondary'; ?> m-1">Étudiants</a>
                            <a href="utilisateurs.php?role=enseignant" class="btn <?php echo $role_filter == 'enseignant' ? 'btn-secondary' : 'btn-outline-secondary'; ?> m-1">Enseignants</a>
                            <a href="utilisateurs.php?role=admin" class="btn <?php echo $role_filter == 'admin' ? 'btn-secondary' : 'btn-outline-secondary'; ?> m-1">Administrateurs</a>
                            <a href="utilisateurs.php?role=agent" class="btn <?php echo $role_filter == 'agent' ? 'btn-secondary' : 'btn-outline-secondary'; ?> m-1">Agents</a>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des utilisateurs -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Date d'inscription</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($resultat) > 0): ?>
                                        <?php while ($utilisateur = mysqli_fetch_assoc($resultat)): ?>
                                            <tr>
                                                <td><?php echo $utilisateur["id"]; ?></td>
                                                <td><?php echo $utilisateur["nom"]; ?></td>
                                                <td><?php echo $utilisateur["prenom"]; ?></td>
                                                <td><?php echo $utilisateur["email"]; ?></td>
                                                <td>
                                                    <?php 
                                                    $role = $utilisateur["role"];
                                                    $classe = "";
                                                    
                                                    switch ($role) {
                                                        case "etudiant":
                                                            $classe = "badge badge-primary";
                                                            break;
                                                        case "enseignant":
                                                            $classe = "badge badge-success";
                                                            break;
                                                        case "admin":
                                                            $classe = "badge badge-danger";
                                                            break;
                                                        case "agent":
                                                            $classe = "badge badge-secondary";
                                                            break;
                                                        default:
                                                            $classe = "badge badge-info";
                                                    }
                                                    ?>
                                                    <span class="<?php echo $classe; ?>"><?php echo $role; ?></span>
                                                </td>
                                                <td><?php echo date("d/m/Y", strtotime($utilisateur["date_creation"])); ?></td>
                                                <td>
                                                    <a href="voir_utilisateur.php?id=<?php echo $utilisateur["id"]; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Aucun utilisateur trouvé</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Note informative -->
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle"></i> En tant qu'agent, vous pouvez consulter les informations des utilisateurs mais vous ne pouvez pas les modifier.
                </div>
            </div>
        </div>
    </div>
    <!-- Scripts JavaScript de Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

     <!-- Footer -->
    <footer class="footer text-dark text-center py-3 mt-5">
        <p>© <?php echo date("Y"); ?> Système de Réservation MMI</p>
    </footer>
    
</body>
</html>
