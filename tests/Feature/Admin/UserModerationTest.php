<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('prevents students from accessing admin moderation endpoints', function () {
    $adminTarget = User::factory()->create();

    $student = User::factory()->create([
        'role' => 'student',
        'status' => 'active',
    ]);

    Sanctum::actingAs($student);

    $response = $this->putJson(
        "/api/v1/admin/users/{$adminTarget->id}/ban",
        [
            'reason' => 'Test ban reason',
        ]
    );

    $response
        ->assertStatus(403);

    expect($adminTarget->fresh()->status)
        ->toBe('active');
});

it('allows an admin to suspend a user', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $target = User::factory()->create([
        'role' => 'student',
        'status' => 'active',
    ]);

    Sanctum::actingAs($admin);

    $until = now()->addDays(3);

    $response = $this->putJson(
        "/api/v1/admin/users/{$target->id}/suspend",
        [
            'suspended_until' => $until->toISOString(),
            'reason' => 'Spam posting',
        ]
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.user_id',
            $target->id
        )
        ->assertJsonPath(
            'data.status',
            'suspended'
        );

    $target->refresh();

    expect($target->status)
        ->toBe('suspended');

    expect($target->moderation_reason)
        ->toBe('Spam posting');

    expect($target->suspended_until)
        ->not->toBeNull();
});

it('requires a reason when suspending a user', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $target = User::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->putJson(
        "/api/v1/admin/users/{$target->id}/suspend",
        [
            'suspended_until' => now()
                ->addDays(3)
                ->toISOString(),
        ]
    );

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'reason',
        ]);
});

it('requires a future suspension date', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $target = User::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->putJson(
        "/api/v1/admin/users/{$target->id}/suspend",
        [
            'suspended_until' => now()
                ->subDay()
                ->toISOString(),
            'reason' => 'Spam reason',
        ]
    );

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'suspended_until',
        ]);
});

it('allows an admin to ban a user', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $target = User::factory()->create([
        'role' => 'student',
        'status' => 'active',
    ]);

    Sanctum::actingAs($admin);

    $response = $this->putJson(
        "/api/v1/admin/users/{$target->id}/ban",
        [
            'reason' => 'Prohibited content',
        ]
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.status', 'banned');

    expect($target->fresh()->status)
        ->toBe('banned');

    expect($target->fresh()->moderation_reason)
        ->toBe('Prohibited content');
});

it('requires a reason when banning a user', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $target = User::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->putJson(
        "/api/v1/admin/users/{$target->id}/ban",
        []
    );

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'reason',
        ]);
});

it('allows an admin to unban a user', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $target = User::factory()->create([
        'role' => 'student',
        'status' => 'banned',
        'moderation_reason' => 'Test reason',
    ]);

    Sanctum::actingAs($admin);

    $response = $this->putJson(
        "/api/v1/admin/users/{$target->id}/unban"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.status', 'active');

    $target->refresh();

    expect($target->status)
        ->toBe('active');

    expect($target->moderation_reason)
        ->toBeNull();

    expect($target->suspended_until)
        ->toBeNull();
});

it('prevents suspended users from logging in', function () {
    $user = User::factory()->create([
        'email' => 'suspended@example.com',
        'status' => 'suspended',
        'role' => 'student',
        'suspended_until' => now()->addDays(3),
        'moderation_reason' => 'Spam',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'email',
        ]);
});

it('prevents banned users from logging in', function () {
    $user = User::factory()->create([
        'email' => 'banned@example.com',
        'status' => 'banned',
        'role' => 'student',
        'moderation_reason' => 'Prohibited content',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'email',
        ]);
});

it('revokes existing tokens when a user is banned', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $target = User::factory()->create([
        'role' => 'student',
        'status' => 'active',
    ]);

    $target->createToken('test-token')->plainTextToken;

    expect($target->tokens()->count())
        ->toBe(1);

    Sanctum::actingAs($admin);

    $this->putJson(
        "/api/v1/admin/users/{$target->id}/ban",
        [
            'reason' => 'Prohibited content',
        ]
    )->assertOk();

    expect($target->fresh()->tokens()->count())
        ->toBe(0);
});

it('revokes existing tokens when a user is suspended', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $target = User::factory()->create([
        'role' => 'student',
        'status' => 'active',
    ]);

    $target->createToken('test-token');

    expect($target->tokens()->count())
        ->toBe(1);

    Sanctum::actingAs($admin);

    $response = $this->putJson(
        "/api/v1/admin/users/{$target->id}/suspend",
        [
            'suspended_until' => now()
                ->addDays(3)
                ->toISOString(),
            'reason' => 'Spam posting',
        ]
    );

    $response->assertOk();

    expect($target->fresh()->tokens()->count())
        ->toBe(0);
});

it('blocks a suspended user from protected endpoints', function () {
    $user = User::factory()->create([
        'status' => 'suspended',
        'role' => 'student',
        'suspended_until' => now()->addDays(3),
        'moderation_reason' => 'Spam',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/me');

    $response
        ->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Your account is temporarily suspended.',
        ]);
});

it('blocks a banned user from protected endpoints', function () {
    $user = User::factory()->create([
        'status' => 'banned',
        'role' => 'student',
        'moderation_reason' => 'Prohibited content',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/me');

    $response
        ->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Your account has been banned.',
        ]);
});
