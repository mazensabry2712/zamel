<?php

namespace Tests\Feature\Api;

use App\Models\AcademicYear;
use App\Models\Faculty;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversityContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_universities_can_be_listed(): void
    {
        University::create([
            'name' => 'جامعة القاهرة',
            'slug' => 'cairo-university',
        ]);

        $response = $this->getJson('/api/v1/universities');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.name', 'جامعة القاهرة');
    }

    public function test_a_single_university_can_be_retrieved(): void
    {
        $university = University::create([
            'name' => 'جامعة القاهرة',
            'slug' => 'cairo-university',
        ]);

        $response = $this->getJson("/api/v1/universities/{$university->id}");

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $university->id)
            ->assertJsonPath('data.name', 'جامعة القاهرة');
    }

    public function test_university_faculties_can_be_listed(): void
    {
        $university = University::create([
            'name' => 'جامعة القاهرة',
            'slug' => 'cairo-university',
        ]);

        Faculty::create([
            'university_id' => $university->id,
            'name' => 'كلية الحاسبات والمعلومات',
            'slug' => 'computers-and-information',
        ]);

        $response = $this->getJson(
            "/api/v1/universities/{$university->id}/faculties"
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.university_id', $university->id)
            ->assertJsonPath('data.0.name', 'كلية الحاسبات والمعلومات');
    }

    public function test_academic_years_can_be_listed(): void
    {
        AcademicYear::create([
            'name' => 'الفرقة الأولى',
            'education_type' => 'university',
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/v1/academic-years');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.name', 'الفرقة الأولى');
    }

    public function test_academic_years_can_be_filtered_by_education_type(): void
    {
        AcademicYear::create([
            'name' => 'الفرقة الأولى',
            'education_type' => 'university',
            'sort_order' => 1,
        ]);

        AcademicYear::create([
            'name' => 'الصف الأول الثانوي',
            'education_type' => 'secondary',
            'sort_order' => 1,
        ]);

        $response = $this->getJson(
            '/api/v1/academic-years?education_type=university'
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.education_type', 'university');
    }

    public function test_missing_university_returns_not_found(): void
    {
        $response = $this->getJson('/api/v1/universities/999999');

        $response->assertNotFound();
    }
}
