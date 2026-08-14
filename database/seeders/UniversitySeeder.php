<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;

class UniversitySeeder extends Seeder
{
    public function run(): void
    {
        $universities = [
            [
                'name' => 'جامعة القاهرة',
                'slug' => 'cairo-university',
            ],
            [
                'name' => 'جامعة الإسكندرية',
                'slug' => 'alexandria-university',
            ],
            [
                'name' => 'جامعة عين شمس',
                'slug' => 'ain-shams-university',
            ],
            [
                'name' => 'جامعة أسيوط',
                'slug' => 'assiut-university',
            ],
            [
                'name' => 'جامعة طنطا',
                'slug' => 'tanta-university',
            ],
            [
                'name' => 'جامعة المنصورة',
                'slug' => 'mansoura-university',
            ],
            [
                'name' => 'جامعة الزقازيق',
                'slug' => 'zagazig-university',
            ],
            [
                'name' => 'جامعة حلوان',
                'slug' => 'helwan-university',
            ],
            [
                'name' => 'جامعة المنيا',
                'slug' => 'minia-university',
            ],
            [
                'name' => 'جامعة المنوفية',
                'slug' => 'menoufia-university',
            ],
            [
                'name' => 'جامعة قناة السويس',
                'slug' => 'suez-canal-university',
            ],
            [
                'name' => 'جامعة جنوب الوادي',
                'slug' => 'south-valley-university',
            ],
            [
                'name' => 'جامعة بنها',
                'slug' => 'benha-university',
            ],
            [
                'name' => 'جامعة الفيوم',
                'slug' => 'fayoum-university',
            ],
            [
                'name' => 'جامعة بني سويف',
                'slug' => 'beni-suef-university',
            ],
            [
                'name' => 'جامعة كفر الشيخ',
                'slug' => 'kafrelsheikh-university',
            ],
            [
                'name' => 'جامعة سوهاج',
                'slug' => 'sohag-university',
            ],
            [
                'name' => 'جامعة بورسعيد',
                'slug' => 'portsaid-university',
            ],
            [
                'name' => 'جامعة دمنهور',
                'slug' => 'damanhur-university',
            ],
            [
                'name' => 'جامعة دمياط',
                'slug' => 'damietta-university',
            ],
            [
                'name' => 'جامعة أسوان',
                'slug' => 'aswan-university',
            ],
            [
                'name' => 'جامعة السويس',
                'slug' => 'suez-university',
            ],
            [
                'name' => 'جامعة مدينة السادات',
                'slug' => 'sadat-city-university',
            ],
            [
                'name' => 'جامعة العريش',
                'slug' => 'arish-university',
            ],
            [
                'name' => 'جامعة مطروح',
                'slug' => 'matrouh-university',
            ],
            [
                'name' => 'جامعة الوادي الجديد',
                'slug' => 'new-valley-university',
            ],
            [
                'name' => 'جامعة الأقصر',
                'slug' => 'luxor-university',
            ],
            [
                'name' => 'جامعة الغردقة',
                'slug' => 'hurghada-university',
            ],
        ];

        foreach ($universities as $university) {
            University::updateOrCreate(
                ['slug' => $university['slug']],
                ['name' => $university['name']]
            );
        }
    }
}
