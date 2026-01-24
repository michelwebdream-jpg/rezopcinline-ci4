# Guide de configuration du fichier .env pour le déploiement

## Vue d'ensemble

Le fichier `.env` contient les configurations spécifiques à l'environnement. L'application détecte automatiquement la plupart des paramètres, mais certains doivent être adaptés selon l'environnement (local ou production).

## Détection automatique

Les éléments suivants sont **détectés automatiquement** et ne nécessitent **aucune modification** :

### 1. `app.baseURL`
- **Détection automatique** via `app/Config/App.php`
- En local : `https://rezopcinline-ci4.local/`
- En production : `https://www.web-dream.fr/rezopcinline/` (détecte automatiquement le sous-dossier)

### 2. Base de données pour `/dev/rezo_flash_code/`
- **Gestion automatique** via `class_DbConnect.php`
- Détecte l'environnement via `HTTP_HOST`
- En local : utilise `127.0.0.1` avec `root/root`
- En production : utilise `webdreamblog.mysql.db` avec les identifiants de production

### 3. `APP_SERVER_URL`
- **IMPORTANT** : Cette variable est utilisée par le code PHP (SignupModel, etc.) pour les appels cURL
- **Doit être définie dans le `.env`** (pas de détection automatique pour PHP)
- En local : `https://rezopcinline-ci4.local`
- En production : `https://www.web-dream.fr`
- **Note** : Dans le JavaScript, `APP_SERVER_URL` est détecté automatiquement, mais pour PHP il doit être défini dans le `.env`

## Configuration manuelle requise

### Pour le développement local

Utilisez le fichier `.env` tel quel. Il est déjà configuré pour le développement local avec MAMP.

### Pour la production

**Option 1 : Modifier le fichier `.env` existant**

1. Changez `CI_ENVIRONMENT` :
   ```env
   CI_ENVIRONMENT = production
   ```

2. Changez `APP_SERVER_URL` :
   ```env
   APP_SERVER_URL = https://www.web-dream.fr
   ```

3. (Optionnel) Les paramètres de base de données dans le `.env` ne sont **PAS nécessaires** pour le fonctionnement normal de l'application car :
   - Les fichiers dans `/dev/rezo_flash_code/` utilisent `class_DbConnect.php` qui détecte automatiquement l'environnement
   - Les sessions utilisent le système de fichiers (pas la base de données)
   - Ces paramètres ne sont utilisés que pour les migrations CI4 (si vous en utilisez)
   
   **Vous pouvez donc ignorer complètement la section DATABASE** dans le `.env` si vous n'utilisez pas les migrations CodeIgniter.

**Option 2 : Utiliser le fichier `.env.production`**

1. Copiez le fichier `.env.production` vers `.env` :
   ```bash
   cp .env.production .env
   ```

2. Vérifiez que `CI_ENVIRONMENT = production` est bien défini.

3. Vérifiez que `APP_SERVER_URL = https://www.web-dream.fr` est bien défini.

4. Adaptez les valeurs si nécessaire (notamment les mots de passe si différents).

## Structure du fichier .env

```
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = development  # ou 'production' en production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
# baseURL détecté automatiquement - pas besoin de le définir
app.forceGlobalSecureRequests = false  # true en production recommandé

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
# Configuration pour CodeIgniter (sessions, etc.)
# Les fichiers /dev/rezo_flash_code/ utilisent class_DbConnect.php
# qui détecte automatiquement l'environnement

#--------------------------------------------------------------------
# SERVER URLs
#--------------------------------------------------------------------
# APP_SERVER_URL détecté automatiquement - pas besoin de le définir
# URIs des endpoints (chemins relatifs)
```

## Vérifications après déploiement

1. ✅ Vérifier que `CI_ENVIRONMENT = production`
2. ✅ Vérifier que `app.forceGlobalSecureRequests = true` (recommandé en production)
3. ✅ Vérifier les permissions sur `writable/` (logs, cache, sessions)
4. ✅ Tester l'application et vérifier les logs dans `application/logs/`

## Notes importantes

- **Ne jamais commiter le fichier `.env`** dans le dépôt Git (il devrait être dans `.gitignore`)
- Le fichier `.env.production` est un **modèle d'exemple** et peut être commité
- Les mots de passe et informations sensibles doivent être protégés
- En production, activez `forceGlobalSecureRequests` pour forcer HTTPS

## Dépannage

### L'application ne détecte pas correctement l'URL de base

Vérifiez que :
- Le serveur web est correctement configuré
- Les variables `$_SERVER['HTTP_HOST']` et `$_SERVER['SCRIPT_NAME']` sont correctement définies
- Le fichier `.htaccess` est présent dans `public/`

### Problèmes de connexion à la base de données

Vérifiez que :
- `class_DbConnect.php` détecte correctement l'environnement (vérifier `HTTP_HOST`)
- Les identifiants de base de données sont corrects
- Le serveur de base de données est accessible depuis le serveur web

### Les chemins ne fonctionnent pas correctement

Vérifiez que :
- `baseURL` est correctement détecté (voir les logs)
- Les fichiers proxy dans `/dev/rezo_galerie/` et `/dev/rezo_flash_code/` détectent correctement le chemin de base
- Le sous-dossier `/rezopcinline/` est correctement détecté
