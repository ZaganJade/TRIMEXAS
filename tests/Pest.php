<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific
| PHPUnit test case class. By default, that class is "PHPUnit\Framework\TestCase".
| Of course, you may need to change it using the "pest()" function to bind a
| different test case class to your tests.
|
*/

use Illuminate\Foundation\Testing\RefreshDatabase;

pest()
    ->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()
    ->extend(Tests\TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| Pest expectations let you tweak the way you assert and chain expectations.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| Custom helpers used inside test files. Keeping them here means we don't
| pollute the global Pest namespace and we can extend them per project.
|
*/

function actingAsAdmin(?\App\Models\User $admin = null): \App\Models\User
{
    $admin ??= \App\Models\User::factory()->create([
        'role' => \App\Models\User::ROLE_ADMIN,
        'approval_status' => \App\Models\User::STATUS_APPROVED,
    ]);

    test()->actingAs($admin);

    return $admin;
}

function actingAsApprovedStudent(?\App\Models\User $student = null): \App\Models\User
{
    $student ??= \App\Models\User::factory()->create([
        'role' => \App\Models\User::ROLE_MAHASISWA,
        'approval_status' => \App\Models\User::STATUS_APPROVED,
    ]);

    test()->actingAs($student);

    return $student;
}
