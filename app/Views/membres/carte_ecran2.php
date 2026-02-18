<?php
$activeUsersWithCoords = $activeUsersWithCoords ?? [];
$maPosition = $maPosition ?? null;
$googleMapsApiKey = $googleMapsApiKey ?? '';
$refreshIntervalSeconds = (int) ($refreshIntervalSeconds ?? 10);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carte REZO+ — Écran 2</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; height: 100%; overflow: hidden; }
        #map_ecran2 { width: 100%; height: 100%; }
        #last_update {
            position: fixed; bottom: 10px; right: 10px;
            background: rgba(0,0,0,0.65); color: #fff;
            padding: 6px 10px; font-size: 12px; border-radius: 4px;
            z-index: 999; font-family: sans-serif;
        }
    </style>
</head>
<body>
    <div id="map_ecran2"></div>
    <div id="last_update"></div>
    <script>
    (function() {
        var REFRESH_MS = <?= (int) $refreshIntervalSeconds * 1000 ?>;
        var jsonUrl = window.location.pathname + '?format=json';
        var googleMapsApiKey = <?= json_encode($googleMapsApiKey) ?>;
        var initialCoords = <?= json_encode($activeUsersWithCoords, JSON_UNESCAPED_UNICODE) ?>;
        var initialMaPosition = <?= json_encode($maPosition, JSON_UNESCAPED_UNICODE) ?>;
        var STORAGE_KEY_FIXES = 'rezo_marqueurs_fixes';
        var defaultCenter = { lat: 46.6, lng: 2.4 };

        var map = null;
        var infowindow = null;
        var markers = [];
        var markerMaPosition = null;
        var markersFixes = [];

        function escapeHtml(str) {
            return String(str == null ? '' : str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getMarqueursFixesFromStorage() {
            try {
                var raw = localStorage.getItem(STORAGE_KEY_FIXES);
                if (!raw) return [];
                var arr = JSON.parse(raw);
                return Array.isArray(arr) ? arr : [];
            } catch (e) { return []; }
        }

        function updateMaPosition(maPos) {
            if (!map || !window.google || !google.maps) return;
            if (markerMaPosition) { markerMaPosition.setMap(null); markerMaPosition = null; }
            if (!maPos || maPos.latitude == null || maPos.longitude == null) return;
            var pos = { lat: parseFloat(maPos.latitude), lng: parseFloat(maPos.longitude) };
            if (isNaN(pos.lat) || isNaN(pos.lng)) return;
            markerMaPosition = new google.maps.Marker({
                position: pos,
                map: map,
                title: 'Ma position (poste de commandement)',
                label: { text: 'PC', color: 'white', fontWeight: 'bold' },
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 12,
                    fillColor: '#1a73e8',
                    fillOpacity: 1,
                    strokeColor: '#0d47a1',
                    strokeWeight: 2
                }
            });
            if (infowindow) {
                markerMaPosition.addListener('click', function() {
                    infowindow.setContent('<div style="padding:10px;"><strong>Ma position</strong><br>Poste de commandement</div>');
                    infowindow.open(map, markerMaPosition);
                });
            }
        }

        function resolveIconUrl(icon) {
            if (!icon || typeof icon !== 'string') return null;
            if (icon.indexOf('http') === 0) return icon;
            return (window.location.origin || '') + (icon.charAt(0) === '/' ? icon : '/' + icon);
        }

        function updateMarqueursFixes() {
            if (!map || !infowindow || !window.google || !google.maps) return;
            markersFixes.forEach(function(m) { try { if (m && m.setMap) m.setMap(null); } catch (e) {} });
            markersFixes = [];
            var list = getMarqueursFixesFromStorage();
            var defaultIcon = {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 8,
                fillColor: '#e65100',
                fillOpacity: 1,
                strokeColor: '#bf360c',
                strokeWeight: 1
            };
            list.forEach(function(item) {
                var lat = parseFloat(item.lat);
                var lng = parseFloat(item.lng);
                var label = (item.label != null && item.label !== '') ? String(item.label) : 'Marqueur fixe';
                if (isNaN(lat) || isNaN(lng)) return;
                var pos = { lat: lat, lng: lng };
                var iconUrl = resolveIconUrl(item.icon);
                var markerOpts = {
                    position: pos,
                    map: map,
                    title: label
                };
                if (iconUrl) {
                    markerOpts.icon = iconUrl;
                } else {
                    markerOpts.label = { text: '\u25cf', color: 'white', fontSize: '10px' };
                    markerOpts.icon = defaultIcon;
                }
                var marker = new google.maps.Marker(markerOpts);
                (function(lbl) {
                    marker.addListener('click', function() {
                        infowindow.setContent('<div style="padding:10px;"><strong>' + escapeHtml(lbl) + '</strong></div>');
                        infowindow.open(map, marker);
                    });
                })(label);
                markersFixes.push(marker);
            });
        }

        function updateMarkers(usersData, maPos) {
            if (!map || !infowindow || !window.google || !google.maps) return;
            if (typeof map.setCenter !== 'function') return;
            usersData = Array.isArray(usersData) ? usersData : [];
            var maPositionCode = (maPos && maPos.code) ? maPos.code : null;
            markers.forEach(function(m) { try { if (m && m.setMap) m.setMap(null); } catch (e) {} });
            markers = [];
            var bounds = new google.maps.LatLngBounds();
            usersData.forEach(function(u) {
                if (maPositionCode && (u.code === maPositionCode)) return;
                var pos = { lat: parseFloat(u.latitude), lng: parseFloat(u.longitude) };
                if (isNaN(pos.lat) || isNaN(pos.lng)) return;
                bounds.extend(pos);
                try {
                    var marker = new google.maps.Marker({ position: pos, map: map, title: u.code });
                    var dateStr = u.derniere_inscription ? new Date(String(u.derniere_inscription).replace(' ', 'T')).toLocaleString('fr-FR') : '—';
                    (function(usr, mkr) {
                        mkr.addListener('click', function() {
                            infowindow.setContent('<div style="padding:10px;"><strong>' + escapeHtml(usr.code || '') + '</strong><br>Dernière position : ' + escapeHtml(dateStr) + '</div>');
                            infowindow.open(map, mkr);
                        });
                    })(u, marker);
                    markers.push(marker);
                } catch (e) {}
            });
            updateMaPosition(maPos || null);
            if (maPos && maPos.latitude != null && maPos.longitude != null) {
                bounds.extend({ lat: parseFloat(maPos.latitude), lng: parseFloat(maPos.longitude) });
            }
            if (usersData.length > 0 || (maPos && maPos.latitude != null)) {
                try {
                    if (bounds.getNorthEast() && !bounds.getNorthEast().equals(bounds.getSouthWest())) {
                        if (markers.length + (markerMaPosition ? 1 : 0) > 1) {
                            map.fitBounds(bounds, 50);
                        } else {
                            map.setCenter(bounds.getCenter());
                            map.setZoom(14);
                        }
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

        function setLastUpdate() {
            var el = document.getElementById('last_update');
            if (el) el.textContent = 'Dernière MAJ : ' + new Date().toLocaleTimeString('fr-FR');
        }

        function fetchAndUpdate() {
            fetch(jsonUrl)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var coords = filterCoords(data.active_geoloc_users || []);
                    updateMarkers(coords, data.ma_position || null);
                    updateMarqueursFixes();
                    setLastUpdate();
                })
                .catch(function() {});
        }

        function initMap() {
            var elMap = document.getElementById('map_ecran2');
            if (!elMap) return;
            var center = (initialCoords && initialCoords.length) ? { lat: initialCoords[0].latitude, lng: initialCoords[0].longitude } : defaultCenter;
            if (initialMaPosition && initialMaPosition.latitude != null && initialMaPosition.longitude != null) {
                center = { lat: parseFloat(initialMaPosition.latitude), lng: parseFloat(initialMaPosition.longitude) };
            }
            map = new google.maps.Map(elMap, {
                zoom: (initialCoords && initialCoords.length) || initialMaPosition ? 10 : 5,
                center: center,
                mapTypeId: 'roadmap',
                fullscreenControl: true
            });
            infowindow = new google.maps.InfoWindow();
            updateMarkers(initialCoords || [], initialMaPosition || null);
            updateMarqueursFixes();
            setLastUpdate();
        }

        function onStorageUpdate(e) {
            if (e && e.key === STORAGE_KEY_FIXES) updateMarqueursFixes();
        }

        function loadGoogleMaps(cb) {
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
            var script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(googleMapsApiKey) + '&callback=__initMapEcran2';
            script.async = true;
            script.defer = true;
            window.__initMapEcran2 = function() { cb(); };
            document.head.appendChild(script);
        }

        loadGoogleMaps(function() {
            initMap();
            fetchAndUpdate();
            setInterval(fetchAndUpdate, REFRESH_MS);
            try { window.addEventListener('storage', onStorageUpdate); } catch (e) {}
        });
    })();
    </script>
</body>
</html>
