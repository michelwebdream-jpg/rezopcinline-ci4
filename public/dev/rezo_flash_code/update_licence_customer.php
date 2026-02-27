<?php
/**
 * Associer une nouvelle clé de licence à un compte existant.
 * POST: mon_code, mon_mot_de_passe, nouvelle_cle
 * Réponses: return_txt=ok|date  ou return_txt=ERR_xxx
 */
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT, -1');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

function getFormatedText($texte) {
    return addslashes((string) $texte);
}

$mon_code = getFormatedText($_POST['mon_code'] ?? '');
$mon_mot_de_passe = getFormatedText($_POST['mon_mot_de_passe'] ?? '');
$nouvelle_cle = trim((string) ($_POST['nouvelle_cle'] ?? ''));

if ($nouvelle_cle === '') {
    echo 'return_txt=ERR_KEY_INVALID';
    exit;
}

if (!function_exists('autoload_classes')) {
    function autoload_classes($class_name) {
        $file = __DIR__ . '/classes/class_' . $class_name . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
}
spl_autoload_register('autoload_classes');

$db = new DbConnect();
if (method_exists($db, 'show_errors')) {
    $db->show_errors();
}
$db->query("SET NAMES 'utf8'");

$conn = $db->Connection();
$mon_code_esc = $conn->real_escape_string($mon_code);
$mon_mot_de_passe_esc = $conn->real_escape_string($mon_mot_de_passe);

$sql = "SELECT mail FROM `REZO_FLASH` WHERE moncode='$mon_code_esc' AND motdepasse='$mon_mot_de_passe_esc' LIMIT 1";
$result = $db->query($sql);
if (!$result || $result->num_rows === 0) {
    echo 'return_txt=ERR_AUTH';
    exit;
}
$row = $result->fetch_array(MYSQLI_ASSOC);
$mail = trim(stripslashes((string) ($row['mail'] ?? '')));
if ($mail === '') {
    echo 'return_txt=ERR_AUTH';
    exit;
}

/**
 * Charge la liste des item_name EDD autorisés.
 */
function get_edd_item_names_update() {
    static $items = null;
    if ($items !== null) return $items;
    $configFile = __DIR__ . '/config_licences_edd.php';
    if (is_file($configFile)) {
        $loaded = require $configFile;
        if (is_array($loaded) && !empty($loaded)) {
            $items = $loaded;
            return $items;
        }
    }
    $items = ['cle-de-licence-rezo-pc-inline-1-an', 'cle-de-licence-rezo-pc-inline-6-mois'];
    return $items;
}
$items = get_edd_item_names_update();

function test_licence_update($licence, $mail, $items) {
    $licenceEncoded = urlencode(trim((string) $licence));
    $email = (string) $mail;
    $sawInvalid = false;
    $sawOtherEmail = false;
    foreach ($items as $itemName) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://www.web-dream.fr/?edd_action=check_license&item_name=' . $itemName . '&license=' . $licenceEncoded . '&url=http://www.web-dream.fr',
        ]);
        $resp = curl_exec($curl);
        curl_close($curl);
        $arr = json_decode($resp, true);
        if (!is_array($arr) || !isset($arr['license'])) continue;
        $status = strtolower(trim((string) ($arr['license'] ?? '')));
        $customerEmail = isset($arr['customer_email']) ? (string) $arr['customer_email'] : '';
        if ($status === 'invalid') {
            $sawInvalid = true;
            continue;
        }
        if ($customerEmail !== $email && $customerEmail !== '') {
            $sawOtherEmail = true;
            continue;
        }
        if ($status === 'valid' || $status === 'active') return '4';
        if ($status === 'inactive') return '0';
        if ($status === 'expired') return '5';
    }
    if ($sawOtherEmail) return '2';
    if ($sawInvalid) return '3';
    return '2';
}

function get_date_licence_update($licence, $mail, $items) {
    $licenceEncoded = urlencode(trim((string) $licence));
    $email = (string) $mail;
    foreach ($items as $itemName) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://www.web-dream.fr/?edd_action=check_license&item_name=' . $itemName . '&license=' . $licenceEncoded . '&url=http://www.web-dream.fr',
        ]);
        $resp = curl_exec($curl);
        curl_close($curl);
        $arr = json_decode($resp, true);
        if (!is_array($arr) || !isset($arr['license'])) continue;
        $status = strtolower(trim((string) ($arr['license'] ?? '')));
        $customerEmail = isset($arr['customer_email']) ? (string) $arr['customer_email'] : '';
        $emailOk = ($customerEmail === $email) || ($customerEmail === '');
        if (in_array($status, ['valid', 'active', 'expired', 'inactive'], true) && $emailOk && isset($arr['expires'])) {
            return $arr['expires'];
        }
    }
    return '';
}

function activate_licence_update($licence, $mail, $items) {
    $licenceEncoded = urlencode(trim((string) $licence));
    $email = (string) $mail;
    foreach ($items as $itemName) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://www.web-dream.fr/?edd_action=check_license&item_name=' . $itemName . '&license=' . $licenceEncoded . '&url=http://www.web-dream.fr',
        ]);
        $resp = curl_exec($curl);
        curl_close($curl);
        $arr = json_decode($resp, true);
        if (!is_array($arr) || !isset($arr['license'])) continue;
        $status = strtolower(trim((string) ($arr['license'] ?? '')));
        $customerEmail = isset($arr['customer_email']) ? (string) $arr['customer_email'] : '';
        if ($status !== 'inactive' || ($customerEmail !== $email && $customerEmail !== '')) continue;
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://www.web-dream.fr/?edd_action=activate_license&item_name=' . $itemName . '&license=' . $licenceEncoded . '&url=http://www.web-dream.fr',
        ]);
        $resp = curl_exec($curl);
        curl_close($curl);
        $r = json_decode($resp, true);
        if (is_array($r) && isset($r['success'])) {
            $s = $r['success'];
            if ($s === true || $s === 'true') return true;
        }
    }
    return false;
}

$test = test_licence_update($nouvelle_cle, $mail, $items);
if ($test === '5') {
    echo 'return_txt=ERR_KEY_EXPIRED';
    exit;
}
if ($test === '3') {
    echo 'return_txt=ERR_KEY_INVALID';
    exit;
}
if ($test === '2') {
    echo 'return_txt=ERR_KEY_OTHER_ACCOUNT';
    exit;
}
if ($test === '0') {
    if (!activate_licence_update($nouvelle_cle, $mail, $items)) {
        echo 'return_txt=ERR_ACTIVATE';
        exit;
    }
}
if ($test !== '4' && $test !== '0') {
    echo 'return_txt=ERR_KEY_INVALID';
    exit;
}

$expires = get_date_licence_update($nouvelle_cle, $mail, $items);
if ($expires === '') {
    echo 'return_txt=ERR_ACTIVATE';
    exit;
}

$expires_clean = trim(strtolower($expires));
if ($expires_clean === 'lifetime' || $expires_clean === '' || $expires_clean === 'null' || $expires_clean === 'none') {
    $date_db = '2099-12-31 23:59:59';
} else {
    $date_obj = date_create($expires);
    $date_db = $date_obj ? date_format($date_obj, 'Y-m-d H:i:s') : '2099-12-31 23:59:59';
}

$nouvelle_cle_esc = $conn->real_escape_string($nouvelle_cle);
$date_db_esc = $conn->real_escape_string($date_db);
$sql = "UPDATE `REZO_FLASH` SET licence='$nouvelle_cle_esc', date_fin_validite_licence='$date_db_esc' WHERE moncode='$mon_code_esc' AND motdepasse='$mon_mot_de_passe_esc'";
$db->query($sql);

$date_display = ($date_db === '2099-12-31 23:59:59') ? 'lifetime' : (date_create($date_db) ? date_format(date_create($date_db), 'd/m/Y H:i:s') : $date_db);
echo 'return_txt=ok|' . $date_display;
