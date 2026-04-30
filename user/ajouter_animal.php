<?php
require_once '../includes/header.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Veterinaire/auth/login.php');
    exit;
}

$user_id  = $_SESSION['user_id'];
$erreur   = '';
$animal   = null;
$edit_id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ── Charger animal si modification ──────────────────────────────────────────
if ($edit_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM animaux WHERE id = ? AND user_id = ?");
    $stmt->execute([$edit_id, $user_id]);
    $animal = $stmt->fetch();
    if (!$animal) {
        header('Location: /Veterinaire/user/animaux.php');
        exit;
    }
}

// ── Traitement formulaire ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom']);
    $espece = trim($_POST['espece']);
    $race   = trim($_POST['race']);
    $age    = (int) $_POST['age'];

    if (empty($nom) || empty($espece)) {
        $erreur = "Le nom et l'espèce sont obligatoires.";
    } else {
        $photo_nom = $animal['photo'] ?? null;

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $extensions_ok = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $extensions_ok)) {
                $erreur = "Format non accepté (jpg, png, webp).";
            } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
                $erreur = "Photo trop lourde (max 2MB).";
            } else {
                // Supprimer ancienne photo
                if ($photo_nom && file_exists('../public/uploads/' . $photo_nom)) {
                    unlink('../public/uploads/' . $photo_nom);
                }
                $photo_nom = uniqid('animal_') . '.' . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], '../public/uploads/' . $photo_nom);
            }
        }

        if (empty($erreur)) {
            if ($edit_id > 0) {
                $stmt = $pdo->prepare("UPDATE animaux SET nom=?, espece=?, race=?, age=?, photo=? WHERE id=? AND user_id=?");
                $stmt->execute([$nom, $espece, $race, $age, $photo_nom, $edit_id, $user_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO animaux (nom, espece, race, age, photo, user_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $espece, $race, $age, $photo_nom, $user_id]);
            }
            header('Location: /Veterinaire/user/animaux.php');
            exit;
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">

            <div class="card-header vert">
                <i class="fas fa-paw me-2"></i>
                <?= $edit_id > 0 ? 'Modifier un animal' : 'Ajouter un animal' ?>
            </div>

            <div class="card-body">

                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom *</label>
                            <input type="text" name="nom" class="form-control"
                                   value="<?= htmlspecialchars($animal['nom'] ?? '') ?>"
                                   required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Espèce *</label>
                            <select name="espece" class="form-select" required>
                                <option value="">-- Choisir --</option>
                                <?php
                                $especes = ['Chien'=>'🐶','Chat'=>'🐱','Lapin'=>'🐰','Oiseau'=>'🐦','Poisson'=>'🐟','Autre'=>'🐾'];
                                foreach ($especes as $val => $emoji):
                                    $selected = ($animal['espece'] ?? '') === $val ? 'selected' : '';
                                ?>
                                    <option value="<?= $val ?>" <?= $selected ?>>
                                        <?= $emoji ?> <?= $val ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Race</label>
                            <input type="text" name="race" class="form-control"
                                   placeholder="Optionnel"
                                   value="<?= htmlspecialchars($animal['race'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Âge (ans)</label>
                            <input type="number" name="age" class="form-control"
                                   min="0" max="30"
                                   value="<?= htmlspecialchars($animal['age'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Photo</label>
                            <?php if (!empty($animal['photo'])): ?>
                                <div class="mb-2">
                                    <img src="/Veterinaire/public/uploads/<?= htmlspecialchars($animal['photo']) ?>"
                                         style="height:100px; border-radius:8px; object-fit:cover;">
                                    <small class="text-muted ms-2">Photo actuelle</small>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="photo" class="form-control"
                                   accept="image/*" onchange="aperçuPhoto(this)">
                            <img id="apercu_photo" src=""
                                 style="display:none; width:100%; height:150px;
                                        object-fit:cover; border-radius:8px; margin-top:10px;">
                            <small class="text-muted">Formats : jpg, png, webp — max 2MB</small>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <a href="animaux.php" class="btn btn-outline-vert w-100">
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