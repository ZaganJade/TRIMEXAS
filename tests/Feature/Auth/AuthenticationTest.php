<?php

use App\Models\Student;
use App\Models\User;

it('renders the login page', function () {
    $this->get('/login')->assertOk();
});

it('logs in an admin and redirects to the admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    expect(auth()->id())->toBe($admin->id);
});

it('logs in an approved student and redirects to the mahasiswa dashboard', function () {
    $student = User::factory()->mahasiswa()->create();

    $this->post('/login', [
        'email' => $student->email,
        'password' => 'password',
    ])->assertRedirect(route('mahasiswa.dashboard'));

    expect(auth()->id())->toBe($student->id);
});

it('rejects login with wrong credentials without leaking which account', function () {
    $user = User::factory()->mahasiswa()->create();

    $this->from('/login')
        ->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
        ->assertRedirect('/login')
        ->assertSessionHasErrors([
            'email' => 'Email atau password salah.',
        ]);

    expect(auth()->check())->toBeFalse();
});

it('rejects login when student is pending verification', function () {
    $student = User::factory()->pending()->create();

    $this->from('/login')
        ->post('/login', [
            'email' => $student->email,
            'password' => 'password',
        ])
        ->assertRedirect('/login')
        ->assertSessionHasErrors([
            'email' => 'Akun Anda menunggu verifikasi admin.',
        ]);

    expect(auth()->check())->toBeFalse();
});

it('rejects login with rejection reason for rejected student', function () {
    $student = User::factory()
        ->rejected('Data IPK belum sesuai persyaratan beasiswa.')
        ->create();

    $response = $this->from('/login')
        ->post('/login', [
            'email' => $student->email,
            'password' => 'password',
        ]);

    $response->assertRedirect('/login')
        ->assertSessionHasErrors('email');

    $errors = $response->getSession()->get('errors')->getBag('default')->get('email');
    expect($errors[0])->toContain('Akun Anda ditolak.')
        ->and($errors[0])->toContain('Data IPK belum sesuai persyaratan beasiswa.');
});
it('registers a new mahasiswa as pending and creates the student record', function () {
    $this->post('/register', [
        'name' => 'Budi Santoso',
        'nim' => '210101001',
        'email' => 'budi@kampus.ac.id',
        'semester' => 4,
        'password' => 'rahasia12',
        'password_confirmation' => 'rahasia12',
    ])->assertRedirect(route('login'));

    $this->assertDatabaseHas('users', [
        'email' => 'budi@kampus.ac.id',
        'role' => User::ROLE_MAHASISWA,
        'approval_status' => User::STATUS_PENDING,
    ]);

    $user = User::where('email', 'budi@kampus.ac.id')->first();
    $this->assertDatabaseHas('students', [
        'user_id' => $user->id,
        'nim' => '210101001',
        'semester' => 4,
    ]);
});

it('rejects registration when email is already taken', function () {
    User::factory()->create(['email' => 'taken@kampus.ac.id']);

    $this->from('/register')
        ->post('/register', [
            'name' => 'X',
            'nim' => '210999999',
            'email' => 'taken@kampus.ac.id',
            'semester' => 1,
            'password' => 'rahasia12',
            'password_confirmation' => 'rahasia12',
        ])
        ->assertRedirect('/register')
        ->assertSessionHasErrors(['email']);
});

it('rejects registration when NIM is already taken', function () {
    $existing = User::factory()->mahasiswa()->create();
    Student::factory()->state(['user_id' => $existing->id, 'nim' => '210000111'])->create();

    $this->from('/register')
        ->post('/register', [
            'name' => 'X',
            'nim' => '210000111',
            'email' => 'fresh@kampus.ac.id',
            'semester' => 1,
            'password' => 'rahasia12',
            'password_confirmation' => 'rahasia12',
        ])
        ->assertRedirect('/register')
        ->assertSessionHasErrors(['nim']);
});

it('rejects registration when password is shorter than 8 characters', function () {
    $this->from('/register')
        ->post('/register', [
            'name' => 'X',
            'nim' => '210000222',
            'email' => 'short@kampus.ac.id',
            'semester' => 1,
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
        ->assertRedirect('/register')
        ->assertSessionHasErrors(['password']);
});

it('logs out an authenticated user and clears the session', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/logout')
        ->assertRedirect(route('home'));

    expect(auth()->check())->toBeFalse();
});
