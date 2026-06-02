<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Sophie Martin',
                'email' => 'sophie.martin@example.com',
                'balance' => 250.00,
                'location' => 'Paris 11e',
                'listings' => [
                    [
                        'category' => 'velos',
                        'title' => 'VTT Rockrider 540 taille L, très bon état',
                        'description' => "Vélo VTT Rockrider 540 (Decathlon), taille L, acheté en 2022. Servi quelques fois en forêt de Fontainebleau, toujours stocké au sec. Freins à disque hydrauliques, fourche suspendue, 21 vitesses Shimano. Entretien à jour, pneus comme neufs. Vendu avec une sacoche et 2 chambres à air de rechange. À récupérer sur place ou je peux livrer dans Paris.",
                        'price' => 280,
                        'seed' => 'mountain-bike-rockrider',
                        'keyword' => 'mountain bike',
                    ],
                    [
                        'category' => 'consoles-jeux-video',
                        'title' => 'PS5 Slim 1To + 2 manettes + 3 jeux',
                        'description' => "PS5 Slim 1To achetée en septembre, sous garantie jusqu'en septembre 2026 (facture fournie). Vendue avec : 2 manettes DualSense (1 blanche + 1 midnight black), 3 jeux physiques (FC25, Spider-Man 2, Astro Bot). Boîte d'origine et tous les câbles. Aucun rayure, fonctionne parfaitement. Cause vente : passage au PC.",
                        'price' => 380,
                        'seed' => 'playstation-5-console',
                        'keyword' => 'gaming console',
                    ],
                ],
            ],
            [
                'name' => 'Lucas Bernard',
                'email' => 'lucas.bernard@example.com',
                'balance' => 120.00,
                'location' => 'Lyon 7e',
                'listings' => [
                    [
                        'category' => 'telephones',
                        'title' => 'iPhone 13 128Go bleu, batterie 89%',
                        'description' => "iPhone 13 128Go couleur bleu, acheté en novembre 2022. État de la batterie : 89%. Très bon état général, quelques micro-rayures sur la coque arrière (toujours utilisé avec coque). Écran impeccable, jamais réparé. Vendu avec câble USB-C/Lightning d'origine. Réinitialisé d'usine avant la vente.",
                        'price' => 450,
                        'seed' => 'iphone-13-blue',
                        'keyword' => 'iphone',
                    ],
                    [
                        'category' => 'chaussures',
                        'title' => 'Vans Old Skool noires/blanches taille 42',
                        'description' => "Vans Old Skool classiques noires et blanches, taille 42. Portées une dizaine de fois, en très bon état. Semelles propres, pas de jaunissement. Pointure trop petite pour moi malheureusement. Achetées 75€ en boutique.",
                        'price' => 35,
                        'seed' => 'vans-old-skool-sneakers',
                        'keyword' => 'sneakers',
                    ],
                ],
            ],
            [
                'name' => 'Camille Dubois',
                'email' => 'camille.dubois@example.com',
                'balance' => 50.00,
                'location' => 'Bordeaux Centre',
                'listings' => [
                    [
                        'category' => 'ordinateurs',
                        'title' => 'MacBook Pro 13 M1 2020 - 16Go/512Go',
                        'description' => "MacBook Pro 13 pouces M1 2020, configuration 16Go RAM / 512Go SSD. Acheté en direct chez Apple en 2021. Très bien entretenu, jamais ouvert, toujours utilisé avec un sticker sur la caméra. Cycles batterie : 234. Aucun défaut esthétique notable. Vendu avec chargeur 61W d'origine et boîte. Idéal pour étudiant ou usage pro léger.",
                        'price' => 750,
                        'seed' => 'macbook-pro-m1',
                        'keyword' => 'macbook',
                    ],
                    [
                        'category' => 'meubles',
                        'title' => 'Canapé 3 places en lin beige - La Redoute',
                        'description' => "Canapé 3 places de chez La Redoute Intérieurs, modèle Jimi en lin beige. Dimensions : 215cm x 90cm x 85cm. Acheté 890€ il y a 2 ans. Quelques traces d'usure sur les accoudoirs mais structure parfaite. À récupérer impérativement sur place (Bordeaux), je ne peux pas livrer.",
                        'price' => 220,
                        'seed' => 'beige-linen-sofa',
                        'keyword' => 'sofa',
                    ],
                ],
            ],
            [
                'name' => 'Antoine Leroy',
                'email' => 'antoine.leroy@example.com',
                'balance' => 0.00,
                'location' => 'Marseille 8e',
                'listings' => [
                    [
                        'category' => 'voitures',
                        'title' => 'Renault Clio IV 2018 essence 95ch 78000km',
                        'description' => "Renault Clio IV 1.2 essence 95ch, première mise en circulation 03/2018. 78 000 km au compteur, entretien suivi en concession Renault (carnet à jour). Climatisation, régulateur de vitesse, GPS, caméra de recul, jantes alu 16''. Contrôle technique de mai 2026 OK sans contre-visite. 2 pneus avant changés en mars. Aucun accident.",
                        'price' => 8500,
                        'seed' => 'renault-clio-grise',
                        'keyword' => 'car',
                    ],
                    [
                        'category' => 'bricolage',
                        'title' => 'Perceuse visseuse Bosch Pro 18V + 2 batteries',
                        'description' => "Perceuse visseuse Bosch Professional GSR 18V-50, vendue avec 2 batteries 2.0Ah, chargeur rapide et coffret L-Boxx. Très peu servi (acheté pour des travaux qui se sont arrêtés). Comme neuve. Facture Leroy Merlin de 2024 fournie.",
                        'price' => 95,
                        'seed' => 'bosch-drill-tool',
                        'keyword' => 'drill',
                    ],
                ],
            ],
            [
                'name' => 'Emma Rousseau',
                'email' => 'emma.rousseau@example.com',
                'balance' => 80.00,
                'location' => 'Nantes Centre',
                'listings' => [
                    [
                        'category' => 'vetements-femme',
                        'title' => 'Robe Maje noire en soie taille S (36)',
                        'description' => "Magnifique robe Maje en soie noire, modèle Rivela, taille S (36). Portée 2 fois seulement pour des soirées. Achetée 295€ en boutique l'an dernier. Aucune tache ni trou. Trop petite suite à un changement de morpho. Possible envoi Mondial Relay (frais à votre charge).",
                        'price' => 80,
                        'seed' => 'black-silk-dress',
                        'keyword' => 'dress',
                    ],
                    [
                        'category' => 'livres',
                        'title' => 'Lot 15 romans contemporains - Foenkinos, Musso, Levy',
                        'description' => "Vide-bibliothèque : lot de 15 romans en très bon état, principalement Foenkinos, Musso et Marc Levy. Édition broché grand format pour la plupart. Liste complète sur demande. Vente uniquement en lot, à récupérer sur place ou Mondial Relay (~12€ pour la France).",
                        'price' => 25,
                        'seed' => 'french-books-stack',
                        'keyword' => 'books',
                    ],
                ],
            ],
        ];

        foreach ($users as $userData) {
            $listingsData = $userData['listings'];
            $userLocation = $userData['location'];
            unset($userData['listings'], $userData['location']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ])
            );

            foreach ($listingsData as $position => $listingData) {
                $category = Category::where('slug', $listingData['category'])->first();
                if (!$category) {
                    $this->command?->warn("Catégorie introuvable : {$listingData['category']}");
                    continue;
                }

                $listing = Listing::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'title' => $listingData['title'],
                    ],
                    [
                        'category_id' => $category->id,
                        'description' => $listingData['description'],
                        'price' => $listingData['price'],
                        'location' => $userLocation,
                        'status' => 'active',
                    ]
                );

                if ($listing->images()->count() === 0) {
                    $this->attachImage($listing, $listingData['seed'], $listingData['keyword']);
                }
            }
        }
    }

    private function attachImage(Listing $listing, string $seed, string $keyword): void
    {
        $url = "https://picsum.photos/seed/{$seed}/1200/800";

        try {
            $response = Http::timeout(15)->withOptions(['allow_redirects' => true])->get($url);
        } catch (\Throwable $e) {
            $this->command?->warn("Image fetch failed for {$listing->title}: {$e->getMessage()}");
            return;
        }

        if (!$response->successful()) {
            $this->command?->warn("Image fetch HTTP {$response->status()} for {$listing->title}");
            return;
        }

        $path = "listings/{$listing->id}/" . $seed . '.jpg';
        Storage::disk('public')->put($path, $response->body());

        $listing->images()->create([
            'original_path' => $path,
            'is_primary' => true,
            'order' => 0,
        ]);
    }
}
