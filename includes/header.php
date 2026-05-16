<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VétoCare — Clinique Vétérinaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Veterinaire/public/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
    .navbar {
        background: #1a3d2b !important;
        padding: 0 0;
        box-shadow: 0 2px 16px rgba(0,0,0,.15);
    }

    .navbar .container {
        padding-top: 12px;
        padding-bottom: 12px;
    }

    /* Brand */
    .navbar-brand {
        font-family: 'DM Sans', sans-serif;
        font-weight: 700;
        font-size: 1.15rem;
        color: #fff !important;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    .brand-icon {
        width: 34px; height: 34px;
        background: rgba(255,255,255,.12);
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
    }

    /* Nav links */
    .navbar .nav-link {
        font-family: 'DM Sans', sans-serif;
        font-size: .88rem;
        font-weight: 500;
        color: rgba(255,255,255,.7) !important;
        padding: 6px 10px !important;
        border-radius: 8px;
        transition: all .2s;
        text-decoration: none;
    }
    .navbar .nav-link:hover {
        color: #fff !important;
        background: rgba(255,255,255,.08);
    }

    /* Bouton créer un compte */
    .navbar .nav-btn {
        background: #fff !important;
        color: #1a3d2b !important;
        font-weight: 600 !important;
        padding: 7px 16px !important;
        border-radius: 20px !important;
    }
    .navbar .nav-btn:hover {
        background: #74c69d !important;
        color: #1a3d2b !important;
    }

    /* User connecté */
    .navbar .nav-user {
        background: rgba(255,255,255,.08) !important;
        border-radius: 20px !important;
        padding: 6px 14px !important;
        color: rgba(255,255,255,.85) !important;
        font-size: .85rem !important;
    }
    .navbar .nav-user:hover {
        background: rgba(255,255,255,.14) !important;
        color: #fff !important;
    }

    /* Badge panier */
    .panier-badge {
        background: #e63946;
        color: white;
        border-radius: 50%;
        padding: 1px 6px;
        font-size: 0.72rem;
        margin-left: 2px;
        font-weight: 700;
    }

    /* Toggler */
    .navbar-toggler {
        border: none !important;
        box-shadow: none !important;
        padding: 4px 8px;
    }
    .navbar-toggler i { color: rgba(255,255,255,.8) !important; }

    /* Connexion link */
    .navbar .nav-link-login {
        color: rgba(255,255,255,.7) !important;
    }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container">

    <a class="navbar-brand" href="/Veterinaire/">
      <div class="brand-icon">🐾</div>
      VétoCare
    </a>

    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#nav">
      <i class="fas fa-bars"></i>
    </button>

    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-center gap-1">

        <li class="nav-item">
          <a class="nav-link" href="/Veterinaire/">Accueil</a>
        </li>

        <?php if (isset($_SESSION['user_id'])): ?>

          <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <li class="nav-item">
              <a class="nav-link" href="/Veterinaire/admin/index.php">
                <i class="fas fa-th-large me-1"></i>Dashboard
              </a>
            </li>

          <?php else: ?>

            <?php
            $nb_panier_header = 0;
            if (!empty($_SESSION['panier'])) {
                foreach ($_SESSION['panier'] as $item) {
                    if (is_array($item) && isset($item['quantite'])) {
                        $nb_panier_header += $item['quantite'];
                    } elseif (is_numeric($item)) {
                        $nb_panier_header += $item;
                    }
                }
            }
            ?>

            <li class="nav-item">
              <a class="nav-link" href="/Veterinaire/user/dashboard.php">Mon espace</a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="/Veterinaire/user/produits.php">
                <i class="fas fa-store me-1"></i> Produits
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="/Veterinaire/user/animaux.php">Mes animaux</a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="/Veterinaire/user/rendez-vous.php">Rendez-vous</a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="/Veterinaire/user/commandes.php">Commandes</a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="/Veterinaire/user/panier.php">
                <i class="fas fa-shopping-cart me-1"></i> Panier
                <?php if ($nb_panier_header > 0): ?>
                  <span class="panier-badge"><?= $nb_panier_header ?></span>
                <?php endif; ?>
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="/Veterinaire/user/profil.php">
                <i class="fas fa-user-circle me-1"></i> Mon profil
              </a>
            </li>

          <?php endif; ?>

          <li class="nav-item ms-2">
            <a class="nav-link nav-user" href="/Veterinaire/auth/logout.php">
              <i class="fas fa-circle me-1" style="font-size:.45rem;color:#74c69d;vertical-align:middle;"></i>
              <?= htmlspecialchars($_SESSION['user_nom']) ?>
              <span style="font-size:.75rem;color:rgba(255,255,255,.4);margin-left:3px;">· Déconnexion</span>
            </a>
          </li>

        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="/Veterinaire/auth/login.php">Connexion</a>
          </li>
          <li class="nav-item ms-1">
            <a class="nav-link nav-btn" href="/Veterinaire/auth/register.php">Créer un compte</a>
          </li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<div class="container fade-up">