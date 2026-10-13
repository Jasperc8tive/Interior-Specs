#!/usr/bin/env bash
# Build production assets and produce installable zips in dist/:
#   dist/interior-specifics.zip        (theme  → Appearance → Themes → Add New → Upload)
#   dist/interior-specifics-core.zip   (plugin → Plugins → Add New → Upload)
# Source files, node_modules and tooling are excluded; compiled build/ output is included.
set -euo pipefail

cd "$(dirname "$0")/.."
ROOT="$(pwd)"
DIST="$ROOT/dist"
STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT
mkdir -p "$DIST"

build() {
  local dir="$1"
  if [ -f "$dir/package.json" ]; then
    echo "→ Building $dir"
    (cd "$dir" && npm ci --no-audit --no-fund > /dev/null && npm run build > /dev/null)
  fi
}

stage() {
  local src="$1" name="$2"
  echo "→ Staging $name"
  mkdir -p "$STAGE/$name"
  (cd "$src" && tar -cf - \
    --exclude=node_modules --exclude=src --exclude=scripts \
    --exclude=package.json --exclude=package-lock.json --exclude=webpack.config.js \
    --exclude='*.map' --exclude='.DS_Store' . ) | (cd "$STAGE/$name" && tar -xf -)
}

zipdir() {
  local name="$1"
  rm -f "$DIST/$name.zip"
  if command -v zip > /dev/null 2>&1; then
    (cd "$STAGE" && zip -qr "$DIST/$name.zip" "$name")
  elif [ -x /c/Windows/System32/tar.exe ]; then
    # Windows' bundled bsdtar writes real zip archives.
    (cd "$STAGE" && /c/Windows/System32/tar.exe -a -c -f "$(cygpath -w "$DIST/$name.zip")" "$name")
  else
    echo "Need 'zip' to package." >&2
    exit 1
  fi
  echo "✔ dist/$name.zip"
}

build themes/interior-specifics
build plugins/interior-specifics-core

stage themes/interior-specifics interior-specifics
stage plugins/interior-specifics-core interior-specifics-core

zipdir interior-specifics
zipdir interior-specifics-core
