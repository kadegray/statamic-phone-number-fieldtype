# Changelog

## Unreleased

Adds the addon's first test coverage — previously every bug fixed in 2.0.0 was only caught by manual testing.

- **PHPUnit suite** (`tests/`, `vendor/bin/phpunit`): unit/feature tests for the fieldtype, controller, both modifiers, and service provider registration, run via [Orchestra Testbench](https://packages.tools/testbench) and Statamic's `AddonTestCase`. Includes direct regression tests: sequential locale requests within a single test process (catches the gettext caching bug from 2.0.0 if it ever comes back), and a feature test building real entries and running the listing filter end-to-end (catches the `isComplete()` fatal error).
- **Playwright suite** (`tests-e2e/`, `npm run test:e2e`): end-to-end tests driving a real Control Panel and front end in a browser, against a disposable Statamic site scaffolded inside the repo via Testbench's "workbench" feature (`workbench/`, `testbench.yaml`) rather than depending on an external install. Covers field rendering, the save/reload round-trip, CP locale handling, the listing filter, and front-end modifier rendering.
- **`TESTING.md`**: documents how to run both suites and what `tests-e2e/setup-workbench.sh` does to get a plain Testbench workbench booting a fully working Statamic site — none of it is default Testbench behavior: seeding Statamic's addon manifest directly (it only recognizes installed dependencies, never the root package being tested), mirroring `composer.lock` into the skeleton for the CP's licensing check, and switching the skeleton to file-based users with Statamic's own `statamic` auth driver (Laravel's default `eloquent` driver silently rejects correct file-based credentials).

Both fixed bugs from 2.0.0 (the listing-filter fatal error and the locale-caching bug) were spot-checked by temporarily reintroducing them and confirming the new tests actually fail, then reverting.

## 2.0.0 (2026-08-17)

Migrates the Control Panel fieldtype to Statamic 6, which was completely broken before this branch — the CP would show "Component phone_number-fieldtype does not exist" for any blueprint using the field, because the compiled bundle referenced a bare global `Fieldtype` mixin and other Vue 2-era APIs that no longer exist in Statamic 6's Vue 3 Control Panel.

**Breaking**: `composer.json` now requires `statamic/cms: ^6.0`. This version will not install on Statamic 3-5.

- Rebuilt the CP fieldtype component (`resources/js/components/fieldtypes/PhoneNumberFieldtype.vue`) using Vue 3's Composition API: `import { Fieldtype } from '@statamic/cms'` instead of the old bare global, `import { Input } from '@statamic/cms/ui'` for the input itself, and `update(value)` instead of `this.$emit('input', value)` to match the current fieldtype contract.
- Replaced the Laravel Mix/webpack build with Vite (`vite.config.js`, `@statamic/cms/vite-plugin`, `laravel-vite-plugin`), matching Statamic's current addon tooling. Built assets are committed under `resources/dist/build` so installing the addon doesn't require a Node toolchain.
- Fixed a fatal error (`Call to undefined method PhoneNumberFieldtypeFilter::isComplete()`) when applying or changing the field's filter in a collection listing. Statamic 6 requires filter classes to implement `isComplete()`; rather than patch the addon's own filter — which only duplicated Statamic's built-in one, minus its operator ("Contains"/"Is"/"Isn't"/etc.) selector — removed the `PhoneNumberFieldtype::filter()` override entirely so it now uses Statamic's own `FieldtypeFilter`.
- Fixed the `{lang}/countries` route always returning English country names regardless of the requested locale, plus a deeper issue underneath it: `sokil/php-isocodes`'s default gettext-based driver caches translations at the process level, so once one locale's catalog loaded, every later request in that PHP worker kept returning it no matter what locale was actually requested. Switched both `PhoneNumberFieldtypeController::getCountries()` and the fieldtype's config-panel country list to `sokil/php-isocodes`'s `SymfonyTranslationDriver`, which loads translations per-instance instead of through global `setlocale()` state.
- Cut the JS bundle loaded on *every* Control Panel page from ~292KB (70KB gzipped) to ~42KB (15KB gzipped) by lazy-loading `intl-tel-input`'s validation library (`utils.js`, ~250KB) again, only when a phone number field actually mounts, instead of bundling it directly into the main script.

## 1.0.3 (2023-05-20)

README-only release, no code changes. Reworded the features list into bold-labeled bullet points (e.g. "**E164 Format**: ...") instead of plain sentences, and rewrote several usage-example paragraphs (fieldtype configuration, modifier examples) for clarity.

## 1.0.2 (2023-05-20)

Fixed a bug where a phone number typed into the field never actually made it into the saved entry.

The field's `mounted()` hook always configured `intl-tel-input` with `utilsScript: '/vendor/statamic-phone-number-fieldtype/js/utils.js'`, telling it to lazy-load its validation library from that URL the first time it's needed. `public/js/utils.js` existed in the package since 1.0.0, but was never added to the addon's `$scripts` in `ServiceProvider.php` — only `addon.js` was. Since `$scripts` is what Statamic actually publishes to `public/vendor/{addon}/js/...` on install, `utils.js` was never copied to that URL on a real site, so the lazy fetch 404'd and the global `intlTelInputUtils` was never defined.

The field's `inputEvent()` handler unconditionally referenced `intlTelInputUtils.validationError.*` on every keystroke, so with the global undefined this threw a `ReferenceError` before either of the handler's two `this.$emit('input', ...)` calls could run — meaning the input's `v-model` binding never updated and the typed value never reached the publish form's save payload. The field would happily accept keystrokes and *look* filled in, but nothing was ever wired back to Statamic.

Fixed by adding `public/js/utils.js` to `$scripts` alongside `addon.js`, so it's published to the expected path (and, as a side effect, is now loaded eagerly rather than the originally-intended lazy fetch).

## 1.0.1 (2023-04-02)

Fixed missing country flag icons in the country-select dropdown. The compiled bundle's CSS pulled the flag sprite images from `node_modules/intl-tel-input/build/img/` via webpack's asset pipeline (`__webpack_require__` on the `.png` files), but the emitted image files themselves were never committed alongside `public/js/addon.js` in 1.0.0 — only the JS/CSS were checked in, not whatever webpack emitted for the image imports. Fixed by committing the flag sprites directly as `public/images/vendor/intl-tel-input/build/flags.png` and `flags@2x.png` (for high-DPI/retina displays), published via the addon's existing `$publishables` mapping (`public/images` → `images`) so they land at a stable URL on install.

Also:
- Fixed `.gitignore` incorrectly ignoring a `vendor` directory anywhere in the repo tree instead of just the addon's own root-level one (`vendor` → `/vendor`).
- Added markdown hard line-breaks (trailing double-spaces) after each example phone number in the README so the international/national examples render on separate lines instead of running together.

## 1.0.0 (2023-04-02)

Initial release.

- **`PhoneNumberFieldtype`** — an international phone number fieldtype. The Control Panel input is built on [`intl-tel-input`](https://intl-tel-input.com/), giving a country-select dropdown plus a live-formatted phone input; values are normalized and stored in [E164 format](https://www.twilio.com/docs/glossary/what-e164) (e.g. `+12015550123`) regardless of how the user typed it, using `giggsey/libphonenumber-for-php` for parsing/validation.
- **Fieldtype configuration options**: toggle to show/hide the country select, set an initial country, a preferred-countries list (shown first in the dropdown), an exclude list, and an "only these countries" allow-list. Country names for these config options are sourced from `sokil/php-isocodes`.
- **`e164_to_national`** and **`e164_to_international`** Antlers modifiers, for rendering a stored E164 value back out in national (`(201) 555-0123`) or international (`+1 201-555-0123`) format.
- **`PhoneNumberFieldtypeFilter`** — a custom collection-listing filter for the fieldtype (a plain text "contains" search against the stored value).
- A `{lang}/countries` action route + `PhoneNumberFieldtypeController`, used by the CP component to fetch localized country names when the Control Panel's language isn't English.
- Initial README with installation and usage docs, plus a follow-up documentation pass adding screenshots of the field in the CP and a tweaked marketplace-listing description in `composer.json`.
