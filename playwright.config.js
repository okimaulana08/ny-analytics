import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './tests/e2e',
    use: {
        baseURL: 'https://novelya-analytics.test',
        ignoreHTTPSErrors: true,
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],
});
