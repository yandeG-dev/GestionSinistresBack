<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class GestionnaireSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nom' => 'Sow',
            'prenom' => 'Aminata',
            'email' => 'gestionnaire@ass.com',
            'password' => Hash::make('password'), // Mdp facile "password"
            'role' => 'Gestionnaire',
            'telephone' => '771112233',
            'adresse' => 'Agence VDN',
            'doit_changer_mdp' => false, // Désactivé pour faciliter vos tests Postman
        ]);
    }
}
