#!/usr/bin/env bash
# Build the theme (JS blocks + CSS). Serialised with a lock directory so several builds
# started at once (e.g. parallel agents) run one after another instead of clobbering build/.
#   bash bin/build-theme.sh
set -uo pipefail

LOCK="${TMPDIR:-/tmp}/is-theme-build.lock"
for _ in $(seq 1 300); do
  if mkdir "$LOCK" 2>/dev/null; then break; fi
  # Break locks older than 5 minutes (a crashed build).
  if [ -n "$(find "$LOCK" -maxdepth 0 -mmin +5 2>/dev/null)" ]; then rmdir "$LOCK" 2>/dev/null; fi
  sleep 2
done
trap 'rmdir "$LOCK" 2>/dev/null' EXIT

cd "$(dirname "$0")/../themes/interior-specifics" || exit 1
npm run build 2>&1 | grep -v "^\s*$" | grep -iE "error|warning|compiled|Done in|ERROR in|Module not found" | head -60
exit "${PIPESTATUS[0]}"
