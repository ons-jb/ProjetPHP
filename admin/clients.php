<?php
// clients.php placeholder
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/header.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /Veterinaire/auth/login.php');
    exit;
}

$succes = '';
$erreur = '';

// ── Supprimer un client ───────────────────────────────────────────────────────
if (isset($_GET['supprimer'])) {
    $id = (int) $_GET['supprimer'];
    try {
        $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ? AND ROLE = 'client'");
        $stmt->execute([$id]);
        $succes = "Client supprimé avec succès.";
    } catch (PDOException $e) {
        $erreur = "Impossible de supprimer ce client (données liées existantes).";
    }
}

// ── Recherche ────────────────────────────────────────────────────────────────
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT u.*,
               COUNT(DISTINCT r.id) as nb_rdv,
               COUNT(DISTINCT c.id) as nb_commandes,
               COUNT(DISTINCT a.id) as nb_animaux
        FROM utilisateurs u
        LEFT JOIN rendez_vous r ON r.user_id = u.id
        LEFT JOIN commandes c ON c.user_id = u.id
        LEFT JOIN animaux a ON a.user_id = u.id
        WHERE u.ROLE = 'client'
          AND (u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ? OR u.telephone LIKE ?)
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    $like = "%$search%";
    $stmt->execute([$like, $like, $like, $like]);
} else {
    $stmt = $pdo->query("
        SELECT u.*,
               COUNT(DISTINCT r.id) as nb_rdv,
               COUNT(DISTINCT c.id) as nb_commandes,
               COUNT(DISTINCT a.id) as nb_animaux
        FROM utilisateurs u
        LEFT JOIN rendez_vous r ON r.user_id = u.id
        LEFT JOIN commandes c ON c.user_id = u.id
        LEFT JOIN animaux a ON a.user_id = u.id
        WHERE u.ROLE = 'client'
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
}
$clients = $stmt->fetchAll();

$nb_total   = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE ROLE='client'")->fetchColumn();
$nb_ce_mois = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE ROLE='client' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-users text-vert me-2"></i>Gestion des clients</h2>
        <p>Liste et suivi de tous les clients inscrits</p>
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
            <div class="chiffre"><?= $nb_total ?></div>
            <div class="label"><i class="fas fa-users me-1"></i> Total clients</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card stat-jaune">
            <div class="chiffre"><?= $nb_ce_mois ?></div>
            <div class="label"><i class="fas fa-user-plus me-1"></i> Nouveaux ce mois</div>
        </div>
    </div>
</div>

<!-- Recherche -->
<form method="GET" class="mb-3 d-flex gap-2">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
           placeholder="Rechercher par nom, email, téléphone..."
           class="form-control" style="max-width:400px;">
    <button type="submit" class="btn btn-vert">
        <i class="fas fa-search me-1"></i> Rechercher
    </button>
    <?php if ($search): ?>
        <a href="clients.php" class="btn btn-outline-vert">Réinitialiser</a>
    <?php endif; ?>
</form>

<!-- Table clients -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($clients)): ?>
            <div class="text-center py-5">
                <div style="font-size:3rem;">👤</div>
                <p class="text-muted mt-2">Aucun client trouvé</p>
            </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Contact</th>
                        <th>Animaux</th>
                        <th>RDV</th>
                        <th>Commandes</th>
                        <th>Inscrit le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:38px;height:38px;border-radius:50%;background:var(--vert-light);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--vert);font-size:1rem;">
                                    <?= strtoupper(substr($client['prenom'], 0, 1) . substr($client['nom'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-700"><?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?></div>
                                    <small class="text-muted">#<?= $client['id'] ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div><i class="fas fa-envelope me-1 text-muted"></i><?= htmlspecialchars($client['email']) ?></div>
                            <?php if ($client['telephone']): ?>
                                <small class="text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($client['telephone']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="background:#e8f5e9;color:#2e7d32;padding:3px 10px;border-radius:20px;font-weight:600;font-size:0.85rem;">
                                🐾 <?= $client['nb_animaux'] ?>
                            </span>
                        </td>
                        <td>
                            <span style="background:#e3f2fd;color:#1565c0;padding:3px 10px;border-radius:20px;font-weight:600;font-size:0.85rem;">
                                📅 <?= $client['nb_rdv'] ?>
                            </span>
                        </td>
                        <td>
                            <span style="background:#fff3e0;color:#e65100;padding:3px 10px;border-radius:20px;font-weight:600;font-size:0.85rem;">
                                🛒 <?= $client['nb_commandes'] ?>
                            </span>
                        </td>
                        <td>
                            <small class="text-muted"><?= date('d/m/Y', strtotime($client['created_at'])) ?></small>
                        </td>
                        <td>
                            <a href="?supprimer=<?= $client['id'] ?>"
                               onclick="return confirm('Supprimer le client <?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?> ?')"
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