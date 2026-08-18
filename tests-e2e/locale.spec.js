import { test, expect } from '@playwright/test';
import { login, openHomeEntry } from './helpers.js';

// The CP fieldtype only fetches localized country names when
// document.documentElement.lang !== 'en'. Rewriting the served HTML's lang
// attribute (rather than setting it client-side after load, which races
// against Vue mounting) reliably exercises that code path.
test('fetches localized country names when the CP locale is non-English', async ({ page }) => {
    const countryRequests = [];
    page.on('response', (res) => {
        if (res.url().includes('/countries')) countryRequests.push(`${res.url()} -> ${res.status()}`);
    });
    const consoleErrors = [];
    page.on('console', (msg) => {
        if (msg.type() === 'error') consoleErrors.push(msg.text());
    });

    await page.route('**/cp/**', async (route) => {
        const response = await route.fetch();
        const contentType = response.headers()['content-type'] || '';
        if (!contentType.includes('text/html')) {
            return route.fulfill({ response });
        }
        const body = await response.text();
        return route.fulfill({
            response,
            body: body.replace('lang="en"', 'lang="fr"'),
        });
    });

    await login(page);
    await openHomeEntry(page);

    expect(countryRequests.some((r) => r.includes('/fr/countries') && r.includes('200'))).toBe(true);

    const telInput = page.locator('input[type="tel"]');
    await expect(telInput).toBeVisible();

    const relevantErrors = consoleErrors.filter((e) => !e.includes('invalid domain') && !e.includes('409'));
    expect(relevantErrors).toEqual([]);
});
