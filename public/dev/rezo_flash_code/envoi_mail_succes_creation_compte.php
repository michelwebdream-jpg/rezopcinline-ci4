<?php

if (!$_POST) {
    exit;
}

$your_email = isset($_POST['destinataire']) ? trim((string) $_POST['destinataire']) : '';
$email_subject = isset($_POST['titre']) ? trim((string) $_POST['titre']) : '';
$email_content = isset($_POST['contenu']) ? (string) $_POST['contenu'] : '';

if ($your_email === '' || $email_subject === '' || $email_content === '' || !filter_var($your_email, FILTER_VALIDATE_EMAIL)) {
    echo 'return_txt=-1';
    exit;
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$appBasePath = preg_replace('#/dev/rezo_flash_code/[^/]+$#', '', $scriptName);
$endpointPath = rtrim((string) $appBasePath, '/') . '/mail/legacy-send';
$endpointUrl = $scheme . '://' . $host . $endpointPath;

if ($host === '') {
    echo 'return_txt=-1';
    exit;
}

$payload = http_build_query([
    'destinataire' => $your_email,
    'titre' => $email_subject,
    'contenu' => $email_content,
]);

$ch = curl_init($endpointUrl);
if ($ch === false) {
    echo 'return_txt=-1';
    exit;
}

$isLocalHost = stripos($host, 'localhost') !== false || stripos($host, '127.0.0.1') !== false || stripos($host, '.local') !== false;
$options = [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_CONNECTTIMEOUT => 8,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
];
if ($scheme === 'https' && $isLocalHost) {
    $options[CURLOPT_SSL_VERIFYPEER] = false;
    $options[CURLOPT_SSL_VERIFYHOST] = 0;
}

curl_setopt_array($ch, $options);
$response = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$errno = curl_errno($ch);
$error = curl_error($ch);
curl_close($ch);

if ($errno !== 0 || $httpCode >= 400 || !is_string($response)) {
    error_log('envoi_mail_succes_creation_compte.php relay error: ' . $errno . ' ' . $error . ' url=' . $endpointUrl . ' code=' . $httpCode);
    echo 'return_txt=-1';
    exit;
}

$response = trim($response);
if ($response === 'return_txt=1' || $response === 'return_txt=-1') {
    echo $response;
    exit;
}

error_log('envoi_mail_succes_creation_compte.php unexpected relay response: ' . substr($response, 0, 300));
echo 'return_txt=-1';

?>