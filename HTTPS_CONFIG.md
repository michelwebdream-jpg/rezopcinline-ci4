# 🔒 Configuration HTTPS - rezopcinline-ci4.local

## ✅ Configuration effectuée

### 1. Certificat SSL créé
- **Outil utilisé :** `mkcert` (outil de développement pour certificats SSL locaux)
- **Certificat :** `/Applications/MAMP/conf/apache/rezopcinline-ci4.local+1.pem`
- **Clé privée :** `/Applications/MAMP/conf/apache/rezopcinline-ci4.local+1-key.pem`
- **Validité :** Jusqu'au 4 avril 2028
- **Domaines couverts :** `rezopcinline-ci4.local` et `www.rezopcinline-ci4.local`

### 2. VirtualHost HTTPS ajouté
- **Fichier modifié :** `/Applications/MAMP/conf/apache/extra/httpd-vhosts.conf`
- **Port :** 443 (HTTPS)
- **Configuration SSL :** Activée avec les certificats créés par mkcert

### 3. VirtualHost HTTP mis à jour
- **Redirection :** Tentative de redirection HTTP → HTTPS (via RewriteEngine)
- **Note :** La redirection peut nécessiter que mod_rewrite soit activé

### 4. BaseURL mise à jour
- **Fichiers modifiés :**
  - `.env` : `app.baseURL = 'https://rezopcinline-ci4.local/'`
  - `app/Config/App.php` : `$baseURL = 'https://rezopcinline-ci4.local/'`

## 🌐 Accès au site

### URL HTTPS (recommandée)
```
https://rezopcinline-ci4.local/
```

### URL HTTP (redirigée vers HTTPS si configuré)
```
http://rezopcinline-ci4.local/
```

## ✅ Vérifications

Tous les tests passent :
- ✅ Certificat SSL présent et valide
- ✅ VirtualHost HTTPS configuré (port 443)
- ✅ VirtualHost HTTP configuré (port 80)
- ✅ BaseURL mise à jour pour HTTPS
- ✅ Connexion HTTPS fonctionnelle (testé avec curl)

## 🔍 Test de la configuration

Pour tester la configuration HTTPS, utilisez le script fourni :

```bash
cd /Applications/MAMP/htdocs/rezopcinline-ci4
./test_https.sh
```

## 📋 Détails techniques

### Certificat SSL
Le certificat a été créé avec `mkcert`, un outil qui :
- Génère des certificats SSL valides pour le développement local
- Installe une autorité de certification locale dans le système
- Permet aux navigateurs d'accepter automatiquement les certificats (pas d'avertissement)

### Configuration Apache

**VirtualHost HTTP (port 80) :**
```apache
<VirtualHost *:80>
    ServerName rezopcinline-ci4.local
    # Redirection vers HTTPS (si mod_rewrite activé)
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
    ...
</VirtualHost>
```

**VirtualHost HTTPS (port 443) :**
```apache
<VirtualHost *:443>
    ServerName rezopcinline-ci4.local
    SSLEngine on
    SSLCertificateFile "/Applications/MAMP/conf/apache/rezopcinline-ci4.local+1.pem"
    SSLCertificateKeyFile "/Applications/MAMP/conf/apache/rezopcinline-ci4.local+1-key.pem"
    ...
</VirtualHost>
```

## 🔄 Redémarrer MAMP

Après la configuration, vous devez redémarrer Apache dans MAMP :

1. Ouvrez MAMP
2. Cliquez sur "Stop Servers"
3. Cliquez sur "Start Servers"

## 🐛 Dépannage

### Le site ne fonctionne pas en HTTPS

1. **Vérifiez que MAMP est démarré**

2. **Vérifiez la syntaxe Apache :**
   ```bash
   /Applications/MAMP/Library/bin/httpd -t
   ```

3. **Vérifiez que le VirtualHost est chargé :**
   ```bash
   /Applications/MAMP/Library/bin/httpd -S | grep rezopcinline
   ```

4. **Vérifiez les logs d'erreur :**
   ```bash
   tail -f /Applications/MAMP/logs/apache_error.log
   ```

### Erreur de certificat dans le navigateur

Si vous voyez un avertissement de certificat :
- Le certificat est créé par mkcert et devrait être automatiquement accepté
- Si ce n'est pas le cas, vérifiez que mkcert est bien installé : `mkcert -install`

### La redirection HTTP → HTTPS ne fonctionne pas

Si la redirection ne fonctionne pas :
1. Vérifiez que `mod_rewrite` est activé dans Apache
2. Vérifiez les logs Apache pour des erreurs
3. Vous pouvez toujours utiliser directement `https://rezopcinline-ci4.local/`

## 📚 Documentation

- [mkcert - GitHub](https://github.com/FiloSottile/mkcert)
- [Apache SSL/TLS Configuration](https://httpd.apache.org/docs/2.4/ssl/)
- [CodeIgniter 4 User Guide - Configuration](https://codeigniter.com/user_guide/)

## 🔐 Sécurité

⚠️ **Note importante :** Ces certificats sont uniquement pour le développement local. Ils ne doivent **JAMAIS** être utilisés en production.

Pour la production, utilisez des certificats SSL valides (Let's Encrypt, etc.).

