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
        var defaultCenter = { lat: 46.6, lng: 2.4 };

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

        function updateMarkers(usersData, maPos) {
            if (!map || !infowindow || !window.google || !google.maps) return;
            if (typeof map.setCenter !== 'function') return;
            usersData = Array.isArray(usersData) ? usersData : [];
            var maPositionCode = (maPos && maPos.code) ? maPos.code : null;
            labelsGeoloc.forEach(function(l) { try { if (l && l.setMap) l.setMap(null); } catch (e) {} });
            labelsGeoloc = [];
            markers.forEach(function(m) { try { if (m && m.setMap) m.setMap(null); } catch (e) {} });
            markers = [];
            var bounds = new google.maps.LatLngBounds();
            var stylesMap = getGeolocStylesFromStorage();
            usersData.forEach(function(u) {
                if (maPositionCode && (u.code === maPositionCode)) return;
                var pos = { lat: parseFloat(u.latitude), lng: parseFloat(u.longitude) };
                if (isNaN(pos.lat) || isNaN(pos.lng)) return;
                bounds.extend(pos);
                try {
                    var style = (stylesMap && u.code && stylesMap[u.code]) ? stylesMap[u.code] : null;
                    var markerOpts = {
                        position: pos,
                        map: map,
                        title: (style && style.label) ? String(style.label) : (u.code || ''),
                        clickable: false
                    };
                    if (style && style.icon) {
                        var iconUrl = resolveIconUrl(style.icon);
                        if (iconUrl) markerOpts.icon = iconUrl;
                    }
                    var marker = new google.maps.Marker(markerOpts);
                    markers.push(marker);
                    if (LabelOverlay) {
                        var labelText = (style && style.label) ? String(style.label) : (u.code || '');
                        var backColor = (style && style.back_color) ? String(style.back_color) : '';
                        var labelOverlay = new LabelOverlay(new google.maps.LatLng(pos.lat, pos.lng), labelText, backColor);
                        labelOverlay.setMap(map);
                        labelsGeoloc.push(labelOverlay);
                    }
                } catch (e) {}
            });
            updateMaPositionFromStorage();
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

        function onStorageUpdate(e) {
            if (e && e.key === STORAGE_KEY_FIXES) updateMarqueursFixes();
            if (e && e.key === STORAGE_KEY_MA_POSITION) updateMaPositionFromStorage();
            if (e && e.key === STORAGE_KEY_MAP_TYPE) applyMapTypeFromStorage();
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

            applyMapTypeFromStorage();
            updateMarkers(initialCoords || [], initialMaPosition || null);
            updateMarqueursFixes();
            updateMaPositionFromStorage();
            setLastUpdate();
            fetchAndUpdate();
            setInterval(fetchAndUpdate, REFRESH_MS);
            try { window.addEventListener('storage', onStorageUpdate); } catch (e) {}
        };
    })();
    </script>
</body>
</html>
