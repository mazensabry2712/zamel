<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

it('allows an active user to suggest a new category', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'status' => 'active',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/categories', [
        'name' => 'Arduino and Robotics',
        'description' => 'Arduino boards and robotics components.',
        'seo_title' => 'Arduino and Robotics Marketplace',
        'seo_description' => 'Student robotics items and Arduino equipment.',
    ]);

    $response
        ->assertStatus(201)
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.is_active', false);

    $this->assertDatabaseHas('categories', [
        'name' => 'Arduino and Robotics',
        'slug' => Str::slug('Arduino and Robotics'),
        'status' => 'pending',
        'created_by' => $user->id,
        'is_active' => false,
    ]);
});

it('prevents unauthenticated users from suggesting categories', function () {
    $response = $this->postJson('/api/v1/categories', [
        'name' => 'Arduino and Robotics',
    ]);

    $response->assertStatus(401);
});

it('requires a unique category name when suggesting a category', function () {
    Category::factory()->create([
        'name' => 'Books',
        'slug' => 'books',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'status' => 'active',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/categories', [
        'name' => 'Books',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('allows an admin to approve a pending category', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $category = Category::factory()->create([
        'status' => 'pending',
        'is_active' => false,
        'created_by' => User::factory()->create()->id,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->putJson(
        "/api/v1/admin/categories/{$category->id}/approve"
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.status', 'approved')
        ->assertJsonPath('data.is_active', true);

    expect($category->fresh()->status)->toBe('approved');
});

it('allows an admin to reject a pending category with a reason', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $category = Category::factory()->create([
        'status' => 'pending',
        'is_active' => false,
        'created_by' => User::factory()->create()->id,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->putJson(
        "/api/v1/admin/categories/{$category->id}/reject",
        [
            'reason' => 'Duplicate category',
        ]
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.status', 'rejected')
        ->assertJsonPath('data.is_active', false)
        ->assertJsonPath('data.moderation_reason', 'Duplicate category');

    expect($category->fresh()->status)->toBe('rejected');
});

it('requires a rejection reason', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $category = Category::factory()->create([
        'status' => 'pending',
        'is_active' => false,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->putJson(
        "/api/v1/admin/categories/{$category->id}/reject",
        []
    );

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['reason']);
});

it('prevents students from approving categories', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'status' => 'active',
    ]);

    $category = Category::factory()->create([
        'status' => 'pending',
        'is_active' => false,
    ]);

    Sanctum::actingAs($student);

    $response = $this->putJson(
        "/api/v1/admin/categories/{$category->id}/approve"
    );

    $response->assertStatus(403);
    expect($category->fresh()->status)->toBe('pending');
});

it('hides pending categories from public category listing', function () {
    Category::factory()->create([
        'name' => 'Pending Category',
        'slug' => 'pending-category',
        'status' => 'pending',
        'is_active' => false,
    ]);

    Category::factory()->create([
        'name' => 'Approved Category',
        'slug' => 'approved-category',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/v1/categories');

    $response
        ->assertOk()
        ->assertJsonFragment([
            'name' => 'Approved Category',
        ])
        ->assertJsonMissing([
            'name' => 'Pending Category',
        ]);
});

it('cannot approve an already approved category', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $category = Category::factory()->create([
        'status' => 'approved',
        'is_active' => true,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->putJson(
        "/api/v1/admin/categories/{$category->id}/approve"
    );

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);

    expect($category->fresh()->status)->toBe('approved');
});

it('cannot reject an already rejected category', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $category = Category::factory()->create([
        'status' => 'rejected',
        'is_active' => false,
        'moderation_reason' => 'Duplicate category',
    ]);

    Sanctum::actingAs($admin);

    $response = $this->putJson(
        "/api/v1/admin/categories/{$category->id}/reject",
        [
            'reason' => 'Still duplicate',
        ]
    );

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);

    expect($category->fresh()->status)->toBe('rejected');
    expect($category->fresh()->moderation_reason)->toBe('Duplicate category');
});
