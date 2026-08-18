import { test, expect } from '@playwright/test';

// Exercises e164_to_national / e164_to_international on the front end,
// against tests-e2e/fixtures/views/home.antlers.html which renders both
// modifiers for the fixture entry's baked-in phone_number value.
test('renders phone number modifiers on the front end', async ({ page }) => {
    await page.goto('/home');

    await expect(page.locator('#phone-raw')).toHaveText('+12025551234');
    await expect(page.locator('#phone-national')).toHaveText('(202) 555-1234');
    await expect(page.locator('#phone-international')).toHaveText('+1 202-555-1234');
});
