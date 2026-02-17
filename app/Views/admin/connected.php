<?php
$users = $users ?? [];
$activeUsers = $activeUsers ?? [];
$activeUsersWithCoords = $activeUsersWithCoords ?? [];
$dbLoad = $dbLoad ?? null;
$showAll = $showAll ?? false;
$activeMinutes = (int) ($activeMinutes ?? 15);
$sessionLifetime = (int) ($sessionLifetime ?? 7200);
$activeGeolocMinutes = (int) ($activeGeolocMinutes ?? 30);
$mapRefreshIntervalSeconds = (int) ($mapRefreshIntervalSeconds ?? 5);
$mapAutoRecenteringDefault = $mapAutoRecenteringDefault ?? true;
$googleMapsApiKey = $googleMapsApiKey ?? '';
$jsonUrl = $jsonUrl ?? '';
$windowLabel = $showAll ? ($sessionLifetime / 60) : $activeMinutes;
?>
<div class="admin-card" style="max-width: 100%;">
    <h2 style="margin: 0 0 1rem 0; color: #334155;">Utilisateurs connectés (sessions)</h2>
    <p class="count" style="margin-bottom: 1rem;">
        <strong><?= count($users) ?></strong> personne(s) active(s) (dernières <?= $windowLabel ?> min)
    </p>
    <?php if (count($users) > 0) : ?>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; background: #fff;">
            <thead>
                <tr>
                    <th style="padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Code</th>
                    <th style="padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Nom</th>
                    <th style="padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Prénom</th>
                    <th style="padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Mail</th>
                    <th style="padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Indicatif</th>
                    <th style="padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Dernière activité</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u) : ?>
                <tr>
                    <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;"><?= esc($u['code']) ?></td>
                    <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;"><?= esc($u['nom']) ?></td>
                    <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;"><?= esc($u['prenom']) ?></td>
                    <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;"><?= esc($u['mail']) ?></td>
                    <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;"><?= esc($u['indicatif']) ?></td>
                    <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;"><?= date('d/m/Y H:i:s', $u['last_activity']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else : ?>
    <p>Aucune session active sur la période.</p>
    <?php endif; ?>
</div>

<div class="admin-card" style="max-width: 100%; margin-top: 1.5rem;">
    <h2 style="margin: 0 0 1rem 0; color: #334155;">Utilisateurs actuellement actifs (géolocalisation)</h2>
    <p class="count" style="margin-bottom: 1rem;">
        <strong><?= count($activeUsers) ?></strong> personne(s) ayant envoyé une position dans les dernières <?= $activeGeolocMinutes ?> minutes
    </p>
    <?php if (count($activeUsers) > 0) : ?>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; background: #fff;">
            <thead>
                <tr>
                    <th style="padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Code</th>
                    <th style="padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Dernière géolocalisation</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activeUsers as $au) : ?>
                <tr>
                    <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;"><?= esc($au['code']) ?></td>
                    <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;"><?= $au['derniere_inscription'] ? date('d/m/Y H:i:s', strtotime($au['derniere_inscription'])) : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else : ?>
    <p>Aucun utilisateur actif (géolocalisation) sur les dernières <?= $activeGeolocMinutes ?> minutes.</p>
    <?php endif; ?>
</div>

<?php if ($dbLoad !== null) : ?>
<div class="admin-card" style="max-width: 100%; margin-top: 1.5rem;">
    <h3 style="margin: 0 0 1rem 0; color: #334155;">Charge BDD</h3>
    <?php $maxConnDisplay = $dbLoad['max_connections'] !== null ? (int) $dbLoad['max_connections'] : 151; ?>
    <div id="db_load_container">
        <p style="margin: 0 0 0.75rem 0;"><strong>Charge (connexions) :</strong></p>
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <div style="flex: 1; height: 14px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                <div id="db_load_bar" style="height: 100%; width: 0%; background: #2e7d32; border-radius: 4px; transition: width 0.3s, background 0.3s;"></div>
            </div>
            <span id="db_load_pct" style="font-weight: 600; min-width: 3.5em;">0 %</span>
        </div>
        <p style="margin: 0 0 0.5rem 0;"><strong>Connexions :</strong> <span id="db_load_connections">—</span> / <span id="db_load_max_conn"><?= $maxConnDisplay ?></span> max</p>
        <p style="margin: 0 0 0.5rem 0;"><strong>Requêtes en cours :</strong> <span id="db_load_running">—</span></p>
        <p style="margin: 0 0 0.5rem 0;"><strong>Uptime :</strong> <span id="db_load_uptime">—</span></p>
        <p style="margin: 0 0 0.5rem 0;"><strong>Questions (total) :</strong> <span id="db_load_questions">—</span></p>
        <p style="margin: 0;"><strong>Requêtes lentes :</strong> <span id="db_load_slow">—</span></p>
    </div>
    <script>
    (function() {
        var DB_REFRESH_MS = 5000;
        var maxConn = <?= $dbLoad['max_connections'] !== null ? (int) $dbLoad['max_connections'] : 151 ?>;
        var jsonUrl = <?= json_encode($jsonUrl) ?>;

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
            if (!jsonUrl) return;
            fetch(jsonUrl).then(function(r) { return r.json(); }).then(updateDbLoad).catch(function() {});
        }

        updateDbLoad({ db_load: <?= json_encode($dbLoad) ?> });
        setInterval(fetchDbLoad, DB_REFRESH_MS);
    })();
    </script>
</div>
<?php endif; ?>

<?php if (count($activeUsersWithCoords) > 0 && $googleMapsApiKey !== '') : ?>
<div class="admin-card" style="max-width: 100%; margin-top: 1.5rem;">
    <h3 style="margin: 0 0 1rem 0; color: #334155;">Carte des positions</h3>
    <p style="margin-bottom: 0.5rem;">
        <button type="button" id="btn_toggle_refresh_map" style="padding: 6px 14px; font-size: 14px; cursor: pointer; border-radius: 4px; background: #334155; color: #fff; border: none;">
            Rafraîchir toutes les <?= $mapRefreshIntervalSeconds ?> s
        </button>
        <label style="margin-left: 1rem; font-size: 14px; cursor: pointer;">
            <input type="checkbox" id="chk_auto_recenter" <?= $mapAutoRecenteringDefault ? 'checked' : '' ?>>
            Recentrage automatique de la carte
        </label>
    </p>
    <div id="map_active_users" style="width: 100%; height: 400px; background: #e2e8f0; border-radius: 4px;"></div>
    <script>
    (function() {
        var REFRESH_INTERVAL_MS = <?= $mapRefreshIntervalSeconds * 1000 ?>;
        var users = <?= json_encode($activeUsersWithCoords) ?>;
        var jsonUrl = <?= json_encode($jsonUrl) ?>;
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
            var chk = document.getElementById('chk_auto_recenter');
            var autoRecenter = chk && chk.checked;
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
            if (!jsonUrl) return;
            fetch(jsonUrl).then(function(r) { return r.json(); }).then(function(data) {
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
                btn.textContent = 'Rafraîchir toutes les <?= $mapRefreshIntervalSeconds ?> s';
                btn.style.background = '#334155';
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
        script.src = 'https://maps.googleapis.com/maps/api/js?key=<?= esc($googleMapsApiKey) ?>&callback=initMap';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
        window.initMap = initMap;
    })();
    </script>
</div>
<?php endif; ?>

<p class="meta" style="margin-top: 1.5rem; font-size: 0.9rem; color: #64748b;">
    <?php $baseAdminConnected = base_url('admin/connected'); ?>
    <?php if ($showAll) : ?>
        <a href="<?= $baseAdminConnected ?>">Voir uniquement les dernières <?= $activeMinutes ?> min</a>
    <?php else : ?>
        <a href="<?= $baseAdminConnected ?>?all=1">Voir toutes les sessions (<?= $sessionLifetime / 60 ?> min)</a>
    <?php endif; ?>
    —
    <a href="<?= esc($jsonUrl) ?>">JSON</a>
</p>
