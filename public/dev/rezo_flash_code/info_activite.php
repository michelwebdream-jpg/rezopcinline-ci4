<?PHP

// Headers CORS pour permettre les requêtes cross-origin depuis localhost
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

// ------------------------------------------------------------
// DEBUG: vérifier quelle version est réellement servie
// ------------------------------------------------------------
if (isset($_GET['__ver'])) {
	header('Content-Type: text/plain; charset=utf-8');
	$hostname = $_SERVER['HTTP_HOST'] ?? '';
	$script = $_SERVER['SCRIPT_NAME'] ?? '';
	echo "info_activite.php VERSION=2026-01-27\n";
	echo "HOST={$hostname}\n";
	echo "SCRIPT_NAME={$script}\n";
	echo "__DIR__=" . __DIR__ . "\n";
	echo "APP_ROOT=" . dirname(__DIR__, 4) . "\n";
	exit;
}

header('Expires: Thu, 01 Jan 1970 00:00:00 GMT, -1');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

# fonction permettant l'encodage des caractres accentuŽs
function getFormatedText($texte){ 
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
// Corrigé pour PHP 7.2+ : __autoload() est déprécié, utiliser spl_autoload_register()
if(!function_exists("autoload_dbconnect")){ 
	function autoload_dbconnect($class_name){
		$file = __DIR__ . '/classes/class_' . $class_name . '.php';
		if (is_file($file)) {
			require_once($file);
		}
	}
}
spl_autoload_register('autoload_dbconnect');

// Définir le fichier de log (CI4: writable/logs)
// __DIR__ = .../public/dev/rezo_flash_code -> remonter à la racine projet
$app_root = dirname(__DIR__, 4);
$log_dir = $app_root . '/writable/logs';
$log_file = $log_dir . '/db_debug.log';

if (!is_dir($log_dir)) {
	// Ne pas faire planter le script si le dossier n'existe pas (hébergement mutualisé)
	@mkdir($log_dir, 0755, true);
}

function safe_debug_log($message) {
	global $log_file;
	@file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// ------------------------------------------------------------
// MODE PROXY (serveurs sans BDD locale)
// ------------------------------------------------------------
// Sur tout hôte différent de www.web-dream.fr, on proxy vers la prod
// pour éviter d'avoir à configurer la BDD sur le serveur de test.
$hostname = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$is_prod_host = (stripos($hostname, 'www.web-dream.fr') !== false);

// Détecter si on est en local
$is_local = false;
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

// Fonction pour détecter le chemin de base de l'application
function detectAppBasePath() {
	$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
	// Détecter automatiquement les sous-dossiers connus: rezopcinline ou rezoci4
	// Exemples:
	// - /rezopcinline/public/dev/... -> /rezopcinline/
	// - /rezoci4/public/dev/... -> /rezoci4/
	if (preg_match('#/(rezopcinline|rezoci4)/public/dev/#', $scriptName, $matches)) {
		return '/' . $matches[1] . '/';
	}
	// Sinon, pas de sous-dossier (sous-domaine ou racine)
	return '/';
}

// Détecter le chemin de base
$appBasePath = detectAppBasePath();

// Si on est en local OU sur un serveur de test (pas www.web-dream.fr), proxy vers la prod
if ($is_local || !$is_prod_host) {
	safe_debug_log("[info_activite] Mode PROXY détecté (host=" . $hostname . ") - Proxy vers serveur PRODUCTION");
	
	// Préparer les données POST
	$postData = '';
	foreach ($_POST as $k => $v) {
		$postData .= $k . '=' . urlencode($v) . '&';
	}
	$postData = rtrim($postData, '&');
	
	// Faire une requête cURL vers le serveur de production
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://www.web-dream.fr/dev/rezo_flash_code/info_activite.php');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // Pour éviter les problèmes de certificat
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	
	$output = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	if (curl_errno($ch)) {
		$error = curl_error($ch);
		safe_debug_log("[info_activite] ERREUR cURL: " . $error);
		curl_close($ch);
		ob_clean();
		echo "return_txt=-1";
		exit;
	}
	
	curl_close($ch);
	
	if ($httpCode >= 400) {
		safe_debug_log("[info_activite] ERREUR HTTP: " . $httpCode);
		ob_clean();
		echo "return_txt=-1";
		exit;
	}
	
	safe_debug_log("[info_activite] Réponse du serveur production reçue (HTTP $httpCode)");
	ob_clean();
	echo $output;
	exit;
}

// Si on est en production, utiliser la connexion locale normale
// CREATE DATABASE OBJECT
$db = new DbConnect();
$db->show_errors();
// SET NAMES n'est plus nécessaire car défini automatiquement dans connect()

$mon_code = isset($_POST['liste_des_codes']) ? $_POST['liste_des_codes'] : '';
$code_PC = isset($_POST['code_PC']) ? $_POST['code_PC'] : '';
$plateforme = isset($_POST['plateforme']) ? $_POST['plateforme'] : '';

safe_debug_log("[info_activite] liste_des_codes reçue: " . substr($mon_code, 0, 200));

if (empty($mon_code)) {
	ob_clean();
	echo "return_txt=-1";
	exit;
}

$mon_array_code=explode("{}",$mon_code);
$mon_array_code=implode("','",$mon_array_code);
$statut="";


// Recherche des membres - UN SEUL SELECT qui sera réutilisé
$sql = "SELECT * FROM `REZO` WHERE moncode IN ('$mon_array_code');"; 
safe_debug_log("[info_activite] SQL SELECT (production): " . substr($sql, 0, 200));
$result = $db->query($sql);
$rows_data = array(); // Stocker les données pour éviter le SELECT dupliqué
$codes_inactifs = array(); // Stocker les codes à mettre à jour en une seule requête

if($result && $result->num_rows){
	while($row = $result->fetch_array(MYSQLI_ASSOC)){
		// Stocker les données pour réutilisation
		$rows_data[] = $row;
		
		// Identifier les utilisateurs inactifs pour UPDATE groupé
		$dernieredateactivee = $db->prepare($row['derniere_inscription']);
		$code = $db->prepare($row['moncode']);
		$ts = strtotime($dernieredateactivee);
		if ($ts<(time()-250))
		{
			$codes_inactifs[] = $code;
		}
	}
	
	// OPTIMISATION: Regrouper tous les UPDATE statut='inactif' en une seule requête
	if (!empty($codes_inactifs)) {
		$codes_inactifs_escaped = array();
		$db_connection = $db->Connection();
		foreach ($codes_inactifs as $code) {
			$codes_inactifs_escaped[] = "'" . $db_connection->real_escape_string($code) . "'";
		}
		$codes_inactifs_str = implode(',', $codes_inactifs_escaped);
		$sql = "UPDATE `REZO` SET `statut`='inactif' WHERE `moncode` IN ($codes_inactifs_str);";
		safe_debug_log("[info_activite] SQL UPDATE statut groupé: " . substr($sql, 0, 200));
		$db->query($sql);
	}
}

/********************************************/
if (!empty($code_PC) && !empty($plateforme))
{
	// Échapper les valeurs pour éviter les injections SQL
	$db_connection = $db->Connection();
	$code_PC_escaped = $db_connection->real_escape_string($code_PC);
	$plateforme_escaped = $db_connection->real_escape_string($plateforme);
	
	$sql = "UPDATE `REZO` SET `geolocparpc`='{$code_PC_escaped}',`plateforme`='{$plateforme_escaped}',`date_geolocparpc`=NOW() WHERE moncode IN ('$mon_array_code');";
	
	safe_debug_log("[info_activite] SQL UPDATE geolocparpc: " . substr($sql, 0, 200));
	$db->query($sql);

}

$resultat="";					
// OPTIMISATION: Réutiliser les données du premier SELECT au lieu de refaire une requête
// Les données sont déjà dans $rows_data
if (!empty($rows_data)) {
	safe_debug_log("[info_activite] Réutilisation des données du premier SELECT (économie d'une requête)");
	foreach($rows_data as $row){
		$code = $db->prepare($row['moncode']);
		$statut= $db->prepare($row['statut']);
		$nom = stripcslashes($db->prepare($row['nom']));
		$prenom = stripcslashes($db->prepare($row['prenom']));
		$indicatif = stripcslashes($db->prepare($row['indicatif']));
		$longitude = $db->prepare($row['longitude']);
		$latitude = $db->prepare($row['latitude']);
		$etat=$db->prepare($row['etat']);
		$iconid=$db->prepare($row['iconid']);
		$tel=$db->prepare($row['mail']);	
		$precision=$db->prepare($row['precision']);	
		$rapport=$db->prepare($row['rapport']);	
		if ($rapport=="")
		{
			$rapport=" ";
		}
		$document_envoye=$db->prepare($row['document_envoye']);
		if ($document_envoye=="" || !isset($document_envoye) || empty($document_envoye))
		{
			$document_envoye=" ";
		}
		$resultat.=$code."><".$statut."><".$nom."><".$prenom."><".$indicatif."><".$longitude."><".$latitude."><".$etat."><".$iconid."><".$tel."><".$precision."><".$rapport."><".$document_envoye."\n";
	}
}

if (!empty($rows_data) && count($rows_data) > 0) 
{
	$resultat = substr($resultat, 0, -1);  
	
	safe_debug_log("[info_activite] SUCCESS - " . count($rows_data) . " membre(s) trouvé(s)");
	
	ob_clean();
	echo "return_txt=$resultat";
    
     $sql = "UPDATE `REZO` SET `document_envoye`='' WHERE moncode IN ('$mon_array_code');";
     $result =$db->query($sql);
    
}else
{
	safe_debug_log("[info_activite] ERROR - Aucun membre trouvé pour les codes: " . substr($mon_array_code, 0, 200));
	ob_clean();
	echo "return_txt=-1"; 
}


?>
