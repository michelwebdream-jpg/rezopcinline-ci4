<?php

// Headers CORS pour permettre les requêtes cross-origin depuis localhost
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$allowed_origins = array(
	'https://localhost',
	'http://localhost',
	'https://127.0.0.1',
	'http://127.0.0.1',
	'https://www.web-dream.fr',
	'https://rezopcinline-ci4.local',
	'http://rezopcinline-ci4.local'
);

if (in_array($origin, $allowed_origins)) {
	header('Access-Control-Allow-Origin: ' . $origin);
}
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(200);
	exit;
}

// Désactiver l'affichage des erreurs AVANT toute autre chose
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Démarrer le buffer de sortie pour capturer toute sortie non désirée
ob_start();

header('Expires: Thu, 01 Jan 1970 00:00:00 GMT, -1');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Content-Type: application/json');

// Récupérer le paramètre ImagePath
$imagePath = isset($_GET['ImagePath']) ? (string) $_GET['ImagePath'] : '';

// ------------------------------------------------------------
// OPTION B: Utiliser le stockage local dans /public/dev/rezo_galerie
// ------------------------------------------------------------
// Structure attendue:
// - /public/dev/rezo_galerie/<CODE_PC>/<DOSSIER>/... (originaux)
// - /public/dev/rezo_galerie/<CODE_PC>/<DOSSIER>/thumb/... (miniatures)

$codePc = trim($imagePath);
if ($codePc === '') {
	ob_clean();
	echo json_encode(['error' => 'Missing ImagePath']);
	exit;
}

// Sécuriser le code (éviter traversal)
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $codePc)) {
	ob_clean();
	echo json_encode(['error' => 'Invalid ImagePath']);
	exit;
}

$baseDir = __DIR__;
$userDir = $baseDir . '/' . $codePc;

if (!is_dir($userDir)) {
	// Pas de docs
	ob_clean();
	echo json_encode("-1");
	exit;
}

function dirSizeBytes(string $dir): int {
	$size = 0;
	$items = @scandir($dir);
	if (!is_array($items)) {
		return 0;
	}
	foreach ($items as $item) {
		if ($item === '.' || $item === '..') continue;
		$path = $dir . '/' . $item;
		if (is_dir($path)) {
			$size += dirSizeBytes($path);
		} else {
			$size += (int) @filesize($path);
		}
	}
	return $size;
}

function listFilesFlat(string $dir): array {
	$files = [];
	$items = @scandir($dir);
	if (!is_array($items)) {
		return $files;
	}
	foreach ($items as $item) {
		if ($item === '.' || $item === '..') continue;
		$path = $dir . '/' . $item;
		if (is_file($path)) {
			// Filtrer grossièrement les fichiers images/doc
			$ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
			if (in_array($ext, ['jpg','jpeg','png','gif','pdf'], true)) {
				$files[] = $item;
			}
		}
	}
	return $files;
}

$dirsOut = [];
$entries = @scandir($userDir);
if (is_array($entries)) {
	foreach ($entries as $entry) {
		if ($entry === '.' || $entry === '..') continue;
		$path = $userDir . '/' . $entry;
		if (!is_dir($path)) continue;
		// Ignorer d'éventuels dossiers techniques
		if ($entry === 'thumb') continue;

		$files = listFilesFlat($path);
		$dirsOut[] = [
			'folder' => $entry,
			'files'  => $files,
		];
	}
}

// Valeurs utilisées par l'UI (barre de progression)
$sizeOfUser = dirSizeBytes($userDir);
$sizeMaxPc = 100 * 1024 * 1024; // 100 MiB par défaut (cohérent avec les anciens écrans)

$payload = [
	'tree' => [
		'dirs' => $dirsOut,
	],
	'size_of_user' => (string) $sizeOfUser,
	'size_max_pc'  => (string) $sizeMaxPc,
];

ob_clean();
echo json_encode($payload);
exit;

