<?php

use App\Models\User;

it('redirects guests to login when accessing /admin/dashboard', function () {
    $this->get('/admin/dashboard')->assertRedirect('/login');
});

it('forbids mahasiswa from accessing /admin/dashboard', function () {
    $student = User::factory()->mahasiswa()->create();

    $this->actingAs($student)
        ->get('/admin/dashboard')
        ->assertForbidden();
});

it('forbids mahasiswa from accessing pending students list', function () {
    $student = User::factory()->mahasiswa()->create();

    $this->actingAs($student)
        ->get('/admin/students/pending')
        ->assertForbidden();
});

it('allows admin to access pending students list', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/students/pending')
        ->assertOk();
});

it('forbids pending mahasiswa from accessing /mahasiswa/dashboard', function () {
    $student = User::factory()->pending()->create();

    $this->actingAs($student)
        ->get('/mahasiswa/dashboard')
        ->assertForbidden();
});

it('allows approved mahasiswa to access /mahasiswa/dashboard', function () {
    $student = User::factory()->mahasiswa()->create();

    $this->actingAs($student)
        ->get('/mahasiswa/dashboard')
        ->assertOk();
});
