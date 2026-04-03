<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppConfigController extends Controller
{
    public function index(): View
    {
        $configs = AppConfig::orderBy('group')->orderBy('label')->get()->groupBy('group');

        return view('admin.system-config', compact('configs'));
    }

    public function update(Request $request, AppConfig $config): RedirectResponse
    {
        $request->validate([
            'value' => ['required', 'string', 'max:500'],
        ]);

        $config->update(['value' => $request->input('value')]);

        return back()->with('success', "Config \"{$config->label}\" berhasil diperbarui.");
    }
}
