<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/header.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /Veterinaire/auth/login.php');
    exit;
}

$succes = '';

// ── Changer statut commande ──────────────────────────────────────────────────
if (isset($_GET['statut']) && isset($_GET['id'])) {
    $id     = (int) $_GET['id'];
    $statut = $_GET['statut'];
    $statuts_ok = ['en_attente', 'confirmee', 'livree', 'annulee'];
    if (in_array($statut, $statuts_ok)) {
        $stmt = $pdo->prepare("UPDATE commandes SET statut = ? WHERE id = ?");
        $stmt->execute([$statut, $id]);
        $succes = "Statut de la commande mis à jour.";
    }
}

// ── Filtre ───────────────────────────────────────────────────────────────────
$filtre = isset($_GET['filtre']) ? $_GET['filtre'] : 'tous';

$where = $filtre !== 'tous' ? "WHERE c.statut = '$filtre'" : '';

$commandes = $pdo->query("
    SELECT c.*,
           u.nom as client_nom, u.prenom as client_prenom, u.telephone,
           COUNT(cp.id) as nb_produits
    FROM commandes c
    LEFT JOIN utilisateurs u ON c.user_id = u.id
    LEFT JOIN commande_produits cp ON cp.commande_id = c.id
    $where
    GROUP BY c.id
    ORDER BY c.created_at DESC
")->fetchAll();

// Compteurs
$nb_attente  = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut='en_attente'")->fetchColumn();
$nb_confirme = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut='confirmee'")->fetchColumn();
$nb_livree   = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut='livree'")->fetchColumn();
$nb_annulee  = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut='annulee'")->fetchColumn();
$ca_total    = $pdo->query("SELECT COALESCE(SUM(total),0) FROM commandes WHERE statut != 'annulee'")->fetchColumn();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-shopping-bag text-vert me-2"></i>Gestion des commandes</h2>
        <p>Suivez et gérez toutes les commandes clients</p>
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
    <div class="col-md-3">
        <div class="stat-card stat-jaune">
            <div class="chiffre"><?= $nb_attente ?></div>
            <div class="label"><i class="fas fa-clock me-1"></i> En attente</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-vert">
            <div class="chiffre"><?= $nb_confirme ?></div>
            <div class="label"><i class="fas fa-check me-1"></i> Confirmées</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background:#e3f2fd;">
            <div class="chiffre" style="color:#1565c0;"><?= $nb_livree ?></div>
            <div class="label" style="color:#1565c0;"><i class="fas fa-truck me-1"></i> Livrées</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background:#1a1a2e;color:#fff;">
            <div class="chiffre"><?= number_format($ca_total, 2) ?> DT</div>
            <div class="label"><i class="fas fa-coins me-1"></i> CA Total</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="mb-3 d-flex gap-2 flex-wrap">
    <?php
    $filtres = [
        'tous'       => 'Tous (' . ($nb_attente + $nb_confirme + $nb_livree + $nb_annulee) . ')',
        'en_attente' => "En attente ($nb_attente)",
        'confirmee'  => "Confirmées ($nb_confirme)",
        'livree'     => "Livrées ($nb_livree)",
        'annulee'    => "Annulées ($nb_annulee)",
    ];
    foreach ($filtres as $val => $label):
    ?>
        <a href="?filtre=<?= $val ?>"
           class="btn btn-sm <?= $filtre === $val ? 'btn-vert' : 'btn-outline-vert' ?>">
            <?= $label ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Table commandes -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($commandes)): ?>
            <div class="text-center py-5">
                <div style="font-size:3rem;">🛒</div>
                <p class="text-muted mt-2">Aucune commande trouvée</p>
            </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Produits</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $badge_styles = [
                    'en_attente' => 'background:#fff3cd;color:#856404;border:1px solid #ffc107;',
                    'confirmee'  => 'background:#d1e7dd;color:#0f5132;border:1px solid #0f5132;',
                    'livree'     => 'background:#cfe2ff;color:#084298;border:1px solid #084298;',
                    'annulee'    => 'background:#f8d7da;color:#842029;border:1px solid #842029;',
                ];
                $badge_icons = [
                    'en_attente' => '⏳',
                    'confirmee'  => '✅',
                    'livree'     => '🚚',
                    'annulee'    => '❌',
                ];
                $badge_labels = [
                    'en_attente' => 'En attente',
                    'confirmee'  => 'Confirmée',
                    'livree'     => 'Livrée',
                    'annulee'    => 'Annulée',
                ];
                ?>
                <?php foreach ($commandes as $cmd): ?>
                <?php
                    $bstyle = $badge_styles[$cmd['statut']] ?? $badge_styles['en_attente'];
                    $bicon  = $badge_icons[$cmd['statut']] ?? '⏳';
                    $blabel = $badge_labels[$cmd['statut']] ?? $cmd['statut'];

                    // Détail produits
                    $produits_stmt = $pdo->prepare("
                        SELECT p.nom, cp.quantite, cp.prix_unitaire
                        FROM commande_produits cp
                        JOIN produits p ON p.id = cp.produit_id
                        WHERE cp.commande_id = ?
                    ");
                    $produits_stmt->execute([$cmd['id']]);
                    $produits_cmd = $produits_stmt->fetchAll();
                ?>
                <tr>
                    <td><span class="fw-700 text-muted">#<?= $cmd['id'] ?></span></td>
                    <td>
                        <div class="fw-700"><?= htmlspecialchars($cmd['client_prenom'] . ' ' . $cmd['client_nom']) ?></div>
                        <?php if ($cmd['telephone']): ?>
                            <small class="text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($cmd['telephone']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php foreach ($produits_cmd as $p): ?>
                            <div style="font-size:0.82rem;">
                                <span class="text-muted">×<?= $p['quantite'] ?></span>
                                <?= htmlspecialchars($p['nom']) ?>
                                <span class="text-vert fw-700"><?= number_format($p['prix_unitaire'], 2) ?> DT</span>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($produits_cmd)): ?>
                            <small class="text-muted">—</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="fw-700 text-vert"><?= number_format($cmd['total'], 2) ?> DT</span>
                    </td>
                    <td>
                        <small><?= date('d/m/Y H:i', strtotime($cmd['created_at'])) ?></small>
                    </td>
                    <td>
                        <span style="<?= $bstyle ?> padding:4px 10px;border-radius:20px;font-size:0.8rem;font-weight:600;white-space:nowrap;display:inline-block;">
                            <?= $bicon ?> <?= $blabel ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <?php if ($cmd['statut'] === 'en_attente'): ?>
                                <a href="?id=<?= $cmd['id'] ?>&statut=confirmee&filtre=<?= $filtre ?>"
                                   class="btn btn-sm" style="background:#d1e7dd;color:#0f5132;border:none;" title="Confirmer">
                                    <i class="fas fa-check"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($cmd['statut'] === 'confirmee'): ?>
                                <a href="?id=<?= $cmd['id'] ?>&statut=livree&filtre=<?= $filtre ?>"
                                   class="btn btn-sm" style="background:#cfe2ff;color:#084298;border:none;" title="Marquer livrée">
                                    <i class="fas fa-truck"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!in_array($cmd['statut'], ['annulee', 'livree'])): ?>
                                <a href="?id=<?= $cmd['id'] ?>&statut=annulee&filtre=<?= $filtre ?>"
                                   onclick="return confirm('Annuler cette commande ?')"
                                   class="btn btn-sm" style="background:#f8d7da;color:#842029;border:none;" title="Annuler">
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