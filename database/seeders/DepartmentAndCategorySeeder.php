<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentAndCategorySeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Technical Support',
                'description' => 'Technical issues and system problems',
                'email' => 'tech@aduan.local',
                'categories' => [
                    ['name' => 'System Error', 'description' => 'System errors and bugs'],
                    ['name' => 'Account Issue', 'description' => 'Account access problems'],
                    ['name' => 'Performance', 'description' => 'Slow performance or loading issues'],
                ],
            ],
            [
                'name' => 'General Enquiry',
                'description' => 'General questions and information requests',
                'email' => 'info@aduan.local',
                'categories' => [
                    ['name' => 'Information Request', 'description' => 'General information requests'],
                    ['name' => 'Feedback', 'description' => 'Feedback and suggestions'],
                    ['name' => 'Other', 'description' => 'Other general enquiries'],
                ],
            ],
            [
                'name' => 'Complaints',
                'description' => 'Complaints and escalations',
                'email' => 'complaints@aduan.local',
                'categories' => [
                    ['name' => 'Service Complaint', 'description' => 'Complaints about service quality'],
                    ['name' => 'Staff Complaint', 'description' => 'Complaints about staff behavior'],
                    ['name' => 'Billing Issue', 'description' => 'Billing and payment complaints'],
                ],
            ],
        ];

        foreach ($departments as $deptData) {
            $categories = $deptData['categories'];
            unset($deptData['categories']);

            $department = Department::firstOrCreate(
                ['name' => $deptData['name']],
                $deptData
            );

            foreach ($categories as $catData) {
                Category::firstOrCreate(
                    [
                        'name' => $catData['name'],
                        'department_id' => $department->id,
                    ],
                    array_merge($catData, ['department_id' => $department->id])
                );
            }
        }
    }
}
