<?php

use App\Models\User;
use App\Notifications\AccountApprovedNotification;
use App\Notifications\AccountRejectedNotification;
use Illuminate\Support\Facades\Notification;

it('approving a student creates a database notification and queues a mail', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $student = User::factory()->pending()->create();

    $this->actingAs($admin)
        ->post(route('admin.students.approve', ['user' => $student->id]))
        ->assertRedirect();

    Notification::assertSentTo($student->fresh(), AccountApprovedNotification::class);
});

it('rejecting a student fires the rejection notification with the reason', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $student = User::factory()->pending()->create();

    $this->actingAs($admin)
        ->post(route('admin.students.reject', ['user' => $student->id]), [
            'reason' => 'Data IPK belum sesuai persyaratan.',
        ])
        ->assertRedirect();

    Notification::assertSentTo(
        $student->fresh(),
        AccountRejectedNotification::class,
        fn (AccountRejectedNotification $n) => $n->reason === 'Data IPK belum sesuai persyaratan.'
    );
});

it('returns notifications + unread count via /notifications', function () {
    $student = User::factory()->mahasiswa()->create();
    $admin = User::factory()->admin()->create();
    $student->notify(new AccountApprovedNotification($admin));

    $this->actingAs($student)
        ->getJson(route('notifications.index'))
        ->assertOk()
        ->assertJsonStructure([
            'notifications' => [['id', 'type', 'data', 'read_at', 'created_at']],
            'unread',
        ]);
});

it('marks all notifications as read', function () {
    $student = User::factory()->mahasiswa()->create();
    $admin = User::factory()->admin()->create();
    $student->notify(new AccountApprovedNotification($admin));

    $this->actingAs($student)
        ->postJson(route('notifications.markRead'))
        ->assertOk()
        ->assertJson(['unread' => 0]);

    expect($student->fresh()->unreadNotifications()->count())->toBe(0);
});
