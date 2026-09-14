<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\AuditLog;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('admin.profile', [
            'profileUser' => $user,
            'profileStats' => [
                'reports_created' => Report::query()->whereBelongsTo($user, 'createdByUser')->count(),
                'activities_logged' => AuditLog::query()->whereBelongsTo($user, 'actorUser')->count(),
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->safe()->only(['name', 'username', 'email', 'password']);
        $passwordChanged = ($validated['password'] ?? '') !== '';

        if (! $passwordChanged) {
            unset($validated['password']);
        }

        $oldValues = $user->only(['name', 'username', 'email']);
        $user->update($validated);

        AuditLog::create([
            'actor_user_id' => $user->id,
            'subject_type' => 'user',
            'subject_id' => $user->id,
            'module' => 'profiles',
            'action' => 'updated',
            'description' => "Profil akun {$user->username} diperbarui.",
            'old_values' => $oldValues,
            'new_values' => [
                ...$user->only(['name', 'username', 'email']),
                'password_changed' => $passwordChanged,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.profile')->with('status', 'Profil berhasil diperbarui.');
    }
}
