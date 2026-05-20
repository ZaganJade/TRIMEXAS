<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mahasiswa\UpdateOwnProfileRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(): Response
    {
        $student = request()->user()->student;
        abort_unless($student, 422, 'Profil mahasiswa tidak ditemukan.');

        return Inertia::render('Mahasiswa/Profile', [
            'student' => [
                'id' => $student->id,
                'nim' => $student->nim,
                'full_name' => $student->full_name,
                'semester' => $student->semester,
                'ipk' => (float) $student->ipk,
                'penghasilan_ortu' => (int) $student->penghasilan_ortu,
                'tanggungan' => (int) $student->tanggungan,
                'phone' => $student->phone,
                'address' => $student->address,
            ],
        ]);
    }

    public function update(UpdateOwnProfileRequest $request): RedirectResponse
    {
        $student = $request->user()->student;
        abort_unless($student, 422, 'Profil mahasiswa tidak ditemukan.');

        $student->fill($request->validated())->save();

        return back()->with('success', 'Profil disimpan.');
    }
}
