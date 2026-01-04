<?php
// Test minimal pour voir si le problème vient de CI4 ou de PHP/MAMP
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Test Minimal</h1>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Date: " . date('Y-m-d H:i:s') . "<br>";

// Test inclusion
echo "<h2>Test inclusion de fichiers</h2>";
$pathsFile = __DIR__ . '/../app/Config/Paths.php';
if (file_exists($pathsFile)) {
    echo "Paths.php existe<br>";
    try {
        require_once $pathsFile;
        echo "Paths.php chargé<br>";
        $paths = new Config\Paths();
        echo "APPPATH: " . $paths->appDirectory . "<br>";
    } catch (Exception $e) {
        echo "ERREUR: " . $e->getMessage() . "<br>";
    } catch (Error $e) {
        echo "ERREUR FATALE: " . $e->getMessage() . "<br>";
        echo "Fichier: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    }
} else {
    echo "Paths.php N'EXISTE PAS à: $pathsFile<br>";
}

echo "<br><strong>Si vous voyez ce message, PHP fonctionne !</strong>";
?>

