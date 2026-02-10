<?PHP

header('Expires: Thu, 01 Jan 1970 00:00:00 GMT, -1');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

/**************************************************************************/

if(!function_exists("autoload_classes")){
	function autoload_classes($class_name){
		require_once('classes/class_'.$class_name.'.php');
	}
}
spl_autoload_register('autoload_classes');

$db = new DbConnect();
$db->show_errors();
$db->query("SET NAMES 'utf8'");

$token = isset($_POST['token']) ? trim($_POST['token']) : '';
$mon_password_new = isset($_POST['mon_password_new']) ? $_POST['mon_password_new'] : '';

if ($token === '' || $mon_password_new === '') {
	echo '-1';
	die;
}

$token_safe = $db->prepare($token);
$sql = "SELECT mail FROM rezo_password_reset WHERE token='$token_safe' AND expires_at > NOW() LIMIT 1";
$row = null;
if ($result = $db->query($sql)) {
	if ($result->num_rows) {
		$row = $result->fetch_array(MYSQLI_ASSOC);
	}
}

if (!$row) {
	echo '-1';
	die;
}

$mail = $row['mail'];
$mail_safe = $db->prepare($mail);
$mon_password_new_safe = $db->prepare($mon_password_new);

$db->query("UPDATE REZO_FLASH SET motdepasse='$mon_password_new_safe' WHERE mail='$mail_safe'");
$db->query("DELETE FROM rezo_password_reset WHERE token='$token_safe'");

echo '1';
die;
