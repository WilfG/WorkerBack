<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profession;
use App\Models\Category;


class ProfessionSeeder extends Seeder
{
    public function run()
    {
        $professionsByCategory = [
            'Maçonnerie' => [
                'Maçon', 'Coffreur', 'Ferrailleur', 'Chef de chantier', 'Manœuvre', 'Tailleur de pierre', 'Conducteur d’engins', 'Bétonneur', 'Façadier', 'Enduiseur'
            ],
            'Plomberie' => [
                'Plombier', 'Installateur sanitaire', 'Chauffagiste', 'Technicien réseaux', 'Dépanneur plomberie', 'Poseur de salle de bain', 'Installateur gaz', 'Monteur en plomberie', 'Technicien traitement eau', 'Plombier zingueur'
            ],
            'Électricité' => [
                'Électricien', 'Technicien courant faible', 'Installateur domotique', 'Chef d’équipe électricien', 'Câbleur', 'Monteur électricien', 'Électricien industriel', 'Installateur photovoltaïque', 'Dépanneur électricien', 'Tableautier'
            ],
            'Peinture' => [
                'Peintre en bâtiment', 'Peintre décorateur', 'Peintre façadier', 'Vernisseur', 'Poseur de revêtements muraux', 'Peintre industriel', 'Peintre en carrosserie', 'Peintre en lettres', 'Peintre en signalisation', 'Peintre en rénovation'
            ],
            'Menuiserie' => [
                'Menuisier', 'Menuisier poseur', 'Menuisier agenceur', 'Ébéniste', 'Charpentier', 'Poseur de parquet', 'Menuisier aluminium', 'Menuisier PVC', 'Menuisier bois', 'Menuisier d’atelier'
            ],
            'Jardinage' => [
                'Jardinier', 'Paysagiste', 'Élagueur', 'Entretien espaces verts', 'Arboriculteur', 'Maraîcher', 'Pépiniériste', 'Technicien irrigation', 'Poseur de clôtures', 'Créateur de jardins'
            ],
            'Nettoyage' => [
                'Agent de nettoyage', 'Agent d’entretien', 'Technicien de surface', 'Agent de propreté', 'Agent de désinfection', 'Agent de nettoyage industriel', 'Agent de nettoyage vitres', 'Agent de nettoyage bureaux', 'Agent de nettoyage immeubles', 'Agent de nettoyage après sinistre'
            ],
            'Déménagement' => [
                'Déménageur', 'Chef d’équipe déménagement', 'Chauffeur déménageur', 'Emballeur', 'Monteur de meubles', 'Technicien monte-meubles', 'Démonteur', 'Manutentionnaire', 'Déménageur international', 'Déménageur d’entreprise'
            ],
            'Serrurerie' => [
                'Serrurier', 'Serrurier métallier', 'Installateur de portes', 'Installateur de serrures', 'Serrurier dépanneur', 'Serrurier poseur', 'Serrurier en bâtiment', 'Serrurier automobile', 'Serrurier coffre-fort', 'Serrurier d’urgence'
            ],
            'Toiture' => [
                'Couvreur', 'Zingueur', 'Étancheur', 'Charpentier couvreur', 'Poseur de tuiles', 'Poseur d’ardoises', 'Technicien toiture', 'Couvreur bardeur', 'Couvreur isolation', 'Couvreur rénovation'
            ],
            'Chauffage' => [
                'Chauffagiste', 'Installateur chaudière', 'Technicien de maintenance chauffage', 'Poseur de radiateurs', 'Technicien pompe à chaleur', 'Technicien chauffage gaz', 'Technicien chauffage fioul', 'Technicien chauffage bois', 'Technicien chauffage solaire', 'Technicien chauffage collectif'
            ],
            'Climatisation' => [
                'Frigoriste', 'Technicien climatisation', 'Installateur climatisation', 'Technicien ventilation', 'Technicien froid industriel', 'Technicien maintenance clim', 'Poseur de climatisation', 'Technicien PAC', 'Technicien split', 'Technicien VRV'
            ],
            'Carrelage' => [
                'Carreleur', 'Poseur de faïence', 'Poseur de mosaïque', 'Poseur de carrelage extérieur', 'Poseur de carrelage mural', 'Poseur de carrelage sol', 'Carreleur mosaïste', 'Carreleur faïencier', 'Carreleur décorateur', 'Carreleur rénovation'
            ],
            'Plâtrerie' => [
                'Plâtrier', 'Plaquiste', 'Staffeur', 'Stucateur', 'Poseur de cloisons', 'Poseur de plafonds', 'Plâtrier décorateur', 'Plâtrier traditionnel', 'Plâtrier isolation', 'Plâtrier rénovation'
            ],
            'Vitrerie' => [
                'Vitrier', 'Poseur de fenêtres', 'Poseur de vitrages', 'Technicien miroiterie', 'Vitrier décorateur', 'Vitrier automobile', 'Vitrier d’art', 'Vitrier isolation', 'Vitrier rénovation', 'Vitrier poseur'
            ],
            'Parquet' => [
                'Poseur de parquet', 'Parqueteur', 'Ponçeur de parquet', 'Vernisseur de parquet', 'Poseur de stratifié', 'Poseur de moquette', 'Poseur de sols souples', 'Poseur de sols PVC', 'Poseur de sols lino', 'Poseur de sols naturels'
            ],
            'Décoration' => [
                'Décorateur intérieur', 'Décorateur événementiel', 'Décorateur vitrine', 'Décorateur floral', 'Décorateur d’extérieur', 'Décorateur mural', 'Décorateur mobilier', 'Décorateur lumière', 'Décorateur textile', 'Décorateur d’art'
            ],
            'Rénovation' => [
                'Chef de projet rénovation', 'Ouvrier polyvalent', 'Coordinateur de travaux', 'Technicien rénovation', 'Peintre rénovation', 'Menuisier rénovation', 'Maçon rénovation', 'Plombier rénovation', 'Électricien rénovation', 'Carreleur rénovation'
            ],
            'Piscine' => [
                'Pisciniste', 'Technicien piscine', 'Poseur de liner', 'Poseur de piscine coque', 'Technicien traitement eau piscine', 'Technicien maintenance piscine', 'Poseur de couverture piscine', 'Poseur d’abri piscine', 'Technicien filtration piscine', 'Technicien chauffage piscine'
            ],
            'Énergies renouvelables' => [
                'Installateur panneaux solaires', 'Technicien éolien', 'Technicien géothermie', 'Installateur pompe à chaleur', 'Technicien photovoltaïque', 'Installateur solaire thermique', 'Technicien biomasse', 'Technicien hydraulique', 'Conseiller énergie renouvelable', 'Technicien maintenance ENR'
            ],
        ];

        foreach ($professionsByCategory as $categoryName => $professions) {
            $category = \App\Models\Category::where('name', $categoryName)->first();
            if ($category) {
                foreach ($professions as $profession) {
                    Profession::create([
                        'name' => $profession,
                        'category_id' => $category->id,
                    ]);
                }
            }
        }
    }
}