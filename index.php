<?php require_once 'includes/header.php'; ?>

<style>
/* ── Fonts ── */
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,700&family=DM+Sans:wght@300;400;500;600&display=swap');

:root {
  --vert-dark: #1a3d2b;
  --vert-mid:  #2d6a4f;
  --vert:      #40916c;
  --vert-light:#74c69d;
  --vert-pale: #d8f3dc;
  --gold:      #e9c46a;
  --cream:     #fafaf7;
  --dark:      #111d13;
}

body { background: var(--cream); }

/* ── Hero ── */
.hero-wrap {
  position: relative;
  min-height: 88vh;
  display: flex;
  align-items: center;
  overflow: hidden;
  padding: 80px 0 60px;
}

.hero-bg {
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 70% 60% at 80% 50%, rgba(64,145,108,.12) 0%, transparent 70%),
    radial-gradient(ellipse 40% 40% at 10% 80%, rgba(116,198,157,.08) 0%, transparent 60%),
    var(--cream);
  z-index: 0;
}

.hero-blob {
  position: absolute;
  right: -60px; top: 50%;
  transform: translateY(-50%);
  width: 520px; height: 520px;
  background: radial-gradient(circle, var(--vert-pale) 0%, rgba(116,198,157,.15) 60%, transparent 80%);
  border-radius: 50%;
  z-index: 0;
  animation: pulse 6s ease-in-out infinite;
}

@keyframes pulse {
  0%,100% { transform: translateY(-50%) scale(1); }
  50%      { transform: translateY(-50%) scale(1.06); }
}

.hero-content { position: relative; z-index: 1; }

.hero-eyebrow {
  display: inline-block;
  font-family: 'DM Sans', sans-serif;
  font-size: .72rem;
  font-weight: 600;
  letter-spacing: .18em;
  text-transform: uppercase;
  color: var(--vert);
  background: rgba(64,145,108,.1);
  border: 1px solid rgba(64,145,108,.2);
  padding: 5px 14px;
  border-radius: 20px;
  margin-bottom: 24px;
}

.hero-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: clamp(2.6rem, 5.5vw, 4.2rem);
  font-weight: 700;
  color: var(--dark);
  line-height: 1.12;
  margin-bottom: 20px;
}

.hero-title em {
  font-style: italic;
  color: var(--vert-mid);
}

.hero-sub {
  font-family: 'DM Sans', sans-serif;
  font-size: 1.05rem;
  color: #555;
  font-weight: 300;
  line-height: 1.7;
  max-width: 480px;
  margin-bottom: 36px;
}

.btn-main {
  font-family: 'DM Sans', sans-serif;
  background: var(--vert-dark);
  color: #fff;
  padding: 14px 28px;
  border-radius: 50px;
  font-weight: 600;
  font-size: .95rem;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all .3s;
  box-shadow: 0 4px 20px rgba(26,61,43,.25);
}
.btn-main:hover {
  background: var(--vert-mid);
  color: #fff;
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(26,61,43,.3);
}

.btn-ghost {
  font-family: 'DM Sans', sans-serif;
  background: transparent;
  color: var(--dark);
  padding: 14px 28px;
  border-radius: 50px;
  font-weight: 500;
  font-size: .95rem;
  text-decoration: none;
  border: 1.5px solid #d0d0c8;
  transition: all .3s;
}
.btn-ghost:hover {
  border-color: var(--vert);
  color: var(--vert-mid);
  background: rgba(64,145,108,.05);
}

/* ── Hero animals illustration ── */
.hero-illustration {
  position: relative;
  z-index: 1;
  display: flex;
  justify-content: center;
  align-items: center;
}

.animal-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  max-width: 380px;
}

.animal-card-mini {
  background: #fff;
  border-radius: 20px;
  padding: 20px;
  text-align: center;
  box-shadow: 0 4px 24px rgba(0,0,0,.07);
  transition: transform .3s;
  animation: floatUp .6s ease both;
}
.animal-card-mini:hover { transform: translateY(-4px); }
.animal-card-mini:nth-child(1) { animation-delay: .1s; }
.animal-card-mini:nth-child(2) { animation-delay: .2s; transform: translateY(20px); }
.animal-card-mini:nth-child(3) { animation-delay: .3s; }
.animal-card-mini:nth-child(4) { animation-delay: .4s; transform: translateY(20px); }

@keyframes floatUp {
  from { opacity:0; transform: translateY(30px); }
  to   { opacity:1; }
}

.animal-emoji { font-size: 2.4rem; margin-bottom: 8px; }
.animal-label {
  font-family: 'DM Sans', sans-serif;
  font-size: .78rem;
  font-weight: 600;
  color: #888;
  text-transform: uppercase;
  letter-spacing: .06em;
}

/* ── Stats band ── */
.stats-band {
  background: var(--vert-dark);
  border-radius: 20px;
  padding: 32px 40px;
  margin: 0 0 80px;
  display: flex;
  justify-content: space-around;
  flex-wrap: wrap;
  gap: 24px;
}

.stat-item { text-align: center; }
.stat-num {
  font-family: 'Playfair Display', serif;
  font-size: 2.4rem;
  font-weight: 700;
  color: var(--vert-light);
  line-height: 1;
}
.stat-lbl {
  font-family: 'DM Sans', sans-serif;
  font-size: .82rem;
  color: rgba(255,255,255,.6);
  margin-top: 4px;
}

/* ── Section title ── */
.section-label {
  font-family: 'DM Sans', sans-serif;
  font-size: .72rem;
  font-weight: 600;
  letter-spacing: .15em;
  text-transform: uppercase;
  color: var(--vert);
  margin-bottom: 12px;
}
.section-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.8rem, 3vw, 2.6rem);
  color: var(--dark);
  font-weight: 700;
  margin-bottom: 48px;
}

/* ── Feature cards ── */
.feature-card-new {
  background: #fff;
  border-radius: 24px;
  padding: 36px 28px;
  height: 100%;
  position: relative;
  overflow: hidden;
  border: 1px solid #efefea;
  transition: all .35s;
}
.feature-card-new:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 50px rgba(0,0,0,.09);
  border-color: var(--vert-light);
}

.feature-card-new::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--vert-mid), var(--vert-light));
  opacity: 0;
  transition: opacity .3s;
}
.feature-card-new:hover::before { opacity: 1; }

.feature-num-new {
  font-family: 'Playfair Display', serif;
  font-size: 3.5rem;
  font-weight: 700;
  color: var(--vert-pale);
  line-height: 1;
  margin-bottom: 12px;
}

.feature-icon {
  width: 52px; height: 52px;
  background: var(--vert-pale);
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem;
  margin-bottom: 20px;
}

.feature-tag-new {
  display: inline-block;
  font-family: 'DM Sans', sans-serif;
  font-size: .7rem;
  font-weight: 600;
  letter-spacing: .1em;
  text-transform: uppercase;
  color: var(--vert-mid);
  background: var(--vert-pale);
  padding: 3px 10px;
  border-radius: 20px;
  margin-bottom: 14px;
}
.feature-tag-new.gold { color: #9a6e00; background: #fef3cd; }

.feature-card-new h4 {
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem;
  color: var(--dark);
  margin-bottom: 10px;
}

.feature-card-new p {
  font-family: 'DM Sans', sans-serif;
  font-size: .9rem;
  color: #666;
  line-height: 1.65;
}

/* ── How it works ── */
.step-wrap {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.step-item {
  display: flex;
  gap: 24px;
  padding: 28px 0;
  border-bottom: 1px solid #efefea;
  align-items: flex-start;
}
.step-item:last-child { border-bottom: none; }

.step-num {
  width: 48px; height: 48px; min-width: 48px;
  border-radius: 50%;
  background: var(--vert-dark);
  color: #fff;
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem;
  font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}

.step-content h5 {
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem;
  color: var(--dark);
  margin-bottom: 6px;
}
.step-content p {
  font-family: 'DM Sans', sans-serif;
  font-size: .88rem;
  color: #666;
  margin: 0;
  line-height: 1.6;
}

/* ── CTA band ── */
.cta-band {
  background: linear-gradient(135deg, var(--vert-dark) 0%, var(--vert-mid) 100%);
  border-radius: 28px;
  padding: 60px 48px;
  text-align: center;
  margin-bottom: 60px;
  position: relative;
  overflow: hidden;
}

.cta-band::before {
  content: '🐾';
  position: absolute;
  font-size: 10rem;
  opacity: .05;
  right: -20px; top: -20px;
}

.cta-band h2 {
  font-family: 'Playfair Display', serif;
  color: #fff;
  font-size: clamp(1.8rem, 3vw, 2.4rem);
  margin-bottom: 12px;
}
.cta-band p {
  font-family: 'DM Sans', sans-serif;
  color: rgba(255,255,255,.75);
  font-size: 1rem;
  margin-bottom: 28px;
}
.btn-cta {
  background: #fff;
  color: var(--vert-dark);
  font-family: 'DM Sans', sans-serif;
  font-weight: 700;
  padding: 14px 32px;
  border-radius: 50px;
  text-decoration: none;
  font-size: .95rem;
  display: inline-block;
  transition: all .3s;
}
.btn-cta:hover {
  background: var(--gold);
  color: var(--dark);
  transform: translateY(-2px);
}

/* ── Animations ── */
.fade-in {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity .6s ease, transform .6s ease;
}
.fade-in.visible {
  opacity: 1;
  transform: translateY(0);
}
</style>

<!-- ═══════════════════════════════ HERO ═══════════════════════════════ -->
<div class="hero-wrap">
  <div class="hero-bg"></div>
  <div class="hero-blob"></div>
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 hero-content">
        <div class="hero-eyebrow">Clinique vétérinaire en ligne</div>
        <h1 class="hero-title">
          Prenez soin de<br>vos animaux,<br><em>simplement.</em>
        </h1>
        <p class="hero-sub">
          Réservez des consultations, commandez des produits
          et gérez la santé de vos animaux depuis chez vous.
        </p>
        <div class="d-flex gap-3 flex-wrap">
          <a href="auth/register.php" class="btn-main">
            Commencer gratuitement
            <i class="fas fa-arrow-right" style="font-size:.8rem"></i>
          </a>
          <a href="auth/login.php" class="btn-ghost">Se connecter</a>
        </div>
      </div>
      <div class="col-lg-6 hero-illustration mt-5 mt-lg-0">
        <div class="animal-grid mx-auto">
          <div class="animal-card-mini">
            <div class="animal-emoji">🐶</div>
            <div class="animal-label">Chiens</div>
          </div>
          <div class="animal-card-mini">
            <div class="animal-emoji">🐱</div>
            <div class="animal-label">Chats</div>
          </div>
          <div class="animal-card-mini">
            <div class="animal-emoji">🐰</div>
            <div class="animal-label">Lapins</div>
          </div>
          <div class="animal-card-mini">
            <div class="animal-emoji">🐦</div>
            <div class="animal-label">Oiseaux</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════ STATS ═══════════════════════════════ -->
<div class="container">
  <div class="stats-band fade-in">
    <div class="stat-item">
      <div class="stat-num">500+</div>
      <div class="stat-lbl">Clients satisfaits</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">1200+</div>
      <div class="stat-lbl">Rendez-vous pris</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">50+</div>
      <div class="stat-lbl">Produits disponibles</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">98%</div>
      <div class="stat-lbl">Taux de satisfaction</div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════ FEATURES ═══════════════════════════════ -->
<div class="container mb-5">
  <div class="text-center fade-in">
    <div class="section-label">Nos services</div>
    <h2 class="section-title">Tout ce dont vos animaux ont besoin</h2>
  </div>
  <div class="row g-4">
    <div class="col-md-4 fade-in">
      <div class="feature-card-new">
        <div class="feature-num-new">01</div>
        <div class="feature-icon">🩺</div>
        <span class="feature-tag-new">Soins</span>
        <h4>Consultations vétérinaires</h4>
        <p>Prenez rendez-vous en ligne avec nos vétérinaires. Choisissez votre créneau, précisez le motif et recevez une confirmation immédiate.</p>
      </div>
    </div>
    <div class="col-md-4 fade-in">
      <div class="feature-card-new">
        <div class="feature-num-new">02</div>
        <div class="feature-icon">💊</div>
        <span class="feature-tag-new gold">Boutique</span>
        <h4>Produits & médicaments</h4>
        <p>Médicaments, accessoires et compléments alimentaires livrés directement chez vous. Stock mis à jour en temps réel.</p>
      </div>
    </div>
    <div class="col-md-4 fade-in">
      <div class="feature-card-new">
        <div class="feature-num-new">03</div>
        <div class="feature-icon">📋</div>
        <span class="feature-tag-new">Suivi</span>
        <h4>Profil de vos animaux</h4>
        <p>Centralisez les informations de santé, les vaccins et l'historique médical de chaque animal en un seul endroit.</p>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════ HOW IT WORKS ═══════════════════════════════ -->
<div class="container mb-5">
  <div class="row g-5 align-items-center">
    <div class="col-lg-5 fade-in">
      <div class="section-label">Comment ça marche</div>
      <h2 class="section-title" style="margin-bottom:0;">Simple comme bonjour</h2>
    </div>
    <div class="col-lg-7 fade-in">
      <div class="step-wrap">
        <div class="step-item">
          <div class="step-num">1</div>
          <div class="step-content">
            <h5>Créez votre compte</h5>
            <p>Inscrivez-vous gratuitement en quelques secondes. Ajoutez votre profil et vos informations de contact.</p>
          </div>
        </div>
        <div class="step-item">
          <div class="step-num">2</div>
          <div class="step-content">
            <h5>Ajoutez vos animaux</h5>
            <p>Enregistrez vos animaux avec leur espèce, race, âge et photo. Chaque animal a son propre profil.</p>
          </div>
        </div>
        <div class="step-item">
          <div class="step-num">3</div>
          <div class="step-content">
            <h5>Réservez ou commandez</h5>
            <p>Prenez un rendez-vous vétérinaire ou commandez des produits directement depuis votre espace client.</p>
          </div>
        </div>
        <div class="step-item">
          <div class="step-num">4</div>
          <div class="step-content">
            <h5>Suivez en temps réel</h5>
            <p>Recevez des confirmations et suivez le statut de vos rendez-vous et commandes depuis votre tableau de bord.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════ CTA ═══════════════════════════════ -->
<div class="container fade-in">
  <div class="cta-band">
    <h2>Prêt à prendre soin de vos animaux ?</h2>
    <p>Rejoignez des centaines de propriétaires qui font confiance à VétoCare</p>
    <a href="auth/register.php" class="btn-cta">
      <i class="fas fa-paw me-2"></i> Créer un compte gratuit
    </a>
  </div>
</div>

<script>
// Scroll animations
const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry, i) => {
    if (entry.isIntersecting) {
      setTimeout(() => entry.target.classList.add('visible'), i * 100);
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
</script>

<?php require_once 'includes/footer.php'; ?>