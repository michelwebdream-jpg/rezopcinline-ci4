<?php
/**
 * Point d'entrée à la racine de l'application (dans le sous-dossier /rezopcinline/)
 * Redirige vers public/index.php avec le chemin correct pour CI4
 */

// Préfixe de l'app dans l'URL (sous-dossier)
$basePath = '/rezopcinline';

// Récupérer le chemin demandé
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$queryString = $_SERVER['QUERY_STRING'] ?? '';

// Enlever le query string de l'URI
if ($queryString) {
    $requestUri = str_replace('?' . $queryString, '', $requestUri);
}

// Extraire le chemin et retirer le préfixe /rezopcinline/
$path = ltrim($requestUri, '/');
if (strpos($path, 'rezopcinline/') === 0) {
    $path = substr($path, strlen('rezopcinline/'));
} elseif ($path === 'rezopcinline' || $path === 'rezopcinline/') {
    $path = '';
}
if ($path === 'index.php') {
    $path = '';
}

// Construire le chemin vers public/index.php
$publicPath = __DIR__ . '/public/index.php';

// Si le fichier existe, ajuster les variables serveur et l'inclure
if (file_exists($publicPath)) {
    // SCRIPT_NAME : point d'entrée (pour que CI4 génère les bonnes URLs)
    $_SERVER['SCRIPT_NAME'] = $basePath . '/index.php';

    // REQUEST_URI : chemin complet avec préfixe (CI4 utilise baseURL pour l'interpréter)
    $_SERVER['REQUEST_URI'] = $basePath . '/' . ltrim($path, '/');
    if (empty($path)) {
        $_SERVER['REQUEST_URI'] = rtrim($_SERVER['REQUEST_URI'], '/') ?: $basePath . '/';
    }
    if ($queryString) {
        $_SERVER['REQUEST_URI'] .= '?' . $queryString;
    }

    // PATH_INFO : chemin pour le routeur CI4 (SANS le préfixe rezopcinline)
    $_SERVER['PATH_INFO'] = empty($path) ? '/' : '/' . $path;

    // FORCER baseURL pour les redirections et liens (avant que CI4 charge sa config)
    $forcedBaseURL = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'www.web-dream.fr') . $basePath . '/';
    putenv('app.baseURL=' . $forcedBaseURL);
    $_ENV['app.baseURL'] = $forcedBaseURL;
    
    // Changer le répertoire de travail vers public/ pour que les chemins relatifs fonctionnent
    chdir(__DIR__ . '/public');
    
    // Inclure le fichier index.php de CodeIgniter
    require $publicPath;
} else {
    http_response_code(404);
    echo 'File not found';
}
