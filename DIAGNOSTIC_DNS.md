# 🔍 Diagnostic DNS - rezopcinline-ci4.local

## Problème

La page ne se charge pas quand vous tapez `http://rezopcinline-ci4.local/` dans votre navigateur. La barre de progression reste bloquée.

## Cause

Le problème vient du **format incorrect** de l'entrée dans `/etc/hosts`. L'entrée est collée à la ligne précédente, ce qui empêche macOS de résoudre le nom de domaine.

## Diagnostic rapide

### 1. Vérifier le format de /etc/hosts

```bash
tail -3 /etc/hosts
```

**Si vous voyez :**
```
## Local - End ##127.0.0.1 rezopcinline-ci4.local www.rezopcinline-ci4.local
```

➡️ **C'est le problème !** L'entrée est collée.

**Ce qui devrait être :**
```
## Local - End ##
127.0.0.1 rezopcinline-ci4.local www.rezopcinline-ci4.local
```

### 2. Tester la résolution DNS

```bash
ping -c 1 rezopcinline-ci4.local
```

**Si vous voyez :**
```
ping: cannot resolve rezopcinline-ci4.local: Unknown host
```

➡️ **Confirme le problème de résolution DNS**

### 3. Tester avec curl

```bash
curl http://rezopcinline-ci4.local/
```

**Si vous voyez :**
```
curl: (6) Could not resolve host: rezopcinline-ci4.local
```

➡️ **Confirme le problème de résolution DNS**

**Mais si vous testez avec l'IP directement :**
```bash
curl -H "Host: rezopcinline-ci4.local" http://127.0.0.1/
```

➡️ **Devrait fonctionner !** Cela confirme que le VirtualHost Apache fonctionne correctement.

## Solution

Voir le fichier `FIX_HOSTS_FORMAT.md` pour les instructions détaillées.

**En résumé :**

1. Corrigez le format dans `/etc/hosts` (ajoutez un saut de ligne)
2. Videz le cache DNS : `sudo killall -HUP mDNSResponder`
3. Testez à nouveau

## Après correction

Une fois corrigé, vous devriez pouvoir :

✅ Résoudre le nom : `ping rezopcinline-ci4.local`  
✅ Accéder via curl : `curl http://rezopcinline-ci4.local/`  
✅ Accéder dans le navigateur : `http://rezopcinline-ci4.local/`

