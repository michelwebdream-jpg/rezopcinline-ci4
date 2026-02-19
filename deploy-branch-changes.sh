#!/usr/bin/env bash
#
# Envoie sur le serveur de PRODUCTION (en FTP) uniquement les fichiers
# modifiés depuis le début de la branche active (par rapport à main).
#
# Utilise curl pour le FTP (présent sur macOS/Linux). Aucun SSH nécessaire.
#
# Usage:
#   ./deploy-branch-changes.sh              # affiche les fichiers qui seraient envoyés (dry-run)
#   ./deploy-branch-changes.sh --deploy     # envoie les fichiers en FTP sur le serveur
#
# À configurer ci-dessous : hôte FTP, utilisateur, chemin distant.

set -e

# --- Configuration FTP serveur de production ---
# Hôte FTP (sans ftp://), ex: ftp.monserveur.com ou monserveur.com
PRODUCTION_FTP_HOST="${PRODUCTION_FTP_HOST:-}"

# Utilisateur FTP
PRODUCTION_FTP_USER="${PRODUCTION_FTP_USER:-}"

# Mot de passe. Laisser vide pour le demander au lancement de --deploy
PRODUCTION_FTP_PASSWORD="${PRODUCTION_FTP_PASSWORD:-}"

# Chemin distant sur le serveur (celui où est l'app, ex: /www/ ou public_html/rezopcinline-ci4)
PRODUCTION_FTP_PATH="${PRODUCTION_FTP_PATH:-}"

# Branche de référence pour calculer les fichiers modifiés (souvent main)
REF_BRANCH="${REF_BRANCH:-main}"

# --- Fin configuration ---

DEPLOY_MODE=false
[[ "${1:-}" == "--deploy" ]] && DEPLOY_MODE=true

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

if [[ -z "$PRODUCTION_FTP_HOST" || -z "$PRODUCTION_FTP_USER" || -z "$PRODUCTION_FTP_PATH" ]]; then
  echo "Erreur: configurez PRODUCTION_FTP_HOST, PRODUCTION_FTP_USER et PRODUCTION_FTP_PATH dans le script." >&2
  echo "Exemple: PRODUCTION_FTP_HOST=\"ftp.monserveur.com\"  PRODUCTION_FTP_USER=\"monuser\"  PRODUCTION_FTP_PATH=\"public_html/rezopcinline\"" >&2
  exit 1
fi

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "Erreur: pas un dépôt git. Exécutez le script depuis la racine du projet." >&2
  exit 1
fi

BRANCH=$(git branch --show-current)
BASE_COMMIT=$(git merge-base "$REF_BRANCH" HEAD 2>/dev/null || true)

if [[ -z "$BASE_COMMIT" ]]; then
  echo "Erreur: impossible de trouver le point de divergence avec '$REF_BRANCH'." >&2
  exit 1
fi

# Si la variable d'environnement FILES est définie, on l'utilise telle quelle
# (permet de déployer un seul fichier depuis l'admin CI4). Sinon, on calcule
# la liste : commits depuis BASE_COMMIT + modifs staged/unstaged + fichiers nouveaux (untracked).
if [[ -z "$FILES" ]]; then
  FILES=$( (git diff --name-only "$BASE_COMMIT"; git diff --name-only; git diff --name-only --cached; git ls-files --others --exclude-standard) | sort -u )
fi

FILE_COUNT=$(echo "$FILES" | grep -c . 2>/dev/null || echo 0)

echo "Branche      : $BRANCH"
echo "Référence    : $REF_BRANCH"
echo "FTP          : $PRODUCTION_FTP_USER@$PRODUCTION_FTP_HOST"
echo "Chemin       : $PRODUCTION_FTP_PATH"
echo "Fichiers     : $FILE_COUNT"
echo ""

if [[ $FILE_COUNT -eq 0 ]]; then
  echo "Aucun fichier modifié depuis le début de la branche."
  exit 0
fi

echo "Fichiers à envoyer :"
echo "$FILES" | sed 's/^/  /'
echo ""

if [[ "$DEPLOY_MODE" != true ]]; then
  echo "Dry-run : rien n'a été envoyé. Pour envoyer en FTP : $0 --deploy"
  exit 0
fi

if [[ -z "$PRODUCTION_FTP_PASSWORD" ]]; then
  echo -n "Mot de passe FTP pour $PRODUCTION_FTP_USER@$PRODUCTION_FTP_HOST : "
  read -s PRODUCTION_FTP_PASSWORD
  echo ""
  [[ -z "$PRODUCTION_FTP_PASSWORD" ]] && { echo "Mot de passe vide, annulé." >&2; exit 1; }
fi

# Enlever un éventuel slash final du chemin pour construire l'URL
FTP_PATH="${PRODUCTION_FTP_PATH%/}"
FTP_URL="ftp://$PRODUCTION_FTP_HOST/$FTP_PATH"

echo "Envoi en cours..."
FAILED=0
while IFS= read -r f; do
  [[ -z "$f" ]] && continue
  if [[ ! -f "$SCRIPT_DIR/$f" ]]; then
    echo "  ignoré (absent) : $f"
    continue
  fi
  # curl crée les répertoires distants avec --ftp-create-dirs
  if curl -sS -T "$SCRIPT_DIR/$f" "$FTP_URL/$f" --user "$PRODUCTION_FTP_USER:$PRODUCTION_FTP_PASSWORD" --ftp-create-dirs; then
    echo "  ok : $f"
  else
    echo "  ERREUR : $f" >&2
    FAILED=1
  fi
done <<< "$FILES"

if [[ $FAILED -eq 0 ]]; then
  echo "Déploiement terminé."
else
  echo "Déploiement terminé avec des erreurs." >&2
  exit 1
fi
