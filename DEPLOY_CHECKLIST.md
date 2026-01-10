# Checklist de déploiement - rezo.les-coches-deau.fr

## Préparation locale

- [ ] Vérifier que tous les fichiers sont à jour
- [ ] Tester l'application en local une dernière fois
- [ ] Vérifier que les modifications dans `class_DbConnect.php` sont correctes

## Upload sur le serveur

- [ ] Uploader tous les fichiers via FTP/SFTP
- [ ] Vérifier la structure des répertoires
- [ ] Vérifier que `public/` est le document root

## Configuration serveur

- [ ] Configurer le virtual host Apache/Nginx
- [ ] Pointer le document root vers `public/`
- [ ] Activer `mod_rewrite` (Apache)
- [ ] Configurer SSL/HTTPS si nécessaire

## Configuration application

- [ ] Créer le fichier `.env` avec les bonnes valeurs
- [ ] Configurer `app.baseURL = 'https://rezo.les-coches-deau.fr/'`
- [ ] Configurer les permissions sur `writable/` (755)
- [ ] Exécuter `composer install --no-dev --optimize-autoloader`

## Base de données

- [ ] Vérifier que `class_DbConnect.php` détecte correctement le domaine
- [ ] Vérifier que la connexion se fait vers `webdreamblog.mysql.db`
- [ ] Tester une connexion à la base de données
- [ ] Vérifier que le firewall permet les connexions MySQL sortantes

## Tests

- [ ] Accéder à `https://rezo.les-coches-deau.fr/`
- [ ] Tester la connexion avec un compte existant
- [ ] Vérifier que les données se chargent correctement
- [ ] Tester les fonctionnalités principales (activités, missions, etc.)
- [ ] Vérifier que les images/CSS/JS se chargent
- [ ] Tester la géolocalisation si possible

## Sécurité

- [ ] Vérifier que `.env` n'est pas accessible publiquement
- [ ] Vérifier les permissions des fichiers (644 pour les fichiers, 755 pour les répertoires)
- [ ] Vérifier que `writable/` n'est pas accessible directement
- [ ] S'assurer que les credentials ne sont pas exposés

## Logs et monitoring

- [ ] Vérifier que les logs sont créés dans `writable/logs/`
- [ ] Vérifier les logs du serveur web
- [ ] Configurer un monitoring si nécessaire

## Fin

- [ ] Noter l'URL finale : `https://rezo.les-coches-deau.fr/`
- [ ] Documenter les éventuelles modifications spécifiques au serveur
- [ ] Informer les utilisateurs si nécessaire
