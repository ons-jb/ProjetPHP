<?php require_once 'includes/header.php'; ?>

<!-- Hero -->
<div class="hero">
  <span class="hero-eyebrow">Clinique vétérinaire en ligne</span>
  <h1>Prenez soin de vos animaux,<br><em>simplement.</em></h1>
  <p>
    Réservez des consultations, commandez des produits<br>
    et gérez la santé de vos animaux depuis chez vous.
  </p>
  <div class="d-flex gap-3 justify-content-center flex-wrap">
    <a href="auth/register.php" class="btn-main">
      Commencer gratuitement
      <i class="fas fa-arrow-right" style="font-size:0.8rem"></i>
    </a>
    <a href="auth/login.php" class="btn-ghost">
      Se connecter
    </a>
  </div>
</div>

<!-- Features -->
<div class="row g-3 mb-5">
  <div class="col-md-4">
    <div class="feature-card">
      <span class="feature-tag">Soins</span>
      <div class="feature-num">01</div>
      <h4>Consultations vétérinaires</h4>
      <p>Prenez rendez-vous en ligne avec nos vétérinaires. Choisissez votre créneau, précisez le motif.</p>
    </div>
  </div>
  <div class="col-md-4">
    <div class="feature-card">
      <span class="feature-tag gold">Boutique</span>
      <div class="feature-num">02</div>
      <h4>Produits & médicaments</h4>
      <p>Médicaments, accessoires et compléments alimentaires livrés directement chez vous.</p>
    </div>
  </div>
  <div class="col-md-4">
    <div class="feature-card">
      <span class="feature-tag">Suivi</span>
      <div class="feature-num">03</div>
      <h4>Profil de vos animaux</h4>
      <p>Centralisez les informations de santé, les vaccins et l'historique médical de chaque animal.</p>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>