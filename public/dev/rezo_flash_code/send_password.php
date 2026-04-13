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
$send_email = isset($_POST['send_email']) ? (int) $_POST['send_email'] : 1;
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

if ($base_url === '') {
	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	$host = $_SERVER['HTTP_HOST'] ?? '';
	if ($host !== '') {
		$base_url = $scheme . '://' . $host;
	}
}
$reset_link = rtrim($base_url, '/') . '/signup/reset_password?token=' . $token;
$reset_link_safe = htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8');

$subject = 'Réinitialisation du mot de passe - REZO+ PC Inline';
$sendmessage = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body style="margin:0; font-family: Arial, sans-serif; font-size: 15px; line-height: 1.5; color: #333; background: #fff;">'
	. '<div style="max-width: 560px; margin: 0 auto; padding: 24px 20px;">'
	. '<h1 style="margin: 0 0 20px; font-size: 20px; color: #222;">Réinitialisation du mot de passe</h1>'
	. '<p style="margin: 0 0 16px;">Vous avez demandé une réinitialisation du mot de passe pour votre compte REZO+ PC Inline.</p>'
	. '<p style="margin: 0 0 16px;">Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe. Ce lien est valable 1 heure.</p>'
	. '<p style="margin: 0 0 24px;">'
	. '<a href="' . $reset_link_safe . '" style="color: #0066cc; text-decoration: underline;">' . $reset_link_safe . '</a>'
	. '</p>'
	. '<p style="margin: 0 0 16px; color: #666; font-size: 13px;">Si vous n\'êtes pas à l\'origine de cette demande, ignorez ce message. Aucune modification ne sera effectuée.</p>'
	. '<p style="margin: 24px 0 0; padding-top: 16px; border-top: 1px solid #eee; color: #888; font-size: 12px;">REZO+ PC Inline - Web-dream</p>'
	. '</div></body></html>';

// Mode CI4: retourne les données pour envoi SMTP (sans utiliser mail()).
if ($send_email !== 1) {
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode([
		'status' => '1',
		'email' => $mon_email,
		'subject' => $subject,
		'html' => $sendmessage,
	], JSON_UNESCAPED_UNICODE);
	die;
}

$subject = 'Réinitialisation du mot de passe - REZO+ PC Inline';
$headers = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
$headers .= 'From: "REZO+ PC Inline" <info@web-dream.fr>' . "\r\n";

$mail_sent = @mail($mon_email, $subject, $sendmessage, $headers);
if (!$mail_sent) {
	error_log('send_password.php: echec envoi mail reset pour ' . $mon_email);
	echo '-2';
	die;
}

echo '1';
die;
