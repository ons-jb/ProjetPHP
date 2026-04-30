<?php
require_once '../includes/header.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Veterinaire/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// ── Annulation ───────────────────────────────────────────────────────────────
if (isset($_GET['annuler'])) {
    $id = (int) $_GET['annuler'];
    $stmt = $pdo->prepare("UPDATE rendez_vous SET statut='annule' WHERE id=? AND user_id=? AND statut='en_attente'");
    $stmt->execute([$id, $user_id]);
}

// ── Récupérer les rendez-vous ────────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT r.*, a.nom as animal_nom, a.espece, s.nom as service_nom, s.prix
    FROM rendez_vous r
    LEFT JOIN animaux a ON r.animal_id = a.id
    LEFT JOIN services s ON r.service_id = s.id
    WHERE r.user_id = ?
    ORDER BY r.date DESC, r.heure DESC
");
$stmt->execute([$user_id]);
$rdvs = $stmt->fetchAll();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-calendar-alt text-vert me-2"></i>Mes rendez-vous</h2>
        <p>Historique et suivi de vos consultations</p>
    </div>
    <a href="prendre_rdv.php" class="btn btn-vert">
        <i class="fas fa-plus me-1"></i> Prendre un RDV
    </a>
</div>

<?php if (isset($_GET['succes'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>
        Rendez-vous pris avec succès ! Nous vous confirmerons bientôt.
    </div>
<?php endif; ?>

<?php if (empty($rdvs)): ?>
    <div class="text-center py-5">
        <div style="font-size:3.5rem; margin-bottom:16px;">📅</div>
        <h5 class="fw-700">Aucun rendez-vous</h5>
        <p class="text-muted mb-3">Prenez votre premier rendez-vous en ligne</p>
        <a href="prendre_rdv.php" class="btn btn-vert">
            <i class="fas fa-plus me-1"></i> Prendre un RDV
        </a>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($rdvs as $rdv): ?>
        <?php
        $icones = ['Chien'=>'🐶','Chat'=>'🐱','Lapin'=>'🐰','Oiseau'=>'🐦','Poisson'=>'🐟'];
        $icone  = $icones[$rdv['espece']] ?? '🐾';
        $badges = [
            'en_attente' => ['class'=>'badge-attente',  'label'=>'En attente'],
            'confirme'   => ['class'=>'badge-confirme', 'label'=>'Confirmé'],
            'annule'     => ['class'=>'badge-annule',   'label'=>'Annulé'],
        ];
        $badge = $badges[$rdv['statut']] ?? ['class'=>'badge-attente','label'=>$rdv['statut']];
        ?>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="fw-700 mb-1">
                                <?= $icone ?> <?= htmlspecialchars($rdv['animal_nom']) ?>
                            </h6>
                            <small class="text-muted">
                                <?= htmlspecialchars($rdv['service_nom']) ?>
                            </small>
                        </div>
                        <span class="badge <?= $badge['class'] ?>">
                            <?= $badge['label'] ?>
                        </span>
                    </div>

                    <div style="background:#f8f9f7; border-radius:8px; padding:12px;">
                        <div class="d-flex gap-3">
                            <div>
                                <small class="text-muted">Date</small><br>
                                <strong>
                                    <i class="fas fa-calendar me-1 text-vert"></i>
                                    <?= date('d/m/Y', strtotime($rdv['date'])) ?>
                                </strong>
                            </div>
                            <div>
                                <small class="text-muted">Heure</small><br>
                                <strong>
                                    <i class="fas fa-clock me-1 text-vert"></i>
                                    <?= $rdv['heure'] ?>
                                </strong>
                            </div>
                            <div>
                                <small class="text-muted">Prix</small><br>
                                <strong class="text-vert">
                                    <?= number_format($rdv['prix'], 2) ?> DT
                                </strong>
                            </div>
                        </div>
                        <?php if ($rdv['motif']): ?>
                            <div class="mt-2">
                                <small class="text-muted">Motif :</small>
                                <small> <?= htmlspecialchars($rdv['motif']) ?></small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($rdv['statut'] === 'en_attente'): ?>
                        <div class="mt-3">
                            <a href="?annuler=<?= $rdv['id'] ?>"
                               onclick="return confirm('Annuler ce rendez-vous ?')"
                               class="btn btn-rose btn-sm w-100">
                                <i class="fas fa-times me-1"></i> Annuler le RDV
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>