<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Initial Standard Departments (idempotent)
        $departments = [
            ['name' => 'ฝ่ายบริหาร / สำนักงานกลาง', 'code' => 'MGT', 'contact_person' => null],
            ['name' => 'แผนกสารสนเทศ (IT)', 'code' => 'IT', 'contact_person' => null],
            ['name' => 'แผนกบัญชีและการเงิน', 'code' => 'ACC', 'contact_person' => null],
            ['name' => 'แผนกทรัพยากรบุคคล (HR)', 'code' => 'HR', 'contact_person' => null],
            ['name' => 'แผนกจัดซื้อและพัสดุ', 'code' => 'PROC', 'contact_person' => null],
            ['name' => 'แผนกการตลาด', 'code' => 'MKT', 'contact_person' => null],
            ['name' => 'แผนกขายและบริการลูกค้า', 'code' => 'SALES', 'contact_person' => null],
            ['name' => 'แผนกคลังสินค้าและจัดส่ง', 'code' => 'WH', 'contact_person' => null],
        ];

        $deptMap = [];
        foreach ($departments as $d) {
            $dept = Department::firstOrCreate(['name' => $d['name']], $d);
            $deptMap[$d['code']] = $dept;
        }

        // 2. Default Initial Admin Account (idempotent)
        User::updateOrCreate(
            ['email' => 'admin@stock.com'],
            [
                'name' => 'ผู้ดูแลระบบ (Admin)',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'department_id' => $deptMap['IT']->id ?? null,
            ]
        );
    }
}
