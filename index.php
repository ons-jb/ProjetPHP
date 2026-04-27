<?php
require_once 'config/db.php';
require_once 'includes/header.php';   
?>

<div class="text-center py-5">
    <h1 class="display-4"> Bienvenue à la clinique vétérinaire </h1>
    <p class="lead">Prenez soin de vos animaux avec nos services professinnels</p>
    <a href="auth/register.php" class="btn btn-success btn-lg me-2">S'inscrire</a>
    <a href="auth/login.php" class="btn btn-outline-success btn-lg">Se connecter</a>
</div>

<div class="row mt-5">
    <div class="col-md-4 text-center">
        <h3>🏥Consultations</h3>
        <p>Réservez un rendez-vous avec nos vétérinaires</p>
    </div>
    <div class="col-md-4 text-center">
        <h3>💊Produits</h3>
        <p>Commandez médicaments et accessoires</p>
    </div>
    <div class="col-md-4 text-center">
        <h3>🐕Vos animaux</h3>
        <p>Gérez le suivi de santé de vos animaux</p>
    </div>
</div>

<?php
require_once 'includes/footer.php' 
?>