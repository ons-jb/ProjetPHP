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

// ── Suppression ──────────────────────────────────────────────────────────────
if (isset($_GET['supprimer'])) {
    $id = (int) $_GET['supprimer'];
    $stmt = $pdo->prepare("SELECT photo FROM produits WHERE id = ?");
    $stmt->execute([$id]);
    $produit = $stmt->fetch();
    if ($produit) {
        if ($produit['photo'] && file_exists('../public/uploads/' . $produit['photo'])) {
            unlink('../public/uploads/' . $produit['photo']);
        }
        $pdo->prepare("DELETE FROM produits WHERE id = ?")->execute([$id]);
        $succes = "Produit supprimé avec succès.";
    }
}

// ── Traitement formulaire ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom         = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $prix        = (float) $_POST['prix'];
    $stock       = (int) $_POST['stock'];
    $categorie   = (int) $_POST['categories_id'];
    $edit_id     = (int) $_POST['edit_id'];

    if (empty($nom) || $prix <= 0) {
        $erreur = "Le nom et le prix sont obligatoires.";
    } else {
        // ── Upload photo ──
        $photo_nom = $_POST['photo_actuelle'] ?? null;

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $extensions_ok = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $extensions_ok)) {
                $erreur = "Format non accepté (jpg, png, webp).";
            } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
                $erreur = "Photo trop lourde (max 2MB).";
            } else {
                if ($photo_nom && file_exists('../public/uploads/' . $photo_nom)) {
                    unlink('../public/uploads/' . $photo_nom);
                }
                $photo_nom = uniqid('produit_') . '.' . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], '../public/uploads/' . $photo_nom);
            }
        }

        if (empty($erreur)) {
            if ($edit_id > 0) {
                $stmt = $pdo->prepare("UPDATE produits SET nom=?, DESCRIPTION=?, prix=?, stock=?, categories_id=?, photo=? WHERE id=?");
                $stmt->execute([$nom, $description, $prix, $stock, $categorie, $photo_nom, $edit_id]);
                $succes = "Produit modifié avec succès.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO produits (nom, DESCRIPTION, prix, stock, categories_id, photo) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $description, $prix, $stock, $categorie, $photo_nom]);
                $succes = "Produit ajouté avec succès.";
            }
        }
    }
}

// ── Récupérer produits ───────────────────────────────────────────────────────
$produits = $pdo->query("
    SELECT p.*, c.nom as categorie_nom
    FROM produits p
    LEFT JOIN categories c ON p.categories_id = c.id
    ORDER BY p.created_at DESC
")->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

// ── Produit à modifier ───────────────────────────────────────────────────────
$edit = null;
if (isset($_GET['modifier'])) {
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
    $stmt->execute([(int)$_GET['modifier']]);
    $edit = $stmt->fetch();
}
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-store text-vert me-2"></i>Gestion des produits</h2>
        <p>Ajouter, modifier et supprimer les produits du catalogue</p>
    </div>
    <a href="ajouter_produit.php" class="btn btn-vert">
        <i class="fas fa-plus me-1"></i> Ajouter un produit
    </a>
</div>

<?php if ($succes): ?>
    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
<?php endif; ?>
<?php if ($erreur): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<!-- Table produits -->
<div class="card">
    <div class="card-header vert-clair">
        <i class="fas fa-list me-2"></i>
        Liste des produits (<?= count($produits) ?>)
    </div>
    <div class="card-body p-0">
        <?php if (empty($produits)): ?>
            <div class="text-center py-5">
                <div style="font-size:3rem;">📦</div>
                <p class="text-muted mt-2">Aucun produit dans le catalogue</p>
            </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produits as $p): ?>
                    <tr>
                        <td>
                            <?php if ($p['photo']): ?>
                                <img src="/Veterinaire/public/uploads/<?= htmlspecialchars($p['photo']) ?>"
                                     style="width:45px; height:45px; border-radius:6px; object-fit:cover;">
                            <?php else: ?>
                                <div style="width:45px; height:45px; background:#f0faf4;
                                            border-radius:6px; display:flex; align-items:center;
                                            justify-content:center;">💊</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-700"><?= htmlspecialchars($p['nom']) ?></div>
                            <?php if ($p['DESCRIPTION']): ?>
                                <small class="text-muted">
                                    <?= htmlspecialchars(substr($p['DESCRIPTION'], 0, 50)) ?>...
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="background:#f0faf4; color:#2d6a4f; padding:3px 8px;
                                         border-radius:20px; font-size:0.8rem; font-weight:600;">
                                <?= htmlspecialchars($p['categorie_nom'] ?? 'Non classé') ?>
                            </span>
                        </td>
                        <td class="fw-700 text-vert"><?= number_format($p['prix'], 2) ?> DT</td>
                        <td>
                            <span style="color:<?= $p['stock'] < 5 ? '#e63946' : '#2d6a4f' ?>; font-weight:600;">
                                <?= $p['stock'] ?>
                                <?php if ($p['stock'] < 5): ?>
                                    <small>(faible)</small>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="ajouter_produit.php?id=<?= $p['id'] ?>"
                                   class="btn btn-outline-vert btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?supprimer=<?= $p['id'] ?>"
                                   onclick="return confirm('Supprimer <?= htmlspecialchars($p['nom']) ?> ?')"
                                   class="btn btn-rose btn-sm">
                                    <i class="fas fa-trash"></i>
                                </a>
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