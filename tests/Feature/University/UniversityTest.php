<?php

use App\Models\Faculty;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists all universities ordered alphabetically', function () {
    $z = University::create([
        'name' => 'Zagazig University',
        'slug' => 'zagazig-university',
    ]);

    $a = University::create([
        'name' => 'Alexandria University',
        'slug' => 'alexandria-university',
    ]);

    $c = University::create([
        'name' => 'Cairo University',
        'slug' => 'cairo-university',
    ]);

    $response = $this->getJson('/api/v1/universities');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.id', $a->id)
        ->assertJsonPath('data.0.name', 'Alexandria University')
        ->assertJsonPath('data.1.id', $c->id)
        ->assertJsonPath('data.1.name', 'Cairo University')
        ->assertJsonPath('data.2.id', $z->id)
        ->assertJsonPath('data.2.name', 'Zagazig University');

    expect($response->json('data'))->toHaveCount(3);
});

it('returns an empty collection when no universities exist', function () {
    $response = $this->getJson('/api/v1/universities');

    $response
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('shows a single university', function () {
    $university = University::create([
        'name' => 'Cairo University',
        'slug' => 'cairo-university',
    ]);

    $this->getJson("/api/v1/universities/{$university->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $university->id)
        ->assertJsonPath('data.name', 'Cairo University')
        ->assertJsonPath('data.slug', 'cairo-university');
});

it('returns not found for a non existent university', function () {
    $this->getJson('/api/v1/universities/999999')
        ->assertNotFound();
});

it('lists university faculties ordered alphabetically', function () {
    $university = University::create([
        'name' => 'Cairo University',
        'slug' => 'cairo-university',
    ]);

    Faculty::create([
        'university_id' => $university->id,
        'name' => 'Zoology Faculty',
        'slug' => 'zoology-faculty',
    ]);

    $first = Faculty::create([
        'university_id' => $university->id,
        'name' => 'Engineering Faculty',
        'slug' => 'engineering-faculty',
    ]);

    $second = Faculty::create([
        'university_id' => $university->id,
        'name' => 'Medicine Faculty',
        'slug' => 'medicine-faculty',
    ]);

    $otherUniversity = University::create([
        'name' => 'Alexandria University',
        'slug' => 'alexandria-university',
    ]);

    Faculty::create([
        'university_id' => $otherUniversity->id,
        'name' => 'Law Faculty',
        'slug' => 'law-faculty',
    ]);

    $response = $this->getJson("/api/v1/universities/{$university->id}/faculties");

    $response
        ->assertOk()
        ->assertJsonPath('data.0.id', $first->id)
        ->assertJsonPath('data.0.name', 'Engineering Faculty')
        ->assertJsonPath('data.1.id', $second->id)
        ->assertJsonPath('data.1.name', 'Medicine Faculty')
        ->assertJsonPath('data.2.name', 'Zoology Faculty');

    expect($response->json('data'))->toHaveCount(3);
});

it('returns an empty faculty collection when the university has no faculties', function () {
    $university = University::create([
        'name' => 'Cairo University',
        'slug' => 'cairo-university',
    ]);

    $this->getJson("/api/v1/universities/{$university->id}/faculties")
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('returns not found when requesting faculties for a non existent university', function () {
    $this->getJson('/api/v1/universities/999999/faculties')
        ->assertNotFound();
});
