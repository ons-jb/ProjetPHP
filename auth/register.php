<?php
require_once '../includes/header.php';
require_once '../config/db.php';

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST['nom']);
    $prenom  = trim($_POST['prenom']);
    $email   = trim($_POST['email']);
    $mdp     = $_POST['mot_de_passe'];
    $confirm = $_POST['confirm_mdp'];
    $tel     = trim($_POST['telephone']);

    if (empty($nom) || empty($prenom) || empty($email) || empty($mdp)) {
        $erreur = "Tous les champs obligatoires doivent être remplis.";
    } elseif ($mdp !== $confirm) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($mdp) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erreur = "Cet email est déjà utilisé.";
        } else {
            $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $mdp_hash, $tel]);
            $succes = "Compte créé avec succès !";
        }
    }
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap');

.register-wrap {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px 16px;
}

.register-box {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 40px rgba(0,0,0,.08);
    overflow: hidden;
    width: 100%;
    max-width: 500px;
}

/* ── Header compact ── */
.register-top {
    background: linear-gradient(135deg, #1a3d2b 0%, #40916c 100%);
    padding: 24px 36px 20px;
    text-align: center;
}
.register-top .paw {
    font-size: 1.6rem;
    margin-bottom: 6px;
    display: block;
}
.register-top h2 {
    font-family: 'Playfair Display', serif;
    color: #fff;
    font-size: 1.45rem;
    margin: 0 0 4px;
}
.register-top p {
    font-family: 'DM Sans', sans-serif;
    color: rgba(255,255,255,.65);
    font-size: .82rem;
    margin: 0;
}

/* ── Body ── */
.register-body {
    padding: 28px 36px 26px;
}

.register-body .form-label {
    font-family: 'DM Sans', sans-serif;
    font-size: .8rem;
    font-weight: 600;
    color: #444;
    margin-bottom: 5px;
}

/* ── Input avec icône ── */
/* SUPPRIMER tout le bloc .input-wrap et remplacer par : */
.input-wrap {
    display: flex;
    align-items: center;
    border: 1.5px solid #e8e8e2;
    border-radius: 10px;
    background: #fafaf7;
    transition: all .2s;
    overflow: hidden;
}
.input-wrap:focus-within {
    border-color: #40916c;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(64,145,108,.1);
}
.input-wrap .fi {
    padding: 0 10px 0 12px;
    color: #b0b0a8;
    font-size: .8rem;
    flex-shrink: 0;
}
.input-wrap .toggle-pass {
    padding: 0 12px;
    color: #b0b0a8;
    font-size: .8rem;
    cursor: pointer;
    flex-shrink: 0;
    transition: color .2s;
}
.input-wrap .toggle-pass:hover { color: #40916c; }

/* Le form-control n'a plus de border propre */
.input-wrap .form-control {
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
    padding: 10px 4px;
    flex: 1;
    min-width: 0;
}
.input-wrap .form-control:focus {
    outline: none;
    box-shadow: none !important;
}

.register-body .form-control {
    font-family: 'DM Sans', sans-serif;
    border: 1.5px solid #e8e8e2;
    border-radius: 10px;
    padding: 10px 12px;
    font-size: .9rem;
    color: #222;
    background: #fafaf7;
    transition: all .2s;
    width: 100%;
}
.register-body .form-control:focus {
    border-color: #40916c;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(64,145,108,.1);
    outline: none;
}

/* ── Force bar ── */
.strength-bar {
    height: 3px;
    border-radius: 4px;
    background: #eee;
    margin-top: 5px;
    overflow: hidden;
}
.strength-fill {
    height: 100%;
    border-radius: 4px;
    width: 0%;
    transition: width .4s, background .4s;
}

/* ── Submit ── */
.btn-register {
    font-family: 'DM Sans', sans-serif;
    background: #1a3d2b;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 12px;
    font-size: .92rem;
    font-weight: 600;
    width: 100%;
    cursor: pointer;
    transition: all .3s;
    margin-top: 6px;
}
.btn-register:hover {
    background: #40916c;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(26,61,43,.22);
}

.login-link {
    font-family: 'DM Sans', sans-serif;
    text-align: center;
    margin-top: 16px;
    font-size: .85rem;
    color: #888;
}
.login-link a {
    color: #40916c;
    font-weight: 600;
    text-decoration: none;
}
.login-link a:hover { text-decoration: underline; }

.field-sep { border: none; border-top: 1px solid #f0f0ea; margin: 16px 0; }

.alert-custom {
    font-family: 'DM Sans', sans-serif;
    font-size: .85rem;
    border-radius: 10px;
    padding: 10px 14px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.alert-custom.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.alert-custom.ok  { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

.opt-label { color: #bbb; font-weight: 400; }
</style>

<div class="register-wrap">
    <div class="register-box">

        <div class="register-top">
            <span class="paw">🐾</span>
            <h2>Créer un compte</h2>
            <p>Rejoignez VétoCare et prenez soin de vos animaux</p>
        </div>

        <div class="register-body">

            <?php if ($erreur): ?>
                <div class="alert-custom err">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <?php if ($succes): ?>
                <div class="alert-custom ok">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($succes) ?>
                    <a href="login.php" style="margin-left:auto;font-weight:600;color:#166534;">Se connecter →</a>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">Nom *</label>
                        <div class="input-wrap">
                            <i class="fas fa-user fi"></i>
                            <input type="text" name="nom" class="form-control"
                                   placeholder="Ben Ali" required
                                   value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Prénom *</label>
                        <div class="input-wrap">
                            <i class="fas fa-user fi"></i>
                            <input type="text" name="prenom" class="form-control"
                                   placeholder="Dorra" required
                                   value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope fi"></i>
                        <input type="email" name="email" class="form-control"
                               placeholder="exemple@email.com" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Téléphone <span class="opt-label">(optionnel)</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-phone fi"></i>
                        <input type="text" name="telephone" class="form-control"
                               placeholder="20 000 000"
                               value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
                    </div>
                </div>

                <hr class="field-sep">

                <div class="mb-3">
                    <label class="form-label">Mot de passe *</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock fi"></i>
                        <input type="password" name="mot_de_passe" id="mot_de_passe"
                               class="form-control" placeholder="Min. 6 caractères"
                               required oninput="checkStrength(this.value)">
                        <i class="fas fa-eye toggle-pass" onclick="togglePass('mot_de_passe', this)"></i>
                    </div>
                    <div class="strength-bar">
                        <div class="strength-fill" id="strength-fill"></div>
                    </div>
                    <small id="strength-label" style="font-family:'DM Sans',sans-serif;font-size:.73rem;color:#aaa;"></small>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirmer le mot de passe *</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock fi"></i>
                        <input type="password" name="confirm_mdp" id="confirm_mdp"
                               class="form-control" placeholder="Répétez le mot de passe"
                               required oninput="checkMatch()">
                        <i class="fas fa-eye toggle-pass" onclick="togglePass('confirm_mdp', this)"></i>
                    </div>
                    <small id="match-label" style="font-family:'DM Sans',sans-serif;font-size:.73rem;"></small>
                </div>

                <button type="submit" class="btn-register">
                    <i class="fas fa-paw me-2"></i> Créer mon compte
                </button>
            </form>

            <div class="login-link">
                Déjà un compte ? <a href="login.php">Se connecter</a>
            </div>
        </div>
    </div>
</div>

<script>
function togglePass(id, icon) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
}

function checkStrength(val) {
    const fill  = document.getElementById('strength-fill');
    const label = document.getElementById('strength-label');
    let score = 0;
    if (val.length >= 6)           score++;
    if (val.length >= 10)          score++;
    if (/[A-Z]/.test(val))         score++;
    if (/[0-9]/.test(val))         score++;
    if (/[^A-Za-z0-9]/.test(val))  score++;
    const levels = [
        { w:'20%',  bg:'#ef4444', txt:'Très faible' },
        { w:'40%',  bg:'#f97316', txt:'Faible'      },
        { w:'60%',  bg:'#eab308', txt:'Moyen'       },
        { w:'80%',  bg:'#84cc16', txt:'Bon'         },
        { w:'100%', bg:'#22c55e', txt:'Excellent'   },
    ];
    const lvl = levels[Math.min(score, 4)];
    fill.style.width      = val.length ? lvl.w  : '0%';
    fill.style.background = lvl.bg;
    label.textContent     = val.length ? lvl.txt : '';
    label.style.color     = lvl.bg;
}

function checkMatch() {
    const mdp     = document.getElementById('mot_de_passe').value;
    const confirm = document.getElementById('confirm_mdp').value;
    const label   = document.getElementById('match-label');
    if (!confirm) { label.textContent = ''; return; }
    if (mdp === confirm) {
        label.textContent = '✓ Les mots de passe correspondent';
        label.style.color = '#22c55e';
    } else {
        label.textContent = '✗ Les mots de passe ne correspondent pas';
        label.style.color = '#ef4444';
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>