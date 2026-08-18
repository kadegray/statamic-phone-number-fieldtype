# Changelog

## 1.0.3 (2023-05-20)

Documentation improvements to the README.

## 1.0.2 (2023-05-20)

Fixed a bug where the phone number wasn't being saved to the entry. The `intl-tel-input` validation script (`utils.js`) wasn't being registered as a Control Panel script alongside `addon.js`, so number validation/formatting didn't run correctly.

## 1.0.1 (2023-04-02)

Fixed missing country flag images in the field's country selector dropdown. Also corrected `.gitignore` so it only ignores the addon's own `vendor` directory instead of any nested `vendor` directory.

## 1.0.0 (2023-04-02)

Initial release. Adds the Phone Number fieldtype — an international phone input (backed by `giggsey/libphonenumber-for-php`) that stores values in E164 format — along with the `e164_to_national` and `e164_to_international` Antlers modifiers for displaying stored numbers.
