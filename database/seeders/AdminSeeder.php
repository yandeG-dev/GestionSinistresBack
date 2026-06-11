<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'nom' => 'Super',
            'prenom' => 'Admin',
            'email' => 'admin@ass.com',
            'password' => Hash::make('password'),
            'role' => 'Admin',
            'telephone' => '771264258',
            'adresse' => 'Dakar',
            'doit_changer_mdp' => false,
        ]);
    }
}
