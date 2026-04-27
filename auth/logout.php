<?php
session_start();
session_destroy();
header('Location: /Veterinaire/index.php');
exit;
?>