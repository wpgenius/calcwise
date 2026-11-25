#!/usr/bin/env bash
# Copies a release of wpgenius/financial-calculators-js into src/vendor/.
#
#   bin/update-financial-calculators.sh v1.0.0
#
# The plugin pins one release: the ES module build is committed and bundled
# into the runtime by `npm run build`. Needs the GitHub CLI (gh).
set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/.."

TAG=${1:?Give the release tag, e.g. v1.0.0}
REPO=wpgenius/financial-calculators-js
DIR=src/vendor/financial-calculators

gh release download "$TAG" --repo "$REPO" --pattern financial-calculators.esm.js --dir "$DIR" --clobber
perl -pi -e "s/^Version: .*/Version: ${TAG#v}/" "$DIR/README.md"
echo "Updated $DIR to $TAG. Run 'npm run build' and commit src/vendor and build/."
