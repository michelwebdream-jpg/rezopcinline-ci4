# 🔧 Correction de la page blanche

## Problèmes identifiés et corrigés

### 1. ✅ BaseURL corrigée
- **Avant :** `http://localhost:8080/`
- **Après :** `https://localhost/rezopcinline-ci4/public/`
- **Fichier :** `app/Config/App.php`

### 2. ✅ Fichiers de test créés
- `public/simple_test.php` - Test PHP de base
- `public/test_ci4.php` - Test CodeIgniter 4 avec erreurs
- `public/debug.php` - Diagnostic complet

## Étapes de diagnostic

### Étape 1 : Tester PHP
Accédez à : `https://localhost/rezopcinline-ci4/public/simple_test.php`

**Si cela fonctionne :** PHP fonctionne, le problème vient de CI4  
**Si page vide :** Problème de configuration MAMP/PHP

### Étape 2 : Tester CI4 avec erreurs
Accédez à : `https://localhost/rezopcinline-ci4/public/test_ci4.php`

**Cela affichera :**
- Les erreurs détaillées
- Les chemins chargés
- Les fichiers manquants
- La trace complète

### Étape 3 : Diagnostic complet
Accédez à : `https://localhost/rezopcinline-ci4/public/debug.php`

**Cela vérifiera :**
- Version PHP
- Chemins (APPPATH, FCPATH, etc.)
- Configuration .env
- Permissions
- Chargement de CI4

## Causes possibles restantes

### 1. .env non lu
**Symptôme :** CI_ENVIRONMENT non défini

**Solution :**
```bash
cd /Applications/MAMP/htdocs/rezopcinline-ci4
# Vérifier que .env existe
ls -la .env
# Vérifier le contenu
cat .env | head -5
```

### 2. Erreur dans le controller
**Symptôme :** Erreur lors du chargement de Signup

**Solution :**
- Vérifier les logs : `writable/logs/log-*.php`
- Vérifier la syntaxe : `php -l app/Controllers/Signup.php`

### 3. Vue non trouvée
**Symptôme :** Erreur "View not found"

**Solution :**
- Vérifier que `app/Views/signup.php` existe
- Vérifier les chemins dans `app/Config/Paths.php`

### 4. Helper non chargé
**Symptôme :** Erreur "Call to undefined function form_open()"

**Solution :**
- Vérifier que `app/Config/Autoload.php` charge le helper
- Vérifier que `app/Helpers/form_helper_compat.php` existe

## Commandes utiles

```bash
# Voir les logs en temps réel
tail -f writable/logs/log-*.php

# Vérifier les permissions
chmod -R 775 writable/

# Tester la syntaxe PHP
php -l app/Controllers/Signup.php
php -l app/Models/SignupModel.php

# Vérifier le .env
cat .env | grep CI_ENVIRONMENT
```

## Prochaines étapes

1. **Accédez à `test_ci4.php`** pour voir les erreurs exactes
2. **Notez les erreurs** affichées
3. **Corrigez** selon les erreurs
4. **Réessayez** l'URL principale

## Si toujours page blanche

1. Vérifiez les logs Apache/MAMP
2. Vérifiez la configuration PHP (php.ini)
3. Vérifiez que `mod_rewrite` est activé
4. Vérifiez les permissions sur tous les dossiers

