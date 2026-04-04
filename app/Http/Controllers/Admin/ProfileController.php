<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /** @var string[] */
    private array $colorOptions = ['blue', 'purple', 'green', 'orange', 'pink', 'red', 'teal', 'indigo'];

    public function index(): View
    {
        $user = AdminUser::findOrFail(session('admin_user.id'));

        return view('admin.profile.index', [
            'user' => $user,
            'colorOptions' => $this->colorOptions,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = AdminUser::findOrFail(session('admin_user.id'));

        $request->validate([
            'name' => 'required|string|max:100',
            'avatar_color' => 'required|in:'.implode(',', $this->colorOptions),
        ]);

        $data = [
            'name' => $request->name,
            'avatar_color' => $request->avatar_color,
        ];

        if ($request->filled('password')) {
            $request->validate([
                'current_password' => 'required',
                'password' => 'min:8|confirmed',
            ]);

            if (! $user->checkPassword($request->current_password)) {
                return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])->withInput();
            }

            $data['password'] = $request->password;
        }

        $user->update($data);

        session([
            'admin_user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_color' => $user->avatar_color,
            ],
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
