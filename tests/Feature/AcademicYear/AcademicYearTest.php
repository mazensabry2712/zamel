<?php

use App\Models\AcademicYear;

it('lists all academic years ordered by sort order', function () {
    AcademicYear::create([
        'name' => 'Third Year',
        'education_type' => 'university',
        'sort_order' => 3,
    ]);

    AcademicYear::create([
        'name' => 'First Year',
        'education_type' => 'university',
        'sort_order' => 1,
    ]);

    AcademicYear::create([
        'name' => 'Second Year',
        'education_type' => 'university',
        'sort_order' => 2,
    ]);

    $response = $this->getJson('/api/v1/academic-years');

    $response
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.name', 'First Year')
        ->assertJsonPath('data.1.name', 'Second Year')
        ->assertJsonPath('data.2.name', 'Third Year');
});

it('filters academic years by education type', function () {
    AcademicYear::create([
        'name' => 'First University Year',
        'education_type' => 'university',
        'sort_order' => 1,
    ]);

    AcademicYear::create([
        'name' => 'First Secondary Year',
        'education_type' => 'secondary',
        'sort_order' => 1,
    ]);

    AcademicYear::create([
        'name' => 'Second University Year',
        'education_type' => 'university',
        'sort_order' => 2,
    ]);

    $response = $this->getJson('/api/v1/academic-years?education_type=university');

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'First University Year')
        ->assertJsonPath('data.0.education_type', 'university')
        ->assertJsonPath('data.1.name', 'Second University Year')
        ->assertJsonPath('data.1.education_type', 'university');
});

it('returns an empty collection when no academic years exist', function () {
    $response = $this->getJson('/api/v1/academic-years');

    $response
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('returns an empty collection when the education type has no academic years', function () {
    AcademicYear::create([
        'name' => 'First University Year',
        'education_type' => 'university',
        'sort_order' => 1,
    ]);

    $response = $this->getJson('/api/v1/academic-years?education_type=secondary');

    $response
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('does not require authentication to list academic years', function () {
    AcademicYear::create([
        'name' => 'First Year',
        'education_type' => 'university',
        'sort_order' => 1,
    ]);

    AcademicYear::create([
        'name' => 'Second Year',
        'education_type' => 'university',
        'sort_order' => 2,
    ]);

    $this->getJson('/api/v1/academic-years')
        ->assertOk();
});
