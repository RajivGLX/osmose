#!/usr/bin/env bash
set -euo pipefail

# ---------- Réglages ----------
REPO_ROOT="$(cd "$(dirname "$0")" && pwd)"            # /var/www/osmose
API_DIR="$REPO_ROOT/api-patient-interface"           # Symfony
SPA_DIR="$REPO_ROOT/center-interface"                # Angular
SPA_DIST="$SPA_DIR/dist/nota-risques"                # sortie build Angular 17
SPA_TARGET="/var/www/osmose/admin-app"               # dossier servi par Nginx (root=/var/www/osmose/admin-app/browser)
PHP_BIN="php"                                        # ou /usr/bin/php8.3
NODE_OPTIONS_DEF="--max_old_space_size=4096"         # mémoire Node pour le build
SYMFONY_ENV="prod"

echo "==> Déploiement $(date '+%F %T')"
cd "$REPO_ROOT"

# Détermine l'ancien commit pour savoir ce qui a changé
OLD_COMMIT="$(git rev-parse HEAD@{1} 2>/dev/null || echo "")"
NEW_COMMIT="$(git rev-parse HEAD)"

if [[ -n "$OLD_COMMIT" ]]; then
  CHANGED=$(git diff --name-only "$OLD_COMMIT" "$NEW_COMMIT" || true)
else
  echo "Pas d'ancien commit détecté (premier déploiement ?), on fait tout."
  CHANGED="ALL"
fi

changed_any() {
  # usage: changed_any "pattern1" "pattern2" ...
  if [[ "$CHANGED" == "ALL" ]]; then return 0; fi
  grep -E -q "$1" <<<"$CHANGED"
}

# ---------- 1) BACK Symfony ----------
if changed_any "^api-patient-interface/composer\.lock|^api-patient-interface/composer\.json"; then
  echo "-> composer install (API)"
  cd "$API_DIR"
  composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
else
  echo "-> composer install sauté (pas de changement composer.lock)"
fi

echo "-> migrations & cache Symfony"
cd "$API_DIR"
$PHP_BIN bin/console doctrine:migrations:migrate --env="$SYMFONY_ENV" --no-interaction --allow-no-migration || true
# Compile AssetMapper si tu l’utilises
if $PHP_BIN -r 'exit(!class_exists("Symfony\\Component\\AssetMapper\\AssetMapperBundle\\AssetMapperBundle"));' 2>/dev/null; then
  $PHP_BIN bin/console asset-map:compile --env="$SYMFONY_ENV" || true
fi
$PHP_BIN bin/console cache:clear --env="$SYMFONY_ENV"

# ---------- 2) FRONT Angular (admin) ----------
if changed_any "^center-interface/|^package(-lock)?\.json|^angular\.json"; then
  echo "-> npm ci + build Angular"
  cd "$SPA_DIR"
  # Installe proprement si le lockfile a changé, sinon npm ci est OK aussi
  npm ci
  NODE_OPTIONS="$NODE_OPTIONS_DEF" npx ng build --configuration production --source-map=false --verbose
else
  # Même si rien n'a changé, tu peux forcer un rebuild en décommentant la ligne suivante
#   echo "-> Rien changé côté Angular, rebuild forcé"; cd "$SPA_DIR"; npm ci; NODE_OPTIONS="$NODE_OPTIONS_DEF" npx ng build --configuration production --source-map=false
  echo "-> build Angular sauté (pas de changement détecté)"
fi

# ---------- 3) Déploiement des fichiers Angular vers Nginx ----------
if [[ -d "$SPA_DIST/browser" ]]; then
  echo "-> rsync du build Angular vers $SPA_TARGET"
  sudo mkdir -p "$SPA_TARGET"
  # On synchronise tout le dossier (qui contient 'browser/')
  rsync -a --delete "$SPA_DIST"/ "$SPA_TARGET"/
  sudo chown -R www-data:www-data "$SPA_TARGET"
else
  echo "ATTENTION: Dossier $SPA_DIST/browser introuvable. Build Angular raté ?"
fi

# ---------- 4) Permissions Symfony ----------
echo "-> permissions Symfony var/"
sudo chown -R www-data:www-data "$API_DIR/var"

# ---------- 5) (Optionnel) Reload services ----------
echo "-> reload nginx (optionnel)"
sudo systemctl reload nginx || true

echo "==> Fin déploiement"
