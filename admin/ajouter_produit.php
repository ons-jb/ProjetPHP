<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /Veterinaire/auth/login.php');
    exit;
}

$erreur = '';
$edit   = null;
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ── Charger produit si modification ─────────────────────────────────────────
if ($edit_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit = $stmt->fetch();
    if (!$edit) {
        header('Location: /Veterinaire/admin/produits.php');
        exit;
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

// ── Traitement formulaire ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom         = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $prix        = (float) $_POST['prix'];
    $stock       = (int) $_POST['stock'];
    $categorie   = (int) $_POST['categories_id'];

    if (empty($nom) || $prix <= 0) {
        $erreur = "Le nom et le prix sont obligatoires.";
    } else {
        $photo_nom = $edit['photo'] ?? null;

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
            } else {
                $stmt = $pdo->prepare("INSERT INTO produits (nom, DESCRIPTION, prix, stock, categories_id, photo) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $description, $prix, $stock, $categorie, $photo_nom]);
            }
            header('Location: /Veterinaire/admin/produits.php');
            exit;
        }
    }
}
require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header vert">
                <i class="fas fa-box me-2"></i>
                <?= $edit_id > 0 ? 'Modifier le produit' : 'Ajouter un produit' ?>
            </div>
            <div class="card-body">

                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nom du produit *</label>
                            <input type="text" name="nom" class="form-control"
                                   value="<?= htmlspecialchars($edit['nom'] ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($edit['DESCRIPTION'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prix (DT) *</label>
                            <input type="number" name="prix" class="form-control"
                                   step="0.01" min="0"
                                   value="<?= htmlspecialchars($edit['prix'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stock *</label>
                            <input type="number" name="stock" class="form-control"
                                   min="0"
                                   value="<?= htmlspecialchars($edit['stock'] ?? 0) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catégorie</label>
                            <select name="categories_id" class="form-select">
                                <option value="">-- Sans catégorie --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"
                                        <?= ($edit['categories_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">
                                Photo du produit
                                <?= $edit_id === 0 ? '' : '(laisser vide pour garder l\'actuelle)' ?>
                            </label>
                            <?php if (!empty($edit['photo'])): ?>
                                <div class="mb-2">
                                    <img src="/Veterinaire/public/uploads/<?= htmlspecialchars($edit['photo']) ?>"
                                         style="height:100px; border-radius:8px; object-fit:cover;">
                                    <small class="text-muted ms-2">Photo actuelle</small>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="photo" class="form-control"
                                   accept="image/*" onchange="aperçuPhoto(this)">
                            <img id="apercu_photo" src=""
                                 style="display:none; width:100%; height:160px;
                                        object-fit:cover; border-radius:8px; margin-top:10px;">
                            <small class="text-muted">Formats : jpg, png, webp — max 2MB</small>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <a href="produits.php" class="btn btn-outline-vert w-100">
                            <i class="fas fa-arrow-left me-1"></i> Retour
                        </a>
                        <button type="submit" class="btn btn-vert w-100">
                            <i class="fas fa-save me-1"></i>
                            <?= $edit_id > 0 ? 'Enregistrer' : 'Ajouter' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function aperçuPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('apercu_photo');
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>