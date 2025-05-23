<?php
// Inclure le fichier de configuration
require_once '../config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["connecte"]) || $_SESSION["connecte"] !== true) {
    header("location: ../login.php");
    exit;
}

// Vérifier si l'utilisateur est un administrateur
if ($_SESSION["role"] !== "admin") {
    header("location: ../login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$id = $_SESSION["id"];
$nom = $_SESSION["nom"];
$prenom = $_SESSION["prenom"];

// Variable pour stocker les messages
$message = "";

// Traitement de la suppression d'un utilisateur
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $id_utilisateur = $_GET['id'];
    
    // Vérifier que l'utilisateur n'est pas en train de se supprimer lui-même
    if ($id_utilisateur == $id) {
        $message = '<div class="alert alert-danger">Vous ne pouvez pas supprimer votre propre compte.</div>';
    } else {
        // Supprimer l'utilisateur
        $sql = "DELETE FROM utilisateur WHERE id = $id_utilisateur";
        if (mysqli_query($conn, $sql)) {
            $message = '<div class="alert alert-success">L\'utilisateur a été supprimé avec succès.</div>';
        } else {
            $message = '<div class="alert alert-danger">Erreur lors de la suppression de l\'utilisateur: ' . mysqli_error($conn) . '</div>';
        }
    }
}

// Récupérer la liste des utilisateurs
$sql = "SELECT * FROM utilisateur ORDER BY role, nom, prenom";
$resultat = mysqli_query($conn, $sql);

// Filtrer par rôle si demandé
if (isset($_GET['role']) && !empty($_GET['role'])) {
    $role_filter = $_GET['role'];
    $sql = "SELECT * FROM utilisateur WHERE role = '$role_filter' ORDER BY nom, prenom";
    $resultat = mysqli_query($conn, $sql);
} else {
    $role_filter = "";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Utilisateurs - Administration</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Inclure Bootstrap pour le style -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/pageadmin.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="index.php"><img src="../images/logopng.png" alt=""></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt icon-align"></i>Tableau de bord</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="utilisateurs.php"><i class="fas fa-users"></i>Utilisateurs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="materiel.php"><i class="fas fa-laptop"></i>Matériel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="salles.php"><i class="fas fa-door-open"></i>Salles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reservations.php"><i class="fas fa-calendar-alt"></i>Réservations</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                </li>
            </ul>
        </div>
    </nav>
            
            <!-- Contenu principal -->
            <div class="col-md-10 content mx-auto">
                <div class="page-header">
                    <h2><i class="fas fa-users"></i> Gestion des Utilisateurs</h2>
                    <p>Consultez, modifiez et supprimez les utilisateurs du système.</p>
                </div>
                
                <!-- Afficher les messages -->
                <?php echo $message; ?>
                
                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Filtrer par rôle</h5>
                        <div class="d-flex flex-wrap">
                            <a href="utilisateurs.php" class="btn <?php echo empty($role_filter) ? 'btn-dark' : 'btn-outline-dark'; ?> m-1">Tous</a>
                            <a href="utilisateurs.php?role=etudiant" class="btn <?php echo $role_filter == 'etudiant' ? 'btn-dark' : 'btn-outline-dark'; ?> m-1">Étudiants</a>
                            <a href="utilisateurs.php?role=enseignant" class="btn <?php echo $role_filter == 'enseignant' ? 'btn-dark' : 'btn-outline-dark'; ?> m-1">Enseignants</a>
                            <a href="utilisateurs.php?role=admin" class="btn <?php echo $role_filter == 'admin' ? 'btn-dark' : 'btn-outline-dark'; ?> m-1">Administrateurs</a>
                            <a href="utilisateurs.php?role=agent" class="btn <?php echo $role_filter == 'agent' ? 'btn-dark' : 'btn-outline-dark'; ?> m-1">Agents</a>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="card mb-3">
                    <a href="../creer_admin_agent.php" class="btn btn-ajout">
                        <i class="fas fa-user-plus"></i> Ajouter un administrateur/agent
                    </a>
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
                                <tbody class="fond">
                                    <?php if (mysqli_num_rows($resultat) > 0): ?>
                                        <?php while ($utilisateur = mysqli_fetch_assoc($resultat)): ?>
                                            <tr>
                                                <td><?php echo $utilisateur["id"]; ?></td>
                                                <td><?php echo $utilisateur["nom"]; ?></td>
                                                <td><?php echo $utilisateur["prenom"]; ?></td>
                                                <td><?php echo $utilisateur["email"]; ?></td>
                                                <td>
                                                    <?php 
                                                    // Afficher le rôle avec une couleur différente
                                                    $role = $utilisateur["role"];
                                                    $classe = "";
                                                    
                                                    if ($role == "etudiant") {
                                                        $classe = "badge badge-primary";
                                                    } elseif ($role == "enseignant") {
                                                        $classe = "badge badge-success";
                                                    } elseif ($role == "admin") {
                                                        $classe = "badge badge-danger";
                                                    } elseif ($role == "agent") {
                                                        $classe = "badge badge-secondary";
                                                    } else {
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
                                                    <?php if ($utilisateur["id"] != $id): ?>
                                                        <a href="utilisateurs.php?action=supprimer&id=<?php echo $utilisateur["id"]; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
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
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer text-dark text-center py-3 mt-5">
        <p>© <?php echo date("Y"); ?> Système de Réservation MMI</p>
    </footer>
    
    <!-- Scripts JavaScript de Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
