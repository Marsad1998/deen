<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class StorefrontCategoriesSeeder extends Seeder
{
    /**
     * Seed the 12 client-confirmed categories from DEEN_Final.pdf.
     */
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Home Appliances',
                'slug'        => 'home-appliances',
                'description' => 'Essential electrical appliances for modern Pakistani homes.',
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Refrigerators & Freezers',
                'slug'        => 'refrigerators-freezers',
                'description' => 'Single door, double door, and deep freezers for food preservation.',
                'sort_order'  => 2,
            ],
            [
                'name'        => 'Washing Machines',
                'slug'        => 'washing-machines',
                'description' => 'Automatic, semi-automatic, and manual laundry washing machines.',
                'sort_order'  => 3,
            ],
            [
                'name'        => 'Air Conditioners',
                'slug'        => 'air-conditioners',
                'description' => 'Inverter and fixed-speed split air conditioners.',
                'sort_order'  => 4,
            ],
            [
                'name'        => 'LED TVs',
                'slug'        => 'led-tvs',
                'description' => 'Smart Android, 4K UHD, and digital television screens.',
                'sort_order'  => 5,
            ],
            [
                'name'        => 'Kitchen Appliances',
                'slug'        => 'kitchen-appliances',
                'description' => 'Microwaves, juicers, blenders, food processors, and cooking ranges.',
                'sort_order'  => 6,
            ],
            [
                'name'        => 'Fans',
                'slug'        => 'fans',
                'description' => 'Ceiling fans, bracket fans, exhaust fans, and pedestal fans.',
                'sort_order'  => 7,
            ],
            [
                'name'        => 'Geysers & Water Dispensers',
                'slug'        => 'geysers-water-dispensers',
                'description' => 'Instant gas geysers, electric geysers, and 3-tap water dispensers.',
                'sort_order'  => 8,
            ],
            [
                'name'        => 'Other Electronics',
                'slug'        => 'other-electronics',
                'description' => 'Power supplies, voltage stabilizers, emergency lamps, and audio systems.',
                'sort_order'  => 9,
            ],
            [
                'name'        => 'Scooters',
                'slug'        => 'scooters',
                'description' => 'Electric and fuel scooters available for online purchase and delivery.',
                'sort_order'  => 10,
            ],
            [
                'name'        => 'Molded Foam / Multiform Products',
                'slug'        => 'molded-foam-multiform',
                'description' => 'Quality molded foam products, multiform padding, and cushions.',
                'sort_order'  => 11,
            ],
            [
                'name'        => 'Furniture',
                'slug'        => 'furniture',
                'description' => 'Home and office furniture selections with separate delivery arrangements.',
                'sort_order'  => 12,
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name'        => $cat['name'],
                    'description' => $cat['description'],
                    'image_url'   => 'images/categories/cat_' . $cat['slug'] . '.webp',
                    'sort_order'  => $cat['sort_order'],
                    'is_active'   => true,
                ]
            );
        }
    }
}
