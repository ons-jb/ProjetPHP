<?php
require_once '../includes/header.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Veterinaire/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// ── Récupérer les commandes ──────────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT c.*, 
           COUNT(cp.id) as nb_articles
    FROM commandes c
    LEFT JOIN commande_produits cp ON c.id = cp.commande_id
    WHERE c.user_id = ?
    GROUP BY c.id
    ORDER BY c.created_at DESC
");
$stmt->execute([$user_id]);
$commandes = $stmt->fetchAll();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-box text-vert me-2"></i>Mes commandes</h2>
        <p>Historique de vos achats</p>
    </div>
    <a href="produits.php" class="btn btn-vert">
        <i class="fas fa-store me-1"></i> Voir les produits
    </a>
</div>

<?php if (isset($_GET['succes'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>
        Commande passée avec succès ! Nous la traiterons dans les plus brefs délais.
    </div>
<?php endif; ?>

<?php if (empty($commandes)): ?>
    <div class="text-center py-5">
        <div style="font-size:3.5rem; margin-bottom:16px;">📦</div>
        <h5 class="fw-700">Aucune commande</h5>
        <p class="text-muted mb-3">Vous n'avez pas encore passé de commande</p>
        <a href="produits.php" class="btn btn-vert">
            <i class="fas fa-store me-1"></i> Voir les produits
        </a>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($commandes as $c): ?>
        <?php
        $badges = [
            'en_attente' => ['class'=>'badge-attente',  'label'=>'En attente'],
            'confirmee'  => ['class'=>'badge-confirme', 'label'=>'Confirmée'],
            'livree'     => ['class'=>'badge-livre',    'label'=>'Livrée'],
            'annulee'    => ['class'=>'badge-annule',   'label'=>'Annulée'],
        ];
        $badge = $badges[$c['statut']] ?? ['class'=>'badge-attente','label'=>$c['statut']];
        ?>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="fw-700 mb-1">
                                Commande #<?= str_pad($c['id'], 4, '0', STR_PAD_LEFT) ?>
                            </h6>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('d/m/Y à H:i', strtotime($c['created_at'])) ?>
                            </small>
                        </div>
                        <span class="badge <?= $badge['class'] ?>">
                            <?= $badge['label'] ?>
                        </span>
                    </div>

                    <div style="background:#f8f9f7; border-radius:8px; padding:12px;">
                        <div class="d-flex gap-4">
                            <div>
                                <small class="text-muted">Articles</small><br>
                                <strong><?= $c['nb_articles'] ?> produit(s)</strong>
                            </div>
                            <div>
                                <small class="text-muted">Total</small><br>
                                <strong class="text-vert">
                                    <?= number_format($c['total'], 2) ?> DT
                                </strong>
                            </div>
                        </div>
                    </div>

                    <!-- Détail articles -->
                    <?php
                    $stmt2 = $pdo->prepare("
                        SELECT cp.*, p.nom, p.photo
                        FROM commande_produits cp
                        JOIN produits p ON cp.produit_id = p.id
                        WHERE cp.commande_id = ?
                    ");
                    $stmt2->execute([$c['id']]);
                    $lignes = $stmt2->fetchAll();
                    ?>
                    <div class="mt-3">
                        <?php foreach ($lignes as $l): ?>
                        <div class="d-flex align-items-center gap-2 mb-1"
                             style="font-size:0.85rem;">
                            <?php if ($l['photo']): ?>
                                <img src="/Veterinaire/public/uploads/<?= htmlspecialchars($l['photo']) ?>"
                                     style="width:30px; height:30px; border-radius:4px; object-fit:cover;">
                            <?php else: ?>
                                <span>💊</span>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($l['nom']) ?></span>
                            <span class="text-muted">x<?= $l['quantite'] ?></span>
                            <span class="ms-auto text-vert fw-700">
                                <?= number_format($l['prix_unitaire'] * $l['quantite'], 2) ?> DT
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>