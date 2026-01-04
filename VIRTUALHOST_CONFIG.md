# 🚀 Configuration du VirtualHost - rezopcinline-ci4.local

## ✅ Modifications effectuées

### 1. Activation des VirtualHosts dans Apache
- **Fichier modifié :** `/Applications/MAMP/conf/apache/httpd.conf`
- **Ligne 668 :** Décommenté `Include /Applications/MAMP/conf/apache/extra/httpd-vhosts.conf`
- **Explication :** Cela permet à Apache de charger le fichier de configuration des VirtualHosts

### 2. Création du VirtualHost
- **Fichier modifié :** `/Applications/MAMP/conf/apache/extra/httpd-vhosts.conf`
- **VirtualHost ajouté :**
  ```apache
  <VirtualHost *:80>
      ServerAdmin webmaster@rezopcinline-ci4.local
      DocumentRoot "/Applications/MAMP/htdocs/rezopcinline-ci4/public"
      ServerName rezopcinline-ci4.local
      ServerAlias www.rezopcinline-ci4.local
      
      <Directory "/Applications/MAMP/htdocs/rezopcinline-ci4/public">
          Options Indexes FollowSymLinks
          AllowOverride All
          Require all granted
      </Directory>
      
      ErrorLog "logs/rezopcinline-ci4-error_log"
      CustomLog "logs/rezopcinline-ci4-access_log" common
  </VirtualHost>
  ```
- **Explication :** Ce VirtualHost configure Apache pour servir le site depuis le dossier `public/` quand on accède à `rezopcinline-ci4.local`

### 3. Mise à jour de la baseURL
- **Fichiers modifiés :**
  - `.env` : `app.baseURL = 'http://rezopcinline-ci4.local/'`
  - `app/Config/App.php` : `$baseURL = 'http://rezopcinline-ci4.local/'`
- **Explication :** La baseURL a été changée pour utiliser le nouveau nom de domaine (sans `/public/`)

## ⚠️ Action requise : Ajouter l'entrée dans /etc/hosts

Pour que le nom de domaine `rezopcinline-ci4.local` fonctionne sur votre machine, vous devez l'ajouter au fichier `/etc/hosts`.

### Option 1 : Utiliser le script fourni (recommandé)
```bash
cd /Applications/MAMP/htdocs/rezopcinline-ci4
sudo ./add_to_hosts.sh
```

### Option 2 : Ajout manuel
1. Ouvrez le fichier `/etc/hosts` avec un éditeur de texte avec droits administrateur :
   ```bash
   sudo nano /etc/hosts
   # ou
   sudo vim /etc/hosts
   ```

2. Ajoutez cette ligne à la fin du fichier :
   ```
   127.0.0.1 rezopcinline-ci4.local www.rezopcinline-ci4.local
   ```

3. Sauvegardez le fichier

### Option 3 : Commande directe
```bash
echo "127.0.0.1 rezopcinline-ci4.local www.rezopcinline-ci4.local" | sudo tee -a /etc/hosts
```

## 🔄 Redémarrer MAMP

Après avoir ajouté l'entrée dans `/etc/hosts`, vous devez redémarrer Apache dans MAMP :

1. Ouvrez MAMP
2. Cliquez sur "Stop Servers"
3. Cliquez sur "Start Servers"

## ✅ Test

Une fois la configuration terminée, vous pouvez accéder à votre site avec :

- **Nouvelle URL :** `http://rezopcinline-ci4.local/`
- **Ancienne URL :** `https://localhost/rezopcinline-ci4/public/` (fonctionnera toujours, mais moins propre)

## 📋 Récapitulatif de l'architecture

### CodeIgniter 3 (ancien)
```
/Applications/MAMP/htdocs/rezopcinline/
├── index.php (à la racine)
└── URL : http://localhost/rezopcinline/
```

### CodeIgniter 4 (nouveau)
```
/Applications/MAMP/htdocs/rezopcinline-ci4/
├── public/
│   └── index.php (dans public/)
├── app/ (protégé - pas accessible via web)
├── writable/ (protégé - pas accessible via web)
└── URL : http://rezopcinline-ci4.local/ (grâce au VirtualHost)
```

## 🔍 Vérification de la configuration

Pour vérifier que votre VirtualHost est correctement configuré :

```bash
# Vérifier la syntaxe Apache
/Applications/MAMP/Library/bin/httpd -t

# Vérifier que le VirtualHost est chargé
/Applications/MAMP/Library/bin/httpd -S
```

## 🐛 Dépannage

### Le site ne fonctionne pas après la configuration

1. **Vérifiez que l'entrée est bien dans /etc/hosts :**
   ```bash
   grep rezopcinline-ci4 /etc/hosts
   ```

2. **Vérifiez que MAMP Apache est redémarré**

3. **Vérifiez les logs d'erreur :**
   ```bash
   tail -f /Applications/MAMP/logs/apache_error.log
   ```

4. **Testez avec curl :**
   ```bash
   curl -H "Host: rezopcinline-ci4.local" http://localhost/
   ```

### Erreur 403 Forbidden

Vérifiez les permissions sur le dossier `public/` :
```bash
chmod -R 755 /Applications/MAMP/htdocs/rezopcinline-ci4/public
```

### Le VirtualHost ne fonctionne pas

1. Vérifiez que l'include est bien activé dans `httpd.conf`
2. Vérifiez la syntaxe du VirtualHost
3. Redémarrez Apache dans MAMP

## 📚 Documentation

- [Documentation Apache VirtualHost](https://httpd.apache.org/docs/2.4/vhosts/)
- [CodeIgniter 4 User Guide - Configuration](https://codeigniter.com/user_guide/)

