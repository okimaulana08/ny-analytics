<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:sqlite.admin_users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        AdminUser::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function toggleActive(AdminUser $adminUser): RedirectResponse
    {
        // Prevent self-deactivation
        $currentId = session('admin_user.id');
        if ($adminUser->id === $currentId) {
            return back()->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        $adminUser->update(['is_active' => !$adminUser->is_active]);

        return back()->with('success', 'Status user diperbarui.');
    }

    public function destroy(AdminUser $adminUser): RedirectResponse
    {
        $currentId = session('admin_user.id');
        if ($adminUser->id === $currentId) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $adminUser->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}
