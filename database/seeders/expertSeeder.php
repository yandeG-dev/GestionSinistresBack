<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class expertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
     User::create([
            'nom' => 'expert',
            'prenom' => 'pape',
            'email' => 'expert@ass.com',
            'password' => Hash::make('password'), // Mdp facile "password"
            'role' => 'Expert',
            'telephone' => '771156564',
            'adresse' => 'Agence Pikine',
            'doit_changer_mdp' => false, // Désactivé pour faciliter vos tests Postman
        ]);
    }
}
