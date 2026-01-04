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

// Détecter si on est en local
$is_local = false;
$hostname = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$local_indicators = array('localhost', '127.0.0.1', '::1', 'local', '.local', '.dev');
foreach ($local_indicators as $indicator) {
	if (stripos($hostname, $indicator) !== false) {
		$is_local = true;
		break;
	}
}
if (!$is_local && filter_var($hostname, FILTER_VALIDATE_IP) !== false) {
	$is_local = true;
}

// Si on est en local, faire une requête cURL vers le serveur de production
if ($is_local) {
	// Construire l'URL du serveur de production
	$url = 'https://www.web-dream.fr/dev/rezo_galerie/backend.php';
	
	// Préparer les données POST
	$postData = '';
	foreach ($_POST as $k => $v) {
		$postData .= $k . '=' . urlencode($v) . '&';
	}
	$postData = rtrim($postData, '&');
	
	// Faire une requête cURL vers le serveur de production
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // Pour éviter les problèmes de certificat
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	
	$output = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	if (curl_errno($ch)) {
		$error = curl_error($ch);
		curl_close($ch);
		ob_clean();
		echo "error";
		exit;
	}
	
	curl_close($ch);
	
	if ($httpCode >= 400) {
		ob_clean();
		echo "error";
		exit;
	}
	
	// Remplacer les URLs relatives par des URLs absolues vers le serveur de production
	// pour que les images soient servies depuis le serveur de production
	$output = str_replace(
		'href="/dev/rezo_galerie/',
		'href="https://www.web-dream.fr/dev/rezo_galerie/',
		$output
	);
	$output = str_replace(
		'source="/dev/rezo_galerie/',
		'source="https://www.web-dream.fr/dev/rezo_galerie/',
		$output
	);
	// Remplacer aussi les chemins relatifs sans slash initial
	$output = preg_replace(
		'/(href|source)="(?!https?:\/\/)([^"]+dev\/rezo_galerie\/[^"]+)"/',
		'$1="https://www.web-dream.fr/$2"',
		$output
	);
	
	ob_clean();
	echo $output;
	exit;
}

// Si on est en production, rediriger vers le fichier de production
// (ce fichier ne devrait normalement pas être appelé en production)
header('Location: https://www.web-dream.fr/dev/rezo_galerie/backend.php');
exit;

?>

