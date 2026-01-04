<?php
// Test CodeIgniter 4 avec affichage d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Test CodeIgniter 4</h1>";

try {
    // Définir FCPATH
    define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
    
    // Charger Paths
    require FCPATH . '../app/Config/Paths.php';
    $paths = new Config\Paths();
    
    echo "<h2>1. Chemins chargés</h2>";
    echo "APPPATH: " . $paths->appDirectory . "<br>";
    echo "System: " . $paths->systemDirectory . "<br>";
    
    // Vérifier que les fichiers existent
    echo "<h2>2. Vérification des fichiers</h2>";
    echo "Paths.php: " . (file_exists($paths->appDirectory . '/Config/Paths.php') ? "OK" : "MANQUANT") . "<br>";
    echo "App.php: " . (file_exists($paths->appDirectory . '/Config/App.php') ? "OK" : "MANQUANT") . "<br>";
    echo "Routes.php: " . (file_exists($paths->appDirectory . '/Config/Routes.php') ? "OK" : "MANQUANT") . "<br>";
    echo "Signup.php: " . (file_exists($paths->appDirectory . '/Controllers/Signup.php') ? "OK" : "MANQUANT") . "<br>";
    
    // Charger Boot
    echo "<h2>3. Chargement de Boot</h2>";
    require $paths->systemDirectory . '/Boot.php';
    echo "Boot.php chargé<br>";
    
    // Tester le boot
    echo "<h2>4. Test du boot</h2>";
    $result = CodeIgniter\Boot::bootWeb($paths);
    echo "Boot terminé avec code: " . $result . "<br>";
    
} catch (Throwable $e) {
    echo "<h2 style='color:red;'>ERREUR</h2>";
    echo "<strong>Message:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Fichier:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Ligne:</strong> " . $e->getLine() . "<br>";
    echo "<strong>Trace:</strong><pre>" . $e->getTraceAsString() . "</pre>";
}
?>

