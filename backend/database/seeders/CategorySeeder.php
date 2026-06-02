<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Véhicules
            ['name' => 'Voitures', 'slug' => 'voitures', 'icon' => 'car'],
            ['name' => 'Motos', 'slug' => 'motos', 'icon' => 'motorcycle'],
            ['name' => 'Vélos', 'slug' => 'velos', 'icon' => 'bicycle'],
            ['name' => 'Pièces auto', 'slug' => 'pieces-auto', 'icon' => 'cog'],
            ['name' => 'Caravaning', 'slug' => 'caravaning', 'icon' => 'caravan'],
            ['name' => 'Nautisme', 'slug' => 'nautisme', 'icon' => 'boat'],

            // Immobilier
            ['name' => 'Ventes immobilières', 'slug' => 'ventes-immobilieres', 'icon' => 'home'],
            ['name' => 'Locations', 'slug' => 'locations', 'icon' => 'key'],
            ['name' => 'Colocations', 'slug' => 'colocations', 'icon' => 'users'],
            ['name' => 'Bureaux & Commerces', 'slug' => 'bureaux-commerces', 'icon' => 'building'],

            // Électronique
            ['name' => 'Téléphones', 'slug' => 'telephones', 'icon' => 'phone'],
            ['name' => 'Ordinateurs', 'slug' => 'ordinateurs', 'icon' => 'laptop'],
            ['name' => 'Tablettes', 'slug' => 'tablettes', 'icon' => 'tablet'],
            ['name' => 'Consoles & Jeux vidéo', 'slug' => 'consoles-jeux-video', 'icon' => 'gamepad'],
            ['name' => 'Photo & Vidéo', 'slug' => 'photo-video', 'icon' => 'camera'],
            ['name' => 'TV & Audio', 'slug' => 'tv-audio', 'icon' => 'tv'],
            ['name' => 'Accessoires', 'slug' => 'accessoires-electronique', 'icon' => 'headphones'],

            // Mode
            ['name' => 'Vêtements homme', 'slug' => 'vetements-homme', 'icon' => 'tshirt'],
            ['name' => 'Vêtements femme', 'slug' => 'vetements-femme', 'icon' => 'dress'],
            ['name' => 'Vêtements enfant', 'slug' => 'vetements-enfant', 'icon' => 'child'],
            ['name' => 'Chaussures', 'slug' => 'chaussures', 'icon' => 'shoe'],
            ['name' => 'Montres & Bijoux', 'slug' => 'montres-bijoux', 'icon' => 'watch'],
            ['name' => 'Sacs & Accessoires', 'slug' => 'sacs-accessoires', 'icon' => 'bag'],
            ['name' => 'Luxe', 'slug' => 'luxe', 'icon' => 'gem'],

            // Maison & Jardin
            ['name' => 'Meubles', 'slug' => 'meubles', 'icon' => 'couch'],
            ['name' => 'Électroménager', 'slug' => 'electromenager', 'icon' => 'blender'],
            ['name' => 'Décoration', 'slug' => 'decoration', 'icon' => 'lamp'],
            ['name' => 'Bricolage', 'slug' => 'bricolage', 'icon' => 'tools'],
            ['name' => 'Jardinage', 'slug' => 'jardinage', 'icon' => 'tree'],

            // Loisirs
            ['name' => 'Sports', 'slug' => 'sports', 'icon' => 'dumbbell'],
            ['name' => 'Livres', 'slug' => 'livres', 'icon' => 'book'],
            ['name' => 'Musique', 'slug' => 'musique', 'icon' => 'music'],
            ['name' => 'Films & DVD', 'slug' => 'films-dvd', 'icon' => 'film'],
            ['name' => 'Jeux & Jouets', 'slug' => 'jeux-jouets', 'icon' => 'puzzle'],
            ['name' => 'Collection', 'slug' => 'collection', 'icon' => 'stamp'],
            ['name' => 'Animaux', 'slug' => 'animaux', 'icon' => 'paw'],
            ['name' => 'Voyage', 'slug' => 'voyage', 'icon' => 'plane'],

            // Services
            ['name' => 'Services', 'slug' => 'services', 'icon' => 'handshake'],
            ['name' => 'Emploi', 'slug' => 'emploi', 'icon' => 'briefcase'],
            ['name' => 'Cours', 'slug' => 'cours', 'icon' => 'graduation'],
            ['name' => 'Événements', 'slug' => 'evenements', 'icon' => 'calendar'],

            // Autre
            ['name' => 'Autres', 'slug' => 'autres', 'icon' => 'box'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
