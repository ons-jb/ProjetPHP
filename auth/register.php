<?php
require_once '../includes/header.php';
require_once '../config/db.php';

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom']);
    $prenom   = trim($_POST['prenom']);
    $email    = trim($_POST['email']);
    $mdp      = $_POST['mot_de_passe'];
    $confirm  = $_POST['confirm_mdp'];
    $tel      = trim($_POST['telephone']);

    // Validation
    if (empty($nom) || empty($prenom) || empty($email) || empty($mdp)) {
        $erreur = "Tous les champs obligatoires doivent être remplis.";
    } elseif ($mdp !== $confirm) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($mdp) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        // Vérifier si email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $erreur = "Cet email est déjà utilisé.";
        } else {
            // Insérer l'utilisateur
            $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilisateurs 
                (nom, prenom, email, mot_de_passe, telephone) 
                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $mdp_hash, $tel]);

            $succes = "Compte créé avec succès ! Vous pouvez vous connecter.";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white text-center">
                <h4>🐾 Créer un compte</h4>
            </div>
            <div class="card-body">

                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>

                <?php if ($succes): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text" name="nom" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prénom *</label>
                            <input type="text" name="prenom" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mot de passe *</label>
                        <input type="password" name="mot_de_passe" id="mot_de_passe" 
                               class="form-control" required 
                               onkeyup="validerMotDePasse()">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirmer le mot de passe *</label>
                        <input type="password" name="confirm_mdp" id="confirm_mdp" 
                               class="form-control" required 
                               onkeyup="validerMotDePasse()">
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        S'inscrire
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="login.php">Déjà un compte ? Se connecter</a>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>