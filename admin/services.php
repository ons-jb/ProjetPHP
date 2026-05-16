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

// ── Supprimer ─────────────────────────────────────────────────────────────────
if (isset($_GET['supprimer'])) {
    $id = (int) $_GET['supprimer'];
    try {
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $succes = "Service supprimé avec succès.";
    } catch (PDOException $e) {
        $erreur = "Impossible de supprimer ce service (rendez-vous liés existants).";
    }
}

// ── Ajouter ───────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $nom  = trim($_POST['nom'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);

    if ($nom && $prix > 0) {
        $stmt = $pdo->prepare("INSERT INTO services (nom, DESCRIPTION, prix) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $desc, $prix]);
        $succes = "Service ajouté avec succès.";
    } else {
        $erreur = "Nom et prix sont obligatoires.";
    }
}

// ── Modifier ──────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier') {
    $id   = (int) $_POST['id'];
    $nom  = trim($_POST['nom'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);

    if ($nom && $prix > 0) {
        $stmt = $pdo->prepare("UPDATE services SET nom=?, DESCRIPTION=?, prix=? WHERE id=?");
        $stmt->execute([$nom, $desc, $prix, $id]);
        $succes = "Service modifié avec succès.";
    } else {
        $erreur = "Nom et prix sont obligatoires.";
    }
}

$services = $pdo->query("
    SELECT s.*, COUNT(r.id) as nb_rdv
    FROM services s
    LEFT JOIN rendez_vous r ON r.service_id = s.id
    GROUP BY s.id
    ORDER BY s.id DESC
")->fetchAll();

$nb_services = count($services);
$revenu_services = $pdo->query("
    SELECT SUM(s.prix) FROM rendez_vous r
    JOIN services s ON r.service_id = s.id
    WHERE r.statut = 'confirme'
")->fetchColumn() ?? 0;
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-stethoscope text-vert me-2"></i>Gestion des services</h2>
        <p>Ajoutez et gérez les consultations et soins proposés</p>
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
    <div class="col-md-6">
        <div class="stat-card stat-vert">
            <div class="chiffre"><?= $nb_services ?></div>
            <div class="label"><i class="fas fa-stethoscope me-1"></i> Services disponibles</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card" style="background:#1a1a2e;color:#fff;">
            <div class="chiffre"><?= number_format($revenu_services, 2) ?> DT</div>
            <div class="label"><i class="fas fa-coins me-1"></i> Revenus (RDV confirmés)</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Formulaire ajout -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header vert">
                <i class="fas fa-plus me-2"></i> Ajouter un service
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="ajouter">
                    <div class="mb-3">
                        <label class="form-label fw-600">Nom du service *</label>
                        <input type="text" name="nom" class="form-control"
                               placeholder="Ex: Consultation générale" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Description du service..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Prix (DT) *</label>
                        <input type="number" name="prix" class="form-control"
                               step="0.01" min="0" placeholder="0.00" required>
                    </div>
                    <button type="submit" class="btn btn-vert w-100">
                        <i class="fas fa-plus me-1"></i> Ajouter
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Liste services -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-body p-0">
                <?php if (empty($services)): ?>
                    <div class="text-center py-5">
                        <div style="font-size:3rem;">🩺</div>
                        <p class="text-muted mt-2">Aucun service enregistré</p>
                    </div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Prix</th>
                                <th>RDV</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $svc): ?>
                            <tr>
                                <td>
                                    <div class="fw-700"><?= htmlspecialchars($svc['nom']) ?></div>
                                    <?php if ($svc['DESCRIPTION']): ?>
                                        <small class="text-muted"><?= htmlspecialchars(substr($svc['DESCRIPTION'], 0, 60)) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-700 text-vert"><?= number_format($svc['prix'], 2) ?> DT</span>
                                </td>
                                <td>
                                    <span style="background:#e3f2fd;color:#1565c0;padding:3px 10px;border-radius:20px;font-weight:600;font-size:0.85rem;">
                                        📅 <?= $svc['nb_rdv'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- Bouton modifier (modal) -->
                                        <button type="button" class="btn btn-sm"
                                                style="background:#d1e7dd;color:#0f5132;border:none;"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalModifier<?= $svc['id'] ?>"
                                                title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?supprimer=<?= $svc['id'] ?>"
                                           onclick="return confirm('Supprimer le service <?= htmlspecialchars($svc['nom']) ?> ?')"
                                           class="btn btn-sm"
                                           style="background:#f8d7da;color:#842029;border:none;"
                                           title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Modifier -->
                            <div class="modal fade" id="modalModifier<?= $svc['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-edit text-vert me-2"></i>Modifier le service
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="modifier">
                                                <input type="hidden" name="id" value="<?= $svc['id'] ?>">
                                                <div class="mb-3">
                                                    <label class="form-label fw-600">Nom *</label>
                                                    <input type="text" name="nom" class="form-control"
                                                           value="<?= htmlspecialchars($svc['nom']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-600">Description</label>
                                                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($svc['DESCRIPTION'] ?? '') ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-600">Prix (DT) *</label>
                                                    <input type="number" name="prix" class="form-control"
                                                           step="0.01" min="0"
                                                           value="<?= $svc['prix'] ?>" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-vert" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-vert">
                                                    <i class="fas fa-save me-1"></i> Enregistrer
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>