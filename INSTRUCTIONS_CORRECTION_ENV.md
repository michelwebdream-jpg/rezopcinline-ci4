# 🔧 Instructions de correction du fichier .env

## Problème actuel

L'erreur indique que la ligne `VERSION_DU_SOFT=Version 5.0` n'est pas entre guillemets.

## Solution immédiate

### Option 1 : Correction manuelle (RECOMMANDÉ)

1. **Ouvrez le fichier `.env`** dans votre éditeur (vous l'avez déjà ouvert)

2. **Cherchez la ligne** qui contient `VERSION_DU_SOFT` (probablement vers la ligne 41 ou plus bas)

3. **Remplacez** cette ligne :
   ```
   VERSION_DU_SOFT=Version 5.0
   ```
   
   Par :
   ```
   VERSION_DU_SOFT="Version 5.0"
   ```

4. **IMPORTANT** : Assurez-vous que :
   - Les guillemets sont des guillemets doubles (`"`) et non des apostrophes (`'`)
   - Il n'y a PAS d'espaces autour du signe `=`
   - Il n'y a PAS d'espaces avant ou après les guillemets

5. **Sauvegardez** le fichier (Cmd+S ou Ctrl+S)

6. **Rechargez** la page dans votre navigateur

### Option 2 : Utiliser le script de correction

Si vous avez accès au terminal, exécutez :

```bash
cd /Applications/MAMP/htdocs/rezopcinline-ci4
php fix_env_force.php
```

Ce script corrige automatiquement le fichier.

## Vérification

Après la correction, la ligne doit ressembler EXACTEMENT à ceci :

```
VERSION_DU_SOFT="Version 5.0"
```

## Si le problème persiste

1. **Vérifiez qu'il n'y a qu'une seule ligne** `VERSION_DU_SOFT` dans le fichier
2. **Vérifiez qu'il n'y a pas d'espaces invisibles** (utilisez "Afficher les caractères invisibles" dans votre éditeur)
3. **Vérifiez que le fichier est bien sauvegardé** (le nom du fichier ne doit pas avoir d'astérisque `*` dans l'onglet)
4. **Videz le cache** de votre navigateur (Cmd+Shift+R ou Ctrl+Shift+R)

## Format correct du fichier .env

Toutes les valeurs avec espaces doivent être entre guillemets :

✅ **Correct :**
```
VERSION_DU_SOFT="Version 5.0"
app.baseURL = 'https://localhost/rezopcinline-ci4/public/'
```

❌ **Incorrect :**
```
VERSION_DU_SOFT=Version 5.0
VERSION_DU_SOFT = "Version 5.0"
VERSION_DU_SOFT='Version 5.0'
```

