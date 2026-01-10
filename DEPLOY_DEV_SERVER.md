# Guide de déploiement sur le serveur de développement

## Configuration du domaine
- **Domaine** : `rezo.les-coches-deau.fr`
- **Base de données** : Distante sur `web-dream.fr` (webdreamblog.mysql.db)

## Étapes de déploiement

### 1. Upload des fichiers sur le serveur

Via FTP/SFTP, uploader tous les fichiers du projet dans le répertoire approprié sur le serveur.

**Structure recommandée :**
```
/home/user/rezo.les-coches-deau.fr/
├── app/
├── public/
├── system/
├── writable/
├── tests/
├── vendor/
├── .env
└── composer.json
```

**Point d'entrée :** Le répertoire `public/` doit être le document root du serveur web.

### 2. Configuration du serveur web (Apache/Nginx)

#### Pour Apache (.htaccess déjà présent)

Le fichier `.htaccess` dans `public/` devrait déjà gérer la redirection. Si vous utilisez Apache, assurez-vous que :
- `mod_rewrite` est activé
- `AllowOverride All` est configuré pour le répertoire

#### Pour Nginx

Ajouter dans la configuration du virtual host :

```nginx
server {
    listen 80;
    server_name rezo.les-coches-deau.fr;
    root /chemin/vers/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock; # Ajuster selon votre version PHP
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
```

### 3. Création du fichier .env

Sur le serveur, créer un fichier `.env` à la racine du projet (copie de `env` ou création manuelle) :

```ini
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------

CI_ENVIRONMENT = development

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------

app.baseURL = 'https://rezo.les-coches-deau.fr/'

app.forceGlobalSecureRequests = false

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------

# Note: La base de données est distante sur web-dream.fr
# La classe DbConnect gère automatiquement la connexion distante
# pour tous les fichiers dans public/dev/

database.default.hostname = 'webdreamblog.mysql.db'
database.default.database = 'webdreamblog'
database.default.username = 'webdreamblog'
database.default.password = 'qD8OvOP2'
database.default.DBDriver = 'MySQLi'
database.default.DBPrefix = ''
database.default.port = 3306

#--------------------------------------------------------------------
# SESSION
#--------------------------------------------------------------------

session.driver = 'CodeIgniter\Session\Handlers\DatabaseHandler'
session.cookieName = 'ci_session'
session.expiration = 7200
session.savePath = 'ci_sessions'
session.matchIP = false
session.timeToUpdate = 300
session.regenerateDestroy = false

#--------------------------------------------------------------------
# LOGGING
#--------------------------------------------------------------------

logger.threshold = 4
```

### 4. Configuration des permissions

```bash
# Depuis le répertoire racine du projet
chmod -R 755 writable/
chown -R www-data:www-data writable/  # Ajuster selon votre utilisateur web
```

### 5. Installation des dépendances Composer

```bash
cd /chemin/vers/projet
composer install --no-dev --optimize-autoloader
```

### 6. Configuration de base_url() dans App.php

Le fichier `app/Config/App.php` détecte automatiquement l'URL de base. Pour le serveur de dev, vous pouvez aussi le forcer :

Vérifier que la ligne 19 dans `app/Config/App.php` utilise :
```php
public string $baseURL = 'https://rezo.les-coches-deau.fr/';
```

OU laisser CodeIgniter le détecter automatiquement (recommandé).

### 7. Configuration de la classe DbConnect

La classe `class_DbConnect.php` détecte automatiquement si on est en local ou non.
Pour le serveur de dev `rezo.les-coches-deau.fr`, elle doit être considérée comme NON-local (production) pour utiliser la BDD distante.

**Vérification :** Le domaine `rezo.les-coches-deau.fr` ne contient pas les indicateurs "local", "localhost", "127.0.0.1", donc il sera automatiquement configuré pour utiliser la BDD distante.

### 8. Configuration du domaine pour la détection d'environnement

Si nécessaire, ajuster la fonction `is_local_environment()` dans :
- `public/dev/rezo_flash_code/classes/class_DbConnect.php`

Pour ajouter des domaines de dev qui doivent être considérés comme "non-local" (utilisant la BDD distante).

### 9. Vérification du SSL/HTTPS

Si le serveur utilise HTTPS (recommandé), s'assurer que :
- Le certificat SSL est configuré
- Dans `.env`, `app.forceGlobalSecureRequests` peut être mis à `true` si nécessaire

### 10. Test du déploiement

1. **Vérifier l'URL de base :**
   ```
   https://rezo.les-coches-deau.fr/
   ```

2. **Tester la connexion à la base de données :**
   - Se connecter avec un compte existant
   - Vérifier que les données se chargent correctement

3. **Vérifier les permissions :**
   - Les sessions doivent fonctionner (writable/sessions/)
   - Les logs doivent fonctionner (writable/logs/)

### 11. Points importants

- ✅ **Base de données distante** : La connexion se fait automatiquement vers `webdreamblog.mysql.db` (serveur web-dream.fr)
- ✅ **Proxy CORS** : Les fichiers proxy (`get_directory_tree_json.php`, `backend.php`) dans `public/dev/rezo_galerie/` fonctionnent aussi sur le serveur de dev
- ✅ **Fichiers statiques** : Images, CSS, JS doivent être accessibles via `public/`
- ✅ **Debug** : La toolbar CodeIgniter est désactivée en production, mais peut être activée en development

### 12. Troubleshooting

**Problème : Erreur 500**
- Vérifier les logs : `writable/logs/log-YYYY-MM-DD.log`
- Vérifier les permissions sur `writable/`

**Problème : Erreur de connexion à la base de données**
- Vérifier que le serveur peut se connecter à `webdreamblog.mysql.db:3306`
- Vérifier les credentials dans `class_DbConnect.php`
- Vérifier que le firewall permet les connexions MySQL sortantes

**Problème : Routes non fonctionnelles**
- Vérifier la configuration Apache/Nginx
- Vérifier que `mod_rewrite` est activé (Apache)
- Vérifier le `.htaccess` dans `public/`

**Problème : Session non fonctionnelle**
- Vérifier les permissions sur `writable/sessions/`
- Vérifier la configuration de session dans `.env`
- Vérifier que la table `ci_sessions` existe dans la base de données

## Checklist de déploiement

- [ ] Fichiers uploadés sur le serveur
- [ ] Configuration du serveur web (Apache/Nginx)
- [ ] Fichier `.env` créé et configuré
- [ ] Permissions sur `writable/` configurées
- [ ] `composer install` exécuté
- [ ] Base de données accessible (connexion distante)
- [ ] URL de base configurée
- [ ] SSL/HTTPS configuré (si nécessaire)
- [ ] Test de connexion réussi
- [ ] Test de chargement des données réussi
- [ ] Logs accessibles et fonctionnels

## Support

En cas de problème, vérifier :
1. Les logs dans `writable/logs/`
2. Les logs du serveur web (Apache/Nginx)
3. La configuration dans `.env`
4. Les permissions des fichiers et répertoires
