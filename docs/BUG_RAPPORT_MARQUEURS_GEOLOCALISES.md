# Rapport de bug — Marqueurs géolocalisés (page membres)

**Date :** 24 janvier 2026  
**Contexte :** Page membres (carte), activité ou mission avec un ou plusieurs marqueurs géolocalisés. Données reçues via `info_activite.php`, affichage sur la carte principale (écran 1) et sur la carte « écran 2 ».

---

## 1. Résumé des symptômes

- **Écran 1 (carte principale) :** Lorsqu’une activité ou une mission est lancée avec un seul (ou plusieurs) marqueur géolocalisé, le marqueur reste à sa position initiale alors que les trames reçues (debug + console) montrent des coordonnées mises à jour (ex. latitude 3.9840818 → 3.9840816).
- **Écran 2 :** Le marqueur géolocalisé ne bouge pas non plus, et d’autres marqueurs « fantômes » apparaissent, issus d’une ancienne géolocalisation.

---

## 2. Ce qui fonctionne (confirmé par le debug)

- Les appels à `recherche_membre_activite()` et à `info_activite.php` sont bien effectués.
- La réponse est reçue (HTTP 200, `return_txt=...` avec les bonnes valeurs).
- Le parsing de la réponse (split en 13 éléments) est correct ; les indices 5 et 6 correspondent bien à la latitude et la longitude.
- `refresh_position_user()` est bien appelé avec les tableaux mis à jour (code, statut, indicatif, latitude, longitude, etc.).
- La logique « trouver un marqueur existant par `marker_id === code[i]` puis appeler `update_marker()` » est bien exécutée lorsque le membre est actif et a des coordonnées valides.

Donc le blocage n’est **pas** dans la réception ni dans le choix entre « créer un nouveau marqueur » et « mettre à jour l’existant ».

---

## 3. Cause probable — Écran 1 : MapLabel et liaison de position

### 3.1 Rôle du MapLabel

Dans `create_marker()` (`rezopcinline.js`, vers 1491–1553) :

- Un **MapLabel** (étiquette avec l’indicatif, ex. « Papa ») est créé avec une `position: latlng` initiale.
- Un **Marker** Google Maps est créé avec la même position.
- Les deux sont liés par :
  - `marker.bindTo('map', mapLabel);`
  - `marker.bindTo('position', mapLabel);`

Dans l’API Google Maps, `bindTo('position', mapLabel)` fait en général que la **position du marqueur suit celle du MapLabel**. Le MapLabel agit comme référence de position pour le marqueur.

### 3.2 Comportement dans `update_marker()`

Dans `update_marker()` (vers 1709–1733) :

- La position du **MapLabel** n’est jamais mise à jour : la ligne suivante est **commentée** :
  - `//marker_pass.mapLabel.set('position',latlng);`
- Seul le marqueur est mis à jour :
  - `marker_pass.setPosition(latlng);`

**Conséquence :**  
Le MapLabel reste à l’ancienne position. Si la liaison `position` fait que le marqueur suit le MapLabel, alors après `setPosition(latlng)` le marqueur peut être « ramené » à la position du MapLabel (ancienne), d’où l’impression que le marqueur ne bouge pas.  
Même si la liaison n’est pas stricte, l’étiquette (indicatif) reste à l’ancien endroit, ce qui peut donner l’impression que « le marqueur » (icône + étiquette) ne bouge pas.

**Conclusion écran 1 :**  
La mise à jour de la position du **MapLabel** est manquante dans `update_marker()`. Il est très probable que le correctif consiste à remettre en service la mise à jour de la position du MapLabel (décommenter et adapter si besoin `marker_pass.mapLabel.set('position', latlng)` ou équivalent).

---

## 4. Cause probable — Écran 2 : Données et localStorage

### 4.1 Deux sources de données différentes

- **Écran 1 :**  
  Données en temps réel via **`info_activite.php`** (AJAX), traitées en JS par `recherche_membre_activite()` puis `refresh_position_user()`. Les positions affichées sont celles des réponses successives à `info_activite.php`.

- **Écran 2 :**  
  Données fournies par **`Membres::carteEcran2()`** avec `?format=json` :
  - Au chargement : `initialCoords` (PHP) = `activeUsersWithCoords` issu de `fetchPositionsRezo()`.
  - En continu : `fetch(jsonUrl)` avec `jsonUrl = .../membres/carte-ecran2?format=json`, qui renvoie `active_geoloc_users` (et `ma_position`).

`fetchPositionsRezo()` lit les positions dans la **table REZO** (BDD) : `latitude`, `longitude`, `derniere_inscription` (dernières X minutes).  
Il n’y a **aucune écriture**, dans le code analysé, des positions « en direct » (issues de `info_activite.php`) dans le localStorage pour les marqueurs géolocalisés. Les clés utilisées pour l’écran 2 sont notamment :

- `rezo_marqueurs_fixes` (marqueurs fixes)
- `rezo_ma_position` (ma position)
- `rezo_geoloc_styles` (styles des moyens géolocalisés)
- `rezo_map_view`, `rezo_geoloc_actif`, etc.

Il n’existe pas de clé du type « positions actuelles des membres géolocalisés » alimentée par l’écran 1.

Donc l’écran 2 ne peut pas afficher les mêmes positions « en direct » que l’écran 1 tant qu’il ne fait que :
- lire le localStorage (marqueurs fixes, ma position, styles, etc.) ;
- et interroger l’API JSON de la carte écran 2 (données REZO en BDD).

### 4.2 Pourquoi le marqueur ne bouge pas et pourquoi des « anciens » marqueurs

- **Marqueur qui ne bouge pas :**  
  L’écran 2 se rafraîchit via `fetchAndUpdate()` → `updateMarkers(coords, maPos)` avec `coords = filterCoords(data.active_geoloc_users || [])`.  
  Si la table REZO n’est pas mise à jour en temps réel pendant l’activité (ou avec un délai), ou si la source côté serveur n’est pas la même que `info_activite.php`, les positions renvoyées par `?format=json` restent anciennes, donc le marqueur ne bouge pas.

- **Anciens marqueurs / « fantômes » :**  
  - Au premier chargement, l’écran 2 reçoit `initialCoords` (PHP) et les affiche.  
  - Ensuite, à chaque poll, il remplace les marqueurs par `data.active_geoloc_users`.  
  Si à un moment le JSON renvoie une liste différente (ex. anciens codes encore en BDD, ou cache), ou si la logique de remplacement n’est pas stricte (ex. cumul au lieu de remplacement), on peut voir à la fois des positions « actuelles » et d’anciennes positions.  
  De plus, si `rezo_geoloc_actif` ou d’autres clés localStorage ne sont pas cohérentes entre écran 1 et écran 2, la condition `if (!isGeolocActif()) coords = [];` peut vider les coords et laisser à l’écran des marqueurs issus d’un état précédent (initial ou ancien fetch).

**Conclusion écran 2 :**  
- Les positions affichées ne viennent pas du même flux que `info_activite.php` ; elles viennent de la BDD (REZO) via l’API JSON de la carte écran 2.  
- L’absence de synchronisation « temps réel » (écran 1 → localStorage ou écran 2) et le possible décalage / cache BDD expliquent à la fois le marqueur qui ne bouge pas et l’apparition d’anciens marqueurs.

---

## 5. Synthèse des causes

| Problème | Cause probable |
|----------|----------------|
| **Marqueur ne bouge pas (écran 1)** | Dans `update_marker()`, la position du **MapLabel** n’est pas mise à jour (ligne `marker_pass.mapLabel.set('position',latlng)` commentée). La liaison `marker.bindTo('position', mapLabel)` peut faire que le marqueur reste aligné sur l’ancienne position du MapLabel. |
| **Marqueur ne bouge pas (écran 2)** | L’écran 2 utilise uniquement les données de l’API JSON (table REZO), pas le flux temps réel de `info_activite.php`. Si la BDD n’est pas mise à jour en temps réel pendant l’activité, les positions restent figées. |
| **Anciens marqueurs sur l’écran 2** | Données REZO encore présentes pour d’anciennes sessions ; pas de clé localStorage « positions géoloc actuelles » écrite par l’écran 1 ; possible mélange entre `initialCoords`, anciens fetch et conditions (`rezo_geoloc_actif`, etc.). |

---

## 6. Pistes de correction (sans modification de code dans ce rapport)

1. **Écran 1 — Mise à jour du MapLabel dans `update_marker()`**  
   Décommenter et valider la mise à jour de la position du MapLabel (ex. `marker_pass.mapLabel.set('position', latlng)` si l’API MapLabel le permet), éventuellement en vérifiant la doc de la lib MapLabel pour la propriété `position`.

2. **Écran 2 — Aligner les données sur le flux temps réel**  
   - Soit exposer les positions « en direct » (issues de `info_activite.php` ou équivalent) via l’API JSON de la carte écran 2 (ou une autre API dédiée).  
   - Soit faire écrire par l’écran 1 les positions courantes dans une clé localStorage (ex. `rezo_geoloc_positions`) et faire lire cette clé par l’écran 2 (avec un `storage` event ou un timer) pour mettre à jour les marqueurs, en plus ou à la place du fetch JSON actuel.

3. **Écran 2 — Nettoyage des anciens marqueurs**  
   - S’assurer que `updateMarkers()` remplace toujours intégralement la liste des marqueurs (pas de cumul).  
   - Au démarrage d’une nouvelle activité/mission côté écran 1, éventuellement écrire un « signal » dans le localStorage (ex. `rezo_geoloc_actif` ou horodatage de session) pour que l’écran 2 réinitialise ou filtre les marqueurs selon la session courante.

---

## 7. Fichiers concernés

- **Écran 1 (marqueurs, mise à jour position) :**  
  `public/js/rezopcinline.js`  
  - `create_marker()` (vers 1491),  
  - `update_marker()` (vers 1709),  
  - `refresh_position_user()` (vers 3031),  
  - `recherche_membre_activite()` (vers 2757).

- **Écran 2 (données et affichage) :**  
  - `app/Views/membres/carte_ecran2.php` (logique JS : `updateMarkers`, `fetchAndUpdate`, localStorage),  
  - `app/Controllers/Membres.php` (`carteEcran2`, `fetchPositionsRezo`).

- **Backend positions (écran 2) :**  
  Table REZO (latitude, longitude, derniere_inscription) et éventuellement le script/serveur derrière `info_activite.php` pour vérifier si et quand la BDD est mise à jour.

---

*Rapport rédigé à partir de l’analyse du code et des captures d’écran (console debug + carte).*
