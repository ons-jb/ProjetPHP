<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/header.php';
require_once '../config/db.php';

// Protection admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /Veterinaire/auth/login.php');
    exit;
}

// ── Statistiques ─────────────────────────────────────────────────────────────
$nb_clients    = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'client'")->fetchColumn();
$nb_animaux    = $pdo->query("SELECT COUNT(*) FROM animaux")->fetchColumn();
$nb_rdv        = $pdo->query("SELECT COUNT(*) FROM rendez_vous")->fetchColumn();
$nb_commandes  = $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn();
$nb_produits   = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();

$rdv_attente   = $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE statut = 'en_attente'")->fetchColumn();
$cmd_attente   = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut = 'en_attente'")->fetchColumn();
$revenu_total  = $pdo->query("SELECT SUM(total) FROM commandes WHERE statut != 'annulee'")->fetchColumn() ?? 0;

// ── Derniers RDV ─────────────────────────────────────────────────────────────
$derniers_rdv = $pdo->query("
    SELECT r.*,
           u.nom as client_nom, u.prenom as client_prenom,
           a.nom as animal_nom, a.espece,
           s.nom as service_nom
    FROM rendez_vous r
    LEFT JOIN utilisateurs u ON r.user_id = u.id
    LEFT JOIN animaux a ON r.animal_id = a.id
    LEFT JOIN services s ON r.service_id = s.id
    ORDER BY r.created_at DESC
    LIMIT 5
")->fetchAll();

// ── Dernières commandes ───────────────────────────────────────────────────────
$dernieres_cmd = $pdo->query("
    SELECT c.*, u.nom, u.prenom
    FROM commandes c
    LEFT JOIN utilisateurs u ON c.user_id = u.id
    ORDER BY c.created_at DESC
    LIMIT 5
")->fetchAll();
?>

<!-- Titre -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-tachometer-alt text-vert me-2"></i>Tableau de bord</h2>
        <p>Bienvenue <?= htmlspecialchars($_SESSION['user_nom']) ?> — Vue d'ensemble de la clinique</p>
    </div>
    <small class="text-muted">
        <i class="fas fa-clock me-1"></i><?= date('d/m/Y H:i') ?>
    </small>
</div>

<!-- Alertes -->
<?php if ($rdv_attente > 0 || $cmd_attente > 0): ?>
<div class="alert alert-success mb-4">
    <i class="fas fa-bell me-2"></i>
    <?php if ($rdv_attente > 0): ?>
        <strong><?= $rdv_attente ?> rendez-vous</strong> en attente de confirmation.
    <?php endif; ?>
    <?php if ($cmd_attente > 0): ?>
        <strong><?= $cmd_attente ?> commandes</strong> en attente de traitement.
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Statistiques principales -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card stat-vert">
            <div class="chiffre"><?= $nb_clients ?></div>
            <div class="label"><i class="fas fa-users me-1"></i> Clients</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card stat-teal">
            <div class="chiffre"><?= $nb_animaux ?></div>
            <div class="label"><i class="fas fa-paw me-1"></i> Animaux</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card stat-jaune">
            <div class="chiffre"><?= $nb_rdv ?></div>
            <div class="label"><i class="fas fa-calendar me-1"></i> Rendez-vous</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card" style="background:#e63946;">
            <div class="chiffre"><?= $nb_commandes ?></div>
            <div class="label"><i class="fas fa-box me-1"></i> Commandes</div>
        </div>
    </div>
</div>

<!-- Revenus + produits -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center py-4">
                <div style="font-size:2.5rem; font-weight:800; color:#2d6a4f;">
                    <?= number_format($revenu_total, 2) ?> DT
                </div>
                <p class="text-muted mt-1">
                    <i class="fas fa-chart-line me-1"></i>
                    Revenus totaux (commandes non annulées)
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center py-4">
                <div style="font-size:2.5rem; font-weight:800; color:#0a9396;">
                    <?= $nb_produits ?>
                </div>
                <p class="text-muted mt-1">
                    <i class="fas fa-box-open me-1"></i>
                    Produits en catalogue
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Accès rapides -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header vert-clair">
                <i class="fas fa-bolt me-2"></i> Accès rapides
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="reservations.php" class="btn btn-vert">
                        <i class="fas fa-calendar me-1"></i>
                        Rendez-vous
                        <?php if ($rdv_attente > 0): ?>
                            <span style="background:#e63946; color:white; border-radius:50%;
                                         padding:1px 6px; font-size:0.75rem; margin-left:4px;">
                                <?= $rdv_attente ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <a href="commandes.php" class="btn btn-vert">
                        <i class="fas fa-box me-1"></i>
                        Commandes
                        <?php if ($cmd_attente > 0): ?>
                            <span style="background:#e63946; color:white; border-radius:50%;
                                         padding:1px 6px; font-size:0.75rem; margin-left:4px;">
                                <?= $cmd_attente ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <a href="produits.php" class="btn btn-outline-vert">
                        <i class="fas fa-store me-1"></i> Produits
                    </a>
                    <a href="clients.php" class="btn btn-outline-vert">
                        <i class="fas fa-users me-1"></i> Clients
                    </a>
                    <a href="animaux.php" class="btn btn-outline-vert">
                        <i class="fas fa-paw me-1"></i> Animaux
                    </a>
                    <a href="services.php" class="btn btn-outline-vert">
                        <i class="fas fa-stethoscope me-1"></i> Services
                    </a>
                    <a href="statistiques.php" class="btn btn-outline-vert">
                        <i class="fas fa-chart-bar me-1"></i> Statistiques
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Derniers RDV -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header vert d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar me-2"></i>Derniers rendez-vous</span>
                <a href="reservations.php"
                   style="color:rgba(255,255,255,0.8); font-size:0.82rem;">
                    Voir tout →
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($derniers_rdv)): ?>
                    <p class="text-muted p-3 mb-0">Aucun rendez-vous.</p>
                <?php else: ?>
                    <?php
                    $badges = [
                        'en_attente' => ['style' => 'background:#fff3cd;color:#856404;border:1px solid #ffc107;', 'label' => '⏳ Attente'],
                        'confirme'   => ['style' => 'background:#d1e7dd;color:#0f5132;border:1px solid #0f5132;', 'label' => '✅ Confirmé'],
                        'annule'     => ['style' => 'background:#f8d7da;color:#842029;border:1px solid #842029;', 'label' => '❌ Annulé'],
                    ];
                    $icones = ['Chien'=>'🐶','Chat'=>'🐱','Lapin'=>'🐰','Oiseau'=>'🐦','Poisson'=>'🐟'];
                    ?>
                    <?php foreach ($derniers_rdv as $rdv): ?>
                    <?php $badge = $badges[$rdv['statut']] ?? ['style' => 'background:#fff3cd;color:#856404;border:1px solid #ffc107;', 'label' => $rdv['statut']]; ?>
                    <div style="padding:12px 16px; border-bottom:1px solid #f0f0f0;
                                display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-weight:600; font-size:0.9rem;">
                                <?= $icones[$rdv['espece']] ?? '🐾' ?>
                                <?= htmlspecialchars($rdv['animal_nom']) ?>
                                <span style="color:#666; font-weight:400;">
                                    — <?= htmlspecialchars($rdv['client_prenom']) ?>
                                    <?= htmlspecialchars($rdv['client_nom']) ?>
                                </span>
                            </div>
                            <small class="text-muted">
                                <?= date('d/m/Y', strtotime($rdv['date'])) ?>
                                à <?= $rdv['heure'] ?>
                                — <?= htmlspecialchars($rdv['service_nom']) ?>
                            </small>
                        </div>
                        <span style="<?= $badge['style'] ?> padding:4px 10px;border-radius:20px;font-size:0.8rem;font-weight:600;display:inline-block;white-space:nowrap;">
                            <?= $badge['label'] ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Dernières commandes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header vert d-flex justify-content-between align-items-center">
                <span><i class="fas fa-box me-2"></i>Dernières commandes</span>
                <a href="commandes.php"
                   style="color:rgba(255,255,255,0.8); font-size:0.82rem;">
                    Voir tout →
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($dernieres_cmd)): ?>
                    <p class="text-muted p-3 mb-0">Aucune commande.</p>
                <?php else: ?>
                    <?php
                    $badges_cmd = [
                        'en_attente' => ['style' => 'background:#fff3cd;color:#856404;border:1px solid #ffc107;', 'label' => '⏳ Attente'],
                        'confirmee'  => ['style' => 'background:#d1e7dd;color:#0f5132;border:1px solid #0f5132;', 'label' => '✅ Confirmée'],
                        'livree'     => ['style' => 'background:#cfe2ff;color:#084298;border:1px solid #084298;', 'label' => '🚚 Livrée'],
                        'annulee'    => ['style' => 'background:#f8d7da;color:#842029;border:1px solid #842029;', 'label' => '❌ Annulée'],
                    ];
                    ?>
                    <?php foreach ($dernieres_cmd as $cmd): ?>
                    <?php $badge = $badges_cmd[$cmd['statut']] ?? ['style' => 'background:#fff3cd;color:#856404;border:1px solid #ffc107;', 'label' => $cmd['statut']]; ?>
                    <div style="padding:12px 16px; border-bottom:1px solid #f0f0f0;
                                display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-weight:600; font-size:0.9rem;">
                                Commande #<?= str_pad($cmd['id'], 4, '0', STR_PAD_LEFT) ?>
                                <span style="color:#666; font-weight:400;">
                                    — <?= htmlspecialchars($cmd['prenom']) ?>
                                    <?= htmlspecialchars($cmd['nom']) ?>
                                </span>
                            </div>
                            <small class="text-muted">
                                <?= date('d/m/Y à H:i', strtotime($cmd['created_at'])) ?>
                            </small>
                        </div>
                        <div class="text-end">
                            <div class="text-vert fw-700">
                                <?= number_format($cmd['total'], 2) ?> DT
                            </div>
                            <span style="<?= $badge['style'] ?> padding:4px 10px;border-radius:20px;font-size:0.8rem;font-weight:600;display:inline-block;white-space:nowrap;">
                                <?= $badge['label'] ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>