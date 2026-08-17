<?php

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('allows the owner to upload an image to their listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
    ]);

    Sanctum::actingAs($owner);

    $response = $this->post('/api/v1/listings/'.$listing->id.'/images', [
        'image' => UploadedFile::fake()->image('book.jpg', 1200, 900),
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.file_name', 'book.jpg')
        ->assertJsonStructure([
            'data' => [
                'id',
                'file_name',
                'mime_type',
                'size',
                'url',
                'thumb_url',
                'medium_url',
                'order',
                'created_at',
            ],
        ]);

    expect($listing->getMedia('images'))->toHaveCount(1);
});

it('requires a fresh moderation review when an image is added to a published listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
        'status' => 'published',
        'moderation_status' => 'approved',
        'moderation_reason' => null,
        'moderated_at' => now(),
    ]);

    Sanctum::actingAs($owner);

    $this->post('/api/v1/listings/'.$listing->id.'/images', [
        'image' => UploadedFile::fake()->image('new-book.jpg'),
    ])->assertCreated();

    $listing->refresh();

    expect($listing->status)->toBe('draft');
    expect($listing->moderation_status)->toBe('pending');
    expect($listing->moderation_reason)->toBeNull();
    expect($listing->moderated_at)->toBeNull();
    expect($listing->published_at)->toBeNull();
});

it('prevents another user from uploading an image', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $otherUser = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
    ]);

    Sanctum::actingAs($otherUser);

    $this->post('/api/v1/listings/'.$listing->id.'/images', [
        'image' => UploadedFile::fake()->image('book.jpg'),
    ])->assertForbidden();
});

it('rejects non-image uploads', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
    ]);

    Sanctum::actingAs($owner);

    $this->post('/api/v1/listings/'.$listing->id.'/images', [
        'image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['image']);
});

it('prevents uploading more than eight images to one listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
    ]);

    for ($i = 1; $i <= 8; $i++) {
        $listing
            ->addMedia(UploadedFile::fake()->image("image-{$i}.jpg"))
            ->toMediaCollection('images');
    }

    Sanctum::actingAs($owner);

    $this->post('/api/v1/listings/'.$listing->id.'/images', [
        'image' => UploadedFile::fake()->image('ninth.jpg'),
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['image']);
});

it('allows the owner to delete one of their listing images', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
    ]);

    $media = $listing
        ->addMedia(UploadedFile::fake()->image('book.jpg'))
        ->toMediaCollection('images');

    Sanctum::actingAs($owner);

    $this->deleteJson(
        "/api/v1/listings/{$listing->id}/images/{$media->id}"
    )->assertOk();

    expect($listing->fresh()->getMedia('images'))->toHaveCount(0);
});

it('requires a fresh moderation review when an image is deleted from a published listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
        'status' => 'published',
        'moderation_status' => 'approved',
        'moderation_reason' => null,
        'moderated_at' => now(),
    ]);

    $media = $listing
        ->addMedia(UploadedFile::fake()->image('book.jpg'))
        ->toMediaCollection('images');

    Sanctum::actingAs($owner);

    $this->deleteJson(
        "/api/v1/listings/{$listing->id}/images/{$media->id}"
    )->assertOk();

    $listing->refresh();

    expect($listing->status)->toBe('draft');
    expect($listing->moderation_status)->toBe('pending');
    expect($listing->moderated_at)->toBeNull();
    expect($listing->published_at)->toBeNull();
});

it('prevents deleting an image belonging to another listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $otherOwner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
    ]);

    $otherListing = Listing::factory()->create([
        'user_id' => $otherOwner->id,
    ]);

    $media = $otherListing
        ->addMedia(UploadedFile::fake()->image('other.jpg'))
        ->toMediaCollection('images');

    Sanctum::actingAs($owner);

    $this->deleteJson(
        "/api/v1/listings/{$listing->id}/images/{$media->id}"
    )->assertForbidden();
});

it('allows the owner to reorder listing images', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
    ]);

    $first = $listing
        ->addMedia(UploadedFile::fake()->image('first.jpg'))
        ->toMediaCollection('images');

    $second = $listing
        ->addMedia(UploadedFile::fake()->image('second.jpg'))
        ->toMediaCollection('images');

    $third = $listing
        ->addMedia(UploadedFile::fake()->image('third.jpg'))
        ->toMediaCollection('images');

    Sanctum::actingAs($owner);

    $response = $this->putJson(
        '/api/v1/listings/'.$listing->id.'/images/reorder',
        [
            'media_ids' => [$third->id, $first->id, $second->id],
        ],
    );

    $response->assertOk();

    $orderedIds = $listing->fresh()
        ->getMedia('images')
        ->sortBy('order_column')
        ->pluck('id')
        ->values()
        ->all();

    expect($orderedIds)->toBe([
        $third->id,
        $first->id,
        $second->id,
    ]);
});

it('rejects a reorder payload containing an image from another listing', function () {
    $owner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $otherOwner = User::factory()->create([
        'status' => 'active',
        'role' => 'student',
    ]);

    $listing = Listing::factory()->create([
        'user_id' => $owner->id,
    ]);

    $otherListing = Listing::factory()->create([
        'user_id' => $otherOwner->id,
    ]);

    $media = $otherListing
        ->addMedia(UploadedFile::fake()->image('other.jpg'))
        ->toMediaCollection('images');

    Sanctum::actingAs($owner);

    $this->putJson(
        '/api/v1/listings/'.$listing->id.'/images/reorder',
        [
            'media_ids' => [$media->id],
        ],
    )
        ->assertStatus(422)
        ->assertJsonValidationErrors(['media_ids']);
});
