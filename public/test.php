<?php
// Test simple pour vérifier que PHP fonctionne
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "FCPATH: " . (defined('FCPATH') ? FCPATH : 'NOT DEFINED') . "<br>";
echo "APPPATH: " . (defined('APPPATH') ? APPPATH : 'NOT DEFINED') . "<br>";

// Test d'inclusion
$pathsConfig = __DIR__ . '/../app/Config/Paths.php';
if (file_exists($pathsConfig)) {
    echo "Paths.php exists: YES<br>";
    require_once $pathsConfig;
    $paths = new Config\Paths();
    echo "APPPATH from Paths: " . $paths->appDirectory . "<br>";
} else {
    echo "Paths.php exists: NO<br>";
}

// Test erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "Error reporting: " . error_reporting() . "<br>";
echo "Display errors: " . ini_get('display_errors') . "<br>";

echo "<br><strong>Test réussi ! PHP fonctionne.</strong>";
?>

