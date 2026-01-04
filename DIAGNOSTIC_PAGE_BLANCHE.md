# 🔍 Diagnostic - Page blanche

## ✅ Corrections déjà appliquées

1. **BaseURL corrigée** dans `app/Config/App.php`
   - Changé de `http://localhost:8080/` à `https://localhost/rezopcinline-ci4/public/`

2. **Helper de compatibilité** chargé dans `BaseController`
   - Les fonctions `form_open()`, `form_error()`, etc. seront disponibles

3. **Fichiers de test créés** pour le diagnostic

## 🔬 Tests à effectuer (dans l'ordre)

### Test 1 : PHP fonctionne-t-il ?
**URL :** `https://localhost/rezopcinline-ci4/public/simple_test.php`

**Résultat attendu :** Affichage de "PHP fonctionne !" et phpinfo()

**Si page vide :**
- Problème de configuration MAMP
- Vérifier que MAMP est démarré
- Vérifier le port (8888 ou autre)

### Test 2 : Test minimal
**URL :** `https://localhost/rezopcinline-ci4/public/minimal_test.php`

**Résultat attendu :** Affichage des informations de diagnostic

**Si erreur :**
- Notez l'erreur exacte
- Vérifiez les chemins

### Test 3 : Test CI4 complet
**URL :** `https://localhost/rezopcinline-ci4/public/test_ci4.php`

**Résultat attendu :** Affichage détaillé du chargement de CI4

**Si erreur :**
- Notez l'erreur complète
- Vérifiez les fichiers manquants

### Test 4 : Diagnostic complet
**URL :** `https://localhost/rezopcinline-ci4/public/debug.php`

**Résultat attendu :** Diagnostic complet du système

## 🔧 Solutions selon les erreurs

### Erreur : "Paths.php not found"
**Solution :**
```bash
cd /Applications/MAMP/htdocs/rezopcinline-ci4
ls -la app/Config/Paths.php
# Si n'existe pas, réinstaller CI4
```

### Erreur : "Class 'Config\Paths' not found"
**Solution :**
- Vérifier que `app/Config/Paths.php` existe
- Vérifier le namespace : `namespace Config;`

### Erreur : "View 'signup' not found"
**Solution :**
```bash
ls -la app/Views/signup.php
# Vérifier que le fichier existe
```

### Erreur : "Call to undefined function form_open()"
**Solution :**
- Vérifier que `app/Helpers/form_helper_compat.php` existe
- Vérifier que `BaseController` charge le helper

### Erreur : "BaseController not found"
**Solution :**
- Vérifier que `app/Controllers/BaseController.php` existe
- Vérifier le namespace : `namespace App\Controllers;`

## 📋 Checklist de vérification

- [ ] MAMP est démarré
- [ ] PHP 8.1+ est sélectionné dans MAMP
- [ ] Le port est correct (8888 ou autre)
- [ ] `mod_rewrite` est activé dans Apache
- [ ] Les permissions sur `writable/` sont correctes (775)
- [ ] Le fichier `.env` existe
- [ ] `CI_ENVIRONMENT = development` dans `.env`
- [ ] `app.baseURL` est correct dans `.env`

## 🚨 Si toujours page blanche

1. **Vérifier les logs Apache/MAMP**
   - Cherchez dans les logs d'erreur Apache
   - Vérifiez les logs PHP

2. **Vérifier la console du navigateur**
   - Ouvrez les outils de développement (F12)
   - Regardez l'onglet Console
   - Regardez l'onglet Network

3. **Vérifier les logs CI4**
   ```bash
   tail -f writable/logs/log-*.php
   ```

4. **Tester avec curl**
   ```bash
   curl -v https://localhost/rezopcinline-ci4/public/
   ```

## 📞 Informations à fournir

Si le problème persiste, fournissez :
1. Le résultat de `simple_test.php`
2. Le résultat de `test_ci4.php`
3. Le contenu des logs : `writable/logs/log-*.php`
4. La version PHP utilisée par MAMP
5. Le port MAMP utilisé

