<?php
$users = $users ?? [];
$activeUsers = $activeUsers ?? [];
$activeUsersWithCoords = $activeUsersWithCoords ?? [];
$dbLoad = $dbLoad ?? null;
$activeMinutes = (int) ($activeMinutes ?? 15);
$activeGeolocMinutes = (int) ($activeGeolocMinutes ?? 30);
$mapRefreshIntervalSeconds = (int) ($mapRefreshIntervalSeconds ?? 5);
$mapAutoRecenteringDefault = $mapAutoRecenteringDefault ?? true;
$googleMapsApiKey = $googleMapsApiKey ?? '';
$jsonUrl = $jsonUrl ?? '';
$windowLabel = $activeMinutes;
?>
<div class="admin-card" style="max-width: 100%;">
    <div style="display:flex; align-items:center; justify-content:space-between; gap: 1rem; flex-wrap: wrap;">
        <div>
            <h2 style="margin: 0; color: #334155;">Rafraîchissement</h2>
            <div style="margin-top: 0.25rem; color:#64748b; font-size: 0.95rem;">
                Tous les blocs se mettent à jour ensemble (toutes les <strong><?= (int) $mapRefreshIntervalSeconds ?></strong> s).
                <span id="last_refresh_at" style="margin-left: 0.5rem;"></span>
                <span id="refresh_status" style="margin-left: 0.5rem;"></span>
            </div>
        </div>
        <button type="button" id="btn_toggle_refresh_global" style="padding: 10px 14px; font-size: 14px; cursor: pointer; border-radius: 6px; background: #0f766e; color: #fff; border: none; font-weight: 600;">
            Rafraîchissement : ON
        </button>
    </div>
</div>

<style>
.connected-two-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start; margin-top: 1.5rem; }
@media (max-width: 900px) { .connected-two-cols { grid-template-columns: 1fr; } }
#refresh_status.working { color: #0f766e; font-weight: 500; }
#refresh_status.error { color: #b91c1c; font-weight: 500; }
</style>
<div class="connected-two-cols">
<div style="display: flex; flex-direction: column; gap: 1.5rem;">
<div class="admin-card" style="max-width: 100%;">
    <h2 style="margin: 0 0 1rem 0; color: #334155;">Utilisateurs connectés (sessions)</h2>
    <p class="count" style="margin-bottom: 1rem;">
        <strong id="sessions_count"><?= count($users) ?></strong> personne(s) active(s) (dernières <?= $windowLabel ?> min)
    </p>
    <p id="sessions_empty" style="display: <?= count($users) ? 'none' : 'block' ?>;">Aucune session active sur la période.</p>
    <div id="sessions_table_wrap" style="overflow-x: auto; display: <?= count($users) ? 'block' : 'none' ?>;">
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
            <tbody id="sessions_tbody">
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
</div>

<div class="admin-card" style="max-width: 100%;">
    <h2 style="margin: 0 0 1rem 0; color: #334155;">Utilisateurs actuellement actifs (géolocalisation)</h2>
    <p class="count" style="margin-bottom: 1rem;">
        <strong id="active_geoloc_count"><?= count($activeUsers) ?></strong> personne(s) ayant envoyé une position dans les dernières <?= $activeGeolocMinutes ?> minutes
    </p>
    <p id="active_geoloc_empty" style="display: <?= count($activeUsers) ? 'none' : 'block' ?>;">Aucun utilisateur actif (géolocalisation) sur les dernières <?= $activeGeolocMinutes ?> minutes.</p>
    <div id="active_geoloc_table_wrap" style="overflow-x: auto; display: <?= count($activeUsers) ? 'block' : 'none' ?>;">
        <table style="width: 100%; border-collapse: collapse; background: #fff;">
            <thead>
                <tr>
                    <th style="padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Code</th>
                    <th style="padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Dernière géolocalisation</th>
                </tr>
            </thead>
            <tbody id="active_geoloc_tbody">
                <?php foreach ($activeUsers as $au) : ?>
                <tr>
                    <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;"><?= esc($au['code']) ?></td>
                    <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;"><?= $au['derniere_inscription'] ? date('d/m/Y H:i:s', strtotime($au['derniere_inscription'])) : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($dbLoad !== null) : ?>
<div class="admin-card" style="max-width: 100%;">
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
</div>
<?php endif; ?>
</div>

<div style="position: sticky; top: 1rem;">
<?php if ($googleMapsApiKey !== '') : ?>
<div class="admin-card" style="max-width: 100%; min-height: 400px;">
    <h3 style="margin: 0 0 1rem 0; color: #334155;">Carte des positions</h3>
    <p style="margin-bottom: 0.5rem;">
        <label style="font-size: 14px; cursor: pointer;">
            <input type="checkbox" id="chk_auto_recenter" <?= $mapAutoRecenteringDefault ? 'checked' : '' ?>>
            Recentrage automatique de la carte
        </label>
    </p>
    <div id="map_active_users" style="width: 100%; height: 400px; min-height: 400px; background: #e2e8f0; border-radius: 4px;"></div>
</div>
<?php endif; ?>
</div>
</div>

<script>
(function() {
    var REFRESH_INTERVAL_MS = <?= (int) $mapRefreshIntervalSeconds * 1000 ?>;
    var jsonUrl = window.location.pathname + '?format=json';
    var googleMapsApiKey = <?= json_encode($googleMapsApiKey) ?>;
    var initialUsersWithCoords = <?= json_encode($activeUsersWithCoords, JSON_UNESCAPED_UNICODE) ?>;
    var initialDbLoad = <?= json_encode($dbLoad, JSON_UNESCAPED_UNICODE) ?>;
    var initialUsers = <?= json_encode($users, JSON_UNESCAPED_UNICODE) ?>;
    var initialActiveUsers = <?= json_encode($activeUsers, JSON_UNESCAPED_UNICODE) ?>;

    var refreshEnabled = true;
    var refreshTimerId = null;

    function pad2(n) { return (n < 10 ? '0' : '') + n; }
    function formatDateFromUnixSeconds(sec) {
        if (!sec) return '—';
        var d = new Date(sec * 1000);
        if (isNaN(d.getTime())) return '—';
        return pad2(d.getDate()) + '/' + pad2(d.getMonth() + 1) + '/' + d.getFullYear() + ' ' + pad2(d.getHours()) + ':' + pad2(d.getMinutes()) + ':' + pad2(d.getSeconds());
    }
    function formatDateFromMysqlDatetime(dt) {
        if (!dt) return '—';
        var d = new Date(String(dt).replace(' ', 'T'));
        if (isNaN(d.getTime())) return '—';
        return d.toLocaleString('fr-FR');
    }
    function setText(id, text) {
        var el = document.getElementById(id);
        if (el) el.textContent = text;
    }
    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // --- Sessions ---
    function renderSessions(users) {
        users = Array.isArray(users) ? users : [];
        setText('sessions_count', users.length);

        var wrap = document.getElementById('sessions_table_wrap');
        var empty = document.getElementById('sessions_empty');
        var tbody = document.getElementById('sessions_tbody');
        if (!wrap || !empty || !tbody) return;

        if (users.length === 0) {
            wrap.style.display = 'none';
            empty.style.display = 'block';
            tbody.innerHTML = '';
            return;
        }

        empty.style.display = 'none';
        wrap.style.display = 'block';
        tbody.innerHTML = users.map(function(u) {
            return '<tr>' +
                '<td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;">' + escapeHtml(u.code) + '</td>' +
                '<td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;">' + escapeHtml(u.nom) + '</td>' +
                '<td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;">' + escapeHtml(u.prenom) + '</td>' +
                '<td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;">' + escapeHtml(u.mail) + '</td>' +
                '<td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;">' + escapeHtml(u.indicatif) + '</td>' +
                '<td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;">' + formatDateFromUnixSeconds(u.last_activity) + '</td>' +
            '</tr>';
        }).join('');
    }

    // --- Active geoloc ---
    function renderActiveGeoloc(activeUsers) {
        activeUsers = Array.isArray(activeUsers) ? activeUsers : [];
        setText('active_geoloc_count', activeUsers.length);

        var wrap = document.getElementById('active_geoloc_table_wrap');
        var empty = document.getElementById('active_geoloc_empty');
        var tbody = document.getElementById('active_geoloc_tbody');
        if (!wrap || !empty || !tbody) return;

        if (activeUsers.length === 0) {
            wrap.style.display = 'none';
            empty.style.display = 'block';
            tbody.innerHTML = '';
            return;
        }

        empty.style.display = 'none';
        wrap.style.display = 'block';
        tbody.innerHTML = activeUsers.map(function(u) {
            return '<tr>' +
                '<td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;">' + escapeHtml(u.code) + '</td>' +
                '<td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;">' + escapeHtml(formatDateFromMysqlDatetime(u.derniere_inscription)) + '</td>' +
            '</tr>';
        }).join('');
    }

    // --- DB load ---
    var maxConnFallback = <?= $dbLoad !== null && $dbLoad['max_connections'] !== null ? (int) $dbLoad['max_connections'] : 151 ?>;
    function formatUptime(seconds) {
        seconds = seconds || 0;
        if (seconds < 60) return seconds + ' s';
        var m = Math.floor(seconds / 60), s = seconds % 60;
        if (m < 60) return m + ' min ' + s + ' s';
        var h = Math.floor(m / 60); m = m % 60;
        if (h < 24) return h + ' h ' + m + ' min';
        var d = Math.floor(h / 24); h = h % 24;
        return d + ' j ' + h + ' h';
    }
    function updateDbLoad(load) {
        if (!load) return;
        var el = document.getElementById('db_load_connections');
        if (el) el.textContent = load.threads_connected;
        var max = (load.max_connections != null && load.max_connections > 0) ? load.max_connections : maxConnFallback;
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
            if (el) el.textContent = load.max_connections != null ? load.max_connections : (max + ' (estim.)');
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

    // --- Map ---
    var map = null;
    var infowindow = null;
    var markers = [];

    function updateMarkers(usersData) {
        if (!map || !infowindow || !window.google || !google.maps) return;
        if (typeof map.setCenter !== 'function') return;
        usersData = Array.isArray(usersData) ? usersData : [];
        markers.forEach(function(m) { try { if (m && m.setMap) m.setMap(null); } catch (e) {} });
        markers = [];
        var bounds = new google.maps.LatLngBounds();
        usersData.forEach(function(u) {
            var pos = { lat: parseFloat(u.latitude), lng: parseFloat(u.longitude) };
            if (isNaN(pos.lat) || isNaN(pos.lng)) return;
            bounds.extend(pos);
            try {
                var marker = new google.maps.Marker({ position: pos, map: map, title: u.code });
                var dateStr = u.derniere_inscription ? new Date(String(u.derniere_inscription).replace(' ', 'T')).toLocaleString('fr-FR') : '—';
                (function(usr, mkr) {
                    mkr.addListener('click', function() {
                        infowindow.setContent('<div style="padding:8px;"><strong>' + escapeHtml(usr.code || '') + '</strong><br>Dernière position : ' + escapeHtml(dateStr) + '</div>');
                        infowindow.open(map, mkr);
                    });
                })(u, marker);
                markers.push(marker);
            } catch (e) {}
        });

        var chk = document.getElementById('chk_auto_recenter');
        var autoRecenter = chk && chk.checked;
        if (autoRecenter && usersData.length > 0) {
            try {
                if (usersData.length > 1) {
                    map.fitBounds(bounds, 40);
                } else {
                    map.setCenter(bounds.getCenter());
                    map.setZoom(14);
                }
            } catch (e) {}
        }
    }

    function filterCoords(activeUsers) {
        activeUsers = Array.isArray(activeUsers) ? activeUsers : [];
        return activeUsers.filter(function(u) {
            return u && u.latitude != null && u.longitude != null && (u.latitude != 0 || u.longitude != 0);
        });
    }

    var defaultMapCenter = { lat: 46.6, lng: 2.4 };

    function initMapIfNeeded(initialCoords) {
        var elMap = document.getElementById('map_active_users');
        if (!elMap) return;
        if (!window.google || !google.maps) return;
        if (map) {
            updateMarkers(initialCoords || []);
            return;
        }
        var center = (initialCoords && initialCoords.length) ? { lat: initialCoords[0].latitude, lng: initialCoords[0].longitude } : defaultMapCenter;
        map = new google.maps.Map(elMap, { zoom: 10, center: center, mapTypeId: 'roadmap' });
        infowindow = new google.maps.InfoWindow();
        updateMarkers(initialCoords || []);
    }

    function loadGoogleMapsIfNeeded(cb) {
        var elMap = document.getElementById('map_active_users');
        if (!elMap) return;
        if (window.google && window.google.maps) { cb(); return; }
        if (!googleMapsApiKey) return;
        var existing = document.querySelector('script[src*="maps.googleapis.com"]');
        if (existing) {
            var wait = setInterval(function() {
                if (window.google && window.google.maps) {
                    clearInterval(wait);
                    cb();
                }
            }, 100);
            setTimeout(function() { clearInterval(wait); }, 15000);
            return;
        }
        if (window.__connectedMapsScriptAdded) {
            (window.__connectedMapsPendingCallbacks = window.__connectedMapsPendingCallbacks || []).push(cb);
            return;
        }
        window.__connectedMapsScriptAdded = true;
        window.__connectedMapsPendingCallbacks = window.__connectedMapsPendingCallbacks || [];
        window.__connectedMapsPendingCallbacks.push(cb);
        window.__initMapConnected = function() {
            (window.__connectedMapsPendingCallbacks || []).forEach(function(f) { f(); });
            window.__connectedMapsPendingCallbacks = [];
            window.__connectedMapsScriptAdded = false;
        };
        var script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(googleMapsApiKey) + '&callback=__initMapConnected';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    }

    // --- Unified refresh ---
    function applyData(data) {
        data = data || {};
        if (data.users) renderSessions(data.users);
        if (data.active_geoloc_users) {
            renderActiveGeoloc(data.active_geoloc_users);
            var coords = filterCoords(data.active_geoloc_users);
            if (document.getElementById('map_active_users')) {
                loadGoogleMapsIfNeeded(function() {
                    initMapIfNeeded(coords);
                    updateMarkers(coords);
                });
            }
        }
        if (data.db_load) updateDbLoad(data.db_load);
        setText('last_refresh_at', '— dernière mise à jour : ' + new Date().toLocaleTimeString('fr-FR'));
        setRefreshStatus('', null);
    }

    function setRefreshStatus(msg, type) {
        var el = document.getElementById('refresh_status');
        if (!el) return;
        el.textContent = msg;
        el.className = type === 'working' ? 'working' : type === 'error' ? 'error' : '';
    }

    function fetchAndRefresh() {
        if (!jsonUrl) return;
        setRefreshStatus('Mise à jour…', 'working');
        return fetch(jsonUrl)
            .then(function(r) {
                if (!r.ok) throw new Error('Erreur réseau');
                return r.json();
            })
            .then(function(data) { applyData(data); })
            .catch(function() {
                setRefreshStatus('Erreur', 'error');
            });
    }

    function setRefreshUi() {
        var btn = document.getElementById('btn_toggle_refresh_global');
        if (!btn) return;
        if (refreshEnabled) {
            btn.textContent = 'Rafraîchissement : ON';
            btn.style.background = '#0f766e';
        } else {
            btn.textContent = 'Rafraîchissement : OFF';
            btn.style.background = '#b91c1c';
        }
    }

    function startRefresh() {
        if (refreshTimerId) return;
        refreshTimerId = setInterval(function() {
            if (!refreshEnabled) return;
            fetchAndRefresh();
        }, REFRESH_INTERVAL_MS);
    }

    function stopRefresh() {
        if (!refreshTimerId) return;
        clearInterval(refreshTimerId);
        refreshTimerId = null;
    }

    function toggleRefresh() {
        refreshEnabled = !refreshEnabled;
        setRefreshUi();
        if (refreshEnabled) {
            fetchAndRefresh();
            startRefresh();
        } else {
            stopRefresh();
        }
    }

    // Init
    setRefreshUi();
    applyData({ users: initialUsers, active_geoloc_users: initialActiveUsers, db_load: initialDbLoad });
    if (document.getElementById('map_active_users')) {
        loadGoogleMapsIfNeeded(function() { initMapIfNeeded(initialUsersWithCoords); });
    }
    fetchAndRefresh();
    startRefresh();

    var btn = document.getElementById('btn_toggle_refresh_global');
    if (btn) btn.addEventListener('click', toggleRefresh);
})();
</script>
