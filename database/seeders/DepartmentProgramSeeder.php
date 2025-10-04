<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Program;

class DepartmentProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Department of Information Communication & Technology
        $ictDept = Department::create([
            'name' => 'Department of Information Communication & Technology',
            'description' => 'Offering cutting-edge programs in ICT, Cybersecurity, and Computer Science',
            'is_active' => true,
            'sort_order' => 1
        ]);

        // Create programs for ICT Department
        Program::create([
            'department_id' => $ictDept->id,
            'name' => 'Bsc Information Communication Technology',
            'description' => 'Comprehensive ICT program covering networking, programming, and system administration',
            'duration' => '4 years',
            'mode' => 'Regular (4yrs)',
            'is_active' => true,
            'sort_order' => 1
        ]);

        Program::create([
            'department_id' => $ictDept->id,
            'name' => 'Bsc Cybersecurity and Digital Forensics',
            'description' => 'Specialized program in cybersecurity and digital forensics',
            'duration' => '4 years',
            'mode' => 'Regular (4yrs)',
            'is_active' => true,
            'sort_order' => 2
        ]);

        Program::create([
            'department_id' => $ictDept->id,
            'name' => 'Bsc Computer Science',
            'description' => 'Core computer science program covering algorithms, data structures, and software engineering',
            'duration' => '4 years',
            'mode' => 'Regular (4yrs)',
            'is_active' => true,
            'sort_order' => 3
        ]);

        // Create Department of Business Studies
        $businessDept = Department::create([
            'name' => 'Department of Business Studies',
            'description' => 'Comprehensive business education covering marketing, HR, finance, and accounting',
            'is_active' => true,
            'sort_order' => 2
        ]);

        // Create programs for Business Department
        Program::create([
            'department_id' => $businessDept->id,
            'name' => 'BSc Marketing',
            'description' => 'Marketing program covering digital marketing, consumer behavior, and brand management',
            'duration' => '4 years',
            'mode' => 'Regular (4yrs)',
            'is_active' => true,
            'sort_order' => 1
        ]);

        Program::create([
            'department_id' => $businessDept->id,
            'name' => 'BSc Human Resource Management',
            'description' => 'HR management program covering recruitment, training, and organizational behavior',
            'duration' => '4 years',
            'mode' => 'Regular (4yrs)',
            'is_active' => true,
            'sort_order' => 2
        ]);

        Program::create([
            'department_id' => $businessDept->id,
            'name' => 'BSc Banking and Finance',
            'description' => 'Banking and finance program covering financial markets, risk management, and investment',
            'duration' => '4 years',
            'mode' => 'Regular (4yrs)',
            'is_active' => true,
            'sort_order' => 3
        ]);

        Program::create([
            'department_id' => $businessDept->id,
            'name' => 'BSc Accounting',
            'description' => 'Accounting program covering financial accounting, auditing, and taxation',
            'duration' => '4 years',
            'mode' => 'Regular (4yrs)',
            'is_active' => true,
            'sort_order' => 4
        ]);

        // Create Department of Nursing and Midwifery
        $nursingDept = Department::create([
            'name' => 'Department of Nursing and Midwifery',
            'description' => 'Healthcare programs in nursing and midwifery practice',
            'is_active' => true,
            'sort_order' => 3
        ]);

        // Create programs for Nursing Department
        Program::create([
            'department_id' => $nursingDept->id,
            'name' => 'Bsc Nursing',
            'description' => 'Comprehensive nursing program covering patient care, medical procedures, and healthcare management',
            'duration' => '4 years',
            'mode' => 'Regular (4yrs)',
            'is_active' => true,
            'sort_order' => 1
        ]);

        Program::create([
            'department_id' => $nursingDept->id,
            'name' => 'BSc Midwifery',
            'description' => 'Specialized midwifery program covering maternal and child healthcare',
            'duration' => '4 years',
            'mode' => 'Regular (4yrs)',
            'is_active' => true,
            'sort_order' => 2
        ]);

        // Add Top-up programs for each department
        Program::create([
            'department_id' => $ictDept->id,
            'name' => 'Bsc Information Communication Technology (Top-up)',
            'description' => 'Top-up program for ICT diploma holders',
            'duration' => '2 years',
            'mode' => 'Top-up',
            'is_active' => true,
            'sort_order' => 4
        ]);

        Program::create([
            'department_id' => $businessDept->id,
            'name' => 'BSc Marketing (Top-up)',
            'description' => 'Top-up program for Marketing diploma holders',
            'duration' => '2 years',
            'mode' => 'Top-up',
            'is_active' => true,
            'sort_order' => 5
        ]);

        Program::create([
            'department_id' => $nursingDept->id,
            'name' => 'Bsc Nursing (Top-up)',
            'description' => 'Top-up program for Nursing diploma holders',
            'duration' => '2 years',
            'mode' => 'Top-up',
            'is_active' => true,
            'sort_order' => 3
        ]);
    }
}
