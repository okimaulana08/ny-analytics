import { test, expect } from '@playwright/test';

const LOGIN_URL = '/admin/login';
const VALID_EMAIL = 'admin@novelya.id';
const VALID_PASSWORD = 'secret123';

test.describe('Admin Login', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto(LOGIN_URL);
    });

    test('shows error message on wrong email', async ({ page }) => {
        await page.locator('#email').fill('salah@example.com');
        await page.locator('#password').fill('wrongpassword');
        await page.getByRole('button', { name: 'Masuk' }).click();

        await expect(page.getByText('Email atau password salah.')).toBeVisible();
    });

    test('shows error message on wrong password', async ({ page }) => {
        await page.locator('#email').fill(VALID_EMAIL);
        await page.locator('#password').fill('wrongpassword');
        await page.getByRole('button', { name: 'Masuk' }).click();

        await expect(page.getByText('Email atau password salah.')).toBeVisible();
    });

    test('redirects to dashboard after successful login', async ({ page }) => {
        await page.locator('#email').fill(VALID_EMAIL);
        await page.locator('#password').fill(VALID_PASSWORD);
        await page.getByRole('button', { name: 'Masuk' }).click();

        await page.waitForURL(/\/admin(?!\/login)/, { timeout: 10000 });
        await expect(page).not.toHaveURL(/\/login/);
    });
});
