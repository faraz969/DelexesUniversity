<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormType;

class FormTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $formTypes = [
            [
                'name' => 'Undergraduate Admission Form',
                'local_price' => 50.00,
                'international_price' => 100.00,
                'description' => 'Application form for undergraduate degree programs',
                'is_active' => true,
            ],
            [
                'name' => 'Postgraduate Admission Form',
                'local_price' => 75.00,
                'international_price' => 150.00,
                'description' => 'Application form for postgraduate degree programs (Masters, PhD)',
                'is_active' => true,
            ],
            [
                'name' => 'Certificate/Short Course Form',
                'local_price' => 25.00,
                'international_price' => 50.00,
                'description' => 'Application form for certificate and short course programs',
                'is_active' => true,
            ],
            [
                'name' => 'Diploma Admission Form',
                'local_price' => 40.00,
                'international_price' => 80.00,
                'description' => 'Application form for diploma programs',
                'is_active' => true,
            ],
        ];

        foreach ($formTypes as $formType) {
            FormType::create($formType);
        }
    }
}