<?php
// Fichier de test pour vérifier que PHP fonctionne
echo "PHP fonctionne !<br>";
echo "DocumentRoot: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'NON_DEFINI') . "<br>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NON_DEFINI') . "<br>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NON_DEFINI') . "<br>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NON_DEFINI') . "<br>";
phpinfo();
?>
