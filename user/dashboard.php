<?php
require_once '../includes/header.php';
require_once '../config/db.php';

// Protection : si pas connecté → redirection
if (!isset($_SESSION['user_id'])) {
    header('Location: /Veterinaire/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les infos de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Compter les animaux
$stmt = $pdo->prepare("SELECT COUNT(*) FROM animaux WHERE user_id = ?");
$stmt->execute([$user_id]);
$nb_animaux = $stmt->fetchColumn();

// Compter les rendez-vous
$stmt = $pdo->prepare("SELECT COUNT(*) FROM rendez_vous WHERE user_id = ?");
$stmt->execute([$user_id]);
$nb_rdv = $stmt->fetchColumn();

// Compter les commandes
$stmt = $pdo->prepare("SELECT COUNT(*) FROM commandes WHERE user_id = ?");
$stmt->execute([$user_id]);
$nb_commandes = $stmt->fetchColumn();
?>

<h2>👋 Bonjour, <?= htmlspecialchars($user['prenom']) ?> !</h2>
<p class="text-muted">Bienvenue dans votre espace personnel</p>

<!-- Cartes statistiques -->
<div class="row mt-4">
    <div class="col-md-4 mb-3">
        <div class="card text-center border-success shadow-sm">
            <div class="card-body">
                <h1 class="display-4 text-success"><?= $nb_animaux ?></h1>
                <p class="card-text">🐾 Mes animaux</p>
                <a href="/Veterinaire/user/animaux.php" 
                   class="btn btn-success btn-sm">Gérer</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-center border-primary shadow-sm">
            <div class="card-body">
                <h1 class="display-4 text-primary"><?= $nb_rdv ?></h1>
                <p class="card-text">📅 Mes rendez-vous</p>
                <a href="/Veterinaire/user/rendez-vous.php" 
                   class="btn btn-primary btn-sm">Gérer</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-center border-warning shadow-sm">
            <div class="card-body">
                <h1 class="display-4 text-warning"><?= $nb_commandes ?></h1>
                <p class="card-text">🛒 Mes commandes</p>
                <a href="/Veterinaire/user/commandes.php" 
                   class="btn btn-warning btn-sm">Voir</a>
            </div>
        </div>
    </div>
</div>

<!-- Derniers rendez-vous -->
<div class="card mt-4 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">📅 Mes derniers rendez-vous</h5>
    </div>
    <div class="card-body">
        <?php
        $stmt = $pdo->prepare("
            SELECT r.*, a.nom as animal_nom, s.nom as service_nom 
            FROM rendez_vous r
            LEFT JOIN animaux a ON r.animal_id = a.id
            LEFT JOIN services s ON r.service_id = s.id
            WHERE r.user_id = ?
            ORDER BY r.date DESC LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $rdvs = $stmt->fetchAll();
        ?>

        <?php if (empty($rdvs)): ?>
            <p class="text-muted">Aucun rendez-vous pour le moment.</p>
            <a href="/Veterinaire/user/rendez-vous.php" 
               class="btn btn-primary btn-sm">Prendre un rendez-vous</a>
        <?php else: ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Animal</th>
                        <th>Service</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rdvs as $rdv): ?>
                    <tr>
                        <td><?= htmlspecialchars($rdv['date']) ?></td>
                        <td><?= htmlspecialchars($rdv['heure']) ?></td>
                        <td><?= htmlspecialchars($rdv['animal_nom']) ?></td>
                        <td><?= htmlspecialchars($rdv['service_nom']) ?></td>
                        <td>
                            <?php
                            $badges = [
                                'en_attente' => 'warning',
                                'confirme'   => 'success',
                                'annule'     => 'danger'
                            ];
                            $b = $badges[$rdv['statut']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $b ?>">
                                <?= htmlspecialchars($rdv['statut']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Infos personnelles -->
<div class="card mt-4 shadow-sm">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">👤 Mes informations</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nom :</strong> <?= htmlspecialchars($user['nom']) ?></p>
                <p><strong>Prénom :</strong> <?= htmlspecialchars($user['prenom']) ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Téléphone :</strong> <?= htmlspecialchars($user['telephone'] ?? 'Non renseigné') ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>