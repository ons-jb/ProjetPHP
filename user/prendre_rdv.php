<?php
require_once '../includes/header.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Veterinaire/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$erreur  = '';

// ── Récupérer les animaux de l'utilisateur ───────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM animaux WHERE user_id = ?");
$stmt->execute([$user_id]);
$animaux = $stmt->fetchAll();

// ── Récupérer les services disponibles ──────────────────────────────────────
$services = $pdo->query("SELECT * FROM services ORDER BY nom")->fetchAll();

// ── Traitement formulaire ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animal_id  = (int) $_POST['animal_id'];
    $service_id = (int) $_POST['service_id'];
    $date       = trim($_POST['date']);
    $heure      = trim($_POST['heure']);
    $motif      = trim($_POST['motif']);

    if (!$animal_id || !$service_id || empty($date) || empty($heure)) {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } elseif ($date < date('Y-m-d')) {
        $erreur = "La date ne peut pas être dans le passé.";
    } else {
        // Vérifier que l'animal appartient à l'utilisateur
        $stmt = $pdo->prepare("SELECT id FROM animaux WHERE id = ? AND user_id = ?");
        $stmt->execute([$animal_id, $user_id]);
        if (!$stmt->fetch()) {
            $erreur = "Animal invalide.";
        } else {
            // Vérifier créneau pas déjà pris
            $stmt = $pdo->prepare("SELECT id FROM rendez_vous WHERE date = ? AND heure = ? AND statut != 'annule'");
            $stmt->execute([$date, $heure]);
            if ($stmt->fetch()) {
                $erreur = "Ce créneau est déjà pris, veuillez choisir un autre horaire.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO rendez_vous 
                    (date, heure, motif, statut, user_id, animal_id, service_id) 
                    VALUES (?, ?, ?, 'en_attente', ?, ?, ?)");
                $stmt->execute([$date, $heure, $motif, $user_id, $animal_id, $service_id]);
                header('Location: /Veterinaire/user/rendez-vous.php?succes=1');
                exit;
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">

            <div class="card-header vert">
                <i class="fas fa-calendar-plus me-2"></i>
                Prendre un rendez-vous
            </div>

            <div class="card-body">

                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>

                <?php if (empty($animaux)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Vous devez d'abord 
                        <a href="ajouter_animal.php" class="alert-link">ajouter un animal</a> 
                        avant de prendre un rendez-vous.
                    </div>
                <?php elseif (empty($services)): ?>
                    <div class="alert alert-danger">
                        Aucun service disponible pour le moment.
                    </div>
                <?php else: ?>

                <form method="POST">

                    <!-- Animal -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-paw me-1 text-vert"></i> Animal *
                        </label>
                        <select name="animal_id" class="form-select" required
                                onchange="afficherPhoto(this)">
                            <option value="">-- Choisir votre animal --</option>
                            <?php foreach ($animaux as $a): ?>
                                <option value="<?= $a['id'] ?>"
                                        data-photo="<?= htmlspecialchars($a['photo'] ?? '') ?>"
                                        data-espece="<?= htmlspecialchars($a['espece']) ?>">
                                    <?php
                                    $icones = ['Chien'=>'🐶','Chat'=>'🐱','Lapin'=>'🐰','Oiseau'=>'🐦','Poisson'=>'🐟'];
                                    echo ($icones[$a['espece']] ?? '🐾') . ' ' . htmlspecialchars($a['nom']);
                                    if ($a['race']) echo ' — ' . htmlspecialchars($a['race']);
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <!-- Aperçu animal sélectionné -->
                        <div id="animal_apercu" style="display:none; margin-top:10px;
                             padding:10px 14px; background:#f0faf4; border-radius:8px;
                             display:none; align-items:center; gap:12px;">
                            <img id="animal_photo" src="" style="width:48px; height:48px;
                                 border-radius:50%; object-fit:cover; border:2px solid #52b788;">
                            <span id="animal_nom" style="font-weight:600; color:#2d6a4f;"></span>
                        </div>
                    </div>

                    <!-- Service -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-stethoscope me-1 text-vert"></i> Service *
                        </label>
                        <select name="service_id" class="form-select" required
                                onchange="afficherPrix(this)">
                            <option value="">-- Choisir un service --</option>
                            <?php foreach ($services as $s): ?>
                                <option value="<?= $s['id'] ?>"
                                        data-prix="<?= $s['prix'] ?>">
                                    <?= htmlspecialchars($s['nom']) ?>
                                    — <?= number_format($s['prix'], 2) ?> DT
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <!-- Prix affiché -->
                        <div id="prix_apercu" style="display:none; margin-top:8px;
                             padding:8px 14px; background:#f0faf4; border-radius:8px;">
                            <i class="fas fa-tag me-1 text-vert"></i>
                            Prix de la consultation : 
                            <strong id="prix_valeur" class="text-vert"></strong>
                        </div>
                    </div>

                    <!-- Date et Heure -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-calendar me-1 text-vert"></i> Date *
                            </label>
                            <input type="date" name="date" class="form-control"
                                   min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-clock me-1 text-vert"></i> Heure *
                            </label>
                            <select name="heure" class="form-select" required>
                                <option value="">-- Choisir --</option>
                                <?php
                                $creneaux = ['08:00','08:30','09:00','09:30','10:00','10:30',
                                             '11:00','11:30','14:00','14:30','15:00','15:30',
                                             '16:00','16:30','17:00','17:30'];
                                foreach ($creneaux as $c):
                                ?>
                                    <option value="<?= $c ?>"><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Motif -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-comment-medical me-1 text-vert"></i>
                            Motif de la consultation
                        </label>
                        <textarea name="motif" class="form-control" rows="3"
                                  placeholder="Décrivez brièvement le problème ou la raison de la visite..."></textarea>
                    </div>

                    <!-- Boutons -->
                    <div class="d-flex gap-2">
                        <a href="rendez-vous.php" class="btn btn-outline-vert w-100">
                            <i class="fas fa-arrow-left me-1"></i> Retour
                        </a>
                        <button type="submit" class="btn btn-vert w-100">
                            <i class="fas fa-calendar-check me-1"></i> Confirmer le RDV
                        </button>
                    </div>

                </form>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
function afficherPhoto(select) {
    const option  = select.options[select.selectedIndex];
    const photo   = option.dataset.photo;
    const espece  = option.dataset.espece;
    const nom     = option.text;
    const apercu  = document.getElementById('animal_apercu');
    const imgEl   = document.getElementById('animal_photo');
    const nomEl   = document.getElementById('animal_nom');

    const icones  = {Chien:'🐶', Chat:'🐱', Lapin:'🐰', Oiseau:'🐦', Poisson:'🐟'};

    if (select.value) {
        apercu.style.display = 'flex';
        nomEl.textContent    = nom;
        if (photo) {
            imgEl.src          = '/Veterinaire/public/uploads/' + photo;
            imgEl.style.display = 'block';
        } else {
            imgEl.style.display = 'none';
        }
    } else {
        apercu.style.display = 'none';
    }
}

function afficherPrix(select) {
    const option = select.options[select.selectedIndex];
    const prix   = option.dataset.prix;
    const apercu = document.getElementById('prix_apercu');
    const valeur = document.getElementById('prix_valeur');

    if (select.value) {
        apercu.style.display = 'block';
        valeur.textContent   = parseFloat(prix).toFixed(2) + ' DT';
    } else {
        apercu.style.display = 'none';
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>