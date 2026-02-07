<?PHP

// Headers CORS pour permettre les requêtes cross-origin en développement
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$allowed_origins = array(
	'https://localhost',
	'http://localhost',
	'https://127.0.0.1',
	'http://127.0.0.1',
	'https://www.web-dream.fr'
);

if (in_array($origin, $allowed_origins)) {
	header('Access-Control-Allow-Origin: ' . $origin);
}
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Toujours éviter un 500 silencieux (OVH)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
@ob_start();

register_shutdown_function(function () {
	$e = error_get_last();
	if (!$e) {
		return;
	}
	$fatalTypes = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR);
	if (in_array($e['type'], $fatalTypes, true)) {
		error_log('[find_parametres_administrateur] FATAL: ' . $e['message'] . ' in ' . $e['file'] . ':' . $e['line']);
		if (function_exists('http_response_code')) {
			http_response_code(200);
		}
		@ob_clean();
		echo "return_txt=-1";
	}
});

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(200);
	exit;
}

header('Expires: Thu, 01 Jan 1970 00:00:00 GMT, -1');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

// ------------------------------------------------------------
// MODE PROXY (serveur de test sans BDD)
// ------------------------------------------------------------
// Sur le serveur de test (ex: rezoci4.web-dream.fr) on n'a pas de BDD locale.
// On proxy donc vers la prod (www.web-dream.fr) qui, elle, a accès aux données.
// En local (localhost, .local, 127.0.0.1) on a la BDD MAMP, donc on utilise la logique locale.
$hostname = $_SERVER['HTTP_HOST'] ?? '';
$is_prod_host = (stripos($hostname, 'www.web-dream.fr') !== false);
$is_local_with_db = (stripos($hostname, 'localhost') !== false)
	|| (stripos($hostname, '127.0.0.1') !== false)
	|| (stripos($hostname, '.local') !== false)
	|| (stripos($hostname, '::1') !== false);
if (!$is_prod_host && !$is_local_with_db) {
	$targetUrl = 'https://www.web-dream.fr/dev/rezo_flash_code/find_parametres_administrateur.php';
	$postData = http_build_query($_POST);

	$output = false;
	$httpCode = 0;

	if (function_exists('curl_init')) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $targetUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_USERAGENT, 'rezoci4-proxy/1.0');
		$output = curl_exec($ch);
		$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlErrNo = curl_errno($ch);
		$curlErr = curl_error($ch);
		curl_close($ch);

		if ($curlErrNo) {
			error_log('[find_parametres_administrateur] Proxy cURL error ' . $curlErrNo . ': ' . $curlErr . ' (target=' . $targetUrl . ')');
		}
	} else {
		// Fallback si cURL n'est pas dispo
		$ctx = stream_context_create([
			'http' => [
				'method'  => 'POST',
				'header'  => "Content-Type: application/x-www-form-urlencoded\r\nUser-Agent: rezoci4-proxy/1.0\r\n",
				'content' => $postData,
				'timeout' => 30,
			],
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
			],
		]);
		$output = @file_get_contents($targetUrl, false, $ctx);
		// Pas d'accès simple au code HTTP ici; on se base sur le contenu
	}

	if ($output === false || ($httpCode >= 400 && $httpCode !== 0) || trim((string)$output) === '') {
		@ob_clean();
		echo "return_txt=-1";
		exit;
	}

	@ob_clean();
	echo $output;
	exit;
}

# fonction permettant l'encodage des caractres accentuŽs
function getFormatedText($texte){ 
$texte = (string) $texte;
$texte =utf8_encode($texte); 
$texte =str_replace( "\r", "\n", $texte); 
$texte =str_replace( "<br>","\n", $texte);
$texte =stripcslashes($texte); 
//$texte =str_replace( "'", "\'", $texte); 
//$texte =mysql_real_escape_string($texte); 
//$texte =addslashes($texte);
return $texte; 
} 

// AUTOLOAD CLASS OBJECTS... YOU CAN USE INCLUDES IF YOU PREFER
if(!function_exists("autoload_classes")){ 
	function autoload_classes($class_name){
		$path = __DIR__ . '/classes/class_' . $class_name . '.php';
		if (is_file($path)) {
			require_once($path);
			return;
		}
		// Log utile en prod (OVH) quand le cwd n'est pas celui attendu
		error_log('[find_parametres_administrateur] Autoload failed for ' . $class_name . ' at ' . $path);
	}
}
spl_autoload_register('autoload_classes');

// CREATE DATABASE OBJECT ( MAKE SURE TO CHANGE LOGIN INFO IN CLASS FILE )
$db = new DbConnect();
$db->show_errors();
$db->query("SET NAMES 'utf8'");

$mon_code=getFormatedText($_POST['mon_code'] ?? '');
if ($mon_code === '') {
	echo "return_txt=-1";
	exit;
}

$db->query("SET NAMES 'utf8'");
// Recherche de l'adresse mail
$sql = "SELECT * FROM `REZO_FLASH` WHERE moncode = '$mon_code';"; 
$result = $db->query($sql);
if ($result && isset($result->num_rows) && $result->num_rows > 0) 
{
	while($row = $result->fetch_array(MYSQLI_ASSOC)){
		$centrage_carte = $db->prepare($row['centrage_carte']);
		$type_carte = $db->prepare($row['type_carte']);
		$alerte_sonore_depart_intervention = $db->prepare($row['alerte_sonore_depart_intervention']);
	}
	$txt=$centrage_carte."><".$type_carte."><".$alerte_sonore_depart_intervention;
	echo"return_txt=$txt";
}else
{
	echo "return_txt=-1"; 
}


?>