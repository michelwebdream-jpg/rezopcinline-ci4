<?php
// Fichier de diagnostic pour CodeIgniter 4
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnostic CodeIgniter 4</h1>";

// 1. Vérifier PHP
echo "<h2>1. PHP</h2>";
echo "Version: " . PHP_VERSION . "<br>";
echo "Error Reporting: " . error_reporting() . "<br>";
echo "Display Errors: " . ini_get('display_errors') . "<br>";

// 2. Vérifier les chemins
echo "<h2>2. Chemins</h2>";
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
echo "FCPATH: " . FCPATH . "<br>";

$pathsConfig = FCPATH . '../app/Config/Paths.php';
if (file_exists($pathsConfig)) {
    require_once $pathsConfig;
    $paths = new Config\Paths();
    echo "APPPATH: " . $paths->appDirectory . "<br>";
    echo "System: " . $paths->systemDirectory . "<br>";
    echo "Writable: " . $paths->writableDirectory . "<br>";
    
    // Vérifier que les dossiers existent
    echo "<br><strong>Vérification des dossiers:</strong><br>";
    echo "app/ existe: " . (is_dir($paths->appDirectory) ? "OUI" : "NON") . "<br>";
    echo "system/ existe: " . (is_dir($paths->systemDirectory) ? "OUI" : "NON") . "<br>";
    echo "writable/ existe: " . (is_dir($paths->writableDirectory) ? "OUI" : "NON") . "<br>";
} else {
    echo "ERREUR: Paths.php non trouvé !<br>";
}

// 3. Vérifier .env
echo "<h2>3. Configuration .env</h2>";
$envFile = FCPATH . '../.env';
if (file_exists($envFile)) {
    echo ".env existe: OUI<br>";
    $envContent = file_get_contents($envFile);
    if (preg_match('/CI_ENVIRONMENT\s*=\s*(\w+)/', $envContent, $matches)) {
        echo "CI_ENVIRONMENT: " . $matches[1] . "<br>";
    }
    if (preg_match('/app\.baseURL\s*=\s*[\'"]([^\'"]+)[\'"]/', $envContent, $matches)) {
        echo "app.baseURL: " . $matches[1] . "<br>";
    }
} else {
    echo ".env existe: NON<br>";
}

// 4. Tester le chargement de CI4
echo "<h2>4. Test chargement CI4</h2>";
try {
    require FCPATH . '../app/Config/Paths.php';
    $paths = new Config\Paths();
    require $paths->systemDirectory . '/Boot.php';
    echo "Boot.php chargé avec succès<br>";
    
    // Tester le chargement de l'app
    $appConfig = $paths->appDirectory . '/Config/App.php';
    if (file_exists($appConfig)) {
        require_once $appConfig;
        echo "App.php chargé<br>";
    }
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "ERREUR FATALE: " . $e->getMessage() . "<br>";
    echo "Fichier: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

// 5. Vérifier les permissions
echo "<h2>5. Permissions</h2>";
$writable = $paths->writableDirectory ?? FCPATH . '../writable';
echo "writable/ est accessible en écriture: " . (is_writable($writable) ? "OUI" : "NON") . "<br>";
echo "writable/logs/ est accessible en écriture: " . (is_writable($writable . '/logs') ? "OUI" : "NON") . "<br>";

echo "<br><strong>Diagnostic terminé.</strong>";
?>

