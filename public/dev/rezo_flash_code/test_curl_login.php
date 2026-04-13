<?php
header('Content-Type: text/plain; charset=utf-8');

$url = 'https://www.web-dream.fr/rezopcinline/dev/rezo_flash_code/lit_info_administrateur.php';
$post = [
    'mon_code' => 'be868089',
    'mon_mot_de_passe' => 'ma050868',
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($post),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_TIMEOUT => 90,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);

$start = microtime(true);
$response = curl_exec($ch);
$errno = curl_errno($ch);
$error = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "errno={$errno}\n";
echo "error={$error}\n";
echo "http_code=" . ($info['http_code'] ?? '') . "\n";
echo "total_time=" . ($info['total_time'] ?? '') . "\n";
echo "connect_time=" . ($info['connect_time'] ?? '') . "\n";
echo "namelookup_time=" . ($info['namelookup_time'] ?? '') . "\n";
echo "primary_ip=" . ($info['primary_ip'] ?? '') . "\n";
echo "response=\n{$response}\n";