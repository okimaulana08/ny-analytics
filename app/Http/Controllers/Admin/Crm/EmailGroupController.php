<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Crm\StoreEmailGroupRequest;
use App\Http\Requests\Admin\Crm\UpdateEmailGroupRequest;
use App\Models\EmailGroup;
use App\Models\EmailGroupMember;
use App\Services\EmailGroupResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailGroupController extends Controller
{
    public function index(): View
    {
        $groups = EmailGroup::withCount('members')->withCount('campaigns')->orderBy('name')->get();

        return view('admin.crm.groups.index', compact('groups'));
    }

    public function create(): View
    {
        return view('admin.crm.groups.create');
    }

    public function store(StoreEmailGroupRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $group = EmailGroup::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'criteria' => $data['type'] === 'dynamic' ? $data['criteria'] : null,
            'is_active' => true,
        ]);

        if ($data['type'] === 'static' && ! empty($data['members'])) {
            foreach ($data['members'] as $member) {
                if (! empty($member['email'])) {
                    EmailGroupMember::create([
                        'email_group_id' => $group->id,
                        'email' => $member['email'],
                        'name' => $member['name'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('admin.crm.groups.index')
            ->with('success', 'Grup email berhasil dibuat.');
    }

    public function edit(EmailGroup $group): View
    {
        $group->load('members');

        return view('admin.crm.groups.edit', compact('group'));
    }

    public function update(UpdateEmailGroupRequest $request, EmailGroup $group): RedirectResponse
    {
        $data = $request->validated();

        $group->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'criteria' => $data['type'] === 'dynamic' ? $data['criteria'] : null,
        ]);

        if ($data['type'] === 'static') {
            $group->members()->delete();

            foreach ($data['members'] ?? [] as $member) {
                if (! empty($member['email'])) {
                    EmailGroupMember::create([
                        'email_group_id' => $group->id,
                        'email' => $member['email'],
                        'name' => $member['name'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('admin.crm.groups.index')
            ->with('success', 'Grup email berhasil diperbarui.');
    }

    public function destroy(EmailGroup $group): RedirectResponse
    {
        $group->delete();

        return redirect()->route('admin.crm.groups.index')
            ->with('success', 'Grup email berhasil dihapus.');
    }

    public function resolvePreview(EmailGroup $group): JsonResponse
    {
        $resolver = app(EmailGroupResolver::class);
        $recipients = $resolver->resolve($group);

        return response()->json([
            'count' => count($recipients),
            'sample' => array_slice(array_map(fn ($r) => [
                'email' => $r['email'],
                'name' => $r['name'],
            ], $recipients), 0, 10),
        ]);
    }
}
