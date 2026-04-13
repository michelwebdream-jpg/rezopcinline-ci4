<?php

/**
 * Diagnostic d'auth via la route qui fonctionne sur cet hébergement :
 *   http://127.0.0.1/rezopcinline/... avec en-tête Host: web-dream.fr (sans www)
 *
 * Usage:
 *   /rezopcinline/dev/rezo_flash_code/test_curl_diag.php?k=VOTRE_CLE&code=XXXX&pass=YYYY
 *
 * IMPORTANT: Supprimez ce fichier après diagnostic (accès sensible).
 */
declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');

/** Clé obligatoire (min. 8 caractères). À personnaliser avant déploiement. */
const REZO_DIAG_SECRET = '0123456789';

/** Host requis pour éviter la redirection 301 sur loopback. */
const REZO_DIAG_LOOPBACK_HOST = 'web-dream.fr';

if (strlen(REZO_DIAG_SECRET) < 8 || REZO_DIAG_SECRET === 'CHANGEZ_MOI_AVANT_UPLOAD') {
    http_response_code(503);
    echo "Configurez REZO_DIAG_SECRET en haut de ce fichier avant utilisation.\n";
    exit;
}

$k = $_GET['k'] ?? '';
if (! hash_equals(REZO_DIAG_SECRET, (string) $k)) {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}

$code = isset($_GET['code']) ? (string) $_GET['code'] : '';
$pass = isset($_GET['pass']) ? (string) $_GET['pass'] : '';

if ($code === '' || $pass === '') {
    echo "Usage: ?k=VOTRE_CLE&code=VOTRE_CODE&pass=VOTRE_MDP\n";
    echo "Exemple: ?k=VOTRE_CLE&code=test&pass=test\n";
    exit;
}

$endpointPath = '/rezopcinline/dev/rezo_flash_code/lit_info_administrateur.php';
$url = 'http://127.0.0.1' . $endpointPath;

$payload = [
    'mon_code'         => $code,
    'mon_mot_de_passe' => $pass,
];

echo "=== ENV ===\n";
echo 'php_version=' . PHP_VERSION . "\n";
echo 'curl=' . (function_exists('curl_init') ? 'yes' : 'no') . "\n";
echo 'server_name=' . ($_SERVER['SERVER_NAME'] ?? '') . "\n";
echo 'http_host=' . ($_SERVER['HTTP_HOST'] ?? '') . "\n";
echo 'server_addr=' . ($_SERVER['SERVER_ADDR'] ?? '') . "\n";
echo 'document_root=' . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n";
echo 'loopback_host=' . REZO_DIAG_LOOPBACK_HOST . "\n";
echo 'endpoint=' . $endpointPath . "\n";
echo "\n";

/**
 * @param array<int, string> $extraHeaders
 */
function runCurl(string $url, array $postData, array $extraHeaders = []): void
{
    $ch = curl_init($url);
    $headers = array_merge(['Expect:'], $extraHeaders);

    $options = [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($postData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT        => 90,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTPHEADER     => $headers,
    ];

    if (stripos($url, 'https://') === 0) {
        $options[CURLOPT_SSL_VERIFYPEER] = true;
        $options[CURLOPT_SSL_VERIFYHOST] = 2;
    } else {
        $options[CURLOPT_SSL_VERIFYPEER] = false;
        $options[CURLOPT_SSL_VERIFYHOST] = 0;
    }
    curl_setopt_array($ch, $options);
    $t0 = microtime(true);
    $response = curl_exec($ch);
    $wallMs = round((microtime(true) - $t0) * 1000, 2);

    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $info  = curl_getinfo($ch);
    curl_close($ch);

    $headerSize = (int) ($info['header_size'] ?? 0);
    $rawHeaders = is_string($response) ? substr($response, 0, $headerSize) : '';
    $body       = is_string($response) ? substr($response, $headerSize) : '';
    $location   = '';
    if ($rawHeaders !== '') {
        foreach (preg_split("/\\r\\n|\\n|\\r/", $rawHeaders) as $line) {
            if (stripos($line, 'location:') === 0) {
                $location = trim(substr($line, strlen('location:')));
                break;
            }
        }
    }

    echo "=== RESULT ===\n";
    echo "url={$url}\n";
    echo "sent_host=" . (count($extraHeaders) ? implode('; ', $extraHeaders) : '') . "\n";
    echo "wall_ms={$wallMs}\n";
    echo "errno={$errno}\n";
    echo "error={$error}\n";
    echo 'http_code=' . ($info['http_code'] ?? '') . "\n";
    echo 'location=' . $location . "\n";
    echo 'namelookup_time=' . ($info['namelookup_time'] ?? '') . "\n";
    echo 'connect_time=' . ($info['connect_time'] ?? '') . "\n";
    echo 'appconnect_time=' . ($info['appconnect_time'] ?? '') . "\n";
    echo 'starttransfer_time=' . ($info['starttransfer_time'] ?? '') . "\n";
    echo 'total_time=' . ($info['total_time'] ?? '') . "\n";
    echo 'primary_ip=' . ($info['primary_ip'] ?? '') . "\n";
    echo 'primary_port=' . ($info['primary_port'] ?? '') . "\n";
    $bodySnippet = is_string($body) ? substr($body, 0, 200) : '';
    echo 'response_snippet=' . str_replace(["\r", "\n"], ['\\r', '\\n'], $bodySnippet) . "\n";
    echo "\n";
}

echo "=== CURL LOOPBACK TEST (POST lit_info_administrateur) ===\n";
runCurl($url, $payload, ['Host: ' . REZO_DIAG_LOOPBACK_HOST]);

echo "Done. Supprimez ce fichier après diagnostic.\n";
