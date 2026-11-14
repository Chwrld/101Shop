#!/usr/bin/env bash
#
# Build a deploy tree for InfinityFree (Method B — flattened root).
#
# InfinityFree serves htdocs/ as the document root. This app expects
# public/ to be the document root with app/ one level above it, so the
# contents of public/ are flattened to the top of the output tree.
#
# Usage:
#   bash scripts/build-infinityfree.sh
#   php -S localhost:8000 -t dist-infinityfree   # verify locally first
#
# Then upload the CONTENTS of dist-infinityfree/ into htdocs/.
#
set -euo pipefail

cd "$(dirname "$0")/.."

OUT=dist-infinityfree
rm -rf "$OUT"
mkdir -p "$OUT"

# 1. Flatten public/ into the document root
cp -r public/. "$OUT/"

# 2. Copy the rest alongside
cp -r app vendor storage "$OUT/"

# 3. The runtime config files are created on the server, never committed.
rm -f "$OUT/app/config/database.local.php" "$OUT/app/config/mail.local.php"

# 4. Shift ONLY the references that point at app/, vendor/ or storage/.
#    Files inside public/ moved together, so their relative paths must stay
#    unchanged — a blanket sed breaks every `require bootstrap.php`.
#    See scripts/flatten-infinityfree.php for the exact rule.
php scripts/flatten-infinityfree.php "$OUT"

echo "Built $OUT/ - upload its contents into htdocs/"
