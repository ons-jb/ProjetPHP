<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/header.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Veterinaire/auth/login.php');
    exit;
}

$succes = '';
$erreur = '';
$user_id = $_SESSION['user_id'];

// ── Modifier infos ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier') {
    $nom       = trim($_POST['nom'] ?? '');
    $prenom    = trim($_POST['prenom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email     = trim($_POST['email'] ?? '');

    if ($nom && $prenom && $email) {
        // Vérifier email unique
        $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $check->execute([$email, $user_id]);
        if ($check->fetch()) {
            $erreur = "Cet email est déjà utilisé par un autre compte.";
        } else {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom=?, prenom=?, telephone=?, email=? WHERE id=?");
            $stmt->execute([$nom, $prenom, $telephone, $email, $user_id]);
            $_SESSION['user_nom'] = $nom;
            $succes = "Profil mis à jour avec succès.";
        }
    } else {
        $erreur = "Nom, prénom et email sont obligatoires.";
    }
}

// ── Changer mot de passe ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'password') {
    $ancien  = $_POST['ancien_mdp'] ?? '';
    $nouveau = $_POST['nouveau_mdp'] ?? '';
    $confirm = $_POST['confirm_mdp'] ?? '';

    $stmt = $pdo->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_mdp = $stmt->fetchColumn();

    if (!password_verify($ancien, $user_mdp)) {
        $erreur = "Ancien mot de passe incorrect.";
    } elseif (strlen($nouveau) < 6) {
        $erreur = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
    } elseif ($nouveau !== $confirm) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } else {
        $hash = password_hash($nouveau, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
        $stmt->execute([$hash, $user_id]);
        $succes = "Mot de passe modifié avec succès.";
    }
}

// ── Récupérer données utilisateur ────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// ── Statistiques utilisateur ─────────────────────────────────────────────────
$nb_animaux   = $pdo->prepare("SELECT COUNT(*) FROM animaux WHERE user_id = ?");
$nb_animaux->execute([$user_id]);
$nb_animaux = $nb_animaux->fetchColumn();

$nb_rdv = $pdo->prepare("SELECT COUNT(*) FROM rendez_vous WHERE user_id = ?");
$nb_rdv->execute([$user_id]);
$nb_rdv = $nb_rdv->fetchColumn();

$nb_commandes = $pdo->prepare("SELECT COUNT(*) FROM commandes WHERE user_id = ?");
$nb_commandes->execute([$user_id]);
$nb_commandes = $nb_commandes->fetchColumn();

// ── Derniers RDV ──────────────────────────────────────────────────────────────
$derniers_rdv = $pdo->prepare("
    SELECT r.*, a.nom as animal_nom, a.espece, s.nom as service_nom
    FROM rendez_vous r
    LEFT JOIN animaux a ON r.animal_id = a.id
    LEFT JOIN services s ON r.service_id = s.id
    WHERE r.user_id = ?
    ORDER BY r.date DESC LIMIT 3
");
$derniers_rdv->execute([$user_id]);
$derniers_rdv = $derniers_rdv->fetchAll();

$icones_espece = ['Chien'=>'🐶','Chat'=>'🐱','Lapin'=>'🐰','Oiseau'=>'🐦','Poisson'=>'🐟'];
$badge_styles = [
    'en_attente' => 'background:#fff3cd;color:#856404;border:1px solid #ffc107;',
    'confirme'   => 'background:#d1e7dd;color:#0f5132;border:1px solid #0f5132;',
    'annule'     => 'background:#f8d7da;color:#842029;border:1px solid #842029;',
];
$badge_labels = ['en_attente'=>'⏳ En attente','confirme'=>'✅ Confirmé','annule'=>'❌ Annulé'];
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-user-circle text-vert me-2"></i>Mon profil</h2>
        <p>Consultez et modifiez vos informations personnelles</p>
    </div>
    <a href="dashboard.php" class="btn btn-outline-vert">
        <i class="fas fa-arrow-left me-1"></i> Dashboard
    </a>
</div>

<?php if ($succes): ?>
    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
<?php endif; ?>
<?php if ($erreur): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<!-- Stats rapides -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-vert">
            <div class="chiffre"><?= $nb_animaux ?></div>
            <div class="label"><i class="fas fa-paw me-1"></i> Mes animaux</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-jaune">
            <div class="chiffre"><?= $nb_rdv ?></div>
            <div class="label"><i class="fas fa-calendar me-1"></i> Mes rendez-vous</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background:#e3f2fd;">
            <div class="chiffre" style="color:#1565c0;"><?= $nb_commandes ?></div>
            <div class="label" style="color:#1565c0;"><i class="fas fa-box me-1"></i> Mes commandes</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Formulaire profil -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header vert">
                <i class="fas fa-user-edit me-2"></i> Mes informations
            </div>
            <div class="card-body">
                <!-- Avatar -->
                <div class="text-center mb-4">
                    <div style="width:80px;height:80px;border-radius:50%;background:var(--vert-light);display:flex;align-items:center;justify-content:center;font-weight:800;color:var(--vert);font-size:2rem;margin:0 auto;border:3px solid var(--vert);">
                        <?= strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)) ?>
                    </div>
                    <div class="fw-700 mt-2"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></div>
                    <small class="text-muted">Membre depuis <?= date('d/m/Y', strtotime($user['created_at'])) ?></small>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="modifier">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-600">Prénom *</label>
                            <input type="text" name="prenom" class="form-control"
                                   value="<?= htmlspecialchars($user['prenom']) ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-600">Nom *</label>
                            <input type="text" name="nom" class="form-control"
                                   value="<?= htmlspecialchars($user['nom']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Email *</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Téléphone</label>
                            <input type="text" name="telephone" class="form-control"
                                   value="<?= htmlspecialchars($user['telephone'] ?? '') ?>"
                                   placeholder="Ex: 20 000 000">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-vert w-100">
                                <i class="fas fa-save me-1"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Changer mot de passe + derniers RDV -->
    <div class="col-md-6 d-flex flex-column gap-4">
        <!-- Mot de passe -->
        <div class="card">
            <div class="card-header vert">
                <i class="fas fa-lock me-2"></i> Changer le mot de passe
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="password">
                    <div class="mb-3">
                        <label class="form-label fw-600">Ancien mot de passe</label>
                        <input type="password" name="ancien_mdp" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Nouveau mot de passe</label>
                        <input type="password" name="nouveau_mdp" class="form-control"
                               placeholder="Min. 6 caractères" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Confirmer</label>
                        <input type="password" name="confirm_mdp" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-outline-vert w-100">
                        <i class="fas fa-key me-1"></i> Modifier le mot de passe
                    </button>
                </form>
            </div>
        </div>

        <!-- Derniers RDV -->
        <div class="card">
            <div class="card-header vert d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar me-2"></i> Mes derniers RDV</span>
                <a href="rendez-vous.php" style="color:rgba(255,255,255,0.8);font-size:0.82rem;">Voir tout →</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($derniers_rdv)): ?>
                    <div class="text-center py-3">
                        <p class="text-muted mb-0">Aucun rendez-vous</p>
                        <a href="prendre_rdv.php" class="btn btn-sm btn-vert mt-2">Prendre un RDV</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($derniers_rdv as $rdv): ?>
                    <div style="padding:10px 16px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-weight:600;font-size:0.88rem;">
                                <?= $icones_espece[$rdv['espece']] ?? '🐾' ?>
                                <?= htmlspecialchars($rdv['animal_nom']) ?>
                                <span class="text-muted fw-400">— <?= htmlspecialchars($rdv['service_nom']) ?></span>
                            </div>
                            <small class="text-muted">
                                <?= date('d/m/Y', strtotime($rdv['date'])) ?> à <?= $rdv['heure'] ?>
                            </small>
                        </div>
                        <span style="<?= $badge_styles[$rdv['statut']] ?? '' ?> padding:3px 9px;border-radius:20px;font-size:0.78rem;font-weight:600;white-space:nowrap;">
                            <?= $badge_labels[$rdv['statut']] ?? $rdv['statut'] ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>