<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@medical-store.com'],
            [
                'nom' => 'GERVAIS KIPRE',
                'password' => Hash::make('changeMoi123!'),
                'role' => 'admin',
                'telephone' => '0769459084',
                'adresse' => 'Abidjan, Côte d\'Ivoire',
            ]
        );
    }
}