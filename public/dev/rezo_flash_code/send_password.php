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

// Table des tokens de réinitialisation (création si nécessaire)
$db->query("CREATE TABLE IF NOT EXISTS rezo_password_reset (
	token VARCHAR(64) PRIMARY KEY,
	mail VARCHAR(255) NOT NULL,
	expires_at DATETIME NOT NULL,
	INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$mon_email = isset($_POST['mon_email']) ? trim($_POST['mon_email']) : '';
$base_url = isset($_POST['base_url']) ? rtrim(trim($_POST['base_url']), '/') : '';
$pass = 0;

if ($mon_email === '') {
	echo '-1';
	die;
}

$mon_email_safe = $db->prepare($mon_email);
$sql = "SELECT moncode FROM REZO_FLASH WHERE mail='$mon_email_safe' LIMIT 1";
if ($result = $db->query($sql)) {
	if ($result->num_rows) {
		$pass = 1;
	}
}

if ($pass !== 1) {
	echo '-1';
	die;
}

// Invalider d'éventuels anciens tokens pour cet email
$db->query("DELETE FROM rezo_password_reset WHERE mail='$mon_email_safe'");

$token = bin2hex(random_bytes(32));
$token_safe = $db->prepare($token);
$db->query("INSERT INTO rezo_password_reset (token, mail, expires_at) VALUES ('$token_safe', '$mon_email_safe', NOW() + INTERVAL 1 HOUR)");

$reset_link = $base_url . '/signup/reset_password?token=' . $token;
$reset_link_safe = htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8');

$message = 'Vous avez demandé une réinitialisation du mot de passe pour votre compte REZO+ PC Inline.<br/><br/>'
	. 'Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe (lien valide 1 heure) :<br/><br/>'
	. '<a href="' . $reset_link_safe . '">' . $reset_link_safe . '</a><br/><br/>'
	. 'Si vous n\'êtes pas à l\'origine de cette demande, ignorez ce message.<br/><br/>Web-dream vous remercie.';

$subject = 'Réinitialisation du mot de passe - REZO+ PC Inline';
$headers = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
$headers .= 'From: info@web-dream.fr' . "\r\n";

$template = '<div style="padding:50px; color:white;"><h2>Réinitialisation du mot de passe</h2><br/>'
	. '<br/>' . $message . '<br/><br/>'
	. 'Envoyé de l\'application web REZO+ PC Inline.'
	. '<br/>';
$sendmessage = "<div style=\"background-color:#7E7E7E; color:white;\">" . $template . "</div>";
$sendmessage = wordwrap($sendmessage, 70);

mail($mon_email, $subject, $sendmessage, $headers);

echo '1';
die;
