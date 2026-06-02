<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProduitController extends Controller
{
    public function index()
    {
        $produits = Produit::latest()->get()->map(function (Produit $produit) {
            $produit->image_url = $produit->image
                ? asset('storage/' . $produit->image)
                : null;
            return $produit;
        });

        return response()->json([
            'produits' => $produits,
        ]);
    }

    public function create()
    {
        return response()->json([
            'message' => 'Utilisez la route POST /produits',
        ], 405);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'prix' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('produits', 'public');
        }

        $produit = Produit::create($validated);

        return response()->json([
            'message' => 'Produit créé avec succès',
            'produit' => $produit,
        ], 201);
    }

    public function show(string $public_id)
    {
        $produit = Produit::where('public_id', $public_id)->firstOrFail();
        $produit->image_url = $produit->image
            ? asset('storage/' . $produit->image)
            : null;

        return response()->json([
            'produit' => $produit,
        ]);
    }

    public function edit(string $public_id)
    {
        $produit = Produit::where('public_id', $public_id)->firstOrFail();

        return response()->json([
            'produit' => $produit,
        ]);
    }

    public function update(Request $request, string $public_id)
    {
        $produit = Produit::where('public_id', $public_id)->firstOrFail();
        $validated = $request->validate([
            'nom' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'prix' => ['sometimes', 'required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($produit->image) {
                Storage::disk('public')->delete($produit->image);
            }
            $validated['image'] = $request->file('image')->store('produits', 'public');
        }

        $produit->update($validated);

        return response()->json([
            'message' => 'Produit mis à jour avec succès',
        ]);
    }

    public function destroy(Produit $produit)
    {
        if ($produit->image) {
            Storage::disk('public')->delete($produit->image);
        }
        $produit->delete();

        return response()->json([
            'message' => 'Produit supprimé avec succès',
        ]);
    }
}
