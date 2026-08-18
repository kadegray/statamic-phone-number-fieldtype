export const HOME_ENTRY_URL = '/cp/collections/pages/entries/e2e00000-0000-0000-0000-000000000001/edit';

export async function login(page) {
    await page.goto('/cp/auth/login');
    await page.fill('input[name="email"]', 'e2e@example.com');
    await page.fill('input[name="password"]', 'e2e-testing-pw');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => {}),
        page.click('button[type="submit"]'),
    ]);
}

export async function dismissLicensingAlert(page) {
    const snooze = page.locator('button:has-text("Snooze")');
    if (await snooze.isVisible().catch(() => false)) {
        await snooze.click();
        await page.waitForTimeout(300);
    }
}

export async function openHomeEntry(page) {
    await page.goto(HOME_ENTRY_URL);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await dismissLicensingAlert(page);
}
