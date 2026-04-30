<?php
require_once '../includes/header.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Veterinaire/auth/login.php');
    exit;
}

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$user_id = $_SESSION['user_id'];
$erreur  = '';

// ── Supprimer un article ─────────────────────────────────────────────────────
if (isset($_GET['supprimer'])) {
    $id = (int) $_GET['supprimer'];
    unset($_SESSION['panier'][$id]);
    header('Location: panier.php');
    exit;
}

// ── Vider le panier ──────────────────────────────────────────────────────────
if (isset($_GET['vider'])) {
    $_SESSION['panier'] = [];
    header('Location: panier.php');
    exit;
}

// ── Mettre à jour quantités ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    foreach ($_POST['quantites'] as $id => $qte) {
        $id  = (int) $id;
        $qte = (int) $qte;
        if ($qte <= 0) {
            unset($_SESSION['panier'][$id]);
        } else {
            if (isset($_SESSION['panier'][$id])) {
                $_SESSION['panier'][$id]['quantite'] = $qte;
            }
        }
    }
    header('Location: panier.php');
    exit;
}

// ── Passer la commande ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commander'])) {
    if (empty($_SESSION['panier'])) {
        $erreur = "Votre panier est vide.";
    } else {
        try {
            $pdo->beginTransaction();

            // Calculer le total
            $total = 0;
            foreach ($_SESSION['panier'] as $item) {
                $total += $item['prix'] * $item['quantite'];
            }

            // Créer la commande
            $stmt = $pdo->prepare("INSERT INTO commandes (user_id, total, statut) VALUES (?, ?, 'en_attente')");
            $stmt->execute([$user_id, $total]);
            $commande_id = $pdo->lastInsertId();

            // Ajouter les lignes + décrémenter le stock
            foreach ($_SESSION['panier'] as $item) {
                $stmt = $pdo->prepare("INSERT INTO commande_produits 
                    (commande_id, produit_id, quantite, prix_unitaire) 
                    VALUES (?, ?, ?, ?)");
                $stmt->execute([$commande_id, $item['id'], $item['quantite'], $item['prix']]);

                $stmt = $pdo->prepare("UPDATE produits SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantite'], $item['id']]);
            }

            $pdo->commit();
            $_SESSION['panier'] = [];
            header('Location: commandes.php?succes=1');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $erreur = "Erreur lors de la commande. Veuillez réessayer.";
        }
    }
}

// ── Calculer total ───────────────────────────────────────────────────────────
$total = 0;
foreach ($_SESSION['panier'] as $item) {
    $total += $item['prix'] * $item['quantite'];
}
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-shopping-cart text-vert me-2"></i>Mon panier</h2>
        <p>Vérifiez votre commande avant de confirmer</p>
    </div>
    <a href="produits.php" class="btn btn-outline-vert">
        <i class="fas fa-arrow-left me-1"></i> Continuer mes achats
    </a>
</div>

<?php if ($erreur): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<?php if (empty($_SESSION['panier'])): ?>
    <div class="text-center py-5">
        <div style="font-size:3.5rem; margin-bottom:16px;">🛒</div>
        <h5 class="fw-700">Votre panier est vide</h5>
        <p class="text-muted mb-3">Ajoutez des produits pour commencer</p>
        <a href="produits.php" class="btn btn-vert">
            <i class="fas fa-store me-1"></i> Voir les produits
        </a>
    </div>
<?php else: ?>
    <div class="row g-4">
        <!-- Articles -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header vert-clair">
                    <i class="fas fa-list me-2"></i>
                    Articles (<?= count($_SESSION['panier']) ?>)
                </div>
                <div class="card-body p-0">
                    <form method="POST">
                        <?php foreach ($_SESSION['panier'] as $id => $item): ?>
                        <div style="padding:16px 20px; border-bottom:1px solid #f0f0f0;
                                    display:flex; align-items:center; gap:14px;">
                            <!-- Photo -->
                            <?php if ($item['photo']): ?>
                                <img src="/Veterinaire/public/uploads/<?= htmlspecialchars($item['photo']) ?>"
                                     style="width:60px; height:60px; border-radius:8px; object-fit:cover;">
                            <?php else: ?>
                                <div style="width:60px; height:60px; background:#f0faf4;
                                            border-radius:8px; display:flex; align-items:center;
                                            justify-content:center; font-size:1.5rem;">💊</div>
                            <?php endif; ?>

                            <!-- Info -->
                            <div style="flex:1;">
                                <div class="fw-700"><?= htmlspecialchars($item['nom']) ?></div>
                                <small class="text-muted">
                                    Prix unitaire : <?= number_format($item['prix'], 2) ?> DT
                                </small>
                            </div>

                            <!-- Quantité -->
                            <input type="number" name="quantites[<?= $id ?>]"
                                   value="<?= $item['quantite'] ?>"
                                   min="0" max="99"
                                   style="width:65px; padding:6px; border:1.5px solid #e0e0e0;
                                          border-radius:6px; text-align:center;">

                            <!-- Sous-total -->
                            <div style="min-width:80px; text-align:right;">
                                <strong class="text-vert">
                                    <?= number_format($item['prix'] * $item['quantite'], 2) ?> DT
                                </strong>
                            </div>

                            <!-- Supprimer -->
                            <a href="?supprimer=<?= $id ?>"
                               style="color:#e63946; text-decoration:none; font-size:1.1rem;"
                               title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>

                        <div style="padding:14px 20px; display:flex; gap:10px;">
                            <button type="submit" name="update"
                                    class="btn btn-outline-vert btn-sm">
                                <i class="fas fa-sync me-1"></i> Mettre à jour
                            </button>
                            <a href="?vider=1"
                               onclick="return confirm('Vider le panier ?')"
                               class="btn btn-rose btn-sm">
                                <i class="fas fa-trash me-1"></i> Vider le panier
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Résumé commande -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header vert">
                    <i class="fas fa-receipt me-2"></i> Résumé
                </div>
                <div class="card-body">
                    <?php foreach ($_SESSION['panier'] as $item): ?>
                    <div class="d-flex justify-content-between mb-2"
                         style="font-size:0.88rem;">
                        <span><?= htmlspecialchars($item['nom']) ?> x<?= $item['quantite'] ?></span>
                        <span><?= number_format($item['prix'] * $item['quantite'], 2) ?> DT</span>
                    </div>
                    <?php endforeach; ?>

                    <hr>
                    <div class="d-flex justify-content-between fw-700 mb-3">
                        <span>Total</span>
                        <span class="text-vert" style="font-size:1.1rem;">
                            <?= number_format($total, 2) ?> DT
                        </span>
                    </div>

                    <form method="POST">
                        <button type="submit" name="commander"
                                class="btn btn-vert w-100">
                            <i class="fas fa-check me-1"></i> Confirmer la commande
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>