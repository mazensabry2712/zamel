<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Faculty;
use App\Models\Profile;
use App\Models\School;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Get Academic Years
        |--------------------------------------------------------------------------
        */

        $firstYear = AcademicYear::where('education_type', 'university')
            ->where('sort_order', 1)
            ->first();

        $secondYear = AcademicYear::where('education_type', 'university')
            ->where('sort_order', 2)
            ->first();

        $thirdYear = AcademicYear::where('education_type', 'university')
            ->where('sort_order', 3)
            ->first();

        $fourthYear = AcademicYear::where('education_type', 'university')
            ->where('sort_order', 4)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Get Universities
        |--------------------------------------------------------------------------
        */

        $cairo = University::where('slug', 'cairo-university')->first();

        $alexandria = University::where('slug', 'alexandria-university')->first();

        $damanhour = University::where('slug', 'damanhour-university')->first();

        /*
        |--------------------------------------------------------------------------
        | Create Users
        |--------------------------------------------------------------------------
        */

        $users = [
            [
                'name' => 'أحمد محمد',
                'email' => 'ahmed@example.com',
                'university' => $cairo,
                'year' => $firstYear,
                'phone' => '01000000001',
                'bio' => 'طالب مهتم بالكتب والمراجع الجامعية.',
            ],

            [
                'name' => 'محمد علي',
                'email' => 'mohamed@example.com',
                'university' => $cairo,
                'year' => $thirdYear,
                'phone' => '01000000002',
                'bio' => 'طالب جامعي وأبيع الكتب الدراسية المستعملة.',
            ],

            [
                'name' => 'عمر خالد',
                'email' => 'omar@example.com',
                'university' => $alexandria,
                'year' => $secondYear,
                'phone' => '01000000003',
                'bio' => 'طالب مهتم بالشراء والبيع بين الطلاب.',
            ],

            [
                'name' => 'يوسف محمود',
                'email' => 'youssef@example.com',
                'university' => $alexandria,
                'year' => $fourthYear,
                'phone' => '01000000004',
                'bio' => 'طالب في السنة النهائية.',
            ],

            [
                'name' => 'محمود حسن',
                'email' => 'mahmoud@example.com',
                'university' => $damanhour,
                'year' => $firstYear,
                'phone' => '01000000005',
                'bio' => 'طالب جديد في الجامعة.',
            ],
        ];

        foreach ($users as $data) {

            $user = User::updateOrCreate(
                [
                    'email' => $data['email'],
                ],
                [
                    'name' => $data['name'],
                    'password' => 'password',
                ]
            );

            Profile::updateOrCreate(
                [
                    'user_id' => $user->id,
                ],
                [
                    'education_type' => 'university',
                    'university_id' => $data['university']?->id,
                    'faculty_id' => null,
                    'school_id' => null,
                    'academic_year_id' => $data['year']?->id,
                    'phone' => $data['phone'],
                    'avatar' => null,
                    'bio' => $data['bio'],
                ]
            );
        }
    }
}
