import { test, expect } from '@playwright/test';
import { login, openHomeEntry } from './helpers.js';

test('typing a number, saving, and reloading round-trips correctly', async ({ page }) => {
    await login(page);
    await openHomeEntry(page);

    const telInput = page.locator('input[type="tel"]');
    await telInput.click({ clickCount: 3 });
    await telInput.type('4155552671');
    await page.waitForTimeout(800);

    await page.click('button:has-text("Save & Publish")');
    await page.waitForTimeout(2000);

    await openHomeEntry(page);

    const reloadedValue = await page.locator('input[type="tel"]').inputValue();
    expect(reloadedValue).toBe('(415) 555-2671');

    const selectedFlag = page.locator('.iti__selected-flag');
    await expect(selectedFlag).toHaveAttribute('title', /United States/);
});
