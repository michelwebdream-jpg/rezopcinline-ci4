<?php

header('Content-Type: text/html; charset=utf-8');

$email   = isset($_POST['email1']) ? trim((string) $_POST['email1']) : '';
$message = isset($_POST['message1']) ? (string) $_POST['message1'] : '';
$nom_activite_mission = isset($_POST['nom_activite_mission']) ? (string) $_POST['nom_activite_mission'] : '';

$email = filter_var($email, FILTER_SANITIZE_EMAIL);

if (filter_var($email, FILTER_VALIDATE_EMAIL) && $message !== '') {

    $subject = 'Historique de : ' . $nom_activite_mission . '.';
    $subject = str_replace(["\r", "\n"], ' ', $subject);

    // Échapper le HTML tout en laissant les <br /> du message s'afficher comme retours à la ligne
    $messageSafe = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $messageSafe = str_replace(['&lt;br /&gt;', '&lt;br/&gt;', '&lt;br&gt;'], '<br />', $messageSafe);

    $template = '<div style="padding:50px; color:white;"><h2>Historique de : ' . htmlspecialchars($nom_activite_mission, ENT_QUOTES, 'UTF-8') . '</h2><br/>'
        . '<br/>' . nl2br($messageSafe) . '<br/><br/>'
        . 'Envoyé de l\'application web REZO+ PC Inline.'
        . '<br/>';
    $sendmessage = "<div style=\"background-color:#7E7E7E; color:white;\">" . $template . "</div>";
    $sendmessage = wordwrap($sendmessage, 70);

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $appBasePath = preg_replace('#/php/contact_form\.php$#', '', $scriptName);
    $endpointPath = rtrim((string) $appBasePath, '/') . '/mail/legacy-send';
    $endpointUrl = $scheme . '://' . $host . $endpointPath;

    if ($host === '') {
        echo "Erreur lors de l'envoi du message.";
        exit;
    }

    $payload = http_build_query([
        'destinataire' => $email,
        'titre' => $subject,
        'contenu' => $sendmessage,
    ]);

    $ch = curl_init($endpointUrl);
    if ($ch === false) {
        echo "Erreur lors de l'envoi du message.";
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

    if ($errno === 0 && $httpCode < 400 && is_string($response) && trim($response) === 'return_txt=1') {
        echo "Message envoyé.";
    } else {
        error_log('contact_form.php relay error: ' . $errno . ' ' . $error . ' url=' . $endpointUrl . ' code=' . $httpCode);
        echo "Erreur lors de l'envoi du message.";
    }

} else {
    echo "<span>* email invalide *</span>";
}
