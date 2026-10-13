#!/usr/bin/env bash
# One-shot local setup: boots Docker, installs WordPress, activates the theme + plugin and
# seeds all content from the Next.js data files. Safe to re-run.
#
#   bash bin/setup.sh            # full setup
#   bash bin/setup.sh --pages    # rebuild page layouts from block defaults (overwrites page content)
set -euo pipefail

cd "$(dirname "$0")/.."
export MSYS_NO_PATHCONV=1   # Git Bash on Windows: don't rewrite /seed into C:/...

[ -f .env ] || cp .env.example .env
# shellcheck disable=SC1091
set -a; . ./.env; set +a
PORT="${WP_PORT:-8090}"
URL="http://localhost:${PORT}"

wp() { docker compose run --rm -T wpcli "$@"; }

echo "→ Exporting seed data from ../lib/*.ts"
node scripts/export-seed.mjs > /dev/null

for dir in themes/interior-specifics plugins/interior-specifics-core; do
  if [ -f "$dir/package.json" ] && [ ! -d "$dir/build" ]; then
    echo "→ Building $dir"
    (cd "$dir" && npm install --no-audit --no-fund && npm run build)
  fi
done

echo "→ Starting containers"
docker compose up -d

echo "→ Waiting for WordPress files and database"
for _ in $(seq 1 60); do
  if wp db check > /dev/null 2>&1; then break; fi
  sleep 2
done

if ! wp core is-installed > /dev/null 2>&1; then
  echo "→ Installing WordPress"
  wp core install --url="$URL" --title="Interior Specifics" \
    --admin_user=admin --admin_password=admin --admin_email=admin@example.com --skip-email
  # Public display name, so posts credited to this account never show the login name.
  wp user update admin --display_name="Interior Specifics" --nickname="Interior Specifics" > /dev/null
fi

wp option update siteurl "$URL" > /dev/null
wp option update home "$URL" > /dev/null

echo "→ Activating theme and plugins"
wp theme activate interior-specifics
wp plugin activate interior-specifics-core
wp plugin is-installed wordpress-seo || wp plugin install wordpress-seo
wp plugin activate wordpress-seo
wp plugin delete akismet hello > /dev/null 2>&1 || true

echo "→ Seeding content"
if [ "${1:-}" = "--pages" ]; then
  wp is seed --dir=/seed --only=pages --force-pages --user=admin
else
  wp is seed --dir=/seed --user=admin
fi
wp rewrite flush --hard > /dev/null 2>&1 || wp rewrite flush

echo
echo "✔ Ready:   $URL"
echo "  Admin:   $URL/wp-admin   (admin / admin)"
echo "  Mailpit: http://localhost:8025"
