<?php

namespace App\Http\Controllers;

use App\Models\Vehicule;
use Illuminate\Http\Request;

class VehiculeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'marque' => 'required|string|max:50',
        'modele' => 'required|string|max:50',
        'immatriculation' => 'required|string|unique:vehicules,immatriculation',
        'type_carburant' => 'required|string|max:20',
        'puissance_fiscale' => 'required|integer|min:1',
        'date_mise_en_circulation' => 'required|date',
        'contrat_id' => 'required|exists:contrats,id'
    ]);

    // Créer le véhicule
    $vehicule = Vehicule::create($request->all());

    return response()->json([
        'message' => 'Véhicule créé avec succès',
        'vehicule' => $vehicule
    ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicule $vehicule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicule $vehicule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicule $vehicule)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicule $vehicule)
    {
        //
    }
}
