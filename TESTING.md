# Testing

This addon has two test suites:

- **PHPUnit** (`tests/`) — unit/feature tests for the PHP side (fieldtype, controller, modifiers, service provider), run in isolation via [Orchestra Testbench](https://packages.tools/testbench) and Statamic's `AddonTestCase`. No browser involved.
- **Playwright** (`tests-e2e/`) — end-to-end tests that drive a real Statamic Control Panel and front end in a browser, covering behavior PHPUnit can't reach (Vue component rendering, the listing filter UI, CP locale handling).

## Prerequisites

```bash
composer install
npm install
npx playwright install chromium
```

## Running the PHPUnit suite

```bash
vendor/bin/phpunit
```

That's it — `AddonTestCase` boots a throwaway Laravel/Statamic app per test run; there's nothing to set up beforehand and it doesn't touch the Playwright workbench site described below.

## Running the Playwright suite

```bash
npm run test:e2e
```

This drives a real, disposable Statamic site scaffolded inside this repo (via Testbench's "workbench" feature — see `workbench/` and `testbench.yaml`), rather than depending on some other Statamic install existing on your machine. `playwright.config.js` starts it automatically: on `npm run test:e2e`, it runs `tests-e2e/setup-workbench.sh` (idempotent — safe to re-run) and then `vendor/bin/testbench serve`, waiting for the CP login page to respond before running specs.

To run a single spec:

```bash
npx playwright test tests-e2e/listing-filter.spec.js
```

To watch it run instead of headless:

```bash
npx playwright test --headed
```

If you want to poke around the workbench site manually instead of running specs, set it up once and serve it yourself:

```bash
bash tests-e2e/setup-workbench.sh
vendor/bin/testbench serve --port=8981
# http://127.0.0.1:8981/cp — log in as e2e@example.com / e2e-testing-pw
```

### What `setup-workbench.sh` does, and why

Getting a plain Testbench workbench to boot a working Statamic site (with this addon self-recognized as an installed addon, not just an autoloaded package) needs a few workarounds beyond the default scaffold. All of them are scripted, not manual:

- **Addon manifest seeding** (`tests-e2e/seed-addon-manifest.php`) — Statamic's addon discovery only recognizes packages listed in `vendor/composer/installed.json`, which never includes the root package. Since this addon *is* the workbench's root package (unlike a real consumer's site, where it's an installed dependency), it's otherwise invisible to Statamic's own addon discovery. This seeds the cached manifest directly.
- **`composer.lock` mirroring** — the CP's licensing/update check reads `composer.lock` relative to the skeleton's own base path, which has none of its own; the script copies ours in.
- **File-based users + the `statamic` auth driver** — the skeleton's default config uses Laravel's `eloquent` user provider and DB-backed users, which need migrations this skeleton doesn't have. The script switches `config/statamic/users.php` to the `file` repository and `config/auth.php`'s provider driver to `statamic` (Laravel's default `eloquent` driver silently rejects correct file-based credentials otherwise).
- **Fixture content** — seeds a `pages` collection/blueprint with a `phone_number` field, two entries with different phone numbers (`tests-e2e/fixtures/entries/`, needed so the listing-filter test can tell a working filter apart from a no-op one), a front-end template exercising both modifiers, and the `e2e@example.com` CP user the specs log in as.

If a spec starts failing with a login or "addon not found" style error, `bash tests-e2e/setup-workbench.sh` (safe to re-run) is the first thing to try — it's meant to bring the workbench back to a known-good state regardless of what's currently in `vendor/orchestra/testbench-core/laravel/`.

## Regression coverage

Several tests exist specifically to catch bugs found (and fixed) during the Statamic 6 migration, rather than just exercising happy paths:

| Bug | Caught by |
|---|---|
| CP fieldtype crashed with "Component ... does not exist" (Vue 2 → Vue 3 migration) | `tests-e2e/fieldtype-renders.spec.js` |
| Listing filter threw a fatal error (missing `isComplete()`) | `tests/Fieldtypes/PhoneNumberFieldtypeTest.php`, `tests/Feature/CollectionListingFilterTest.php`, `tests-e2e/listing-filter.spec.js` |
| Country names cached across requests regardless of requested locale (gettext process-caching) | `tests/Controllers/PhoneNumberFieldtypeControllerTest.php` |

This was verified directly: the fixed bugs were temporarily reintroduced and confirmed to fail the corresponding tests before being reverted.
