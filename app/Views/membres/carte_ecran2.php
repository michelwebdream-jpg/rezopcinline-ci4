<?php
$map = $map ?? null;
$activeUsersWithCoords = $activeUsersWithCoords ?? [];
$maPosition = $maPosition ?? null;
$maPositionIconUrl = $maPositionIconUrl ?? '';
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
        #ecran2_sync_unlock {
            position: fixed; top: 10px; left: 10px;
            background: rgba(255,255,255,0.95);
            padding: 8px 12px; font-size: 13px; border-radius: 4px;
            z-index: 999; font-family: sans-serif;
            box-shadow: 0 1px 4px rgba(0,0,0,0.2);
        }
        #ecran2_sync_unlock label { cursor: pointer; user-select: none; }
        .ecran2-marker-label {
            position: absolute;
            padding: 4px 8px;
            font-size: 12px;
            font-family: sans-serif;
            color: #000;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
            white-space: nowrap;
            pointer-events: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            z-index: 1;
        }
    </style>
    <?php if (!empty($map['js'])): ?>
    <?= $map['js'] ?>
    <?php endif; ?>
</head>
<body>
    <?php if (!empty($map['html'])): ?>
    <?= $map['html'] ?>
    <?php else: ?>
    <div id="map_ecran2"></div>
    <?php endif; ?>
    <div id="ecran2_sync_unlock"><label><input type="checkbox" id="ecran2_cb_unlock" /> Déverrouiller la carte (manipulation indépendante)</label></div>
    <div id="last_update"></div>
    <script>
    (function() {
        var REFRESH_MS = <?= (int) $refreshIntervalSeconds * 1000 ?>;
        var jsonUrl = window.location.pathname + '?format=json';
        var initialCoords = <?= json_encode($activeUsersWithCoords, JSON_UNESCAPED_UNICODE) ?>;
        var initialMaPosition = <?= json_encode($maPosition, JSON_UNESCAPED_UNICODE) ?>;
        var maPositionIconUrl = <?= json_encode($maPositionIconUrl, JSON_UNESCAPED_UNICODE) ?>;
        var STORAGE_KEY_FIXES = 'rezo_marqueurs_fixes';
        var STORAGE_KEY_MA_POSITION = 'rezo_ma_position';
        var STORAGE_KEY_MAP_TYPE = 'rezo_map_type';
        var STORAGE_KEY_STYLES   = 'rezo_geoloc_styles';
        var STORAGE_KEY_CENTRAGE_AUTO = 'rezo_centrage_auto';
        var STORAGE_KEY_GEOLOC_ACTIF = 'rezo_geoloc_actif';
        var STORAGE_KEY_ECRAN2_LOGOUT = 'rezo_ecran2_logout';
        var STORAGE_KEY_DFCI = 'rezo_dfci_on';
        var STORAGE_KEY_KML = 'rezo_kml_layers';
        var STORAGE_KEY_LIGNE_FEUX = 'rezo_ligne_feux';
        var STORAGE_KEY_MAP_VIEW = 'rezo_map_view';
        var LOGIN_URL = <?= json_encode($loginUrl ?? '') ?>;
        var defaultCenter = { lat: 46.6, lng: 2.4 };
        var dfciOverlayOn = false;
        var kmlLayersEcran2 = [];
        var ligneFeuxEcran2 = [];
        var syncMapViewUnlocked = false;

        function isGeolocActif() {
            try { return localStorage.getItem(STORAGE_KEY_GEOLOC_ACTIF) === '1'; } catch (e) { return false; }
        }
        function isCentrageAutoEnabled() {
            try { return localStorage.getItem(STORAGE_KEY_CENTRAGE_AUTO) === '1'; } catch (e) { return false; }
        }

        var map = null;
        var infowindow = null;
        var markers = [];
        var markerMaPosition = null;
        var labelMaPosition = null;
        var markersFixes = [];
        var labelsFixes = [];
        var labelsGeoloc = [];
        var LabelOverlay = null;

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

        function getGeolocStylesFromStorage() {
            try {
                var raw = localStorage.getItem(STORAGE_KEY_STYLES);
                if (!raw) return {};
                var obj = JSON.parse(raw);
                return (obj && typeof obj === 'object') ? obj : {};
            } catch (e) { return {}; }
        }

        function updateMaPositionFromStorage() {
            if (!map || !window.google || !google.maps || !LabelOverlay) return;
            if (labelMaPosition) { labelMaPosition.setMap(null); labelMaPosition = null; }
            if (markerMaPosition) { markerMaPosition.setMap(null); markerMaPosition = null; }
            var raw, data;
            try {
                raw = localStorage.getItem(STORAGE_KEY_MA_POSITION);
                if (!raw) return;
                data = JSON.parse(raw);
            } catch (e) { return; }
            if (!data || !data.visible || data.lat == null || data.lng == null) return;
            var pos = { lat: parseFloat(data.lat), lng: parseFloat(data.lng) };
            if (isNaN(pos.lat) || isNaN(pos.lng)) return;
            var iconOpt = (maPositionIconUrl && maPositionIconUrl.length > 0)
                ? maPositionIconUrl
                : {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 12,
                    fillColor: '#1a73e8',
                    fillOpacity: 1,
                    strokeColor: '#0d47a1',
                    strokeWeight: 2
                };
            markerMaPosition = new google.maps.Marker({
                position: pos,
                map: map,
                title: 'Ma position (poste de commandement)',
                icon: iconOpt
            });
            if (LabelOverlay) {
                labelMaPosition = new LabelOverlay(new google.maps.LatLng(pos.lat, pos.lng), 'Ma position');
                labelMaPosition.setMap(map);
            }
            markerMaPosition.setClickable(false);
        }

        function resolveIconUrl(icon) {
            if (!icon || typeof icon !== 'string') return null;
            if (icon.indexOf('http') === 0) return icon;
            return (window.location.origin || '') + (icon.charAt(0) === '/' ? icon : '/' + icon);
        }

        function updateMarqueursFixes() {
            if (!map || !infowindow || !window.google || !google.maps || !LabelOverlay) return;
            labelsFixes.forEach(function(l) { try { if (l && l.setMap) l.setMap(null); } catch (e) {} });
            labelsFixes = [];
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
                    title: label,
                    clickable: false
                };
                if (iconUrl) {
                    markerOpts.icon = iconUrl;
                } else {
                    markerOpts.label = { text: '\u25cf', color: 'white', fontSize: '10px' };
                    markerOpts.icon = defaultIcon;
                }
                var marker = new google.maps.Marker(markerOpts);
                markersFixes.push(marker);
                if (LabelOverlay) {
                    var labelOverlay = new LabelOverlay(new google.maps.LatLng(pos.lat, pos.lng), label, null);
                    labelOverlay.setMap(map);
                    labelsFixes.push(labelOverlay);
                }
            });
        }

        function getLatLng(u) {
            var lat = u.latitude != null ? parseFloat(u.latitude) : parseFloat(u.lat);
            var lng = u.longitude != null ? parseFloat(u.longitude) : parseFloat(u.lng);
            if (isNaN(lat) || isNaN(lng)) return null;
            return { lat: lat, lng: lng };
        }

        function updateMarkers(usersData, maPos) {
            var targetMap = window.map_ecran2 || map;
            if (!targetMap || !window.google || !google.maps) return;
            if (typeof targetMap.setCenter !== 'function') return;
            map = targetMap;
            usersData = Array.isArray(usersData) ? usersData : [];
            var maPositionCode = (maPos && maPos.code) ? String(maPos.code) : null;
            labelsGeoloc.forEach(function(l) { try { if (l && l.setMap) l.setMap(null); } catch (e) {} });
            labelsGeoloc = [];
            markers.forEach(function(m) { try { if (m && m.setMap) m.setMap(null); } catch (e) {} });
            markers = [];
            var bounds = new google.maps.LatLngBounds();
            var stylesMap = getGeolocStylesFromStorage();
            usersData.forEach(function(u) {
                if (!u) return;
                if (maPositionCode && String(u.code) === maPositionCode) return;
                var pos = getLatLng(u);
                if (!pos) return;
                bounds.extend(pos);
                try {
                    var style = (stylesMap && u.code != null && stylesMap[u.code]) ? stylesMap[u.code] : null;
                    var markerOpts = {
                        position: pos,
                        map: targetMap,
                        title: (style && style.label) ? String(style.label) : (u.code != null ? String(u.code) : ''),
                        clickable: false
                    };
                    if (style && style.icon) {
                        var iconUrl = resolveIconUrl(style.icon);
                        if (iconUrl) markerOpts.icon = iconUrl;
                    }
                    var marker = new google.maps.Marker(markerOpts);
                    marker.setMap(targetMap);
                    markers.push(marker);
                    if (LabelOverlay) {
                        var labelText = (style && style.label) ? String(style.label) : (u.code != null ? String(u.code) : '');
                        var backColor = (style && style.back_color) ? String(style.back_color) : '';
                        var labelOverlay = new LabelOverlay(new google.maps.LatLng(pos.lat, pos.lng), labelText, backColor);
                        labelOverlay.setMap(targetMap);
                        labelsGeoloc.push(labelOverlay);
                    }
                } catch (e) {}
            });
            updateMaPositionFromStorage();
            if (maPos && maPos.latitude != null && maPos.longitude != null) {
                bounds.extend({ lat: parseFloat(maPos.latitude), lng: parseFloat(maPos.longitude) });
            }
            if (!isCentrageAutoEnabled()) return;
            if (!syncMapViewUnlocked) return;
            try {
                var rawMa = localStorage.getItem(STORAGE_KEY_MA_POSITION);
                if (rawMa) {
                    var dataMa = JSON.parse(rawMa);
                    if (dataMa && dataMa.visible && dataMa.lat != null && dataMa.lng != null) {
                        bounds.extend({ lat: parseFloat(dataMa.lat), lng: parseFloat(dataMa.lng) });
                    }
                }
                var listFixes = getMarqueursFixesFromStorage();
                if (Array.isArray(listFixes)) {
                    listFixes.forEach(function(item) {
                        if (!item) return;
                        var lat = parseFloat(item.lat);
                        var lng = parseFloat(item.lng);
                        if (!isNaN(lat) && !isNaN(lng)) bounds.extend({ lat: lat, lng: lng });
                    });
                }
                var n = markers.length + (markerMaPosition ? 1 : 0) + (listFixes ? listFixes.length : 0);
                if (n === 0) return;
                if (bounds.getNorthEast() && !bounds.getNorthEast().equals(bounds.getSouthWest())) {
                    if (n > 1) {
                        targetMap.fitBounds(bounds, 50);
                    } else {
                        targetMap.setCenter(bounds.getCenter());
                        targetMap.setZoom(14);
                    }
                }
            } catch (e) {}
        }

        function filterCoords(activeUsers) {
            activeUsers = Array.isArray(activeUsers) ? activeUsers : [];
            return activeUsers.filter(function(u) {
                if (!u) return false;
                var lat = u.latitude != null ? parseFloat(u.latitude) : parseFloat(u.lat);
                var lng = u.longitude != null ? parseFloat(u.longitude) : parseFloat(u.lng);
                return !isNaN(lat) && !isNaN(lng) && (lat !== 0 || lng !== 0);
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
                    var maPos = data.ma_position || null;
                    // Ne pas effacer les marqueurs géoloc si le fetch renvoie vide (garder l’état précédent)
                    if (!isGeolocActif()) coords = [];
                    updateMarkers(coords, maPos);
                    updateMarqueursFixes();
                    updateMaPositionFromStorage();
                    setLastUpdate();
                })
                .catch(function() {});
        }

        function applyMapTypeFromStorage() {
            if (!map || !map.setMapTypeId) return;
            try {
                var id = localStorage.getItem(STORAGE_KEY_MAP_TYPE);
                if (id && map.getMapTypeId) {
                    var current = map.getMapTypeId();
                    if (current !== id) map.setMapTypeId(id);
                }
            } catch (e) {}
        }

        function applyMapViewFromStorage() {
            if (!map || syncMapViewUnlocked) return;
            try {
                var raw = localStorage.getItem(STORAGE_KEY_MAP_VIEW);
                if (!raw) return;
                var data = JSON.parse(raw);
                if (!data || data.lat == null || data.lng == null) return;
                var lat = parseFloat(data.lat), lng = parseFloat(data.lng), zoom = parseInt(data.zoom, 10);
                if (isNaN(lat) || isNaN(lng)) return;
                map.setCenter(new google.maps.LatLng(lat, lng));
                if (!isNaN(zoom) && zoom >= 0 && zoom <= 22) map.setZoom(zoom);
            } catch (e) {}
        }

        function applyDfciFromStorage() {
            if (!map || !map.overlayMapTypes) return;
            try {
                var on = localStorage.getItem(STORAGE_KEY_DFCI) === '1';
                if (on && !dfciOverlayOn && window.tiledLayer_carroyages_dfci) {
                    map.overlayMapTypes.insertAt(0, window.tiledLayer_carroyages_dfci);
                    dfciOverlayOn = true;
                } else if (!on && dfciOverlayOn) {
                    map.overlayMapTypes.removeAt(0);
                    dfciOverlayOn = false;
                }
            } catch (e) {}
        }

        function updateKmlFromStorage() {
            if (!map || !window.google || !google.maps || !google.maps.KmlLayer) return;
            var targetMap = window.map_ecran2 || map;
            var i;
            for (i = 0; i < kmlLayersEcran2.length; i++) {
                try {
                    if (kmlLayersEcran2[i] && kmlLayersEcran2[i].setMap) kmlLayersEcran2[i].setMap(null);
                } catch (e) {}
            }
            kmlLayersEcran2 = [];
            try {
                var raw = localStorage.getItem(STORAGE_KEY_KML);
                if (!raw) return;
                var list = JSON.parse(raw);
                if (!Array.isArray(list)) return;
                list.forEach(function (item) {
                    if (!item || !item.url || !item.visible) return;
                    try {
                        var layer = new google.maps.KmlLayer({ url: item.url, map: targetMap });
                        kmlLayersEcran2.push(layer);
                    } catch (e) {}
                });
            } catch (e) {}
        }

        function updateLigneFeuxFromStorage() {
            if (!map || !window.google || !google.maps || !google.maps.geometry || !google.maps.geometry.spherical) return;
            var targetMap = window.map_ecran2 || map;
            var i;
            for (i = 0; i < ligneFeuxEcran2.length; i++) {
                try {
                    if (ligneFeuxEcran2[i] && ligneFeuxEcran2[i].setMap) ligneFeuxEcran2[i].setMap(null);
                } catch (e) {}
            }
            ligneFeuxEcran2 = [];
            try {
                var raw = localStorage.getItem(STORAGE_KEY_LIGNE_FEUX);
                if (!raw) return;
                var list = JSON.parse(raw);
                if (!Array.isArray(list)) return;
                list.forEach(function (item) {
                    if (!item || item.lat == null || item.lng == null) return;
                    var latlng = new google.maps.LatLng(parseFloat(item.lat), parseFloat(item.lng));
                    var angle = typeof item.angle === 'number' ? item.angle : parseFloat(item.angle) || 0;
                    var latlng_final = google.maps.geometry.spherical.computeOffset(latlng, 1000 * 1000, angle);
                    var line = new google.maps.Polyline({
                        strokeColor: '#000',
                        strokeOpacity: 0.8,
                        strokeWeight: 4,
                        path: [latlng, latlng_final],
                        map: targetMap
                    });
                    ligneFeuxEcran2.push(line);
                });
            } catch (e) {}
        }

        function onStorageUpdate(e) {
            if (e && e.key === STORAGE_KEY_FIXES) updateMarqueursFixes();
            if (e && e.key === STORAGE_KEY_MA_POSITION) updateMaPositionFromStorage();
            if (e && e.key === STORAGE_KEY_MAP_TYPE) applyMapTypeFromStorage();
            if (e && e.key === STORAGE_KEY_GEOLOC_ACTIF && e.newValue !== '1') updateMarkers([], null);
            if (e && e.key === STORAGE_KEY_DFCI) applyDfciFromStorage();
            if (e && e.key === STORAGE_KEY_KML) updateKmlFromStorage();
            if (e && e.key === STORAGE_KEY_LIGNE_FEUX) updateLigneFeuxFromStorage();
            if (e && e.key === STORAGE_KEY_MAP_VIEW) applyMapViewFromStorage();
            if (e && e.key === STORAGE_KEY_ECRAN2_LOGOUT) {
                try { window.close(); } catch (e2) {}
                if (!window.closed) window.location.href = (LOGIN_URL || (location.origin + '/signup/login'));
            }
        }

        window.ecran2PostInit = function() {
            if (typeof window.map_ecran2 === 'undefined' || !window.map_ecran2) return;
            map = window.map_ecran2;
            infowindow = typeof window.iw_map_ecran2 !== 'undefined' ? window.iw_map_ecran2 : new (window.google && google.maps && google.maps.InfoWindow ? google.maps.InfoWindow : function(){})();

            if (window.google && google.maps && google.maps.OverlayView) {
                function LabelOverlayCtor(position, text, backColor) {
                    this.position = position;
                    this.text = text;
                    this.backColor = backColor || '';
                    this.div_ = null;
                }
                LabelOverlayCtor.prototype = new google.maps.OverlayView();
                LabelOverlayCtor.prototype.onAdd = function() {
                    this.div_ = document.createElement('div');
                    this.div_.className = 'ecran2-marker-label';
                    this.div_.textContent = this.text;
                    if (this.backColor) {
                        this.div_.style.backgroundColor = this.backColor;
                    }
                    var panes = this.getPanes();
                    if (panes && panes.overlayLayer) panes.overlayLayer.appendChild(this.div_);
                };
                LabelOverlayCtor.prototype.draw = function() {
                    var projection = this.getProjection();
                    if (!projection || !this.div_) return;
                    var pos = projection.fromLatLngToDivPixel(this.position);
                    if (!pos) return;
                    this.div_.style.left = pos.x + 'px';
                    this.div_.style.top = pos.y + 'px';
                    var w = this.div_.offsetWidth || 80, h = this.div_.offsetHeight || 22;
                    this.div_.style.marginLeft = (-(w / 2)) + 'px';
                    this.div_.style.marginTop = (-62 - h) + 'px';
                };
                LabelOverlayCtor.prototype.onRemove = function() {
                    if (this.div_ && this.div_.parentNode) this.div_.parentNode.removeChild(this.div_);
                    this.div_ = null;
                };
                LabelOverlay = LabelOverlayCtor;
            }

            applyMapViewFromStorage();
            applyMapTypeFromStorage();
            applyDfciFromStorage();
            updateKmlFromStorage();
            updateLigneFeuxFromStorage();
            updateMarqueursFixes();
            updateMaPositionFromStorage();
            setLastUpdate();
            setTimeout(function() {
                var coordsInit = isGeolocActif() ? (initialCoords || []) : [];
                updateMarkers(coordsInit, initialMaPosition || null);
                fetchAndUpdate();
            }, 0);
            setInterval(fetchAndUpdate, REFRESH_MS);
            try { window.addEventListener('storage', onStorageUpdate); } catch (e) {}
            var cbUnlock = document.getElementById('ecran2_cb_unlock');
            if (cbUnlock) {
                cbUnlock.addEventListener('change', function() {
                    syncMapViewUnlocked = !!cbUnlock.checked;
                    if (!syncMapViewUnlocked) applyMapViewFromStorage();
                });
            }
            var hadOpener = !!window.opener;
            if (hadOpener) {
                setInterval(function() {
                    try {
                        if (window.closed) return;
                        if (!window.opener || window.opener.closed) window.close();
                    } catch (e2) {}
                }, 500);
            }
        };
    })();
    </script>
</body>
</html>
