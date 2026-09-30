<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ProfileUpdateRequest;
use App\Http\Requests\Profile\StoreSignatureRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Services\Shared\AuditLogService;
use App\Services\Shared\SignatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = request()->user()->load(['department', 'position']);
        $this->authorize('updateProfile', $user);

        return view('shared.profile.show', [
            'user' => $user,
            'signature' => app(SignatureService::class)->activeSignature($user),
        ]);
    }

    public function update(ProfileUpdateRequest $request, AuditLogService $auditLog): RedirectResponse
    {
        $user = $request->user();
        $this->authorize('updateProfile', $user);
        $user->fill($request->validated())->save();
        $auditLog->record($user, $user, 'PROFILE_UPDATED', ['changed_fields' => array_keys($request->validated())]);

        return redirect()->route($user->role->value.'.profile')->with('status', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(UpdatePasswordRequest $request, AuditLogService $auditLog): RedirectResponse
    {
        $user = $request->user();
        $this->authorize('updateProfile', $user);
        $user->forceFill(['password' => $request->validated('password')])->save();
        $auditLog->record($user, $user, 'PASSWORD_CHANGED');
        $request->session()->regenerate();

        return redirect()->route($user->role->value.'.profile')->with('status', 'Kata sandi berhasil diubah.');
    }

    public function storeSignature(StoreSignatureRequest $request, SignatureService $service): RedirectResponse
    {
        $user = $request->user();
        $this->authorize('updateProfile', $user);
        $service->upload($user, $request->file('signature'));

        return redirect()->route($user->role->value.'.profile')->with('status', 'Tanda tangan berhasil disimpan.');
    }

    public function signature(SignatureService $service): Response
    {
        $user = request()->user();
        $this->authorize('viewSignature', $user);
        $signature = $service->activeSignature($user);
        abort_if($signature === null, 404);

        return response($service->contents($signature), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="tanda-tangan.png"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
