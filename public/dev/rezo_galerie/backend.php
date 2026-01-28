<?PHP

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

// ------------------------------------------------------------
// OPTION B: Générer l'XML depuis le stockage local (/public/dev/rezo_galerie)
// ------------------------------------------------------------
// Attendu par le JS: une réponse XML avec <items>...</items> puis, après </items>,
// une valeur de taille (utilisée par l'UI).

$baseUrl = isset($_POST['base_url']) ? (string) $_POST['base_url'] : '';
$baseUrlThumb = isset($_POST['base_url_thumb']) ? (string) $_POST['base_url_thumb'] : 'thumb/';
$codePc = isset($_POST['code_pc']) ? (string) $_POST['code_pc'] : '';
$codeMoyen = isset($_POST['code_moyen']) ? (string) $_POST['code_moyen'] : '';

if ($codePc === '' || $codeMoyen === '') {
	ob_clean();
	echo "error";
	exit;
}

// Sécuriser (éviter traversal)
$codePcSafe = preg_replace('/[^a-zA-Z0-9_-]/', '', $codePc);
$codeMoyenSafe = preg_replace('/[^a-zA-Z0-9 _\\-\\.]/', '', $codeMoyen);

$origDir = __DIR__ . '/' . $codePcSafe . '/' . $codeMoyenSafe;
$thumbDir = $origDir . '/thumb';

if (!is_dir($origDir)) {
	ob_clean();
	echo "error";
	exit;
}

// Construire les URLs absolues
$baseUrl = rtrim($baseUrl, '/') . '/';
$thumbUrlBase = $baseUrl . $codePcSafe . '/' . rawurlencode($codeMoyenSafe) . '/' . trim($baseUrlThumb, '/') . '/';
$origUrlBase = $baseUrl . $codePcSafe . '/' . rawurlencode($codeMoyenSafe) . '/';

// Lister les fichiers images (on utilise le dossier thumb si présent, sinon le dossier original)
$listDir = is_dir($thumbDir) ? $thumbDir : $origDir;
$items = @scandir($listDir);
if (!is_array($items)) {
	ob_clean();
	echo "error";
	exit;
}

// Taille utilisateur (bytes) - dossier original + thumb
function dirSizeBytes(string $dir): int {
	$size = 0;
	$entries = @scandir($dir);
	if (!is_array($entries)) return 0;
	foreach ($entries as $e) {
		if ($e === '.' || $e === '..') continue;
		$p = $dir . '/' . $e;
		if (is_dir($p)) $size += dirSizeBytes($p);
		else $size += (int) @filesize($p);
	}
	return $size;
}
$sizeOfUser = dirSizeBytes(dirname($origDir)); // tout le codePc

// Générer XML
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><items></items>');

foreach ($items as $file) {
	if ($file === '.' || $file === '..') continue;
	$path = $listDir . '/' . $file;
	if (!is_file($path)) continue;

	$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
	if (!in_array($ext, ['jpg','jpeg','png','gif'], true)) continue;

	$source = (is_dir($thumbDir) ? $thumbUrlBase : $origUrlBase) . rawurlencode($file);
	$item = $xml->addChild('item');
	$item->addAttribute('label', $file);
	$item->addAttribute('source', $source);
}

$xmlString = $xml->asXML();
if ($xmlString === false) {
	ob_clean();
	echo "error";
	exit;
}

// IMPORTANT: l'ancien code ajoute une "taille" après </items>
ob_clean();
echo $xmlString . $sizeOfUser;
exit;

?>

