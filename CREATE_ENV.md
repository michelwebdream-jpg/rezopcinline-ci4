# 🔧 Création du fichier .env

## Problème

Le fichier `.env` n'existe pas ou n'est pas accessible. CodeIgniter 4 a besoin de ce fichier pour fonctionner.

## Solution

Créez un fichier `.env` à la racine du projet avec le contenu suivant :

```env
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = development

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'https://localhost/rezopcinline-ci4/public/'
app.forceGlobalSecureRequests = false
app.sessionDriver = 'CodeIgniter\Session\Handlers\FileHandler'
app.sessionCookieName = 'ci_session'
app.sessionExpiration = 7200
app.sessionSavePath = WRITEPATH . 'session'
app.sessionMatchIP = false
app.sessionTimeToUpdate = 300
app.sessionRegenerateDestroy = false

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
database.default.hostname = 127.0.0.1
database.default.database = webdreamblog
database.default.username = root
database.default.password = root
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306

#--------------------------------------------------------------------
# CUSTOM
#--------------------------------------------------------------------
VERSION_DU_SOFT="Version 5.0"
```

## Instructions

1. Créez le fichier `.env` à la racine du projet
2. Copiez le contenu ci-dessus
3. Ajustez les valeurs selon votre configuration :
   - `app.baseURL` : votre URL locale
   - `database.*` : vos identifiants de base de données
4. **IMPORTANT :** Les valeurs avec espaces (comme `VERSION_DU_SOFT`) doivent être entre guillemets

## Vérification

Après avoir créé le `.env`, testez :
- `https://localhost/rezopcinline-ci4/public/test_ci4.php`
- `https://localhost/rezopcinline-ci4/public/`

