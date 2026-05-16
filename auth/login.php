<?php
require_once '../includes/header.php';
require_once '../config/db.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mdp   = $_POST['mot_de_passe'];

    if (empty($email) || empty($mdp)) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT id, nom, prenom, email, mot_de_passe, role FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($mdp, $user['mot_de_passe'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_nom']  = $user['nom'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
               header('Location: http://localhost/Veterinaire/admin/index.php');
            } else {
                header('Location: http://localhost/Veterinaire/user/dashboard.php');
            }
            exit;
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    }
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap');

.login-wrap {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px 16px;
}

.login-box {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 40px rgba(0,0,0,.08);
    overflow: hidden;
    width: 100%;
    max-width: 440px;
}

/* ── Header ── */
.login-top {
    background: linear-gradient(135deg, #1a3d2b 0%, #40916c 100%);
    padding: 28px 36px 22px;
    text-align: center;
}
.login-top .paw { font-size: 1.6rem; margin-bottom: 6px; display: block; }
.login-top h2 {
    font-family: 'Playfair Display', serif;
    color: #fff;
    font-size: 1.45rem;
    margin: 0 0 4px;
}
.login-top p {
    font-family: 'DM Sans', sans-serif;
    color: rgba(255,255,255,.65);
    font-size: .82rem;
    margin: 0;
}

/* ── Body ── */
.login-body { padding: 28px 36px 26px; }

.login-body .form-label {
    font-family: 'DM Sans', sans-serif;
    font-size: .8rem;
    font-weight: 600;
    color: #444;
    margin-bottom: 5px;
    display: block;
}

/* ── Input wrapper ── */
.iw {
    display: flex;
    align-items: center;
    border: 1.5px solid #e8e8e2;
    border-radius: 10px;
    background: #fafaf7;
    transition: border-color .2s, box-shadow .2s, background .2s;
    overflow: hidden;
}
.iw:focus-within {
    border-color: #40916c;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(64,145,108,.1);
}
.iw .iw-icon {
    padding: 0 10px 0 13px;
    color: #c0c0b8;
    font-size: .78rem;
    flex-shrink: 0;
    pointer-events: none;
}
.iw .iw-toggle {
    padding: 0 13px;
    color: #c0c0b8;
    font-size: .78rem;
    cursor: pointer;
    flex-shrink: 0;
    transition: color .2s;
}
.iw .iw-toggle:hover { color: #40916c; }
.iw input {
    flex: 1;
    min-width: 0;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    background: transparent !important;
    padding: 10px 6px 10px 0;
    font-family: 'DM Sans', sans-serif;
    font-size: .9rem;
    color: #222;
}
.iw input::placeholder { color: #c8c8c0; }

/* ── Submit ── */
.btn-login {
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
.btn-login:hover {
    background: #40916c;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(26,61,43,.22);
}

.register-link {
    font-family: 'DM Sans', sans-serif;
    text-align: center;
    margin-top: 16px;
    font-size: .85rem;
    color: #888;
}
.register-link a { color: #40916c; font-weight: 600; text-decoration: none; }
.register-link a:hover { text-decoration: underline; }

.alert-custom {
    font-family: 'DM Sans', sans-serif;
    font-size: .85rem;
    border-radius: 10px;
    padding: 10px 14px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
</style>

<div class="login-wrap">
    <div class="login-box">

        <div class="login-top">
            <span class="paw">🐾</span>
            <h2>Se connecter</h2>
            <p>Bon retour sur VétoCare !</p>
        </div>

        <div class="login-body">

            <?php if ($erreur): ?>
                <div class="alert-custom">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <div class="iw">
                        <i class="fas fa-envelope iw-icon"></i>
                        <input type="email" name="email" placeholder="exemple@email.com" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Mot de passe *</label>
                    <div class="iw">
                        <i class="fas fa-lock iw-icon"></i>
                        <input type="password" name="mot_de_passe" id="mot_de_passe"
                               placeholder="Votre mot de passe" required>
                        <i class="fas fa-eye iw-toggle" onclick="togglePass('mot_de_passe', this)"></i>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i> Se connecter
                </button>
            </form>

            <div class="register-link">
                Pas encore de compte ? <a href="register.php">S'inscrire</a>
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
</script>

<?php require_once '../includes/footer.php'; ?>