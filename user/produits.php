<?php
require_once '../includes/header.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Veterinaire/auth/login.php');
    exit;
}

// ── Initialiser le panier ────────────────────────────────────────────────────
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// ── Ajouter au panier ────────────────────────────────────────────────────────
if (isset($_GET['ajouter'])) {
    $id  = (int) $_GET['ajouter'];
    $qte = isset($_GET['qte']) ? max(1, (int)$_GET['qte']) : 1;

    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ? AND stock > 0");
    $stmt->execute([$id]);
    $produit = $stmt->fetch();

    if ($produit) {
        if (isset($_SESSION['panier'][$id])) {
            $_SESSION['panier'][$id]['quantite'] += $qte;
        } else {
            $_SESSION['panier'][$id] = [
                'id'       => $produit['id'],
                'nom'      => $produit['nom'],
                'prix'     => $produit['prix'],
                'photo'    => $produit['photo'],
                'quantite' => $qte
            ];
        }
        $succes = "Produit ajouté au panier !";
    }
}

// ── Filtrage par catégorie ───────────────────────────────────────────────────
$categorie_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;

$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

if ($categorie_id > 0) {
   $stmt = $pdo->prepare("SELECT p.*, c.nom as categorie_nom 
                       FROM produits p 
                       LEFT JOIN categories c ON p.categories_id = c.id 
                       WHERE p.categories_id = ? AND p.stock > 0
                       ORDER BY p.nom");
    $stmt->execute([$categorie_id]);
} else {
    $stmt = $pdo->query("SELECT p.*, c.nom as categorie_nom 
                     FROM produits p 
                     LEFT JOIN categories c ON p.categories_id = c.id 
                     WHERE p.stock > 0
                     ORDER BY p.nom");
}
$produits = $stmt->fetchAll();

// Nombre articles panier
$nb_panier = array_sum(array_column($_SESSION['panier'], 'quantite'));
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-store text-vert me-2"></i>Nos produits</h2>
        <p>Médicaments, accessoires et soins pour vos animaux</p>
    </div>
    <a href="panier.php" class="btn btn-vert position-relative">
        <i class="fas fa-shopping-cart me-1"></i> Panier
        <?php if ($nb_panier > 0): ?>
            <span style="position:absolute; top:-8px; right:-8px;
                         background:#e63946; color:white; border-radius:50%;
                         width:20px; height:20px; font-size:0.7rem;
                         display:flex; align-items:center; justify-content:center;
                         font-weight:700;">
                <?= $nb_panier ?>
            </span>
        <?php endif; ?>
    </a>
</div>

<?php if (isset($succes)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($succes) ?>
        <a href="panier.php" class="alert-link ms-2">Voir le panier →</a>
    </div>
<?php endif; ?>

<!-- Filtres catégories -->
<?php if (!empty($categories)): ?>
<div class="mb-4 d-flex gap-2 flex-wrap">
    <a href="produits.php"
       class="btn btn-sm <?= $categorie_id === 0 ? 'btn-vert' : 'btn-outline-vert' ?>">
        Tous
    </a>
    <?php foreach ($categories as $cat): ?>
        <a href="?categorie=<?= $cat['id'] ?>"
           class="btn btn-sm <?= $categorie_id === $cat['id'] ? 'btn-vert' : 'btn-outline-vert' ?>">
            <?= htmlspecialchars($cat['nom']) ?>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Liste produits -->
<?php if (empty($produits)): ?>
    <div class="text-center py-5">
        <div style="font-size:3.5rem; margin-bottom:16px;">📦</div>
        <h5 class="fw-700">Aucun produit disponible</h5>
        <p class="text-muted">Revenez bientôt !</p>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($produits as $p): ?>
        <div class="col-md-4 col-sm-6">
            <div class="card h-100">
                <!-- Photo -->
                <?php if ($p['photo']): ?>
                    <img src="/Veterinaire/public/uploads/<?= htmlspecialchars($p['photo']) ?>"
                         style="width:100%; height:180px; object-fit:cover;
                                border-radius:10px 10px 0 0;">
                <?php else: ?>
                    <div style="height:180px; background:#f0faf4; display:flex;
                                align-items:center; justify-content:center;
                                font-size:3rem; border-radius:10px 10px 0 0;">
                        💊
                    </div>
                <?php endif; ?>

                <div class="card-body d-flex flex-column">
                    <!-- Catégorie -->
                    <?php if ($p['categorie_nom']): ?>
                        <small style="color:#52b788; font-weight:600; font-size:0.75rem;
                                      text-transform:uppercase; letter-spacing:0.5px;">
                            <?= htmlspecialchars($p['categorie_nom']) ?>
                        </small>
                    <?php endif; ?>

                    <h6 class="fw-700 mt-1 mb-1"><?= htmlspecialchars($p['nom']) ?></h6>

                    <?php if ($p['DESCRIPTION']): ?>
    <p class="text-muted mb-2" style="font-size:0.85rem; flex-grow:1;">
        <?= htmlspecialchars(substr($p['DESCRIPTION'], 0, 80)) ?>
        <?= strlen($p['DESCRIPTION']) > 80 ? '...' : '' ?>
    </p>
<?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2"
                         style="border-top:1px solid #f0f0f0;">
                        <strong class="text-vert" style="font-size:1.1rem;">
                            <?= number_format($p['prix'], 2) ?> DT
                        </strong>
                        <small class="text-muted">
                            Stock : <?= $p['stock'] ?>
                        </small>
                    </div>

                    <a href="?ajouter=<?= $p['id'] ?>"
                       class="btn btn-vert btn-sm mt-2 w-100">
                        <i class="fas fa-cart-plus me-1"></i> Ajouter au panier
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>