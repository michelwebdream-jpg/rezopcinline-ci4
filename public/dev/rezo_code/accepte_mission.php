<?PHP


header('Expires: Thu, 01 Jan 1970 00:00:00 GMT, -1');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

# fonction permettant l'encodage des caractères accentués (compatible PHP 7.4 et 8.3)
function getFormatedText($texte) {
	$texte = (function_exists('mb_convert_encoding')) ? mb_convert_encoding($texte, 'UTF-8', 'ISO-8859-1') : utf8_encode($texte);
	$texte = str_replace("\r", "\n", $texte);
	$texte = str_replace("<br>", "\n", $texte);
	$texte = stripcslashes($texte);
	return $texte;
} 

// AUTOLOAD CLASS OBJECTS... YOU CAN USE INCLUDES IF YOU PREFER
if (!function_exists('autoload_classes')) {
	function autoload_classes($class_name){
		$file = __DIR__ . '/classes/class_' . $class_name . '.php';
		if (is_file($file)) {
			require_once($file);
		}
	}
}
spl_autoload_register('autoload_classes');

// CREATE DATABASE OBJECT ( MAKE SURE TO CHANGE LOGIN INFO IN CLASS FILE )
$db = new DbConnect();
$db->show_errors();
$db->query("SET NAMES 'utf8'");

$mon_code_perso = $db->prepare(isset($_POST['mon_code']) ? $_POST['mon_code'] : '') . ',';
// Id de la table REZO_FLASH_MISSION (clé auto_increment, type INT)
$id_de_la_mission = (int) (isset($_POST['id_de_la_mission']) ? $_POST['id_de_la_mission'] : 0);

$sql = "UPDATE `REZO_FLASH_MISSION` SET `membres_lu_mission`=CONCAT(IFNULL(membres_lu_mission,''), '$mon_code_perso') WHERE Id='$id_de_la_mission';";

$result = $db->query($sql);		
echo "ok";
//echo "return_txt=1";

?>