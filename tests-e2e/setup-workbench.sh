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

# Seed a minimal "pages" collection/blueprint/entry with a phone_number
# field, plus a front-end template exercising both modifiers, and a CP
# super user for Playwright to log in with.
mkdir -p "$SKELETON/content/collections/pages"
mkdir -p "$SKELETON/resources/blueprints/collections/pages"
cp tests-e2e/fixtures/collections/pages.yaml "$SKELETON/content/collections/pages.yaml"
cp tests-e2e/fixtures/blueprints/pages.yaml "$SKELETON/resources/blueprints/collections/pages/pages.yaml"
cp tests-e2e/fixtures/entries/home.md "$SKELETON/content/collections/pages/home.md"
cp tests-e2e/fixtures/entries/about.md "$SKELETON/content/collections/pages/about.md"
cp tests-e2e/fixtures/views/home.antlers.html "$SKELETON/resources/views/home.antlers.html"

# Statamic's default install uses the eloquent user repository, which
# needs migrations this skeleton doesn't have. Use flat-file users instead,
# matching how the addon's fixtures/content are already file-based.
sed -i.bak "s/'repository' => 'eloquent'/'repository' => 'file'/" "$SKELETON/config/statamic/users.php"
rm -f "$SKELETON/config/statamic/users.php.bak"

# The skeleton's stock config/auth.php still points the "users" auth
# provider at Laravel's default eloquent driver. Statamic registers its own
# "statamic" auth provider driver (which reads from the file-based user
# repository above); without switching to it, login always fails even
# though the file-based user genuinely exists with a matching password.
sed -i.bak "s/'driver' => 'eloquent',/'driver' => 'statamic',/" "$SKELETON/config/auth.php"
rm -f "$SKELETON/config/auth.php.bak"

vendor/bin/testbench statamic:stache:refresh --no-interaction --ansi

vendor/bin/testbench tinker --execute='
$email = "e2e@example.com";
if (! \Statamic\Facades\User::findByEmail($email)) {
    \Statamic\Facades\User::make()->email($email)->password("e2e-testing-pw")->makeSuper(true)->save();
    echo "created e2e user\n";
} else {
    echo "e2e user already exists\n";
}
'
