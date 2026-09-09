<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Contrat;
use App\Models\Vehicule;

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

        // 2. Création du véhicule de l'assuré
        $vehicule = Vehicule::create([
            'marque' => 'Toyota',
            'modele' => 'Corolla',
            'immatriculation' => 'DK-1234-A',
            'type_carburant' => 'Essence',
            'puissance_fiscale' => 7,
            'date_mise_en_circulation' => '2020-01-15',
        ]);

        // 3. Création de son contrat auto lié au véhicule
        Contrat::create([
            'numeroContrat' => 'AUTO-2024-DK-01',
            'typeContrat' => 'Automobile',
            'dateDebut' => '2024-01-01',
            'dateFin' => '2024-12-31',
            'franchise' => 20000,
            'prime' => 150000,
            'garantie' => 'Tous Risques',
            'assure_id' => $assure->id,
            'vehicule_id' => $vehicule->id,
        ]);
    }
}
