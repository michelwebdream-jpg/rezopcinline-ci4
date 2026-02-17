<?php
/**
 * check_connected.php (CI4 - rezopcinline-ci4)
 * Affiche le nombre et la liste des utilisateurs connectés (sessions actives).
 * Affiche également les utilisateurs actifs (géolocalisation récente via table REZO).
 * Protégé par token : ?token=VOTRE_SECRET
 *
 * Configuration : définir CHECK_CONNECTED_TOKEN ci-dessous (ou laisser vide pour désactiver la vérification).
 */
// --- Configuration ---
define('CHECK_CONNECTED_TOKEN', ''); // ex: 'mon_secret_123' — à définir en production
$activeMinutes = 15;   // considérer "actif" si dernière activité dans les X dernières minutes
$sessionLifetime = 7200; // expiration session (secondes), pour option ?all=1
$activeGeolocMinutes = 30; // utilisateur "actif" si derniere_inscription >= maintenant - 30 min
$rezoCodeColumn = 'moncode'; // colonne identifiant utilisateur dans REZO
$googleMapsApiKey = getenv('GOOGLE_MAPS_API_KEY') ?: 'AIzaSyBfDAk5Xb1ZDwMDNj5qBitkVRSec3YlXic'; // clé API Google Maps (identique à Googlemaps.php)
$mapRefreshIntervalSeconds = 5; // intervalle de rafraîchissement des positions sur la carte (secondes)
$mapAutoRecenteringDefault = true; // recentrage automatique de la carte lors de la mise à jour des marqueurs (true par défaut)

// Chemin des fichiers de session (même que app/Config/Session.php : WRITEPATH . 'session')
$sessionPath = __DIR__ . '/../writable/session';

// --- Chargement .env pour la base de données ---
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $envLines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $val] = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($key, $_ENV)) {
            putenv("$key=$val");
            $_ENV[$key] = $_SERVER[$key] = $val;
        }
    }
}

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

// --- Utilisateurs actifs (géolocalisation récente, table REZO) ---
$activeUsers = [];
$dbHost = getenv('database.default.hostname') ?: '127.0.0.1';
$dbName = getenv('database.default.database') ?: '';
$dbUser = getenv('database.default.username') ?: 'root';
$dbPass = getenv('database.default.password') ?: '';
$dbPort = (int) (getenv('database.default.port') ?: 3306);

if ($dbName !== '') {
    $mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
    if ($mysqli->connect_error) {
        $activeUsers = []; // pas de connexion possible
    } else {
        $mysqli->set_charset('utf8mb4');
        // Utilisateurs dont derniere_inscription >= NOW() - 30 min (géolocalisation récente = actif)
        $col = in_array($rezoCodeColumn, ['code', 'code_administrateur', 'moncode']) ? $rezoCodeColumn : 'moncode';
        $sql = "SELECT `$col` AS code, derniere_inscription, latitude, longitude FROM REZO " .
               "WHERE derniere_inscription >= DATE_SUB(NOW(), INTERVAL ? MINUTE) " .
               "AND latitude IS NOT NULL AND longitude IS NOT NULL " .
               "ORDER BY derniere_inscription DESC";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('i', $activeGeolocMinutes);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $lat = (float) ($row['latitude'] ?? 0);
                    $lng = (float) ($row['longitude'] ?? 0);
                    if ($lat !== 0.0 || $lng !== 0.0) {
                        $activeUsers[] = [
                            'code' => (string) ($row['code'] ?? ''),
                            'derniere_inscription' => $row['derniere_inscription'] ?? null,
                            'latitude' => $lat,
                            'longitude' => $lng,
                        ];
                    }
                }
            }
            $stmt->close();
        }
        // --- Charge BDD (statut MySQL) ---
        $dbLoad = null;
        $statusVars = ['Threads_connected', 'Threads_running', 'Connections', 'Questions', 'Slow_queries', 'Uptime'];
        $res = @$mysqli->query('SHOW GLOBAL STATUS');
        if ($res) {
            $status = [];
            while ($row = $res->fetch_row()) {
                $status[$row[0]] = $row[1];
            }
            $res->close();
            $maxConn = null;
            $res2 = @$mysqli->query("SHOW VARIABLES LIKE 'max_connections'");
            if ($res2 && $r = $res2->fetch_row()) {
                $maxConn = (int) $r[1];
            }
            if ($res2) $res2->close();
            $dbLoad = [
                'threads_connected' => (int) ($status['Threads_connected'] ?? 0),
                'threads_running'    => (int) ($status['Threads_running'] ?? 0),
                'connections'        => (int) ($status['Connections'] ?? 0),
                'questions'          => (int) ($status['Questions'] ?? 0),
                'slow_queries'       => (int) ($status['Slow_queries'] ?? 0),
                'uptime_seconds'     => (int) ($status['Uptime'] ?? 0),
                'max_connections'    => $maxConn,
            ];
        }
        $mysqli->close();
    }
}
if (!isset($dbLoad)) {
    $dbLoad = null;
}

// --- Sortie ---
header('Content-Type: ' . ($format === 'json' ? 'application/json' : 'text/html') . '; charset=UTF-8');

if ($format === 'json') {
    echo json_encode([
        'count' => count($users),
        'users'  => $users,
        'active_geoloc_count' => count($activeUsers),
        'active_geoloc_users' => $activeUsers,
        'active_geoloc_minutes' => $activeGeolocMinutes,
        'window_seconds' => $windowSeconds,
        'active_minutes' => $activeMinutes,
        'db_load' => $dbLoad,
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

    <h2 style="margin-top: 2rem; color: #333;">Utilisateurs actuellement actifs (géolocalisation)</h2>
    <p class="count"><strong><?php echo count($activeUsers); ?></strong> personne(s) ayant envoyé une position dans les dernières <?php echo $activeGeolocMinutes; ?> minutes</p>
    <?php if (count($activeUsers)) : ?>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Dernière géolocalisation</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($activeUsers as $au) : ?>
            <tr>
                <td><?php echo htmlspecialchars($au['code']); ?></td>
                <td><?php echo $au['derniere_inscription'] ? date('d/m/Y H:i:s', strtotime($au['derniere_inscription'])) : '—'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else : ?>
    <p>Aucun utilisateur actif (géolocalisation) sur les dernières <?php echo $activeGeolocMinutes; ?> minutes.</p>
    <?php endif; ?>

    <?php if ($dbLoad !== null) : ?>
    <h3 style="margin-top: 2rem; color: #333;">Charge BDD</h3>
    <?php $maxConnDisplay = $dbLoad['max_connections'] !== null ? (int) $dbLoad['max_connections'] : 151; ?>
    <div id="db_load_container" style="background: #fff; padding: 1rem; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,.1); max-width: 500px;">
        <p style="margin: 0 0 0.75rem 0;"><strong>Charge (connexions) :</strong></p>
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <div style="flex: 1; height: 14px; background: #e0e0e0; border-radius: 4px; overflow: hidden;">
                <div id="db_load_bar" style="height: 100%; width: 0%; background: #2e7d32; border-radius: 4px; transition: width 0.3s, background 0.3s;"></div>
            </div>
            <span id="db_load_pct" style="font-weight: 600; min-width: 3.5em;">0 %</span>
        </div>
        <p style="margin: 0 0 0.5rem 0;"><strong>Connexions :</strong> <span id="db_load_connections">—</span> / <span id="db_load_max_conn"><?php echo $maxConnDisplay; ?></span> max</p>
        <p style="margin: 0 0 0.5rem 0;"><strong>Requêtes en cours :</strong> <span id="db_load_running">—</span></p>
        <p style="margin: 0 0 0.5rem 0;"><strong>Uptime :</strong> <span id="db_load_uptime">—</span></p>
        <p style="margin: 0 0 0.5rem 0;"><strong>Questions (total) :</strong> <span id="db_load_questions">—</span></p>
        <p style="margin: 0;"><strong>Requêtes lentes :</strong> <span id="db_load_slow">—</span></p>
    </div>
    <script>
        (function() {
            var DB_REFRESH_MS = 5000;
            var maxConn = <?php echo $dbLoad['max_connections'] !== null ? (int) $dbLoad['max_connections'] : 151; ?>; // 151 = défaut MySQL si inconnu

            function formatUptime(seconds) {
                if (seconds < 60) return seconds + ' s';
                var m = Math.floor(seconds / 60), s = seconds % 60;
                if (m < 60) return m + ' min ' + s + ' s';
                var h = Math.floor(m / 60); m = m % 60;
                if (h < 24) return h + ' h ' + m + ' min';
                var d = Math.floor(h / 24); h = h % 24;
                return d + ' j ' + h + ' h';
            }

            function updateDbLoad(data) {
                var load = data && data.db_load;
                if (!load) return;
                var el = document.getElementById('db_load_connections');
                if (el) el.textContent = load.threads_connected;
                var max = (load.max_connections != null && load.max_connections > 0) ? load.max_connections : maxConn;
                if (max > 0) {
                    var pct = Math.min(100, Math.round(100 * (load.threads_connected || 0) / max));
                    el = document.getElementById('db_load_bar');
                    if (el) {
                        el.style.width = pct + '%';
                        el.style.background = pct < 70 ? '#2e7d32' : pct < 90 ? '#ed6c02' : '#c62828';
                    }
                    el = document.getElementById('db_load_pct');
                    if (el) el.textContent = pct + ' %';
                    el = document.getElementById('db_load_max_conn');
                    if (el) el.textContent = load.max_connections != null ? load.max_connections : max + ' (estim.)';
                }
                el = document.getElementById('db_load_running');
                if (el) el.textContent = load.threads_running;
                el = document.getElementById('db_load_uptime');
                if (el) el.textContent = formatUptime(load.uptime_seconds || 0);
                el = document.getElementById('db_load_questions');
                if (el) el.textContent = (load.questions || 0).toLocaleString('fr-FR');
                el = document.getElementById('db_load_slow');
                if (el) el.textContent = (load.slow_queries || 0).toLocaleString('fr-FR');
            }

            function fetchDbLoad() {
                var qs = location.search ? '&' + location.search.slice(1) : '';
                fetch(location.pathname + '?format=json' + qs).then(function(r) { return r.json(); }).then(updateDbLoad).catch(function() {});
            }

            updateDbLoad({ db_load: <?php echo json_encode($dbLoad); ?> });
            setInterval(fetchDbLoad, DB_REFRESH_MS);
        })();
    </script>
    <?php endif; ?>

    <?php
    $activeUsersWithCoords = array_filter($activeUsers, function ($u) {
        return isset($u['latitude'], $u['longitude']) && ($u['latitude'] != 0 || $u['longitude'] != 0);
    });
    if (count($activeUsersWithCoords) > 0) : ?>
    <h3 style="margin-top: 2rem; color: #333;">Carte des positions</h3>
    <p style="margin-bottom: 0.5rem;">
        <button type="button" id="btn_toggle_refresh_map" style="padding: 6px 14px; font-size: 14px; cursor: pointer; border-radius: 4px; background: #333; color: #fff; border: none;">
            Rafraîchir toutes les <?php echo (int) $mapRefreshIntervalSeconds; ?> s
        </button>
        <label style="margin-left: 1rem; font-size: 14px; cursor: pointer;">
            <input type="checkbox" id="chk_auto_recenter" <?php echo $mapAutoRecenteringDefault ? 'checked' : ''; ?>>
            Recentrage automatique de la carte
        </label>
    </p>
    <div id="map_active_users" style="width: 100%; height: 400px; background: #e0e0e0; border-radius: 4px; margin: 1rem 0;"></div>
    <script>
        (function() {
            var REFRESH_INTERVAL_MS = <?php echo (int) $mapRefreshIntervalSeconds * 1000; ?>;
            var users = <?php echo json_encode(array_values($activeUsersWithCoords)); ?>;
            var map, infowindow, markers = [], refreshTimerId = null;

            function updateMarkers(usersData) {
                usersData = usersData || [];
                markers.forEach(function(m) { m.setMap(null); });
                markers = [];
                if (!map || !infowindow) return;
                var bounds = new google.maps.LatLngBounds();
                usersData.forEach(function(u) {
                    var pos = { lat: parseFloat(u.latitude), lng: parseFloat(u.longitude) };
                    if (isNaN(pos.lat) || isNaN(pos.lng)) return;
                    bounds.extend(pos);
                    var marker = new google.maps.Marker({
                        position: pos,
                        map: map,
                        title: u.code
                    });
                    var dateStr = u.derniere_inscription ? new Date(u.derniere_inscription).toLocaleString('fr-FR') : '—';
                    (function(usr, mkr) {
                        mkr.addListener('click', function() {
                            infowindow.setContent('<div style="padding:8px;"><strong>' + (usr.code || '') + '</strong><br>Dernière position : ' + dateStr + '</div>');
                            infowindow.open(map, mkr);
                        });
                    })(u, marker);
                    markers.push(marker);
                });
                var autoRecenter = document.getElementById('chk_auto_recenter') && document.getElementById('chk_auto_recenter').checked;
                if (autoRecenter && usersData.length > 0) {
                    if (usersData.length > 1) {
                        map.fitBounds(bounds, 40);
                    } else {
                        map.setCenter(bounds.getCenter());
                        map.setZoom(14);
                    }
                }
            }

            function fetchAndUpdate() {
                var qs = location.search ? '&' + location.search.slice(1) : '';
                var url = location.pathname + '?format=json' + qs;
                fetch(url).then(function(r) { return r.json(); }).then(function(data) {
                    var active = (data.active_geoloc_users || []).filter(function(u) {
                        return u.latitude != null && u.longitude != null && (u.latitude != 0 || u.longitude != 0);
                    });
                    updateMarkers(active);
                }).catch(function() {});
            }

            function toggleRefresh() {
                var btn = document.getElementById('btn_toggle_refresh_map');
                if (refreshTimerId) {
                    clearInterval(refreshTimerId);
                    refreshTimerId = null;
                    btn.textContent = 'Rafraîchir toutes les <?php echo (int) $mapRefreshIntervalSeconds; ?> s';
                    btn.style.background = '#333';
                } else {
                    refreshTimerId = setInterval(fetchAndUpdate, REFRESH_INTERVAL_MS);
                    btn.textContent = 'Arrêter le rafraîchissement';
                    btn.style.background = '#c33';
                    fetchAndUpdate();
                }
            }

            function initMap() {
                var center = users.length ? { lat: users[0].latitude, lng: users[0].longitude } : { lat: 46.6, lng: 2.4 };
                map = new google.maps.Map(document.getElementById('map_active_users'), {
                    zoom: 10,
                    center: center,
                    mapTypeId: 'roadmap'
                });
                infowindow = new google.maps.InfoWindow();
                updateMarkers(users);
                document.getElementById('btn_toggle_refresh_map').addEventListener('click', toggleRefresh);
            }

            var script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($googleMapsApiKey); ?>&callback=initMap';
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
            window.initMap = initMap;
        })();
    </script>
    <?php endif; ?>

    <p class="meta">
        <a href="?token=<?php echo urlencode($_GET['token'] ?? ''); ?>&amp;all=1">Voir toutes les sessions (<?php echo $sessionLifetime / 60; ?> min)</a>
        —
        <a href="?token=<?php echo urlencode($_GET['token'] ?? ''); ?>&amp;format=json">JSON</a>
    </p>
</body>
</html>
