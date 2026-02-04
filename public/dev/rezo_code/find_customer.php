<?PHP

header('Content-Type: text/html; charset=UTF-8');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT, -1');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

# fonction permettant l'encodage des caractères accentués (compatible PHP 7.4 et 8.3)
function getFormatedText($texte){
$texte = (function_exists('mb_convert_encoding')) ? mb_convert_encoding($texte, 'ISO-8859-1', 'UTF-8') : utf8_decode($texte); 
$texte =str_replace( "\r", "\n", $texte); 
$texte =str_replace( "<br>","\n", $texte);
$texte =stripcslashes($texte); 
//$texte =str_replace( "'", "\'", $texte); 
//$texte =mysql_real_escape_string($texte); 
//$texte =addslashes($texte);
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

$mon_nom="";
$mon_prenom="";
$mon_mail="";
$mon_indicatif="";
$mon_iconid="";

$mon_code = $db->prepare(getFormatedText(isset($_POST['mon_code']) ? $_POST['mon_code'] : ''));

$result = false;
$db->query("SET NAMES 'utf8'");
// Recherche de l'adresse mail
$sql = "SELECT * FROM `REZO` WHERE moncode = '$mon_code';";
if ($result = $db->query($sql)) {
	if ($result->num_rows) {
		while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
			$mon_nom = $db->prepare($row['nom']);
			$mon_prenom = $db->prepare($row['prenom']);
			$mon_mail = $db->prepare($row['mail']);
			$mon_indicatif = $db->prepare($row['indicatif']);
			$mon_iconid = $db->prepare($row['iconid']);
		}
	}
}

if ($result && $result->num_rows > 0) 
{
	echo $mon_mail."\n".$mon_nom."\n".$mon_prenom."\n".$mon_indicatif."\n".$mon_iconid;
	
}else
{
	echo "-1"; 
}



?>