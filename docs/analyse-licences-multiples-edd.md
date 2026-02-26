# Analyse : support de plusieurs types de clés de licence (1 an, 6 mois, évolutif)

## Contexte

- **EDD** (Easy Digital Downloads) ne permet pas un seul produit avec plusieurs durées de licence ; il faut donc **un produit EDD par durée** (ex. 1 an, 6 mois).
- L’application doit accepter **une seule clé** saisie par l’utilisateur et reconnaître automatiquement le type de produit (sans que l’utilisateur choisisse « 1 an » ou « 6 mois »).
- La solution doit rester **évolutive** pour un 3ᵉ type (ex. 2 ans, 3 mois, etc.) sans refonte.

---

## 1. Où la licence est utilisée aujourd’hui

### 1.1 Côté application CI4

| Fichier | Rôle |
|--------|------|
| `app/Models/SignupModel.php` | `check_code_et_licence()` → appelle `lit_info_administrateur.php` ; `creer_compte_administrateur()` → appelle `creat_customer.php`. Envoie uniquement la clé (`ma_licence`), jamais d’`item_name`. |
| `app/Controllers/Signup.php` | Utilise les réponses (ok, -1, -2, -3, codes 2–5) et la trame `code><...><date_fin_validite_licence><...`. Aucune référence à un type de produit. |
| `app/Views/signup.php`, `signup_sav.php` | Lien d’achat unique vers `cle-de-licence-rezo-pc-inline-1-an`. |
| `public/js/rezopcinline.js` | Appelle `test_licence_administrateur.php` ; attend `return_txt=ok` + code + date. Aucun `item_name`. |

**Conclusion** : côté CI4, **aucune logique métier ne dépend du type de produit**. Seule la présence d’une clé valide et la date de fin de validité comptent. Les changements CI4 seront surtout **affichage** (liens d’achat) et éventuellement **config** si on centralise les URLs.

### 1.2 Côté scripts rezo_flash_code (PHP)

Tous utilisent en dur l’`item_name` EDD :  
`cle-de-licence-rezo-pc-inline-1-an`

| Fichier | Utilisation de l’item_name |
|--------|----------------------------|
| `test_licence_administrateur.php` | `test_licence()` et `get_date_licence()` : appels EDD `check_license` avec cet item_name. |
| `lit_info_administrateur.php` | Idem : `test_licence()` et `get_date_licence()` pour la lecture du compte existant. |
| `creat_customer.php` | `test_licence()`, `get_date_licence()`, `activate_licence()` : vérification, récupération de la date, activation. |

**Conclusion** : toute la logique « quel produit EDD » est dans **rezo_flash_code**. C’est là qu’il faut rendre le système multi-produits et évolutif.

---

## 2. Principe de la solution évolutive

- **Une seule liste** de produits EDD (item_name) acceptés par l’application, par ex. :
  - `cle-de-licence-rezo-pc-inline-1-an`
  - `cle-de-licence-rezo-pc-inline-6-mois`
- Pour une clé saisie : **tester chaque item_name** via l’API EDD `check_license` jusqu’à obtenir une réponse « reconnue » (`valid`, `inactive` ou `expired` avec le bon email). Le premier produit qui matche donne à la fois le **statut** et l’**item_name** à utiliser pour la suite.
- Une fois l’item_name connu : **get_date** et **activate_license** utilisent systématiquement ce même item_name.

Ainsi, l’utilisateur ne choisit pas le type de licence : l’application le déduit de la clé. L’ajout d’un 3ᵉ (ou Nᵉ) produit = ajout d’une ligne dans la config, sans changer la logique d’appel.

---

## 3. Modifications détaillées

### 3.1 Nouveau fichier de configuration (rezo_flash_code)

**Fichier** : `public/dev/rezo_flash_code/config_licences_edd.php` (ou au même niveau que les scripts qui l’incluent).

**Contenu** : liste des slugs EDD (item_name) acceptés, dans l’ordre de test (ex. 1 an en premier, puis 6 mois).

```php
<?php
/**
 * Liste des produits EDD (item_name) acceptés pour REZO+ PC Inline.
 * Ordre = ordre de test pour identifier la clé (premier qui matche gagne).
 */
return [
    'cle-de-licence-rezo-pc-inline-1-an',
    'cle-de-licence-rezo-pc-inline-6-mois',
    // Ajouter ici un 3e produit si besoin, ex. :
    // 'cle-de-licence-rezo-pc-inline-2-ans',
];
```

**Évolution** : pour un nouveau type (ex. 2 ans), créer le produit sur EDD puis ajouter une ligne dans ce tableau. Aucun autre fichier à toucher pour la « reconnaissance » de la clé.

---

### 3.2 Fonctions EDD partagées (rezo_flash_code)

Idée : **une seule fonction** qui, pour une clé et un email donnés, parcourt la liste des item_name et retourne le **statut** + l’**item_name** reconnu (ou null).

- **Option A – Fichier inclus dédié**  
  Créer par ex. `public/dev/rezo_flash_code/edd_license_helpers.php` :
  - Charger `config_licences_edd.php`.
  - Exposer une fonction du type :
    - `check_license_multi_product($licence, $mail)`  
      Retour : `['status' => '0'|'1'|'-1'|'2'|'3'|'4'|'5', 'item_name' => '...'|null]`  
      avec les mêmes conventions de statut que aujourd’hui (1=valid+email ok, 0=inactive+email ok, -1=expired+email ok, 3=invalid, 4=valid+email différent, etc.).
  - Exposer :
    - `get_date_licence_with_item($licence, $item_name)`  
      Un seul appel EDD avec le bon `item_name`, retour `expires`.
    - `activate_licence_with_item($licence, $item_name)`  
      Un seul appel EDD `activate_license` avec le bon `item_name`.

- **Option B – Tout dans chaque script**  
  Dupliquer la boucle et les appels dans `test_licence_administrateur.php`, `lit_info_administrateur.php`, `creat_customer.php`. Peu recommandé (maintenance, risque d’oubli).

**Recommandation** : **Option A** (fichier inclus) pour une seule source de vérité et une évolution simple (ajout de produits dans la config + mise à jour des 3 scripts pour utiliser les helpers).

---

### 3.3 Modifications dans chaque script rezo_flash_code

#### 3.3.1 `test_licence_administrateur.php`

- Inclure `config_licences_edd.php` et `edd_license_helpers.php`.
- Remplacer l’appel actuel à `test_licence($license, $mail)` par :
  - `$result = check_license_multi_product($license, $mail);`
  - Si `$result['item_name'] === null` → même comportement qu’aujourd’hui « clé invalide / inconnue » (ex. `return_txt=-1`).
  - Sinon utiliser `$result['status']` comme aujourd’hui (1 → ok, -1 → expirée, etc.).
- Pour la date : remplacer `get_date_licence($license, $mail)` par  
  `get_date_licence_with_item($license, $result['item_name'])`  
  (uniquement quand le statut indique « ok » et qu’on a un item_name).

Aucun changement de format de sortie (`return_txt=ok$mon_code$date_fin_validite_licence`, etc.) : le JS et le contrôleur CI4 restent inchangés.

#### 3.3.2 `lit_info_administrateur.php`

- Même inclusion config + helpers.
- Remplacer les deux appels (équivalents à `test_licence` et `get_date_licence`) par :
  - Un appel à `check_license_multi_product($licence, $mail)`.
  - Si `item_name` présent, utiliser `get_date_licence_with_item($licence, $item_name)` pour récupérer `expires` et construire la trame (dont `date_fin_validite_licence`).
- Conserver le même format de réponse (trame avec `><`) pour que `Signup.php` et le modèle continuent de fonctionner sans changement.

#### 3.3.3 `creat_customer.php`

- Même inclusion config + helpers.
- Remplacer :
  - `test_licence($ma_licence, $mon_mail)` par `check_license_multi_product($ma_licence, $mon_mail)`.
  - Interpréter `result['status']` comme aujourd’hui (0 = inactive → on activera ; 4 = déjà valide → pas d’activation).
  - Si `result['item_name']` est null → retour d’erreur (ex. `return_txt=3`).
  - Remplacer `activate_licence($ma_licence)` par `activate_licence_with_item($ma_licence, $result['item_name'])`.
  - Remplacer `get_date_licence($ma_licence, $mon_mail)` par `get_date_licence_with_item($ma_licence, $result['item_name'])`.

Les codes de retour et le format `return_txt=ok$mon_code$date_fin_validite_licence` restent identiques.

---

### 3.4 Côté application CI4 (optionnel mais cohérent)

Aucune modification **obligatoire** pour que les clés 6 mois (et plus tard un 3ᵉ type) fonctionnent : la reconnaissance est entièrement côté rezo_flash_code.

Modifications **recommandées** pour l’UX et la cohérence :

1. **Liens d’achat sur la page d’inscription**
   - **Fichiers** : `app/Views/signup.php`, `app/Views/signup_sav.php`.
   - **Actuel** : un seul lien vers `cle-de-licence-rezo-pc-inline-1-an`.
   - **Évolution** : afficher deux liens (ou une phrase avec deux liens), par ex. :
     - « Vous pouvez en acquérir une : 1 an [ici], 6 mois [ici]. »
   - Pour rester évolutif : soit une liste en dur (1 an, 6 mois) avec les URLs EDD, soit une **config** CI4 (tableau d’entrées `['label' => '1 an', 'url' => '...']`) lue dans la vue. La config permet d’ajouter un 3ᵉ lien sans retoucher au HTML.

2. **Centralisation des URLs produits (optionnel)**
   - Si on souhaite que l’app CI4 « sache » quels produits existent (pour affichage, stats, etc.) : ajouter dans `app/Config/` une config dédiée (ex. `Licences.php`) avec la liste des produits (slug + libellé + URL d’achat). Les vues et éventuels helpers utilisent cette config. Les slugs doivent rester alignés avec `config_licences_edd.php` côté rezo_flash_code pour que les liens correspondent aux produits testés.

3. **Contrôleur / modèle**
   - Aucun changement nécessaire : ils ne reçoivent pas et n’ont pas besoin de connaître l’item_name.

---

## 4. Récapitulatif des fichiers à modifier ou créer

| Zone | Fichier | Action |
|------|--------|--------|
| rezo_flash_code | `config_licences_edd.php` | **Créer** : liste des item_name. |
| rezo_flash_code | `edd_license_helpers.php` | **Créer** : `check_license_multi_product`, `get_date_licence_with_item`, `activate_licence_with_item`. |
| rezo_flash_code | `test_licence_administrateur.php` | **Modifier** : utiliser config + helpers, plus d’item_name en dur. |
| rezo_flash_code | `lit_info_administrateur.php` | **Modifier** : idem. |
| rezo_flash_code | `creat_customer.php` | **Modifier** : idem. |
| CI4 (optionnel) | `app/Views/signup.php` | **Modifier** : liens d’achat 1 an + 6 mois (ou depuis config). |
| CI4 (optionnel) | `app/Views/signup_sav.php` | **Modifier** : idem. |
| CI4 (optionnel) | `app/Config/Licences.php` | **Créer** si on centralise les libellés/URLs produits. |

---

## 5. Ordre de déploiement recommandé

1. Créer le produit **6 mois** sur EDD (slug cohérent, ex. `cle-de-licence-rezo-pc-inline-6-mois`).
2. Créer `config_licences_edd.php` avec 1 an + 6 mois.
3. Créer `edd_license_helpers.php` et faire en sorte que les conventions de statut (0, 1, -1, 2, 3, 4, 5) restent identiques à l’existant.
4. Adapter les 3 scripts PHP un par un (test_licence_administrateur, lit_info_administrateur, creat_customer), en vérifiant après chaque étape (inscription avec clé 1 an, puis avec clé 6 mois).
5. Mettre à jour les vues signup pour les liens d’achat.
6. (Optionnel) Introduire une config CI4 des produits si vous voulez une seule source pour les libellés/URLs.

---

## 6. Ajout d’un 3ᵉ type (ex. 2 ans) plus tard

- **EDD** : créer le produit (ex. `cle-de-licence-rezo-pc-inline-2-ans`).
- **rezo_flash_code** : ajouter cette ligne dans `config_licences_edd.php`.
- **CI4** : si vous utilisez une config ou une liste de liens, ajouter l’entrée correspondante.

Aucun changement dans la logique des helpers ni dans le contrôleur/model CI4.

---

*Document généré pour le projet REZO+ PC Inline (CodeIgniter 4).*
