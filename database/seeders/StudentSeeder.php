<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ClassRoom;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $classRoom = ClassRoom::create([
            'code' => 'TKA1',
            'name' => 'Teknik Kendaraan Ringan 1',
        ]);

        $guru = User::firstOrCreate(
            ['email' => 'guru@skillcheck.test'],
            [
                'name' => 'Budi Santoso',
                'password' => bcrypt('password'),
                'role' => 'guru',
            ]
        );

        $students = [
            ['nis' => '001', 'name' => 'Ahmad Rizki'],
            ['nis' => '002', 'name' => 'Budi Hermawan'],
            ['nis' => '003', 'name' => 'Cahyo Wibowo'],
            ['nis' => '004', 'name' => 'Dani Wijaya'],
            ['nis' => '005', 'name' => 'Eka Putra'],
            ['nis' => '006', 'name' => 'Fajar Sutrisno'],
            ['nis' => '007', 'name' => 'Gede Suryanto'],
            ['nis' => '008', 'name' => 'Hendra Kusuma'],
            ['nis' => '009', 'name' => 'Iqbal Ramadhan'],
            ['nis' => '010', 'name' => 'Joko Santoso'],
        ];

        foreach ($students as $student) {
            Student::create([
                'nis' => $student['nis'],
                'name' => $student['name'],
                'class_room_id' => $classRoom->id,
                'guru_id' => $guru->id,
            ]);
        }
    }
}
