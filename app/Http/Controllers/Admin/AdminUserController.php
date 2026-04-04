<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = AdminUser::orderByDesc('created_at')->get();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:admin_users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        AdminUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(AdminUser $adminUser): View
    {
        return view('admin.users.edit', [
            'user' => $adminUser,
            'colorOptions' => $this->colorOptions(),
        ]);
    }

    public function update(Request $request, AdminUser $adminUser): RedirectResponse
    {
        $rules = [
            'name' => 'required|string|max:100',
            'email' => [
                'required', 'email',
                Rule::unique('admin_users', 'email')->ignore($adminUser->id),
            ],
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'min:8|confirmed';
        }

        $request->validate($rules);

        $data = ['name' => $request->name];

        if (! $adminUser->isSuperAdmin()) {
            $data['email'] = $request->email;
        }

        $isEditingOwnAccount = $adminUser->id === session('admin_user.id');

        if ($request->filled('password') && (! $adminUser->isSuperAdmin() || $isEditingOwnAccount)) {
            $data['password'] = $request->password;
        }

        $adminUser->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function toggleActive(AdminUser $adminUser): RedirectResponse
    {
        if ($adminUser->isSuperAdmin()) {
            return back()->with('error', 'Super Admin tidak dapat dinonaktifkan.');
        }

        $currentId = session('admin_user.id');
        if ($adminUser->id === $currentId) {
            return back()->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        $adminUser->update(['is_active' => ! $adminUser->is_active]);

        return back()->with('success', 'Status user diperbarui.');
    }

    public function destroy(AdminUser $adminUser): RedirectResponse
    {
        if ($adminUser->isSuperAdmin()) {
            return back()->with('error', 'Super Admin tidak dapat dihapus.');
        }

        $currentId = session('admin_user.id');
        if ($adminUser->id === $currentId) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $adminUser->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }

    /** @return string[] */
    private function colorOptions(): array
    {
        return ['blue', 'purple', 'green', 'orange', 'pink', 'red', 'teal', 'indigo'];
    }
}
