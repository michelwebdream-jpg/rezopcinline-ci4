# Analyse : mise à jour de la licence sur un compte existant (« Associer une nouvelle clé »)

## Objectif

Permettre à un utilisateur **déjà connecté** de remplacer la clé de licence de son compte (ex. passer d’une clé démo à une clé 1 an) **sans créer un nouveau compte**, en conservant son code REZO+, son historique et ses données.

---

## 1. Flux métier

1. L’utilisateur est **connecté** (session avec `deliverdata` contenant `code_administrateur`, `mail_administrateur`, etc.).
2. Il accède à une interface **« Mettre à jour ma licence »** (dans Mon compte ou page dédiée).
3. Il saisit sa **nouvelle clé** et valide.
4. L’application :
   - identifie le compte (mon_code + vérification session ou mot de passe) ;
   - récupère l’**email** du compte (REZO_FLASH.mail) ;
   - appelle **EDD** (check_license) pour la nouvelle clé sur tous les produits autorisés ;
   - vérifie que la clé est **valide ou inactive** et que **customer_email** EDD correspond à l’email du compte ;
   - si la clé est **inactive**, appelle **activate_license** pour ce produit ;
   - récupère la **date d’expiration** (expires) ;
   - met à jour **REZO_FLASH** : `licence` = nouvelle clé, `date_fin_validite_licence` = date EDD ;
   - met à jour la **session** pour refléter la nouvelle date de validité (optionnel mais conseillé).
5. Retour à l’utilisateur : succès ou message d’erreur explicite.

---

## 1 bis. Problème : licence expirée = plus d’accès à Mon compte

Si la **licence démo est expirée** (ex. après 14 jours), la connexion est refusée (retour « Clé expirée »). L’utilisateur ne peut donc **pas** ouvrir « Mon compte » pour saisir sa nouvelle clé (1 an ou 6 mois).

**Solution : page « Associer une nouvelle clé » accessible sans être connecté**

- Une **page dédiée**, **publique** (pas de session requise), où l’utilisateur saisit :
  - **Code REZO+**
  - **Mot de passe**
  - **Nouvelle clé de licence**
- L’authentification repose uniquement sur **code + mot de passe** (vérification en BDD REZO_FLASH). Aucune vérification de licence à la connexion : on ne fait qu’appeler le même script `update_licence_customer.php` avec ces trois champs.
- Après succès : redirection vers la **page de connexion** avec un message du type « Votre licence a été mise à jour. Vous pouvez vous connecter. »
- **Cas d’usage** : essai terminé → l’utilisateur reçoit un lien (email, message sur l’écran de login) « Votre essai est terminé ? Associez votre nouvelle clé ici » → il remplit code + mot de passe + nouvelle clé → compte mis à jour → il se connecte normalement.

On a donc **deux points d’entrée** pour la même action « associer une nouvelle clé » :

| Contexte | Accès | Formulaire |
|----------|--------|------------|
| **Utilisateur connecté** (licence encore valide) | Depuis Mon compte | Code + mot de passe pris de la session ; seul le champ « Nouvelle clé » est affiché. |
| **Utilisateur non connecté** (licence expirée ou perte de session) | Page dédiée (ex. `/signup/associer_cle`) | Trois champs : Code REZO+, Mot de passe, Nouvelle clé. Aucune session requise. |

---

## 2. Fichiers à créer

### 2.1 Script côté rezo_flash_code (backend)

| Fichier | Rôle |
|--------|------|
| `public/dev/rezo_flash_code/update_licence_customer.php` | Point d’entrée unique pour « associer une nouvelle clé » au compte. |

**Responsabilités :**

- Recevoir en POST : `mon_code`, `mon_mot_de_passe` (pour authentifier le compte), `nouvelle_cle`.
- Vérifier que le compte existe (REZO_FLASH où moncode + motdepasse) et récupérer `mail`.
- Réutiliser la logique EDD existante (liste des produits, check_license multi-produits, get_date, activate si inactive) comme dans `creat_customer.php` / `lit_info_administrateur.php`.
- Contraintes :
  - La nouvelle clé doit être **valide** ou **inactive** pour au moins un produit.
  - Si **inactive** : appeler `activate_license` pour le bon item_name avant de mettre à jour la BDD.
  - **customer_email** EDD doit correspondre au **mail** du compte (sinon refus : « Cette clé est associée à un autre compte »).
- Si tout est OK :  
  `UPDATE REZO_FLASH SET licence = ?, date_fin_validite_licence = ? WHERE moncode = ?`
- Réponse en texte simple pour le contrôleur CI4, par ex. :
  - `return_txt=ok` + éventuellement la nouvelle date formatée (pour mise à jour session) ;
  - `return_txt=ERR_xxx` avec codes d’erreur définis (voir section 5).

**Sécurité :**

- Ne pas accepter la requête sans `mon_code` + `mon_mot_de_passe` valides (ou, si on choisit un token session côté CI4, alors le script recevrait un token vérifié côté CI4 — ici on reste sur code + mot de passe pour rester cohérent avec `updateuser_customer.php` et `lit_info_administrateur`).
- Échappement / requêtes préparées pour éviter les injections SQL (comme dans les autres scripts du dossier).

---

### 2.2 Côté CI4 (application)

| Fichier | Rôle |
|--------|------|
| `app/Models/SignupModel.php` | Nouvelle méthode, ex. `update_licence_compte($mon_code, $mon_mot_de_passe, $nouvelle_cle)`, qui appelle en POST le script `update_licence_customer.php` et retourne la réponse brute ou parsée. |
| `app/Controllers/Mon_compte.php` | Pour les **utilisateurs connectés** : afficher le formulaire « Nouvelle clé » (code + mot de passe pris de la session) et traiter le POST ; mise à jour de `deliverdata['date_fin_validite_licence']` en cas de succès. |
| `app/Controllers/Signup.php` | Nouvelle méthode, ex. `associer_cle()` : **page publique** (pas de vérification de session). GET = formulaire avec 3 champs (Code REZO+, Mot de passe, Nouvelle clé). POST = appel au modèle `update_licence_compte`, puis redirection vers `signup/login` avec message de succès ou affichage des erreurs. |
| `app/Views/mon_compte.php` (ou vue partielle) | Bloc « Mettre à jour ma licence » : champ « Nouvelle clé de licence », bouton « Associer cette clé » (pour utilisateur connecté). |
| `app/Views/associer_cle.php` (nouvelle vue) | Page dédiée **sans connexion** : formulaire Code REZO+, Mot de passe, Nouvelle clé de licence ; texte d’aide « Votre licence a expiré ? Associez votre nouvelle clé ici si vous avez acheté une nouvelle licence avec une clé différente. Si vous avez simplement renouvelé votre licence existante en saisissant votre clé actuelle lors du paiement sur la boutique, une connexion normale suffira : la nouvelle date de validité sera automatiquement prise en compte. » |

**URL et routes :**

- **Depuis Mon compte** : même page ou route `mon_compte/update_licence` (utilisateur déjà connecté).
- **Page publique (licence expirée)** : route dédiée **`signup/associer_cle`** (GET + POST). À ajouter dans `app/Config/Routes.php`, ex. :
  - `$routes->get('signup/associer_cle', 'Signup::associer_cle');`
  - `$routes->post('signup/associer_cle', 'Signup::associer_cle');`

**Affichage du lien « Associer une nouvelle clé » (uniquement en cas de licence expirée) :**

- **Ne pas** afficher le lien sur la page de connexion en temps normal (pas de lien pour les visiteurs ou les utilisateurs qui n’ont pas encore tenté de se connecter).
- **Afficher le lien uniquement** lorsque l’utilisateur a saisi son code et son mot de passe et que l’application retourne le statut **« licence expirée »** (réponse API `return_txt=-1`). Dans ce cas, réafficher le formulaire de connexion avec le message d’erreur habituel **et** le lien vers `signup/associer_cle`.
- **Texte du lien** (valable essai démo comme licence 1 an/6 mois expirée) : **« Votre licence a expiré ? Associez votre nouvelle clé ici »** (lien vers `signup/associer_cle`).
- **Texte d’aide (sous le lien)** : reprendre le même message que sur la page `associer_cle`, par ex. : « Vous pouvez associer ici une nouvelle clé si vous avez acheté une licence avec une clé différente. Si vous avez simplement renouvelé votre licence existante en saisissant votre clé actuelle lors du paiement sur la boutique, une connexion normale suffira : la nouvelle date de validité sera automatiquement prise en compte. »

**Implémentation côté code :**

- Dans **Signup** (traitement du POST login) : lorsque la réponse est `-1` (licence expirée), passer à la vue login une variable, ex. `show_associer_cle_link = true` (en plus du message d’erreur).
- Dans la **vue login** : n’afficher le bloc avec le lien « Votre licence a expiré ? Associez votre nouvelle clé ici » que si `show_associer_cle_link` est vrai. Ainsi le lien n’apparaît qu’après une tentative de connexion ayant échoué pour cause de licence expirée.

---

## 3. Fichiers à modifier (résumé)

| Fichier | Modification |
|--------|---------------|
| `public/dev/rezo_flash_code/update_licence_customer.php` | **Créer** (voir 2.1). |
| `app/Models/SignupModel.php` | **Ajouter** une méthode `update_licence_compte($mon_code, $mon_mot_de_passe, $nouvelle_cle)` qui fait le POST vers `update_licence_customer.php` (URL depuis `getAppServerURL()` + URI dédiée, ex. `UPDATE_LICENCE_URI`). |
| `app/Controllers/Mon_compte.php` | **Ajouter** la logique formulaire « Mettre à jour ma licence » + traitement POST (code/mot de passe depuis la session, mise à jour de `deliverdata['date_fin_validite_licence']` si succès). |
| `app/Controllers/Signup.php` | **Ajouter** la méthode `associer_cle()` : page publique (GET = formulaire 3 champs, POST = appel modèle + redirection vers login avec message succès ou erreurs). |
| `app/Views/mon_compte.php` | **Ajouter** un bloc formulaire « Nouvelle clé » + message succès/erreur (pour utilisateur connecté). |
| `app/Views/associer_cle.php` | **Créer** : page « Associer une nouvelle clé » avec formulaire Code REZO+, Mot de passe, Nouvelle clé (style proche de login/signup). |
| `app/Views/login.php` | **Ajouter** un bloc conditionnel : si `show_associer_cle_link` est vrai (passé par le contrôleur uniquement quand l’API a retourné « licence expirée »), afficher le lien « Votre licence a expiré ? Associez votre nouvelle clé ici » vers `signup/associer_cle`. Ne pas afficher ce lien sinon. |
| `app/Controllers/Signup.php` (traitement login) | Lorsque la réponse de `lit_info_administrateur` est `-1` (licence expirée), passer à la vue login la variable `show_associer_cle_link = true` en plus du message d’erreur, afin d’afficher le lien vers `associer_cle`. |
| `app/Config/Routes.php` | **Ajouter** les routes `signup/associer_cle` (GET et POST) ; optionnellement `mon_compte/update_licence` si formulaire dédié. |
| `.env` (ou config) | **Optionnel** : `UPDATE_LICENCE_URI=/dev/rezo_flash_code/update_licence_customer.php`. |

---

## 4. Réutilisation du code EDD existant

- **Liste des produits** : `config_licences_edd.php` + `get_edd_item_names()` (à inclure ou dupliquer dans `update_licence_customer.php`).
- **Vérification multi-produits** : même logique que dans `creat_customer.php` (test_licence, get_date_licence, activate_licence avec le bon item_name). On peut soit :
  - inclure un fichier commun (ex. `edd_license_helpers.php`) si tu en crées un plus tard, soit
  - recopier / adapter les fonctions dans `update_licence_customer.php` pour rester autonome comme les autres scripts du dossier.

La nouvelle clé ne doit **pas** déjà être présente dans un autre compte (optionnel mais recommandé : vérifier que `nouvelle_cle` n’existe pas dans `REZO_FLASH.licence` pour un autre `moncode` ; si oui, retourner une erreur « Cette clé est déjà utilisée par un autre compte »).

---

## 5. Codes de retour proposés pour `update_licence_customer.php`

À définir de façon cohérente avec le reste de l’appli (ex. même style que `creat_customer.php`). Exemple :

| Code retour | Signification |
|-------------|---------------|
| `return_txt=ok` (évent. + date formatée) | Licence mise à jour avec succès. |
| `return_txt=ERR_AUTH` | Compte non trouvé ou mot de passe incorrect. |
| `return_txt=ERR_KEY_INVALID` | Clé inconnue ou invalide pour tous les produits. |
| `return_txt=ERR_KEY_OTHER_ACCOUNT` | Clé valide mais associée à un autre email (customer_email ≠ mail du compte). |
| `return_txt=ERR_KEY_EXPIRED` | Clé expirée. |
| `return_txt=ERR_ACTIVATE` | Échec de l’activation de la clé (statut inactive mais activate_license a échoué). |
| `return_txt=ERR_KEY_ALREADY_USED` | (Optionnel) Cette clé est déjà utilisée par un autre compte (même email ou non). |

Le contrôleur CI4 traduit ces codes en messages utilisateur (ex. « Cette clé est expirée », « Cette clé est associée à un autre compte »).

---

## 6. Mise à jour de la session après succès

Après une mise à jour réussie, pour que l’utilisateur voie tout de suite la nouvelle date de validité sans se reconnecter :

- Soit le script PHP retourne la **date formatée** (ex. `return_txt=ok|dd/mm/yyyy HH:ii:ss`) et le contrôleur met à jour `$this->session->set('deliverdata', [... 'date_fin_validite_licence' => $nouvelle_date ...])`.
- Soit le contrôleur refait un appel « lit infos » (ex. `check_code_et_licence` ou un équivalent en lecture seule) pour recharger `deliverdata` — plus lourd mais cohérent avec le flux actuel.

Recommandation : retourner la date dans la réponse du script et mettre à jour la session côté `Mon_compte` pour éviter un aller-retour supplémentaire.

---

## 7. Récapitulatif création / modification

| Action | Fichier |
|--------|--------|
| **Créer** | `public/dev/rezo_flash_code/update_licence_customer.php` |
| **Créer** | `app/Views/associer_cle.php` (page publique « Associer une nouvelle clé ») |
| **Modifier** | `app/Models/SignupModel.php` (ajout méthode `update_licence_compte`) |
| **Modifier** | `app/Controllers/Mon_compte.php` (formulaire + traitement POST + mise à jour session) |
| **Modifier** | `app/Controllers/Signup.php` (ajout méthode `associer_cle()` pour accès sans connexion) |
| **Modifier** | `app/Views/mon_compte.php` (bloc formulaire « Mettre à jour ma licence ») |
| **Modifier** | `app/Views/login.php` (bloc conditionnel : lien « Votre licence a expiré ? Associez votre nouvelle clé ici » uniquement si `show_associer_cle_link` est vrai) |
| **Modifier** | `app/Controllers/Signup.php` (traitement login : passer `show_associer_cle_link = true` lorsque l’API retourne `-1` licence expirée) |
| **Modifier** | `app/Config/Routes.php` (routes `signup/associer_cle` GET/POST ; optionnel `mon_compte/update_licence`) |
| **Modifier** (optionnel) | `.env` / config (URI du script) |

Aucune modification de la structure des tables **REZO** ou **REZO_FLASH** n’est nécessaire : on ne fait qu’un `UPDATE` sur les colonnes existantes `licence` et `date_fin_validite_licence`.

---

*Document d’analyse pour le projet REZO+ PC Inline – mise à jour de licence sur compte existant.*
