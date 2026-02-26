<?PHP


header('Expires: Thu, 01 Jan 1970 00:00:00 GMT, -1');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

/**************************************************************************/
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

    // Fallback si config absente (ex. non déployée en prod) : les 2 produits connus
    $items = ['cle-de-licence-rezo-pc-inline-1-an', 'cle-de-licence-rezo-pc-inline-6-mois'];
    return $items;
}

/**
 * Teste la licence sur tous les produits EDD connus.
 * Retourne :
 * - "1" : licence valide pour ce mail
 * - "0" : licence invalide / inactive / appartient à un autre compte
 * - "-1" : licence expirée pour ce mail
 */
function test_licence($licence, $mail)
{
    $items = get_edd_item_names();
    $licenceEncoded = urlencode(trim((string) $licence));
    $email = trim((string) $mail);
    $lastStatus = '';
    $lastItem = '';

    foreach ($items as $itemName) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://www.web-dream.fr/?edd_action=check_license&item_name=' . $itemName . '&license=' . $licenceEncoded . '&url=http://www.web-dream.fr',
        ));
        $resp = curl_exec($curl);
        curl_close($curl);

        $myArrayReponse = json_decode($resp, true);
        $lastItem = $itemName;

        if (!is_array($myArrayReponse) || !isset($myArrayReponse['license'])) {
            continue;
        }

        $status        = strtolower(trim((string) ($myArrayReponse['license'] ?? '')));
        $lastStatus = $status;
        $customerEmail = isset($myArrayReponse['customer_email']) ? (string) $myArrayReponse['customer_email'] : '';

        // Valid ou active (certaines versions EDD renvoient "active") : on accepte
        if ($status === 'valid' || $status === 'active') {
            return "1";
        }
        if ($status === 'expired') {
            return "-1";
        }

        // Inactive : on exige la correspondance email pour ce statut
        if ($status === 'inactive' && $customerEmail === $email) {
            return "0";
        }
        if ($status === 'inactive') {
            continue;
        }

        // invalid ou autre statut pour ce produit
        if ($status === 'invalid') {
            continue;
        }
        return "0";
    }

    // Diagnostic : log du dernier statut EDD vu (sans données sensibles)
    error_log('lit_info_administrateur: test_licence aucun produit reconnu. Dernier item=' . $lastItem . ' status=' . $lastStatus);
    return "0";
}
/**************************************************************************/
function get_date_licence($licence, $mail)
{
    $items = get_edd_item_names();
    $licenceEncoded = urlencode(trim((string) $licence));
    $email = trim((string) $mail);

    foreach ($items as $itemName) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://www.web-dream.fr/?edd_action=check_license&item_name=' . $itemName . '&license=' . $licenceEncoded . '&url=http://www.web-dream.fr',
        ));
        $resp = curl_exec($curl);
        curl_close($curl);

        $myArrayReponse = json_decode($resp, true);

        if (!is_array($myArrayReponse) || !isset($myArrayReponse['license'])) {
            continue;
        }

        $customerEmail = isset($myArrayReponse['customer_email']) ? (string) $myArrayReponse['customer_email'] : '';
        $status        = strtolower(trim((string) ($myArrayReponse['license'] ?? '')));

        // Accepter si valid/active/expired/inactive et (email correspond ou EDD n'a pas renvoyé l'email)
        $emailOk = ($customerEmail === $email) || ($customerEmail === '');
        if (($status === 'valid' || $status === 'active' || $status === 'expired' || $status === 'inactive') && $emailOk && isset($myArrayReponse['expires'])) {
            return $myArrayReponse['expires'];
        }
    }

    return '';
}
/**************************************************************************/

// AUTOLOAD CLASS OBJECTS... YOU CAN USE INCLUDES IF YOU PREFER
// Corrigé pour PHP 7.2+ : __autoload() est déprécié, utiliser spl_autoload_register()
if(!function_exists("autoload_classes")){ 
	function autoload_classes($class_name){
		require_once('classes/class_'.$class_name.'.php');
	}
}
spl_autoload_register('autoload_classes');

// CREATE DATABASE OBJECT ( MAKE SURE TO CHANGE LOGIN INFO IN CLASS FILE )
$db = new DbConnect();
$db->show_errors();
$db->query("SET NAMES 'utf8'");

$mon_code=$_POST['mon_code'];
$mon_mot_de_passe=$_POST['mon_mot_de_passe'];
$pass=0;

$license="";
$date_fin_validite_licence_from_db = '';
// Recherche des mail actif ou inactif
$sql = "SELECT * FROM `REZO_FLASH` WHERE moncode='$mon_code' AND motdepasse='$mon_mot_de_passe';"; 
if($result = $db->query($sql))
{
					if($result->num_rows){
						while($row = $result->fetch_array(MYSQLI_ASSOC)){
							$license = $db->prepare($row['licence']);
							$date_fin_validite_licence_from_db = isset($row['date_fin_validite_licence']) ? trim((string) $row['date_fin_validite_licence']) : '';
							$mail = $db->prepare($row['mail']);
							$date_creation= $db->prepare($row['date_creation']);
							$code= $db->prepare($row['moncode']);
							$pass=$pass+1;
						}
					}
}

$sql = "SELECT * FROM `REZO` WHERE moncode='$mon_code';"; 
if($result = $db->query($sql))
{
					if($result->num_rows){
						while($row = $result->fetch_array(MYSQLI_ASSOC)){
							
							
							$nom =stripcslashes( $db->prepare($row['nom']));
							$prenom=stripcslashes( $db->prepare($row['prenom']));
							$telephone=stripcslashes( $db->prepare($row['mail']));
							$statut=stripcslashes( $db->prepare($row['statut']));
							$iconid=stripcslashes( $db->prepare($row['iconid']));
							$indicatif=stripcslashes( $db->prepare($row['indicatif']));
							$etat=stripcslashes( $db->prepare($row['etat']));							
							$pass=$pass+1;
							
						}
					}
}

if ($pass==2){

//$date_creation=date_create($date_creation);
//$date_creation=date_format($date_creation, 'd/m/Y H:i:s');
//$date_fin_validite_licence=date_create($date_fin_validite_licence);
//$date_fin_validite_licence=date_format($date_fin_validite_licence, 'd/m/Y H:i:s');

	$resp=test_licence($license,$mail);

	if ($resp=="1"){
		
		$expiration_license=get_date_licence($license,$mail);
		
		// Gérer le cas où la licence est 'lifetime' (pas de date d'expiration)
		$date_fin_validite_licence_db = ($expiration_license == 'lifetime') ? '2099-12-31 23:59:59' : $expiration_license;
		
		$sql = "UPDATE `REZO_FLASH` SET `date_fin_validite_licence`='".$date_fin_validite_licence_db."' WHERE `moncode`='{$mon_code}';";
		$db->query($sql);

		$date_creation=date_create($date_creation);
		$date_creation=date_format($date_creation, 'd/m/Y H:i:s');
		// Pour l'affichage, utiliser 'lifetime' si c'est le cas, sinon formater la date
		if ($expiration_license == 'lifetime') {
			$date_fin_validite_licence = 'lifetime';
		} else {
			$date_fin_validite_licence=date_create($expiration_license);
			$date_fin_validite_licence=date_format($date_fin_validite_licence, 'd/m/Y H:i:s');
		}
		
		// DEBUG: Vérifier la valeur du mail avant construction de la trame
		error_log('=== DEBUG lit_info_administrateur: $mail = "' . ($mail ?? 'NON_DEFINI') . '" (type: ' . gettype($mail) . ', empty: ' . (empty($mail) ? 'true' : 'false') . ') ===');
		error_log('=== DEBUG lit_info_administrateur: $code = "' . ($code ?? 'NON_DEFINI') . '" ===');
		error_log('=== DEBUG lit_info_administrateur: $nom = "' . ($nom ?? 'NON_DEFINI') . '" ===');
		error_log('=== DEBUG lit_info_administrateur: $prenom = "' . ($prenom ?? 'NON_DEFINI') . '" ===');
		error_log('=== DEBUG lit_info_administrateur: $telephone = "' . ($telephone ?? 'NON_DEFINI') . '" ===');
		error_log('=== DEBUG lit_info_administrateur: $indicatif = "' . ($indicatif ?? 'NON_DEFINI') . '" ===');
		error_log('=== DEBUG lit_info_administrateur: $iconid = "' . ($iconid ?? 'NON_DEFINI') . '" ===');
		error_log('=== DEBUG lit_info_administrateur: $etat = "' . ($etat ?? 'NON_DEFINI') . '" ===');
		
		$resultat=$code."><".$nom."><".$prenom."><".$telephone."><".$mail."><".$indicatif."><".$iconid."><".$etat."><".$date_fin_validite_licence."><".$date_creation;
		
		// DEBUG: Vérifier la trame construite
		error_log('=== DEBUG lit_info_administrateur: TRAME CONSTRUITE = "' . $resultat . '" ===');
		
		echo "return_txt=$resultat";
	
	}else if ($resp=="-1"){
		echo "return_txt=-1";
	}else{
		// EDD a renvoyé "0" (inactive/inconnu) mais la BDD peut avoir une date encore valide (ex. licence 6 mois, EDD renvoie un format non reconnu)
		$accepter_sur_la_bdd = false;
		if ($date_fin_validite_licence_from_db !== '') {
			$expires_lower = strtolower(trim($date_fin_validite_licence_from_db));
			if ($expires_lower === 'lifetime' || strpos($expires_lower, '2099') !== false) {
				$accepter_sur_la_bdd = true;
			} else {
				$ts_expire = strtotime($date_fin_validite_licence_from_db);
				if ($ts_expire !== false && $ts_expire > time()) {
					$accepter_sur_la_bdd = true;
				}
			}
		}
		if ($accepter_sur_la_bdd) {
			error_log('lit_info_administrateur: EDD a retourne 0 mais BDD date valide -> connexion acceptee (code=' . substr($mon_code, 0, 4) . '...)');
			$date_creation=date_create($date_creation);
			$date_creation=date_format($date_creation, 'd/m/Y H:i:s');
			if (strtolower(trim($date_fin_validite_licence_from_db)) === 'lifetime' || strpos($date_fin_validite_licence_from_db, '2099') !== false) {
				$date_fin_validite_licence = 'lifetime';
			} else {
				$date_obj = date_create($date_fin_validite_licence_from_db);
				$date_fin_validite_licence = ($date_obj !== false) ? date_format($date_obj, 'd/m/Y H:i:s') : $date_fin_validite_licence_from_db;
			}
			$resultat=$code."><".$nom."><".$prenom."><".$telephone."><".$mail."><".$indicatif."><".$iconid."><".$etat."><".$date_fin_validite_licence."><".$date_creation;
			echo "return_txt=$resultat";
		} else {
			echo "return_txt=-3";
		}
	}

}else{
	echo "return_txt=-2";
}
?>