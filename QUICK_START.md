# 🚀 Guide de démarrage rapide - CodeIgniter 4

## Accès à l'application

### URL locale
```
https://localhost/rezopcinline-ci4/public/
```

### Configuration MAMP
Si vous utilisez MAMP, vous devrez peut-être :
1. Créer un VirtualHost pointant vers `rezopcinline-ci4/public/`
2. Ou accéder via : `https://localhost:8888/rezopcinline-ci4/public/` (selon votre port MAMP)

## Vérifications rapides

### 1. Vérifier PHP
```bash
php -v
# Doit afficher PHP 8.1+ (8.3 recommandé)
```

### 2. Vérifier les permissions
```bash
chmod -R 775 writable/
```

### 3. Vérifier la base de données
- MAMP doit être démarré
- MySQL doit être actif
- Les credentials dans `.env` doivent correspondre

## Tests de base

### Test 1 : Page d'accueil
Accéder à : `https://localhost/rezopcinline-ci4/public/`
- Doit afficher la page d'inscription

### Test 2 : Connexion
Accéder à : `https://localhost/rezopcinline-ci4/public/signup/login`
- Doit afficher le formulaire de connexion

### Test 3 : Vérifier les logs
```bash
tail -f writable/logs/log-*.php
```

## En cas d'erreur

### Erreur 404
1. Vérifier que `mod_rewrite` est activé dans Apache
2. Vérifier le `.htaccess` dans `public/`
3. Vérifier la `baseURL` dans `.env`

### Erreur de base de données
1. Vérifier que MAMP est démarré
2. Vérifier les credentials dans `.env`
3. Vérifier les logs : `writable/logs/`

### Erreur de session
1. Vérifier les permissions sur `writable/session/`
2. Vérifier la configuration dans `.env`

## Commandes utiles

### Vider le cache
```bash
rm -rf writable/cache/*
```

### Voir les logs
```bash
tail -f writable/logs/log-*.php
```

### Vérifier les routes
Les routes sont définies dans : `app/Config/Routes.php`

## Support

- Documentation CI4 : https://codeigniter.com/user_guide/
- Guide de migration : `MIGRATION_COMPLETE.md`
- État de la migration : `MIGRATION_STATUS.md`

