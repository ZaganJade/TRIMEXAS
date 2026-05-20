<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentAchievement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AchievementVerificationController extends Controller
{
    public function verify(Request $request, StudentAchievement $achievement): RedirectResponse
    {
        $note = (string) $request->input('note', '');
        $achievement->forceFill([
            'verified_by_admin' => true,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'verification_note' => $note !== '' ? $note : null,
        ])->save();

        return back()->with('success', 'Entri prestasi diverifikasi.');
    }

    public function unverify(Request $request, StudentAchievement $achievement): RedirectResponse
    {
        $achievement->forceFill([
            'verified_by_admin' => false,
            'verified_by' => null,
            'verified_at' => null,
            'verification_note' => null,
        ])->save();

        return back()->with('success', 'Verifikasi entri dibatalkan.');
    }
}
