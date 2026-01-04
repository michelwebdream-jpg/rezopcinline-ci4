# 🔧 Correction du format de /etc/hosts

## Problème identifié

L'entrée pour `rezopcinline-ci4.local` dans `/etc/hosts` est collée à la ligne précédente, ce qui empêche la résolution DNS.

**Format actuel (incorrect) :**
```
## Local - End ##127.0.0.1 rezopcinline-ci4.local www.rezopcinline-ci4.local
```

**Format correct :**
```
## Local - End ##
127.0.0.1 rezopcinline-ci4.local www.rezopcinline-ci4.local
```

## Solution

### Option 1 : Commande automatique (recommandée)

Ouvrez un terminal et exécutez :

```bash
sudo sed -i '' 's/## Local - End ##127\.0\.0\.1 rezopcinline-ci4\.local/## Local - End ##\
127.0.0.1 rezopcinline-ci4.local/' /etc/hosts
```

### Option 2 : Édition manuelle

1. Ouvrez le fichier `/etc/hosts` avec un éditeur de texte avec droits administrateur :
   ```bash
   sudo nano /etc/hosts
   ```
   ou
   ```bash
   sudo vim /etc/hosts
   ```

2. Trouvez la ligne qui contient :
   ```
   ## Local - End ##127.0.0.1 rezopcinline-ci4.local www.rezopcinline-ci4.local
   ```

3. Remplacez-la par deux lignes :
   ```
   ## Local - End ##
   127.0.0.1 rezopcinline-ci4.local www.rezopcinline-ci4.local
   ```

4. Sauvegardez le fichier (dans nano : Ctrl+O, puis Ctrl+X)

### Option 3 : Supprimer et réajouter

1. Supprimez la ligne incorrecte :
   ```bash
   sudo sed -i '' '/rezopcinline-ci4\.local/d' /etc/hosts
   ```

2. Ajoutez-la correctement :
   ```bash
   echo "127.0.0.1 rezopcinline-ci4.local www.rezopcinline-ci4.local" | sudo tee -a /etc/hosts
   ```

## Après la correction

1. **Videz le cache DNS de macOS :**
   ```bash
   sudo killall -HUP mDNSResponder
   ```

2. **Testez la résolution :**
   ```bash
   ping -c 1 rezopcinline-ci4.local
   ```
   
   Vous devriez voir :
   ```
   PING rezopcinline-ci4.local (127.0.0.1): 56 data bytes
   64 bytes from 127.0.0.1: icmp_seq=0 ttl=64 time=0.043 ms
   ```

3. **Testez avec curl :**
   ```bash
   curl -I http://rezopcinline-ci4.local/
   ```
   
   Vous devriez voir une réponse HTTP (200, 301, 302, etc.)

4. **Testez dans votre navigateur :**
   Ouvrez : `http://rezopcinline-ci4.local/`

## Vérification

Pour vérifier que la correction a fonctionné :

```bash
cat /etc/hosts | grep -A 1 "Local - End"
```

Vous devriez voir :
```
## Local - End ##
127.0.0.1 rezopcinline-ci4.local www.rezopcinline-ci4.local
```

## Si le problème persiste

1. Vérifiez que le fichier `/etc/hosts` est bien modifié :
   ```bash
   cat /etc/hosts | tail -5
   ```

2. Videz à nouveau le cache DNS :
   ```bash
   sudo killall -HUP mDNSResponder
   sudo dscacheutil -flushcache
   ```

3. Redémarrez votre navigateur

4. Si cela ne fonctionne toujours pas, essayez d'utiliser directement l'IP :
   - Ouvrez : `http://127.0.0.1/` dans votre navigateur
   - Ajoutez manuellement l'en-tête Host (nécessite une extension de navigateur)

