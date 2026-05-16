<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/header.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /Veterinaire/auth/login.php');
    exit;
}

// ── KPIs globaux ─────────────────────────────────────────────────────────────
$nb_clients   = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE ROLE='client'")->fetchColumn();
$nb_animaux   = $pdo->query("SELECT COUNT(*) FROM animaux")->fetchColumn();
$nb_produits  = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$nb_services  = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();

$nb_rdv_total     = $pdo->query("SELECT COUNT(*) FROM rendez_vous")->fetchColumn();
$nb_rdv_attente   = $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE statut='en_attente'")->fetchColumn();
$nb_rdv_confirme  = $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE statut='confirme'")->fetchColumn();
$nb_rdv_annule    = $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE statut='annule'")->fetchColumn();

$nb_cmd_total    = $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn();
$ca_total        = $pdo->query("SELECT COALESCE(SUM(total),0) FROM commandes WHERE statut != 'annulee'")->fetchColumn();
$ca_mois         = $pdo->query("SELECT COALESCE(SUM(total),0) FROM commandes WHERE statut != 'annulee' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();

// ── RDV par mois (12 derniers mois) ──────────────────────────────────────────
$rdv_par_mois = $pdo->query("
    SELECT DATE_FORMAT(date, '%Y-%m') as mois, COUNT(*) as total
    FROM rendez_vous
    WHERE date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY mois ORDER BY mois ASC
")->fetchAll();

// ── Commandes par mois ───────────────────────────────────────────────────────
$cmd_par_mois = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as mois, COUNT(*) as total, SUM(total) as ca
    FROM commandes
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY mois ORDER BY mois ASC
")->fetchAll();

// ── Top services ─────────────────────────────────────────────────────────────
$top_services = $pdo->query("
    SELECT s.nom, COUNT(r.id) as nb
    FROM services s
    LEFT JOIN rendez_vous r ON r.service_id = s.id
    GROUP BY s.id ORDER BY nb DESC LIMIT 5
")->fetchAll();

// ── Top produits ─────────────────────────────────────────────────────────────
$top_produits = $pdo->query("
    SELECT p.nom, SUM(cp.quantite) as total_vendu
    FROM produits p
    LEFT JOIN commande_produits cp ON cp.produit_id = p.id
    GROUP BY p.id ORDER BY total_vendu DESC LIMIT 5
")->fetchAll();

// ── Répartition espèces ──────────────────────────────────────────────────────
$especes = $pdo->query("
    SELECT espece, COUNT(*) as nb FROM animaux GROUP BY espece ORDER BY nb DESC
")->fetchAll();

// ── Nouveaux clients par mois ────────────────────────────────────────────────
$nouveaux_clients = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as mois, COUNT(*) as total
    FROM utilisateurs WHERE ROLE='client'
    AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY mois ORDER BY mois ASC
")->fetchAll();

// ── JSON pour Chart.js ───────────────────────────────────────────────────────
$rdv_labels  = json_encode(array_column($rdv_par_mois, 'mois'));
$rdv_data    = json_encode(array_column($rdv_par_mois, 'total'));

$cmd_labels  = json_encode(array_column($cmd_par_mois, 'mois'));
$cmd_data    = json_encode(array_column($cmd_par_mois, 'total'));
$ca_data     = json_encode(array_column($cmd_par_mois, 'ca'));

$esp_labels  = json_encode(array_column($especes, 'espece'));
$esp_data    = json_encode(array_column($especes, 'nb'));

$cli_labels  = json_encode(array_column($nouveaux_clients, 'mois'));
$cli_data    = json_encode(array_column($nouveaux_clients, 'total'));
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2><i class="fas fa-chart-bar text-vert me-2"></i>Statistiques</h2>
        <p>Vue d'ensemble de l'activité de la clinique</p>
    </div>
    <a href="index.php" class="btn btn-outline-vert">
        <i class="fas fa-arrow-left me-1"></i> Dashboard
    </a>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card stat-vert">
            <div class="chiffre"><?= $nb_clients ?></div>
            <div class="label"><i class="fas fa-users me-1"></i> Clients</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-jaune">
            <div class="chiffre"><?= $nb_animaux ?></div>
            <div class="label"><i class="fas fa-paw me-1"></i> Animaux</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:#e3f2fd;">
            <div class="chiffre" style="color:#1565c0;"><?= $nb_rdv_total ?></div>
            <div class="label" style="color:#1565c0;"><i class="fas fa-calendar me-1"></i> RDV Total</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:#1a1a2e;color:#fff;">
            <div class="chiffre"><?= number_format($ca_total, 0) ?> DT</div>
            <div class="label"><i class="fas fa-coins me-1"></i> CA Total</div>
        </div>
    </div>
</div>

<!-- Ligne 2 KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div style="font-size:1.5rem;font-weight:700;color:#856404;"><?= $nb_rdv_attente ?></div>
            <small class="text-muted">RDV en attente</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div style="font-size:1.5rem;font-weight:700;color:#0f5132;"><?= $nb_rdv_confirme ?></div>
            <small class="text-muted">RDV confirmés</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div style="font-size:1.5rem;font-weight:700;color:var(--vert);"><?= $nb_cmd_total ?></div>
            <small class="text-muted">Commandes</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div style="font-size:1.5rem;font-weight:700;color:var(--vert);"><?= number_format($ca_mois, 0) ?> DT</div>
            <small class="text-muted">CA ce mois</small>
        </div>
    </div>
</div>

<!-- Graphiques ligne 1 -->
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card p-3">
            <h6 class="fw-700 mb-3"><i class="fas fa-calendar-alt text-vert me-2"></i>Rendez-vous par mois</h6>
            <canvas id="chartRdv" height="100"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <h6 class="fw-700 mb-3"><i class="fas fa-paw text-vert me-2"></i>Répartition des espèces</h6>
            <canvas id="chartEspeces" height="180"></canvas>
        </div>
    </div>
</div>

<!-- Graphiques ligne 2 -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card p-3">
            <h6 class="fw-700 mb-3"><i class="fas fa-shopping-bag text-vert me-2"></i>Commandes & CA par mois</h6>
            <canvas id="chartCommandes" height="120"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3">
            <h6 class="fw-700 mb-3"><i class="fas fa-user-plus text-vert me-2"></i>Nouveaux clients par mois</h6>
            <canvas id="chartClients" height="120"></canvas>
        </div>
    </div>
</div>

<!-- Top Services & Produits -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card p-3">
            <h6 class="fw-700 mb-3"><i class="fas fa-star text-vert me-2"></i>Top Services demandés</h6>
            <?php if (empty($top_services)): ?>
                <p class="text-muted text-center py-3">Aucune donnée</p>
            <?php else: ?>
                <?php $max_s = max(array_column($top_services, 'nb')) ?: 1; ?>
                <?php foreach ($top_services as $i => $s): ?>
                <div class="mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="fw-600"><?= htmlspecialchars($s['nom']) ?></small>
                        <small class="text-muted"><?= $s['nb'] ?> RDV</small>
                    </div>
                    <div style="background:#f0f0f0;border-radius:10px;height:8px;">
                        <div style="width:<?= round($s['nb']/$max_s*100) ?>%;background:var(--vert);border-radius:10px;height:8px;transition:width 0.5s;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3">
            <h6 class="fw-700 mb-3"><i class="fas fa-box text-vert me-2"></i>Top Produits vendus</h6>
            <?php if (empty($top_produits)): ?>
                <p class="text-muted text-center py-3">Aucune donnée</p>
            <?php else: ?>
                <?php $max_p = max(array_column($top_produits, 'total_vendu')) ?: 1; ?>
                <?php foreach ($top_produits as $p): ?>
                <div class="mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="fw-600"><?= htmlspecialchars($p['nom']) ?></small>
                        <small class="text-muted"><?= $p['total_vendu'] ?? 0 ?> vendus</small>
                    </div>
                    <div style="background:#f0f0f0;border-radius:10px;height:8px;">
                        <div style="width:<?= round(($p['total_vendu'] ?? 0)/$max_p*100) ?>%;background:#1565c0;border-radius:10px;height:8px;transition:width 0.5s;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const vert = '#4caf50';
const bleu = '#1565c0';
const jaune = '#ffc107';

// RDV par mois
new Chart(document.getElementById('chartRdv'), {
    type: 'bar',
    data: {
        labels: <?= $rdv_labels ?>,
        datasets: [{
            label: 'Rendez-vous',
            data: <?= $rdv_data ?>,
            backgroundColor: vert + '99',
            borderColor: vert,
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});

// Espèces (donut)
new Chart(document.getElementById('chartEspeces'), {
    type: 'doughnut',
    data: {
        labels: <?= $esp_labels ?>,
        datasets: [{
            data: <?= $esp_data ?>,
            backgroundColor: [vert, bleu, jaune, '#e63946', '#ff6b35', '#9c27b0'],
            borderWidth: 2,
        }]
    },
    options: { plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }, cutout: '60%' }
});

// Commandes & CA
new Chart(document.getElementById('chartCommandes'), {
    type: 'bar',
    data: {
        labels: <?= $cmd_labels ?>,
        datasets: [
            {
                label: 'Commandes',
                data: <?= $cmd_data ?>,
                backgroundColor: bleu + '99',
                borderColor: bleu,
                borderWidth: 2,
                borderRadius: 4,
                yAxisID: 'y',
            },
            {
                label: 'CA (DT)',
                data: <?= $ca_data ?>,
                type: 'line',
                borderColor: vert,
                backgroundColor: vert + '22',
                fill: true,
                tension: 0.4,
                yAxisID: 'y1',
            }
        ]
    },
    options: {
        scales: {
            y:  { beginAtZero: true, position: 'left',  ticks: { stepSize: 1 } },
            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } }
        }
    }
});

// Nouveaux clients
new Chart(document.getElementById('chartClients'), {
    type: 'line',
    data: {
        labels: <?= $cli_labels ?>,
        datasets: [{
            label: 'Nouveaux clients',
            data: <?= $cli_data ?>,
            borderColor: jaune,
            backgroundColor: jaune + '33',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: jaune,
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>

<?php require_once '../includes/footer.php'; ?>