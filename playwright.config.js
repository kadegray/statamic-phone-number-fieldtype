import { defineConfig } from '@playwright/test';

const PORT = 8981;
const BASE_URL = `http://127.0.0.1:${PORT}`;

export default defineConfig({
    testDir: './tests-e2e',
    testMatch: '*.spec.js',
    fullyParallel: false,
    workers: 1,
    retries: 0,
    reporter: 'list',
    use: {
        baseURL: BASE_URL,
        trace: 'retain-on-failure',
    },
    webServer: {
        command: `bash tests-e2e/setup-workbench.sh && vendor/bin/testbench serve --port=${PORT} --no-interaction`,
        url: `${BASE_URL}/cp/auth/login`,
        reuseExistingServer: !process.env.CI,
        timeout: 180 * 1000,
    },
});
