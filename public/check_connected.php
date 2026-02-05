<?php
/**
 * check_connected.php (CI4 - rezopcinline-ci4)
 * Affiche le nombre et la liste des utilisateurs connectés (sessions actives).
 * Protégé par token : ?token=VOTRE_SECRET
 *
 * Configuration : définir CHECK_CONNECTED_TOKEN ci-dessous (ou laisser vide pour désactiver la vérification).
 */
// --- Configuration ---
define('CHECK_CONNECTED_TOKEN', ''); // ex: 'mon_secret_123' — à définir en production
$activeMinutes = 15;   // considérer "actif" si dernière activité dans les X dernières minutes
$sessionLifetime = 7200; // expiration session (secondes), pour option ?all=1

// Chemin des fichiers de session (même que app/Config/Session.php : WRITEPATH . 'session')
$sessionPath = __DIR__ . '/../writable/session';

// --- Vérification d'accès ---
if (CHECK_CONNECTED_TOKEN !== '' && (!isset($_GET['token']) || $_GET['token'] !== CHECK_CONNECTED_TOKEN)) {
    header('HTTP/1.0 403 Forbidden');
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Accès refusé.';
    exit;
}

$format = isset($_GET['format']) && strtolower($_GET['format']) === 'json' ? 'json' : 'html';
$showAll = !empty($_GET['all']);
$windowSeconds = $showAll ? $sessionLifetime : ($activeMinutes * 60);
$cutoff = time() - $windowSeconds;

// --- Lecture des sessions (fichiers) ---
$users = [];
if (!is_dir($sessionPath)) {
    header('Content-Type: ' . ($format === 'json' ? 'application/json' : 'text/html') . '; charset=UTF-8');
    if ($format === 'json') {
        echo json_encode(['error' => 'Répertoire session introuvable', 'count' => 0, 'users' => []], JSON_UNESCAPED_UNICODE);
    } else {
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Connectés</title></head><body><p>Erreur : répertoire session introuvable.</p></body></html>';
    }
    exit;
}

session_start();
$files = glob($sessionPath . '/*');
foreach ($files as $file) {
    if (!is_file($file)) {
        continue;
    }
    if (filemtime($file) < $cutoff) {
        continue;
    }
    $data = @file_get_contents($file);
    if ($data === false || $data === '') {
        continue;
    }
    $_SESSION = [];
    if (@session_decode($data) === false) {
        continue;
    }
    if (empty($_SESSION['logged']) || empty($_SESSION['login'])) {
        continue;
    }
    $deliver = $_SESSION['deliverdata'] ?? [];
    $users[] = [
        'code'          => (string) ($_SESSION['login'] ?? ''),
        'nom'           => (string) ($deliver['nom_administrateur'] ?? ''),
        'prenom'        => (string) ($deliver['prenom_administrateur'] ?? ''),
        'mail'          => (string) ($deliver['mail_administrateur'] ?? ''),
        'indicatif'     => (string) ($deliver['indicatif_administrateur'] ?? ''),
        'last_activity' => (int) filemtime($file),
    ];
}
$_SESSION = [];
session_write_close();

// --- Sortie ---
header('Content-Type: ' . ($format === 'json' ? 'application/json' : 'text/html') . '; charset=UTF-8');

if ($format === 'json') {
    echo json_encode([
        'count' => count($users),
        'users'  => $users,
        'window_seconds' => $windowSeconds,
        'active_minutes' => $activeMinutes,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilisateurs connectés — REZO+ PC Inline</title>
    <style>
        body { font-family: sans-serif; margin: 1rem; background: #f5f5f5; }
        h1 { color: #333; }
        .count { font-size: 1.2rem; margin-bottom: 1rem; }
        table { border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        th, td { padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #333; color: #fff; }
        tr:hover { background: #f9f9f9; }
        .meta { margin-top: 1rem; font-size: 0.9rem; color: #666; }
    </style>
</head>
<body>
    <h1>Utilisateurs connectés</h1>
    <p class="count"><strong><?php echo count($users); ?></strong> personne(s) active(s) (dernières <?php echo $showAll ? $sessionLifetime / 60 : $activeMinutes; ?> min)</p>
    <?php if (count($users)) : ?>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Mail</th>
                <th>Indicatif</th>
                <th>Dernière activité</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u) : ?>
            <tr>
                <td><?php echo htmlspecialchars($u['code']); ?></td>
                <td><?php echo htmlspecialchars($u['nom']); ?></td>
                <td><?php echo htmlspecialchars($u['prenom']); ?></td>
                <td><?php echo htmlspecialchars($u['mail']); ?></td>
                <td><?php echo htmlspecialchars($u['indicatif']); ?></td>
                <td><?php echo date('d/m/Y H:i:s', $u['last_activity']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else : ?>
    <p>Aucune session active sur la période.</p>
    <?php endif; ?>
    <p class="meta">
        <a href="?token=<?php echo urlencode($_GET['token'] ?? ''); ?>&amp;all=1">Voir toutes les sessions (<?php echo $sessionLifetime / 60; ?> min)</a>
        —
        <a href="?token=<?php echo urlencode($_GET['token'] ?? ''); ?>&amp;format=json">JSON</a>
    </p>
</body>
</html>
