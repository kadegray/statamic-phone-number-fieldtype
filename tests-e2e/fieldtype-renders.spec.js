import { test, expect } from '@playwright/test';
import { login, openHomeEntry } from './helpers.js';

// Direct regression test for the bug that started this whole branch: the
// compiled CP bundle referenced Vue 2-era APIs (a bare global `Fieldtype`
// mixin) that don't exist under Statamic 6's Vue 3 Control Panel, so the
// field never rendered at all — just a "Component phone_number-fieldtype
// does not exist" message.
test('the phone number field renders in the CP without error', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', (msg) => {
        if (msg.type() === 'error') consoleErrors.push(msg.text());
    });
    page.on('pageerror', (err) => consoleErrors.push(err.message));

    await login(page);
    await openHomeEntry(page);

    const bodyText = await page.textContent('body');
    expect(bodyText).not.toContain('phone_number-fieldtype does not exist');

    const telInput = page.locator('input[type="tel"]');
    await expect(telInput).toBeVisible();

    const relevantErrors = consoleErrors.filter((e) => !e.includes('invalid domain') && !e.includes('409'));
    expect(relevantErrors).toEqual([]);
});
