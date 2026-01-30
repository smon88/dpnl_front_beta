<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'dev1lb0y666'],
            [
                'password' => Hash::make('TheNewCardingEra666666@'), // Cambiar en producción
                'alias' => 'DEV1LB0Y',
                'tg_user' => '@Dev1lb0y666', // Cambiar por tu @username
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
