<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinique Vétérinaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Veterinaire/public/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="/Veterinaire/">Clinique Vétérinaire</a>
            <div class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['user_id'])):?>
                    <a class="nav-link text-white" href="/Veterinaire/user/dashboard.php">Mon espace</a>
                    <a class="nav-link text-white" href="/Veterinaire/auth/logout.php">Déconnexion</a>
                    <?php else: ?>
                        <a class="nav-link text-white" href="/Veterinaire/auth/login.php">Connexion</a>
                        <a class="nav-link text-white" href="/Veterinaire/auth/register.php">Inscription</a>
                        <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container mt-4"> 
</body>
</html>