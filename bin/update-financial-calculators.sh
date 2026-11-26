#!/usr/bin/env bash
# Updates the bundled @wpgenius/financial-calculators to a release.
#
#   bin/update-financial-calculators.sh v1.2.0
#
# The library is an npm dependency (package.json), so GitHub's dependency graph
# lists it. The zip ships no node_modules, so its readable ES module build is
# copied into src/vendor/ and bundled into the runtime by `npm run build`.
# CI runs `npm run vendor:financial-calculators:check` to keep the two in sync.
set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/.."

TAG=${1:?Give the release tag, e.g. v1.2.0}

npm install --save-exact=false "@wpgenius/financial-calculators@^${TAG#v}"
npm run vendor:financial-calculators
npm run build
echo "Updated to ${TAG#v}. Commit package.json, package-lock.json, src/vendor, build/ and the docs."
