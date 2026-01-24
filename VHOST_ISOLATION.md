# Isolation des VirtualHosts - Éviter les conflits entre projets

## ✅ Configuration effectuée

### 1. VirtualHost par défaut ajouté
Un VirtualHost par défaut pour `localhost` a été ajouté **en premier** dans la configuration. Cela garantit que :
- Les requêtes vers `http://localhost/` ou `http://127.0.0.1/` pointent vers `/Applications/MAMP/htdocs` (comportement par défaut de MAMP)
- Les autres projets dans `htdocs/` continuent de fonctionner normalement
- Seules les requêtes spécifiques à `rezopcinline-ci4.local` sont dirigées vers ce projet

### 2. Ordre des VirtualHosts
L'ordre est important dans Apache : le premier VirtualHost devient le serveur par défaut.

**Ordre actuel :**
1. **localhost** (par défaut) - Port 80 et 443
2. **rezopcinline-ci4.local** - Port 80 et 443

## 📋 Comment ça fonctionne

### Requêtes HTTP (port 80)
- `http://localhost/` → `/Applications/MAMP/htdocs/` (tous vos autres projets)
- `http://localhost/rezopcinline/` → `/Applications/MAMP/htdocs/rezopcinline/`
- `http://localhost/bellonie/` → `/Applications/MAMP/htdocs/bellonie/`
- `http://rezopcinline-ci4.local/` → `/Applications/MAMP/htdocs/rezopcinline-ci4/public/` (redirigé vers HTTPS)

### Requêtes HTTPS (port 443)
- `https://localhost/` → `/Applications/MAMP/htdocs/` (tous vos autres projets)
- `https://rezopcinline-ci4.local/` → `/Applications/MAMP/htdocs/rezopcinline-ci4/public/`

## ➕ Ajouter un nouveau VirtualHost pour un autre projet

Si vous voulez ajouter un VirtualHost pour un autre projet (par exemple `bellonie.local`), ajoutez-le **après** le VirtualHost par défaut mais **avant** ou **après** celui de rezopcinline-ci4 :

```apache
# VirtualHost par défaut (déjà présent)
<VirtualHost *:80>
    ServerName localhost
    ...
</VirtualHost>

# Nouveau projet
<VirtualHost *:80>
    ServerName bellonie.local
    DocumentRoot "/Applications/MAMP/htdocs/bellonie/public"
    ...
</VirtualHost>

# rezopcinline-ci4
<VirtualHost *:80>
    ServerName rezopcinline-ci4.local
    ...
</VirtualHost>
```

**N'oubliez pas d'ajouter l'entrée dans `/etc/hosts` :**
```bash
echo "127.0.0.1 bellonie.local" | sudo tee -a /etc/hosts
```

## 🔍 Vérification

Pour vérifier que la configuration est correcte :

```bash
# Vérifier la syntaxe
/Applications/MAMP/Library/bin/httpd -t

# Voir l'ordre des VirtualHosts
/Applications/MAMP/Library/bin/httpd -S
```

Vous devriez voir :
```
*:80                   localhost (/Applications/MAMP/conf/apache/extra/httpd-vhosts.conf:XX)
                       is a NameVirtualHost
         default server localhost
         port 80 namevhost rezopcinline-ci4.local
```

## ⚠️ Points importants

1. **Ordre des VirtualHosts** : Le premier VirtualHost devient le serveur par défaut
2. **ServerName spécifique** : Chaque VirtualHost doit avoir un ServerName unique
3. **Pas de conflit** : Les VirtualHosts avec ServerName spécifiques ne capturent que les requêtes correspondantes
4. **localhost reste accessible** : Le VirtualHost par défaut garantit que `localhost` fonctionne pour tous vos autres projets

## 🐛 Dépannage

### Un autre projet ne fonctionne plus

Si un autre projet ne fonctionne plus après cette configuration :

1. **Vérifiez que vous accédez via localhost :**
   - ✅ `http://localhost/nom-du-projet/`
   - ❌ `http://nom-du-projet.local/` (nécessite un VirtualHost spécifique)

2. **Vérifiez les logs :**
   ```bash
   tail -f /Applications/MAMP/logs/apache_error.log
   ```

3. **Testez avec curl :**
   ```bash
   curl -H "Host: localhost" http://localhost/nom-du-projet/
   ```

### Le VirtualHost par défaut ne fonctionne pas

Si `localhost` ne fonctionne plus :

1. Vérifiez que le VirtualHost localhost est bien en premier dans `httpd-vhosts.conf`
2. Redémarrez MAMP
3. Vérifiez la syntaxe Apache : `/Applications/MAMP/Library/bin/httpd -t`

## 📚 Bonnes pratiques

1. **Toujours avoir un VirtualHost par défaut** pour localhost
2. **Utiliser des ServerName spécifiques** pour chaque projet avec domaine personnalisé
3. **Documenter** chaque nouveau VirtualHost ajouté
4. **Tester** après chaque modification de configuration
