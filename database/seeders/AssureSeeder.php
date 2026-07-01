<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Contrat;

class AssureSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Création de l'Assuré
        $assure = User::create([
            'nom' => 'Diop',
            'prenom' => 'Moussa',
            'email' => 'assure@client.com',
            'password' => Hash::make('password'), // Mdp facile "password"
            'role' => 'Assure',
            'telephone' => '789990011',
            'adresse' => 'Plateau, Dakar',
            'doit_changer_mdp' => false, // Désactivé pour faciliter vos tests Postman
        ]);

        // 2. Création de son contrat auto
        Contrat::create([
            'numeroContrat' => 'AUTO-2024-DK-01',
            'typeContrat' => 'Automobile',
            'dateDebut' => '2024-01-01',
            'dateFin' => '2024-12-31',
            'franchise' => 20000,
            'prime' => 150000,
            'garantie' => 'Tous Risques',
            'nomSouscripteur' => $assure->nom . ' ' . $assure->prenom,
            'policeAssurance' => 'POL-998877',
            'immatriculation' => 'DK-1234-A',
            'marque_vehicule' => 'Toyota',
            'modele_vehicule' => 'Corolla',
            'assure_id' => $assure->id
        ]);
    }
}
