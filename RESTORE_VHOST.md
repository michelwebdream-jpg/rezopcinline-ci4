# Restauration du VirtualHost - rezopcinline-ci4.local

## ✅ Modifications effectuées

### 1. VirtualHosts décommentés
- **Fichier modifié :** `/Applications/MAMP/conf/apache/extra/httpd-vhosts.conf`
- **VirtualHost HTTP (port 80)** : Décommenté et activé
- **VirtualHost HTTPS (port 443)** : Décommenté et activé

### 2. Configuration vérifiée
- ✅ Certificats SSL présents : `/Applications/MAMP/conf/apache/rezopcinline-ci4.local+1.pem`
- ✅ Entrée dans `/etc/hosts` : `127.0.0.1 rezopcinline-ci4.local`
- ✅ Syntaxe Apache : OK
- ✅ Include des vhosts activé dans `httpd.conf`

## 🔄 Redémarrer MAMP

**IMPORTANT :** Vous devez redémarrer Apache dans MAMP pour que les changements prennent effet :

1. Ouvrez MAMP
2. Cliquez sur **"Stop Servers"**
3. Cliquez sur **"Start Servers"**

## ✅ Test

Une fois MAMP redémarré, vous pouvez accéder à :

- **HTTPS :** `https://rezopcinline-ci4.local/`
- **HTTP :** `http://rezopcinline-ci4.local/` (redirigé vers HTTPS)

## 🔍 Vérification

Si l'URL ne fonctionne toujours pas après le redémarrage :

1. **Vérifiez que MAMP utilise les ports 80 et 443 :**
   - Dans MAMP, allez dans **Preferences > Ports**
   - Apache Port : **80**
   - Apache SSL Port : **443**

2. **Vérifiez les logs d'erreur :**
   ```bash
   tail -f /Applications/MAMP/logs/apache_error.log
   ```

3. **Testez avec curl :**
   ```bash
   curl -k https://rezopcinline-ci4.local/
   ```

4. **Vérifiez que mod_ssl est activé :**
   ```bash
   grep "LoadModule.*ssl" /Applications/MAMP/conf/apache/httpd.conf | grep -v "^#"
   ```

## 📋 Configuration actuelle

### VirtualHost HTTP (port 80)
- DocumentRoot : `/Applications/MAMP/htdocs/rezopcinline-ci4/public`
- Redirection automatique vers HTTPS

### VirtualHost HTTPS (port 443)
- DocumentRoot : `/Applications/MAMP/htdocs/rezopcinline-ci4/public`
- SSL activé avec certificats mkcert
- Certificat : `rezopcinline-ci4.local+1.pem`
- Clé privée : `rezopcinline-ci4.local+1-key.pem`

## 🐛 Dépannage

### Erreur "SSL certificate problem"
Si vous voyez une erreur de certificat dans le navigateur :
- Les certificats sont créés par `mkcert` et devraient être automatiquement acceptés
- Si ce n'est pas le cas, vérifiez que mkcert est installé : `mkcert -install`

### Erreur 403 Forbidden
Vérifiez les permissions :
```bash
chmod -R 755 /Applications/MAMP/htdocs/rezopcinline-ci4/public
```

### Le site ne se charge pas
1. Vérifiez que MAMP est démarré
2. Vérifiez que les ports 80 et 443 ne sont pas utilisés par un autre processus
3. Vérifiez les logs Apache pour des erreurs
