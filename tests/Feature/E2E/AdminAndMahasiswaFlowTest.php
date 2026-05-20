<?php

use App\Models\SelectionBatch;
use App\Models\SelectionResult;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\User;
use Database\Seeders\CriteriaSeeder;
use Database\Seeders\OutputThresholdSeeder;
use Database\Seeders\RuleSeeder;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AccountApprovedNotification;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed([CriteriaSeeder::class, RuleSeeder::class, OutputThresholdSeeder::class]);
});

it('walks the full admin flow: login → approve → edit kriteria → run → ranking → audit → export', function () {
    Notification::fake();

    // 1. Login
    $admin = User::factory()->admin()->create();
    $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect();

    // 2. Approve a pending student
    $pending = User::factory()->pending()->create();
    Student::factory()->create([
        'user_id' => $pending->id,
        'nim' => 'E2E001',
        'full_name' => $pending->name,
        'ipk' => 3.85,
        'penghasilan_ortu' => 2_500_000,
        'tanggungan' => 4,
    ]);
    $this->post(route('admin.students.approve', ['user' => $pending->id]))->assertRedirect();
    expect($pending->fresh()->approval_status)->toBe(User::STATUS_APPROVED);
    Notification::assertSentTo($pending->fresh(), AccountApprovedNotification::class);

    // Add achievement entries so the student has agregat akademis/non.
    StudentAchievement::factory()->create([
        'student_id' => $pending->student->id,
        'category' => 'akademis',
        'level' => 'nasional',
        'rank' => 'juara_2',
        'score' => 25,
        'verified_by_admin' => true,
    ]);
    StudentAchievement::factory()->create([
        'student_id' => $pending->student->id,
        'category' => 'non_akademis',
        'level' => 'nasional',
        'rank' => 'juara_3',
        'score' => 12,
        'verified_by_admin' => true,
    ]);

    // 3. Edit kriteria — narrow IPK sedang
    $sedang = \App\Models\FuzzySet::query()
        ->whereHas('criterion', fn ($q) => $q->where('code', 'ipk'))
        ->where('name', 'sedang')->first();
    $this->put(route('admin.criteria.update', $sedang->id), [
        'a' => 3.30, 'b' => 3.55, 'c' => 3.75,
    ])->assertRedirect();

    // 4. Run selection
    $this->post(route('admin.selection.run'), ['label' => 'E2E Admin Flow'])->assertRedirect();
    $batch = SelectionBatch::query()->latest('id')->first();
    expect($batch->status)->toBe(SelectionBatch::STATUS_COMPLETED);

    // 5. Ranking page
    $this->get(route('admin.selection.show', $batch->id))->assertOk();

    // 6. Audit per kandidat
    $this->get(route('admin.selection.audit', [
        'batch' => $batch->id,
        'candidate' => $pending->student->id,
    ]))->assertOk();

    // 7. Export
    $this->get(route('admin.selection.export.csv', $batch->id))->assertOk();
    $this->get(route('admin.selection.export.pdf', $batch->id))->assertOk();

    // Audit log captures the run.
    expect(Activity::query()->where('log_name', 'selection')->count())->toBeGreaterThan(0);
    expect(Activity::query()->where('log_name', 'export')->count())->toBeGreaterThan(0);
});

it('walks the full mahasiswa flow: register → tunggu approval → login → status → ranking publik', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();

    // 1. Register
    $this->post('/register', [
        'name' => 'Mahasiswa E2E',
        'nim' => 'E2EMHS001',
        'email' => 'mhs.e2e@kampus.ac.id',
        'semester' => 4,
        'password' => 'rahasia12',
        'password_confirmation' => 'rahasia12',
    ])->assertRedirect(route('login'));

    /** @var User $user */
    $user = User::query()->where('email', 'mhs.e2e@kampus.ac.id')->firstOrFail();
    expect($user->approval_status)->toBe(User::STATUS_PENDING);

    // 2. Login attempt while pending — rejected
    $this->from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'rahasia12',
    ])->assertRedirect('/login')->assertSessionHasErrors('email');

    // 3. Admin approves
    $this->actingAs($admin)
        ->post(route('admin.students.approve', ['user' => $user->id]))
        ->assertRedirect();
    auth()->logout();

    // 4. Login as approved mahasiswa
    $this->post('/login', [
        'email' => $user->email,
        'password' => 'rahasia12',
    ])->assertRedirect(route('mahasiswa.dashboard'));

    // 5. Dashboard shows status without leaking other students.
    $response = $this->get(route('mahasiswa.dashboard'));
    $response->assertOk();
    $page = $response->viewData('page')['props'];
    expect($page['profile']['approval_status'])->toBe(User::STATUS_APPROVED);

    // 6. Ranking publik (still empty — no batch yet).
    $response = $this->get(route('mahasiswa.ranking.index'));
    $response->assertOk();
    $page = $response->viewData('page')['props'];
    expect($page['rankings'])->toBe([]);
});
