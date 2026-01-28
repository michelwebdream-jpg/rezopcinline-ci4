<?php
/**
 * Point d'entrée à la racine de l'application
 * Redirige vers public/index.php avec le chemin correct
 */

// Récupérer le chemin demandé
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$queryString = $_SERVER['QUERY_STRING'] ?? '';

// Enlever le query string de l'URI
if ($queryString) {
    $requestUri = str_replace('?' . $queryString, '', $requestUri);
}

// Enlever le slash initial et 'index.php' si présent
$path = ltrim($requestUri, '/');
if ($path === 'index.php') {
    $path = '';
}

// Construire le chemin vers public/index.php
$publicPath = __DIR__ . '/public/index.php';

// Si le fichier existe, ajuster les variables serveur et l'inclure
if (file_exists($publicPath)) {
    // Ajuster SCRIPT_NAME pour que CodeIgniter pense que le script est dans public/
    $_SERVER['SCRIPT_NAME'] = '/public/index.php';
    
    // Ajuster REQUEST_URI pour enlever le préfixe si nécessaire
    // (CodeIgniter utilisera SCRIPT_NAME pour déterminer le baseURL)
    
    // Définir PATH_INFO avec le chemin demandé
    if (!empty($path)) {
        $_SERVER['PATH_INFO'] = '/' . $path;
    } else {
        // Si pas de chemin, PATH_INFO doit être vide ou '/'
        $_SERVER['PATH_INFO'] = '/';
    }
    
    // Changer le répertoire de travail vers public/ pour que les chemins relatifs fonctionnent
    chdir(__DIR__ . '/public');
    
    // Inclure le fichier index.php de CodeIgniter
    require $publicPath;
} else {
    http_response_code(404);
    echo 'File not found';
}
