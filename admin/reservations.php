<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/header.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /Veterinaire/auth/login.php');
    exit;
}

$succes = '';

// ── Changer statut ───────────────────────────────────────────────────────────
if (isset($_GET['statut']) && isset($_GET['id'])) {
    $id     = (int) $_GET['id'];
    $statut = $_GET['statut'];
    $statuts_ok = ['confirme', 'annule', 'en_attente'];
    if (in_array($statut, $statuts_ok)) {
        $stmt = $pdo->prepare("UPDATE rendez_vous SET statut = ? WHERE id = ?");
        $stmt->execute([$statut, $id]);
        $succes = "Statut mis à jour avec succès.";
    }
}

// ── Filtre par statut ────────────────────────────────────────────────────────
$filtre = isset($_GET['filtre']) ? $_GET['filtre'] : 'tous';

if ($filtre !== 'tous') {
    $stmt = $pdo->prepare("
        SELECT r.*, 
               u.nom as client_nom, u.prenom as client_prenom, u.telephone,
               a.nom as animal_nom, a.espece,
               s.nom as service_nom, s.prix
        FROM rendez_vous r
        LEFT JOIN utilisateurs u ON r.user_id = u.id
        LEFT JOIN animaux a ON r.animal_id = a.id
        LEFT JOIN services s ON r.service_id = s.id
        WHERE r.statut = ?
        ORDER BY r.date ASC, r.heure ASC
    ");
    $stmt->execute([$filtre]);
} else {
    $stmt = $pdo->query("
        SELECT r.*, 
               u.nom as client_nom, u.prenom as client_prenom, u.telephone,
               a.nom as animal_nom, a.espece,
               s.nom as service_nom, s.prix
        FROM rendez_vous r
        LEFT JOIN utilisateurs u ON r.user_id = u.id
        LEFT JOIN animaux a ON r.animal_id = a.id
        LEFT JOIN services s ON r.service_id = s.id
        ORDER BY r.date ASC, r.heure ASC
    ");
}
$rdvs = $stmt->fetchAll();

// Compteurs
$nb_attente  = $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE statut = 'en_attente'")->fetchColumn();
$nb_confirme = $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE statut = 'confirme'")->fetchColumn();
$nb_annule   = $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE statut = 'annule'")->fetchColumn();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-calendar-alt text-vert me-2"></i>Gestion des rendez-vous</h2>
        <p>Confirmez ou annulez les demandes de rendez-vous</p>
    </div>
    <a href="index.php" class="btn btn-outline-vert">
        <i class="fas fa-arrow-left me-1"></i> Dashboard
    </a>
</div>

<?php if ($succes): ?>
    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
<?php endif; ?>

<!-- Compteurs -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-jaune">
            <div class="chiffre"><?= $nb_attente ?></div>
            <div class="label"><i class="fas fa-clock me-1"></i> En attente</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-vert">
            <div class="chiffre"><?= $nb_confirme ?></div>
            <div class="label"><i class="fas fa-check me-1"></i> Confirmés</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background:#e63946;">
            <div class="chiffre"><?= $nb_annule ?></div>
            <div class="label"><i class="fas fa-times me-1"></i> Annulés</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="mb-3 d-flex gap-2 flex-wrap">
    <a href="?filtre=tous"
       class="btn btn-sm <?= $filtre === 'tous' ? 'btn-vert' : 'btn-outline-vert' ?>">
        Tous (<?= $nb_attente + $nb_confirme + $nb_annule ?>)
    </a>
    <a href="?filtre=en_attente"
       class="btn btn-sm <?= $filtre === 'en_attente' ? 'btn-vert' : 'btn-outline-vert' ?>">
        En attente (<?= $nb_attente ?>)
    </a>
    <a href="?filtre=confirme"
       class="btn btn-sm <?= $filtre === 'confirme' ? 'btn-vert' : 'btn-outline-vert' ?>">
        Confirmés (<?= $nb_confirme ?>)
    </a>
    <a href="?filtre=annule"
       class="btn btn-sm <?= $filtre === 'annule' ? 'btn-vert' : 'btn-outline-vert' ?>">
        Annulés (<?= $nb_annule ?>)
    </a>
</div>

<!-- Table RDV -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($rdvs)): ?>
            <div class="text-center py-5">
                <div style="font-size:3rem;">📅</div>
                <p class="text-muted mt-2">Aucun rendez-vous trouvé</p>
            </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Animal</th>
                        <th>Service</th>
                        <th>Date & Heure</th>
                        <th>Motif</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $badges = [
                        'en_attente' => ['class'=>'badge-attente',  'label'=>'En attente'],
                        'confirme'   => ['class'=>'badge-confirme', 'label'=>'Confirmé'],
                        'annule'     => ['class'=>'badge-annule',   'label'=>'Annulé'],
                    ];
                    $icones = ['Chien'=>'🐶','Chat'=>'🐱','Lapin'=>'🐰','Oiseau'=>'🐦','Poisson'=>'🐟'];
                    ?>
                    <?php foreach ($rdvs as $rdv): ?>
                    <?php $badge = $badges[$rdv['statut']] ?? ['class'=>'badge-attente','label'=>$rdv['statut']]; ?>
                    <tr>
                        <td>
                            <div class="fw-700">
                                <?= htmlspecialchars($rdv['client_prenom']) ?>
                                <?= htmlspecialchars($rdv['client_nom']) ?>
                            </div>
                            <?php if ($rdv['telephone']): ?>
                                <small class="text-muted">
                                    <i class="fas fa-phone me-1"></i>
                                    <?= htmlspecialchars($rdv['telephone']) ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $icones[$rdv['espece']] ?? '🐾' ?>
                            <?= htmlspecialchars($rdv['animal_nom']) ?>
                            <br>
                            <small class="text-muted"><?= htmlspecialchars($rdv['espece']) ?></small>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($rdv['service_nom']) ?></div>
                            <small class="text-vert fw-700">
                                <?= number_format($rdv['prix'], 2) ?> DT
                            </small>
                        </td>
                        <td>
                            <div class="fw-700">
                                <?= date('d/m/Y', strtotime($rdv['date'])) ?>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i><?= $rdv['heure'] ?>
                            </small>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= $rdv['motif'] ? htmlspecialchars(substr($rdv['motif'], 0, 40)) . '...' : '—' ?>
                            </small>
                        </td>
                        <td>
    <?php
    $styles = [
        'en_attente' => 'background:#fff3cd; color:#856404; border:1px solid #ffc107;',
        'confirme'   => 'background:#d1e7dd; color:#0f5132; border:1px solid #0f5132;',
        'annule'     => 'background:#f8d7da; color:#842029; border:1px solid #842029;',
    ];
    $icones_statut = [
        'en_attente' => '⏳',
        'confirme'   => '✅',
        'annule'     => '❌',
    ];
    $style = $styles[$rdv['statut']] ?? $styles['en_attente'];
    $icone_s = $icones_statut[$rdv['statut']] ?? '';
    ?>
    <span style="<?= $style ?> padding:5px 10px; border-radius:20px; font-size:0.8rem; font-weight:600; white-space:nowrap; display:inline-block;">
        <?= $icone_s ?> <?= $badge['label'] ?>
    </span>
</td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <?php if ($rdv['statut'] !== 'confirme'): ?>
                                    <a href="?id=<?= $rdv['id'] ?>&statut=confirme&filtre=<?= $filtre ?>"
                                       class="btn btn-sm"
                                       style="background:#d1e7dd; color:#0f5132; border:none;"
                                       title="Confirmer">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($rdv['statut'] !== 'annule'): ?>
                                    <a href="?id=<?= $rdv['id'] ?>&statut=annule&filtre=<?= $filtre ?>"
                                       onclick="return confirm('Annuler ce rendez-vous ?')"
                                       class="btn btn-sm"
                                       style="background:#f8d7da; color:#842029; border:none;"
                                       title="Annuler">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>