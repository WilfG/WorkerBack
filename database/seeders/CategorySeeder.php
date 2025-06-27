<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Maçonnerie',
                'icon' => 'construction',
                'description' => 'Travaux de maçonnerie, fondations, murs, etc.'
            ],
            [
                'name' => 'Plomberie',
                'icon' => 'water',
                'description' => 'Installation et réparation de plomberie.'
            ],
            [
                'name' => 'Électricité',
                'icon' => 'flash',
                'description' => 'Travaux électriques et installations.'
            ],
            [
                'name' => 'Peinture',
                'icon' => 'color-palette',
                'description' => 'Peinture intérieure et extérieure.'
            ],
            [
                'name' => 'Menuiserie',
                'icon' => 'hammer',
                'description' => 'Travaux de bois et menuiserie.'
            ],
            [
                'name' => 'Jardinage',
                'icon' => 'leaf',
                'description' => 'Entretien et aménagement de jardins.'
            ],
            [
                'name' => 'Nettoyage',
                'icon' => 'broom',
                'description' => 'Services de nettoyage divers.'
            ],
            [
                'name' => 'Déménagement',
                'icon' => 'cube',
                'description' => 'Services de déménagement.'
            ],
            [
                'name' => 'Serrurerie',
                'icon' => 'key',
                'description' => 'Ouverture de portes, installation de serrures.'
            ],
            [
                'name' => 'Toiture',
                'icon' => 'home',
                'description' => 'Travaux de toiture et couverture.'
            ],
            [
                'name' => 'Chauffage',
                'icon' => 'thermometer',
                'description' => 'Installation et entretien de chauffage.'
            ],
            [
                'name' => 'Climatisation',
                'icon' => 'snow',
                'description' => 'Installation et entretien de climatisation.'
            ],
            [
                'name' => 'Carrelage',
                'icon' => 'grid',
                'description' => 'Pose de carrelage et faïence.'
            ],
            [
                'name' => 'Plâtrerie',
                'icon' => 'layers',
                'description' => 'Travaux de plâtre et cloisons.'
            ],
            [
                'name' => 'Vitrerie',
                'icon' => 'aperture',
                'description' => 'Pose et réparation de vitres.'
            ],
            [
                'name' => 'Parquet',
                'icon' => 'reorder-four',
                'description' => 'Pose et rénovation de parquet.'
            ],
            [
                'name' => 'Décoration',
                'icon' => 'brush',
                'description' => 'Décoration intérieure et extérieure.'
            ],
            [
                'name' => 'Rénovation',
                'icon' => 'build',
                'description' => 'Travaux de rénovation tous corps d\'état.'
            ],
            [
                'name' => 'Piscine',
                'icon' => 'water-outline',
                'description' => 'Construction et entretien de piscines.'
            ],
            [
                'name' => 'Énergies renouvelables',
                'icon' => 'sunny',
                'description' => 'Installation de solutions d\'énergies renouvelables.'
            ],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }
    }
}
