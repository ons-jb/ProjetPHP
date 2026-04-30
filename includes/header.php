<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="fr">
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
    <link rel="stylesheet" href="/Veterinaire/public/css/style.css">
    <!-- Bootstrap JS ici pour éviter les problèmes de chargement -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container">

    <a class="navbar-brand" href="/Veterinaire/">
      <div class="brand-icon">🐾</div>
      VétoCare
    </a>

    <button class="navbar-toggler border-0 shadow-none" type="button"
            data-bs-toggle="collapse" data-bs-target="#nav">
      <i class="fas fa-bars" style="color:var(--dark)"></i>
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
            <li class="nav-item">
              <a class="nav-link" href="/Veterinaire/user/dashboard.php">Mon espace</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/Veterinaire/user/animaux.php">
                Mes animaux
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/Veterinaire/user/rendez-vous.php">
                Rendez-vous
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/Veterinaire/user/commandes.php">
                Commandes
              </a>
            </li>
          <?php endif; ?>

          <li class="nav-item ms-2">
            <a class="nav-link nav-user" href="/Veterinaire/auth/logout.php">
              <i class="fas fa-circle" 
                 style="font-size:0.5rem;color:var(--green-light)"></i>
              <?= htmlspecialchars($_SESSION['user_nom']) ?>
              <span style="font-size:0.75rem;color:var(--gray-400)">
                · Déconnexion
              </span>
            </a>
          </li>

        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="/Veterinaire/auth/login.php">Connexion</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-btn" href="/Veterinaire/auth/register.php">
              Créer un compte
            </a>
          </li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<div class="container fade-up">