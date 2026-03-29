import { test, expect } from '@playwright/test';

const LOGIN_URL = '/admin/login';
const VALID_EMAIL = 'admin@novelya.id';
const VALID_PASSWORD = 'secret123';

test.describe('Admin Logout', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto(LOGIN_URL);
        await page.locator('#email').fill(VALID_EMAIL);
        await page.locator('#password').fill(VALID_PASSWORD);
        await page.getByRole('button', { name: 'Masuk' }).click();
        await page.waitForURL(/\/admin(?!\/login)/, { timeout: 10000 });
    });

    test('redirects to login page after logout', async ({ page }) => {
        await page.locator('button[title="Logout"]').click();

        await page.waitForURL(/\/admin\/login/, { timeout: 10000 });
        await expect(page).toHaveURL(/\/admin\/login/);
    });

    test('cannot access dashboard after logout', async ({ page }) => {
        await page.locator('button[title="Logout"]').click();
        await page.waitForURL(/\/admin\/login/, { timeout: 10000 });

        await page.goto('/admin/dashboard');
        await expect(page).toHaveURL(/\/admin\/login/);
    });
});
