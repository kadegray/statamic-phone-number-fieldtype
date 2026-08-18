import { test, expect } from '@playwright/test';
import { login, dismissLicensingAlert } from './helpers.js';

// Regression test for the fatal error fixed in commit a30e620: Statamic 6
// requires filter classes to implement isComplete(), which the addon's
// now-removed custom PhoneNumberFieldtypeFilter never had. Applying or
// changing the field's filter in a collection listing threw
// "Call to undefined method ...::isComplete()".
//
// Uses two fixture entries (Home: +12025551234, About: +61412345678) so a
// working filter is actually distinguishable from a no-op one.
test('the phone number field can be used as a collection listing filter', async ({ page }) => {
    await login(page);

    await page.goto('/cp/collections/pages');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await dismissLicensingAlert(page);

    await expect(page.getByRole('link', { name: 'Home' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'About' })).toBeVisible();

    await page.click('button:has-text("Filters")');
    await page.waitForTimeout(500);
    await page.click('input[placeholder="Add Field"]');
    await page.waitForTimeout(400);
    await page.click('text=Phone Number');
    await page.waitForTimeout(500);

    await page.fill('input[placeholder="Value"]', '2025');
    await page.waitForTimeout(400);
    await page.click('button:has-text("Done")');
    await page.waitForTimeout(1000);

    let bodyText = await page.textContent('body');
    expect(bodyText).not.toContain('isComplete');
    expect(bodyText).not.toContain('Undefined method');
    await expect(page.getByRole('link', { name: 'Home' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'About' })).not.toBeVisible();

    // Change the existing filter's value — this is specifically what
    // triggered isComplete() and the fatal error, since it re-validates an
    // already-applied filter rather than a newly-added one.
    await page.click('button:has-text("Filters")');
    await page.waitForTimeout(500);
    await page.fill('input[placeholder="Value"]', '9999');
    await page.waitForTimeout(400);
    await page.click('button:has-text("Done")');
    await page.waitForTimeout(1000);

    bodyText = await page.textContent('body');
    expect(bodyText).not.toContain('isComplete');
    expect(bodyText).not.toContain('Undefined method');
    await expect(page.getByRole('link', { name: 'Home' })).not.toBeVisible();
    await expect(page.getByRole('link', { name: 'About' })).not.toBeVisible();
});
