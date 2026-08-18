#!/usr/bin/env bash
# Prepares the Testbench workbench app (a real, disposable Statamic site
# used only for Playwright to drive) so it boots correctly. Safe to re-run.
set -e
cd "$(dirname "$0")/.."

SKELETON="vendor/orchestra/testbench-core/laravel"

if [ ! -f testbench.yaml ]; then
    vendor/bin/testbench workbench:install --no-interaction --basic
fi

vendor/bin/testbench migrate --no-interaction --force
vendor/bin/testbench statamic:install --no-interaction --ansi

php tests-e2e/seed-addon-manifest.php

# Composer::installedVersion() (used by the CP's licensing/update check)
# reads composer.lock relative to the skeleton's own base path, which has
# no composer.lock of its own. Mirror ours in so it resolves.
cp composer.lock "$SKELETON/composer.lock"

vendor/bin/testbench vendor:publish --tag=statamic-phone-number-fieldtype --force --no-interaction --ansi
