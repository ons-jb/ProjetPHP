<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/header.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /Veterinaire/auth/login.php');
    exit;
}

$succes = '';
$erreur = '';

// ── Supprimer un animal ───────────────────────────────────────────────────────
if (isset($_GET['supprimer'])) {
    $id = (int) $_GET['supprimer'];
    try {
        // Récupérer la photo pour la supprimer
        $stmt = $pdo->prepare("SELECT photo FROM animaux WHERE id = ?");
        $stmt->execute([$id]);
        $animal = $stmt->fetch();
        if ($animal && $animal['photo'] && file_exists('../public/uploads/' . $animal['photo'])) {
            unlink('../public/uploads/' . $animal['photo']);
        }
        $stmt = $pdo->prepare("DELETE FROM animaux WHERE id = ?");
        $stmt->execute([$id]);
        $succes = "Animal supprimé avec succès.";
    } catch (PDOException $e) {
        $erreur = "Impossible de supprimer cet animal (rendez-vous liés existants).";
    }
}

// ── Recherche / filtre ────────────────────────────────────────────────────────
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filtre_espece = isset($_GET['espece']) ? $_GET['espece'] : '';

$where = "WHERE 1=1";
$params = [];

if ($search !== '') {
    $where .= " AND (a.nom LIKE ? OR u.nom LIKE ? OR u.prenom LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like]);
}
if ($filtre_espece !== '') {
    $where .= " AND a.espece = ?";
    $params[] = $filtre_espece;
}

$stmt = $pdo->prepare("
    SELECT a.*, 
           u.nom as owner_nom, u.prenom as owner_prenom, u.telephone,
           COUNT(r.id) as nb_rdv
    FROM animaux a
    LEFT JOIN utilisateurs u ON a.user_id = u.id
    LEFT JOIN rendez_vous r ON r.animal_id = a.id
    $where
    GROUP BY a.id
    ORDER BY a.created_at DESC
");
$stmt->execute($params);
$animaux = $stmt->fetchAll();

// Compteurs
$nb_total   = $pdo->query("SELECT COUNT(*) FROM animaux")->fetchColumn();
$especes    = $pdo->query("SELECT espece, COUNT(*) as nb FROM animaux GROUP BY espece ORDER BY nb DESC")->fetchAll();
$nb_ce_mois = $pdo->query("SELECT COUNT(*) FROM animaux WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();

$icones_espece = ['Chien'=>'🐶','Chat'=>'🐱','Lapin'=>'🐰','Oiseau'=>'🐦','Poisson'=>'🐟'];
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-paw text-vert me-2"></i>Gestion des animaux</h2>
        <p>Liste de tous les animaux enregistrés sur la plateforme</p>
    </div>
    <a href="index.php" class="btn btn-outline-vert">
        <i class="fas fa-arrow-left me-1"></i> Dashboard
    </a>
</div>

<?php if ($succes): ?>
    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
<?php endif; ?>
<?php if ($erreur): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<!-- Compteurs -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-vert">
            <div class="chiffre"><?= $nb_total ?></div>
            <div class="label"><i class="fas fa-paw me-1"></i> Total animaux</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-jaune">
            <div class="chiffre"><?= $nb_ce_mois ?></div>
            <div class="label"><i class="fas fa-plus me-1"></i> Ajoutés ce mois</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background:#e3f2fd;">
            <div class="chiffre" style="color:#1565c0;"><?= count($especes) ?></div>
            <div class="label" style="color:#1565c0;"><i class="fas fa-list me-1"></i> Espèces différentes</div>
        </div>
    </div>
</div>

<!-- Répartition espèces -->
<?php if (!empty($especes)): ?>
<div class="d-flex gap-2 flex-wrap mb-3">
    <?php foreach ($especes as $esp): ?>
        <span style="background:#f0f9f0;border:1px solid #c8e6c9;color:#2e7d32;padding:4px 12px;border-radius:20px;font-size:0.85rem;font-weight:600;">
            <?= $icones_espece[$esp['espece']] ?? '🐾' ?> <?= htmlspecialchars($esp['espece']) ?> (<?= $esp['nb'] ?>)
        </span>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Recherche & Filtres -->
<form method="GET" class="mb-3 d-flex gap-2 flex-wrap">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
           placeholder="Rechercher par nom animal ou propriétaire..."
           class="form-control" style="max-width:350px;">
    <select name="espece" class="form-select" style="max-width:180px;">
        <option value="">Toutes les espèces</option>
        <?php foreach ($especes as $esp): ?>
            <option value="<?= htmlspecialchars($esp['espece']) ?>" <?= $filtre_espece === $esp['espece'] ? 'selected' : '' ?>>
                <?= $icones_espece[$esp['espece']] ?? '🐾' ?> <?= htmlspecialchars($esp['espece']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-vert">
        <i class="fas fa-search me-1"></i> Filtrer
    </button>
    <?php if ($search || $filtre_espece): ?>
        <a href="animaux.php" class="btn btn-outline-vert">Réinitialiser</a>
    <?php endif; ?>
</form>

<!-- Table animaux -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($animaux)): ?>
            <div class="text-center py-5">
                <div style="font-size:3rem;">🐾</div>
                <p class="text-muted mt-2">Aucun animal trouvé</p>
            </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Animal</th>
                        <th>Espèce / Race</th>
                        <th>Âge</th>
                        <th>Propriétaire</th>
                        <th>RDV</th>
                        <th>Ajouté le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($animaux as $animal): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($animal['photo'] && file_exists('../public/uploads/' . $animal['photo'])): ?>
                                    <img src="/Veterinaire/public/uploads/<?= htmlspecialchars($animal['photo']) ?>"
                                         style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #e8f5e9;">
                                <?php else: ?>
                                    <div style="width:40px;height:40px;border-radius:50%;background:#e8f5e9;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                                        <?= $icones_espece[$animal['espece']] ?? '🐾' ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-700"><?= htmlspecialchars($animal['nom']) ?></div>
                                    <small class="text-muted">#<?= $animal['id'] ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="background:#e8f5e9;color:#2e7d32;padding:3px 10px;border-radius:20px;font-size:0.82rem;font-weight:600;">
                                <?= $icones_espece[$animal['espece']] ?? '🐾' ?> <?= htmlspecialchars($animal['espece']) ?>
                            </span>
                            <?php if ($animal['race']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($animal['race']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($animal['age']): ?>
                                <span class="fw-600"><?= $animal['age'] ?> ans</span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-700"><?= htmlspecialchars($animal['owner_prenom'] . ' ' . $animal['owner_nom']) ?></div>
                            <?php if ($animal['telephone']): ?>
                                <small class="text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($animal['telephone']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="background:#e3f2fd;color:#1565c0;padding:3px 10px;border-radius:20px;font-weight:600;font-size:0.85rem;">
                                📅 <?= $animal['nb_rdv'] ?>
                            </span>
                        </td>
                        <td>
                            <small class="text-muted"><?= date('d/m/Y', strtotime($animal['created_at'])) ?></small>
                        </td>
                        <td>
                            <a href="?supprimer=<?= $animal['id'] ?>"
                               onclick="return confirm('Supprimer l\'animal <?= htmlspecialchars($animal['nom']) ?> ?')"
                               class="btn btn-sm"
                               style="background:#f8d7da;color:#842029;border:none;"
                               title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </a>
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