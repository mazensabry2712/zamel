<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UniversitySeeder::class,
            AcademicYearSeeder::class,
            FacultySeeder::class,
            UserSeeder::class,

        ]);
    }
}
