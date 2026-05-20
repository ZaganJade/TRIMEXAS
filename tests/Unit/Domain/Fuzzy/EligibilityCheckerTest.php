<?php

use App\Domain\Fuzzy\CandidateInput;
use App\Domain\Fuzzy\EligibilityChecker;

function makeCandidate(array $overrides = []): CandidateInput
{
    return new CandidateInput(
        candidateId: $overrides['candidateId'] ?? '1',
        ipk: $overrides['ipk'] ?? 3.5,
        penghasilanOrtu: $overrides['penghasilanOrtu'] ?? 4_000_000,
        prestasiAkademis: $overrides['prestasiAkademis'] ?? 20,
        prestasiNonAkademis: $overrides['prestasiNonAkademis'] ?? 20,
        tanggungan: $overrides['tanggungan'] ?? 3,
        statusMahasiswa: $overrides['statusMahasiswa'] ?? 'aktif',
        semester: $overrides['semester'] ?? 4,
        approvalStatus: $overrides['approvalStatus'] ?? 'approved',
    );
}

it('passes all four gates for an ideal candidate', function () {
    $result = (new EligibilityChecker())->check(makeCandidate());

    expect($result->eligible)->toBeTrue();
    expect($result->reasons)->toBe([]);
});

it('fails when IPK is below 3.0', function () {
    $result = (new EligibilityChecker())->check(makeCandidate(['ipk' => 2.9]));

    expect($result->eligible)->toBeFalse();
    expect($result->reasons)->toBe(['IPK < 3.0']);
});

it('fails when semester is above 6', function () {
    $result = (new EligibilityChecker())->check(makeCandidate(['semester' => 8]));

    expect($result->eligible)->toBeFalse();
    expect($result->reasons)->toBe(['Semester > 6']);
});

it('fails when status is not aktif', function () {
    $result = (new EligibilityChecker())->check(makeCandidate(['statusMahasiswa' => 'cuti']));

    expect($result->eligible)->toBeFalse();
    expect($result->reasons[0])->toContain('Status bukan aktif');
});

it('fails when approval status is not approved', function () {
    $result = (new EligibilityChecker())->check(makeCandidate(['approvalStatus' => 'pending']));

    expect($result->eligible)->toBeFalse();
    expect($result->reasons)->toBe(['Akun belum di-approve']);
});

it('reports multiple reasons when several gates fail', function () {
    $result = (new EligibilityChecker())->check(makeCandidate([
        'semester' => 8,
        'ipk' => 2.5,
    ]));

    expect($result->eligible)->toBeFalse();
    expect($result->reasons)->toBe(['Semester > 6', 'IPK < 3.0']);
});

it('reports all four reasons when every gate fails', function () {
    $result = (new EligibilityChecker())->check(makeCandidate([
        'statusMahasiswa' => 'cuti',
        'semester' => 9,
        'ipk' => 2.4,
        'approvalStatus' => 'pending',
    ]));

    expect($result->eligible)->toBeFalse();
    expect($result->reasons)->toHaveCount(4);
});
