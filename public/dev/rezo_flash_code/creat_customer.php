<?PHP

header('Expires: Thu, 01 Jan 1970 00:00:00 GMT, -1');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

// Ne pas afficher les erreurs (éviter 500 en prod), les logger si possible
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

# fonction permettant l'encodage des caractères accentués
function getFormatedText($texte){
    $texte = addslashes((string) $texte);
    return $texte;
}
/**************************************************************************/
function unique_id($l = 8) {
    return substr(md5(uniqid((string) mt_rand(), true)), 0, $l);
}

/**
 * Charge la liste des item_name EDD autorisés.
 */
function get_edd_item_names()
{
    static $items = null;

    if ($items !== null) {
        return $items;
    }

    $configFile = __DIR__ . '/config_licences_edd.php';
    if (is_file($configFile)) {
        $loaded = require $configFile;
        if (is_array($loaded) && !empty($loaded)) {
            $items = $loaded;
            return $items;
        }
    }

    // Fallback si config absente : les 2 produits connus
    $items = ['cle-de-licence-rezo-pc-inline-1-an', 'cle-de-licence-rezo-pc-inline-6-mois'];
    return $items;
}

// Autoload DOIT être défini avant spl_autoload_register (sinon fatal en PHP 8)
if (!function_exists('autoload_classes')) {
    function autoload_classes($class_name) {
        $file = __DIR__ . '/classes/class_' . $class_name . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
}
spl_autoload_register('autoload_classes');
/**************************************************************************/
/**
 * Teste la licence sur tous les produits EDD connus.
 * Retourne les mêmes codes métier qu'avant :
 * - "0" : licence inactive pour ce mail
 * - "2" : licence associée à un autre compte / erreur générique
 * - "3" : licence inexistante
 * - "4" : licence déjà active pour ce mail
 * - "5" : licence expirée pour ce mail
 */
function test_licence($licence, $mail)
{
    $items = get_edd_item_names();
    $licenceEncoded = urlencode((string) $licence);
    $email = (string) $mail;

    $sawExistingLicenseOtherEmail = false;
    $sawInvalid = false;

    foreach ($items as $itemName) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://www.web-dream.fr/?edd_action=check_license&item_name=' . $itemName . '&license=' . $licenceEncoded . '&url=http://www.web-dream.fr'
        ));
        $resp = curl_exec($curl);
        curl_close($curl);

        $myArrayReponse = json_decode($resp, true);

        if (!is_array($myArrayReponse) || !isset($myArrayReponse['license'])) {
            continue;
        }

        $status        = strtolower(trim((string) ($myArrayReponse['license'] ?? '')));
        $customerEmail = isset($myArrayReponse['customer_email']) ? (string) $myArrayReponse['customer_email'] : '';

        if ($status === 'invalid') {
            $sawInvalid = true;
            continue;
        }

        // Licence trouvée pour un autre mail
        if ($customerEmail !== $email && $customerEmail !== '') {
            $sawExistingLicenseOtherEmail = true;
            continue;
        }

        // Licence associée à ce mail (ou EDD n'a pas renvoyé l'email) : on renvoie le code adapté
        if ($status === 'valid' || $status === 'active') {
            return "4";
        }
        if ($status === 'inactive') {
            return "0";
        }
        if ($status === 'expired') {
            return "5";
        }
    }

    if ($sawExistingLicenseOtherEmail) {
        // Licence existante mais associée à un autre compte
        return "2";
    }

    if ($sawInvalid) {
        // Licence inexistante sur tous les produits testés
        return "3";
    }

    // Cas générique (pas de réponse exploitable)
    return "2";
}
/**************************************************************************/
function get_date_licence($licence, $mail)
{
    $items = get_edd_item_names();
    $licenceEncoded = urlencode((string) $licence);
    $email = (string) $mail;

    foreach ($items as $itemName) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://www.web-dream.fr/?edd_action=check_license&item_name=' . $itemName . '&license=' . $licenceEncoded . '&url=http://www.web-dream.fr'
        ));
        $resp = curl_exec($curl);
        curl_close($curl);

        $myArrayReponse = json_decode($resp, true);

        if (!is_array($myArrayReponse) || !isset($myArrayReponse['license'])) {
            continue;
        }

        $customerEmail = isset($myArrayReponse['customer_email']) ? (string) $myArrayReponse['customer_email'] : '';
        $status        = strtolower(trim((string) ($myArrayReponse['license'] ?? '')));

        $emailOk = ($customerEmail === $email) || ($customerEmail === '');
        if (($status === 'valid' || $status === 'active' || $status === 'expired' || $status === 'inactive') && $emailOk && isset($myArrayReponse['expires'])) {
            return $myArrayReponse['expires'];
        }
    }

    return '';
}
/**************************************************************************/
function activate_licence($licence, $mail)
{
    $items = get_edd_item_names();
    $licenceEncoded = urlencode((string) $licence);
    $email = (string) $mail;

    foreach ($items as $itemName) {
        // Vérifier d'abord que cette licence correspond bien à ce produit et à ce mail
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://www.web-dream.fr/?edd_action=check_license&item_name=' . $itemName . '&license=' . $licenceEncoded . '&url=http://www.web-dream.fr'
        ));
        $resp = curl_exec($curl);
        curl_close($curl);

        $myArrayReponse = json_decode($resp, true);

        if (!is_array($myArrayReponse) || !isset($myArrayReponse['license'])) {
            continue;
        }

        $status        = strtolower(trim((string) ($myArrayReponse['license'] ?? '')));
        $customerEmail = isset($myArrayReponse['customer_email']) ? (string) $myArrayReponse['customer_email'] : '';

        if ($status !== 'inactive' || ($customerEmail !== $email && $customerEmail !== '')) {
            continue;
        }

        // On a trouvé le bon produit, on l'active
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://www.web-dream.fr/?edd_action=activate_license&item_name=' . $itemName . '&license=' . $licenceEncoded . '&url=http://www.web-dream.fr'
        ));
        $resp = curl_exec($curl);
        curl_close($curl);

        $activationResponse = json_decode($resp, true);
        if (!is_array($activationResponse) || !isset($activationResponse['success'])) {
            return false;
        }
        $s = $activationResponse['success'];
        return ($s === true || $s === 'true');
    }

    return false;
}
/**************************************************************************/

// CREATE DATABASE OBJECT ( MAKE SURE TO CHANGE LOGIN INFO IN CLASS FILE )
$db = new DbConnect();
if (method_exists($db, 'show_errors')) {
    $db->show_errors();
}
$db->query("SET NAMES 'utf8'");

$mon_nom = getFormatedText($_POST['mon_nom'] ?? '');
$mon_prenom = getFormatedText($_POST['mon_prenom'] ?? '');
$mon_telephone = getFormatedText($_POST['mon_telephone'] ?? '');
$mon_mail = getFormatedText($_POST['mon_mail'] ?? '');
$mon_iconid = $_POST['mon_iconid'] ?? '';
$mon_indicatif = getFormatedText($_POST['mon_indicatif'] ?? '');
$mon_etat = getFormatedText($_POST['mon_etat'] ?? '');
$mon_password = getFormatedText($_POST['mon_password'] ?? '');
$ma_licence = getFormatedText($_POST['ma_licence'] ?? '');

if(isset($_POST['size_limit'])){
    $size_limit=intval($_POST['size_limit']);
}else{
    $size_limit=0;
}
if(isset($_POST['appli_type_PC'])){
    $appli_type_PC=intval($_POST['appli_type_PC']);
}else{
    $appli_type_PC=-1;
}

$mon_code=unique_id(8);

// Vérification du nombre d'utilisation de l'adresse mail
$sql = "SELECT * FROM `REZO` WHERE moncode = '$mon_code';";
$result = $db->query($sql);
if ($result && $result->num_rows > 0) {
	echo "return_txt=6";
	return;
}

$sql = "SELECT * FROM `REZO_FLASH` WHERE mail = '$mon_mail';";
$result = $db->query($sql);
if ($result && $result->num_rows > 0) {
	echo "return_txt=1";
	return;
}

$resultat_du_test = test_licence($ma_licence, $mon_mail);

// "0" = inactive, email correspond -> on active puis on crée le compte
// "4" = déjà valide, email correspond -> on crée le compte sans rappeler l'API
if ($resultat_du_test !== "0" && $resultat_du_test !== "4") {
	echo "return_txt=$resultat_du_test";
	return;
}

$do_activate = ($resultat_du_test === "0");
if ($do_activate && !activate_licence($ma_licence, $mon_mail)) {
	echo "return_txt=-1";
	return;
}

$expiration_license = get_date_licence($ma_licence, $mon_mail);

{
		// Ordre des colonnes REZO (convention app : colonne "mail" = téléphone, "prenom" = prénom)
		// Si la table est moncode, nom, mail, prenom,... alors il faut téléphone en 3e, prénom en 4e
		$sql = "INSERT INTO `REZO`
						VALUES (
							'$mon_code',
							'$mon_nom',
							'$mon_telephone',
							'$mon_prenom',
							'0.0',
							'0.0',
							'0.0',
							'0',
							'inactif',
							'$mon_iconid',
							'$mon_indicatif',
							'$mon_etat',
							'',
							NOW(),
							NOW(),
							'',
							'',
							NOW(),
                            '$size_limit',
                            '$appli_type_PC',
                            ''
						) ;";
		
		
		
		$result = $db->query($sql);		
		
		$sql = "INSERT INTO `REZO_FLASH` 
						VALUES (
							'$mon_code',
							'$mon_mail', 
							'$mon_password', 
							NOW(), 
							'$ma_licence',
							'".$expiration_license."',
							'1',
							'1',
							'1'
						) ;";
		
		
		
		$result = $db->query($sql);

		$sql = "SELECT * FROM `REZO_FLASH` WHERE moncode = '$mon_code';";
		$date_fin_validite_licence = '';
		$result = $db->query($sql);
		if ($result && $result->num_rows) {
			while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
				$date_fin_validite_licence = $row['date_fin_validite_licence'] ?? '';
			}
		}
		if ($date_fin_validite_licence !== '') {
			$date = date_create($date_fin_validite_licence);
			$date_fin_validite_licence = $date ? date_format($date, 'd/m/Y H:i:s') : $date_fin_validite_licence;
		}
		echo "return_txt=ok$mon_code$date_fin_validite_licence";

		if ($appli_type_PC > 0 && !file_exists('../rezo_galerie/' . $mon_code)) {
			mkdir('../rezo_galerie/' . $mon_code, 0777, true);
		}
}
?>