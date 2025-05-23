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

// Récupérer les informations spécifiques à l'étudiant
$sql = "SELECT numero_etudiant, filiere, annee_etude, groupe_tp, groupe_td FROM utilisateur WHERE id = $id";
$resultat = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($resultat);

$numero_etudiant = $row["numero_etudiant"];
$filiere = $row["filiere"];
$annee_etude = $row["annee_etude"];
$groupe_tp = $row["groupe_tp"];
$groupe_td = $row["groupe_td"];

// Récupérer les réservations de l'étudiant
$sql = "SELECT r.id, r.date_debut, r.date_fin, r.statut, m.nom as materiel_nom, s.nom as salle_nom 
        FROM reservation r 
        LEFT JOIN materiel m ON r.materiel_id = m.id 
        LEFT JOIN salle s ON r.salle_id = s.id 
        WHERE r.utilisateur_id = $id 
        ORDER BY r.date_debut DESC 
        LIMIT 3";
$reservations = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil Étudiant - Système de Réservation MMI</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/pageadmin.css">
    <style>
        .dashboard-card {
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
    </style>
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
                <li class="nav-item">
                    <a class="nav-link" href="materiel.php"><i class="fas fa-laptop"></i>Matériel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="salles.php"><i class="fas fa-door-open"></i>Salles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reservations.php"><i class="fas fa-calendar-alt"></i>Mes Réservations</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="profil.php"><i class="fas fa-user"></i>Mon Profil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="welcome-card">
            <h2>Bienvenue, <?php echo $prenom . ' ' . $nom; ?> !</h2>
            <p>Vous êtes connecté en tant qu'étudiant.</p>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body text-center">
                        <i class="fas fa-laptop card-icon text-dark"></i>
                        <h5 class="card-title">Réserver du Matériel</h5>
                        <p class="card-text">Accédez au matériel disponible pour vos projets MMI.</p>
                        <a href="materiel.php" class="btn btn-primary">Voir le Matériel</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body text-center">
                        <i class="fas fa-door-open card-icon text-dark"></i>
                        <h5 class="card-title">Réserver une Salle</h5>
                        <p class="card-text">Réservez une salle pour vos travaux de groupe.</p>
                        <a href="salles.php" class="btn btn-success">Voir les Salles</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt card-icon text-dark"></i>
                        <h5 class="card-title">Mes Réservations</h5>
                        <p class="card-text">Consultez l'historique de vos réservations.</p>
                        <a href="reservations.php" class="btn btn-warning">Voir mes Réservations</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Informations de l'étudiant -->
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Mes informations</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <p><strong>Numéro étudiant:</strong> <?php echo $numero_etudiant ? $numero_etudiant : "Non renseigné"; ?></p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Filière:</strong> <?php echo $filiere ? $filiere : "Non renseignée"; ?></p>
                    </div>
                    <div class="col-md-2">
                        <p><strong>Année:</strong> <?php echo $annee_etude ? $annee_etude : "Non renseignée"; ?></p>
                    </div>
                    <div class="col-md-2">
                        <p><strong>Groupe TP:</strong> <?php echo $groupe_tp ? $groupe_tp : "Non renseigné"; ?></p>
                    </div>
                    <div class="col-md-2">
                        <p><strong>Groupe TD:</strong> <?php echo $groupe_td ? $groupe_td : "Non renseigné"; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Dernières réservations -->
        <div class="card mt-4">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Mes dernières réservations</h5>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($reservations) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Nom</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reservation = mysqli_fetch_assoc($reservations)): ?>
                                    <tr>
                                        <td>
                                            <?php if ($reservation["materiel_nom"]): ?>
                                                <span class="badge badge-primary">Matériel</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Salle</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $reservation["materiel_nom"] ? $reservation["materiel_nom"] : $reservation["salle_nom"]; ?>
                                        </td>
                                        <td><?php echo date("d/m/Y H:i", strtotime($reservation["date_debut"])); ?></td>
                                        <td><?php echo date("d/m/Y H:i", strtotime($reservation["date_fin"])); ?></td>
                                        <td>
                                            <?php 
                                            $statut = $reservation["statut"];
                                            $classe = "";
                                            
                                            if ($statut == "en_attente") {
                                                $classe = "badge badge-warning";
                                            } elseif ($statut == "approuvee") {
                                                $classe = "badge badge-success";
                                            } elseif ($statut == "refusee") {
                                                $classe = "badge badge-danger";
                                            } else {
                                                $classe = "badge badge-secondary";
                                            }
                                            ?>
                                            <span class="<?php echo $classe; ?>"><?php echo $statut; ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>Vous n'avez pas encore de réservations.</p>
                <?php endif; ?>
                <a href="reservations.php" class="btn btn-warning">Voir toutes mes réservations</a>
            </div>
        </div>
        
        <div class="card mt-4 mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Informations Importantes</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>Les réservations de matériel doivent être effectuées au moins 24 heures à l'avance.</li>
                    <li>Les salles peuvent être réservées pour une durée maximale de 4 heures.</li>
                    <li>Tout matériel endommagé doit être signalé immédiatement.</li>
                    <li>Pour toute question, contactez le service MMI à support@mmi.fr</li>
                </ul>
            </div>
        </div>
    </div>
    
  <!-- Footer -->
    <footer class="footer text-dark text-center py-3 mt-5">
        <p>© <?php echo date("Y"); ?> Système de Réservation MMI</p>
    </footer>

    <script>
    document.querySelectorAll('.btn-select').forEach(button => {
        button.addEventListener('click', () => {
            // Supprime l'état actif de tous les boutons
            document.querySelectorAll('.btn-select').forEach(btn => {
                btn.classList.remove('active');
            });

            // Ajoute l'état actif au bouton cliqué
            button.classList.add('active');
        });
    });
    </script>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
