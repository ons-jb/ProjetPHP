<?php
require_once '../includes/header.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Veterinaire/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$succes  = '';

// ── Suppression ──────────────────────────────────────────────────────────────
if (isset($_GET['supprimer'])) {
    $id = (int) $_GET['supprimer'];
    $stmt = $pdo->prepare("SELECT id, photo FROM animaux WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $animal = $stmt->fetch();
    if ($animal) {
        if ($animal['photo'] && file_exists('../public/uploads/' . $animal['photo'])) {
            unlink('../public/uploads/' . $animal['photo']);
        }
        $pdo->prepare("DELETE FROM animaux WHERE id = ?")->execute([$id]);
        $succes = "Animal supprimé avec succès.";
    }
}

// ── Récupérer les animaux ────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM animaux WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$animaux = $stmt->fetchAll();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-paw text-vert me-2"></i>Mes animaux</h2>
        <p>Gérez les profils de vos animaux de compagnie</p>
    </div>
    <a href="ajouter_animal.php" class="btn btn-vert">
        <i class="fas fa-plus me-1"></i> Ajouter un animal
    </a>
</div>

<?php if ($succes): ?>
    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
<?php endif; ?>

<!-- Liste -->
<?php if (empty($animaux)): ?>
    <div class="text-center py-5">
        <div style="font-size:3.5rem; margin-bottom:16px;">🐾</div>
        <h5 class="fw-700">Aucun animal enregistré</h5>
        <p class="text-muted mb-3">Ajoutez votre premier animal pour commencer</p>
        <a href="ajouter_animal.php" class="btn btn-vert">
            <i class="fas fa-plus me-1"></i> Ajouter un animal
        </a>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($animaux as $a): ?>
        <div class="col-md-4 col-sm-6">
            <div class="animal-card">
                <?php if ($a['photo']): ?>
                    <img src="/Veterinaire/public/uploads/<?= htmlspecialchars($a['photo']) ?>"
                         alt="<?= htmlspecialchars($a['nom']) ?>">
                <?php else: ?>
                    <div style="height:160px; background:#f0faf4; display:flex;
                                align-items:center; justify-content:center; font-size:3rem;">
                        <?php
                        $icones = [
                            'chien'=>'🐶', 'chat'=>'🐱',
                            'lapin'=>'🐰', 'oiseau'=>'🐦', 'poisson'=>'🐟'
                        ];
                        echo $icones[strtolower($a['espece'])] ?? '🐾';
                        ?>
                    </div>
                <?php endif; ?>
                <div class="animal-info">
                    <h6><?= htmlspecialchars($a['nom']) ?></h6>
                    <small>
                        <i class="fas fa-tag me-1"></i>
                        <?= htmlspecialchars($a['espece']) ?>
                        <?php if ($a['race']): ?>
                            — <?= htmlspecialchars($a['race']) ?>
                        <?php endif; ?>
                    </small><br>
                    <?php if ($a['age']): ?>
                        <small>
                            <i class="fas fa-birthday-cake me-1"></i>
                            <?= $a['age'] ?> ans
                        </small>
                    <?php endif; ?>
                    <div class="mt-3 d-flex gap-2">
                        <a href="ajouter_animal.php?id=<?= $a['id'] ?>"
                           class="btn btn-outline-vert btn-sm w-100">
                            <i class="fas fa-edit me-1"></i>Modifier
                        </a>
                        <a href="?supprimer=<?= $a['id'] ?>"
                           onclick="return confirm('Supprimer <?= htmlspecialchars($a['nom']) ?> ?')"
                           class="btn btn-rose btn-sm w-100">
                            <i class="fas fa-trash me-1"></i>Supprimer
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>