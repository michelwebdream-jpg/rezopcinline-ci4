# 🔧 Dépannage - Page vide

## Tests à effectuer

### 1. Test PHP de base
Accédez à : `https://localhost/rezopcinline-ci4/public/simple_test.php`
- Si cela fonctionne → PHP fonctionne
- Si page vide → Problème de configuration PHP/MAMP

### 2. Test CodeIgniter 4
Accédez à : `https://localhost/rezopcinline-ci4/public/test_ci4.php`
- Affichera les erreurs détaillées
- Montrera où le problème se situe

### 3. Test de diagnostic
Accédez à : `https://localhost/rezopcinline-ci4/public/debug.php`
- Diagnostic complet du système
- Vérification des chemins et permissions

## Causes possibles

### 1. BaseURL incorrecte
**Symptôme :** Page blanche, pas d'erreur visible

**Solution :**
- Vérifier que `app/Config/App.php` contient : `$baseURL = 'https://localhost/rezopcinline-ci4/public/';`
- Vérifier que `.env` contient : `app.baseURL = 'https://localhost/rezopcinline-ci4/public/'`

### 2. Erreur PHP non affichée
**Symptôme :** Page blanche

**Solution :**
- Vérifier que `CI_ENVIRONMENT = development` dans `.env`
- Vérifier les logs : `writable/logs/log-*.php`
- Activer l'affichage des erreurs dans `app/Config/Boot/development.php`

### 3. Problème de permissions
**Symptôme :** Erreurs dans les logs

**Solution :**
```bash
chmod -R 775 writable/
```

### 4. Vue non trouvée
**Symptôme :** Erreur 404 ou page vide

**Solution :**
- Vérifier que les vues existent dans `app/Views/`
- Vérifier les chemins dans les controllers

### 5. Problème avec le helper de compatibilité
**Symptôme :** Erreur lors du chargement

**Solution :**
- Vérifier que `app/Config/Autoload.php` charge le helper
- Vérifier que `app/Helpers/form_helper_compat.php` existe

## Commandes de diagnostic

```bash
# Vérifier les logs
tail -f writable/logs/log-*.php

# Vérifier les permissions
ls -la writable/

# Tester PHP
php -v

# Tester la syntaxe
php -l app/Controllers/Signup.php
```

## Prochaines étapes

1. Accédez à `https://localhost/rezopcinline-ci4/public/test_ci4.php`
2. Notez les erreurs affichées
3. Corrigez selon les erreurs
4. Réessayez `https://localhost/rezopcinline-ci4/public/`

