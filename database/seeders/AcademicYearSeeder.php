<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | التعليم الجامعي
        |--------------------------------------------------------------------------
        */

        $universityYears = [
            [
                'name' => 'الفرقة الأولى',
                'education_type' => 'university',
                'sort_order' => 1,
            ],
            [
                'name' => 'الفرقة الثانية',
                'education_type' => 'university',
                'sort_order' => 2,
            ],
            [
                'name' => 'الفرقة الثالثة',
                'education_type' => 'university',
                'sort_order' => 3,
            ],
            [
                'name' => 'الفرقة الرابعة',
                'education_type' => 'university',
                'sort_order' => 4,
            ],
            [
                'name' => 'الفرقة الخامسة',
                'education_type' => 'university',
                'sort_order' => 5,
            ],
            [
                'name' => 'الفرقة السادسة',
                'education_type' => 'university',
                'sort_order' => 6,
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | التعليم الثانوي
        |--------------------------------------------------------------------------
        */

        $secondaryYears = [
            [
                'name' => 'الصف الأول الثانوي',
                'education_type' => 'secondary',
                'sort_order' => 1,
            ],
            [
                'name' => 'الصف الثاني الثانوي',
                'education_type' => 'secondary',
                'sort_order' => 2,
            ],
            [
                'name' => 'الصف الثالث الثانوي',
                'education_type' => 'secondary',
                'sort_order' => 3,
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | تعليم آخر
        |--------------------------------------------------------------------------
        */

        $otherYears = [
            [
                'name' => 'دبلوم',
                'education_type' => 'other',
                'sort_order' => 1,
            ],
            [
                'name' => 'دراسات عليا',
                'education_type' => 'other',
                'sort_order' => 2,
            ],
            [
                'name' => 'ماجستير',
                'education_type' => 'other',
                'sort_order' => 3,
            ],
            [
                'name' => 'دكتوراه',
                'education_type' => 'other',
                'sort_order' => 4,
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Insert
        |--------------------------------------------------------------------------
        */

        foreach (
            array_merge(
                $universityYears,
                $secondaryYears,
                $otherYears
            ) as $year
        ) {
            AcademicYear::updateOrCreate(
                [
                    'name' => $year['name'],
                    'education_type' => $year['education_type'],
                ],
                [
                    'sort_order' => $year['sort_order'],
                ]
            );
        }
    }
}
