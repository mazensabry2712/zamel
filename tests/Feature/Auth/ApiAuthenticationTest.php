<?php

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

it('registers a user through the api and creates a profile', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'API Test User',
        'email' => 'api-test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'education_type' => 'other',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.user.email', 'api-test@example.com')
        ->assertJsonPath('data.profile.education_type', 'other')
        ->assertJsonPath('data.token', fn ($token) => is_string($token) && $token !== '');

    $user = User::where('email', 'api-test@example.com')->firstOrFail();

    $this->assertDatabaseHas('profiles', [
        'user_id' => $user->id,
        'education_type' => 'other',
    ]);
});

it('rejects invalid api registration data', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'x',
        'email' => 'not-an-email',
        'password' => 'short',
        'password_confirmation' => 'different',
        'education_type' => 'invalid',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'name',
            'email',
            'password',
            'education_type',
        ]);
});

it('logs a user in through the api', function () {
    $user = User::factory()->create([
        'email' => 'login-api@example.com',
        'password' => 'password123',
        'status' => 'active',
    ]);

    Profile::create([
        'user_id' => $user->id,
        'education_type' => 'other',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.email', $user->email)
        ->assertJsonPath('data.profile.education_type', 'other')
        ->assertJsonPath('data.token', fn ($token) => is_string($token) && $token !== '');
});

it('rejects invalid api login credentials', function () {
    $user = User::factory()->create([
        'email' => 'invalid-login@example.com',
        'password' => 'password123',
        'status' => 'active',
    ]);

    Profile::create([
        'user_id' => $user->id,
        'education_type' => 'other',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('returns the authenticated user from the me endpoint', function () {
    $user = User::factory()->create(['status' => 'active']);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.email', $user->email);
});

it('requires authentication for the me endpoint', function () {
    $this->getJson('/api/v1/auth/me')
        ->assertUnauthorized();
});

it('returns the authenticated users profile', function () {
    $user = User::factory()->create(['status' => 'active']);

    Profile::create([
        'user_id' => $user->id,
        'education_type' => 'other',
        'phone' => '01000000000',
        'bio' => 'API profile',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/auth/profile')
        ->assertOk()
        ->assertJsonPath('data.profile.user_id', $user->id)
        ->assertJsonPath('data.profile.education_type', 'other')
        ->assertJsonPath('data.profile.phone', '01000000000');
});

it('updates the authenticated users profile through the api', function () {
    $user = User::factory()->create(['status' => 'active']);

    Profile::create([
        'user_id' => $user->id,
        'education_type' => 'other',
    ]);

    Sanctum::actingAs($user);

    $this->putJson('/api/v1/auth/profile', [
        'phone' => '01111111111',
        'bio' => 'Updated bio',
    ])
        ->assertOk()
        ->assertJsonPath('data.profile.phone', '01111111111')
        ->assertJsonPath('data.profile.bio', 'Updated bio');
});

it('requires authentication to update the api profile', function () {
    $this->putJson('/api/v1/auth/profile', [
        'phone' => '01111111111',
    ])->assertUnauthorized();
});

it('changes the authenticated users password through the api', function () {
    $user = User::factory()->create([
        'status' => 'active',
        'password' => 'password123',
    ]);

    Sanctum::actingAs($user);

    $this->putJson('/api/v1/auth/change-password', [
        'current_password' => 'password123',
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Password changed successfully.');

    expect(Hash::check('new-password123', $user->refresh()->password))->toBeTrue();
});

it('rejects a wrong current password through the api', function () {
    $user = User::factory()->create([
        'status' => 'active',
        'password' => 'password123',
    ]);

    Sanctum::actingAs($user);

    $this->putJson('/api/v1/auth/change-password', [
        'current_password' => 'wrong-password',
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['current_password']);
});

it('requires authentication to change the api password', function () {
    $this->putJson('/api/v1/auth/change-password', [
        'current_password' => 'password123',
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ])->assertUnauthorized();
});
