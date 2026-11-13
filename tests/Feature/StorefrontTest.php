<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\StorefrontCategoriesSeeder;
use Database\Seeders\StorefrontDemoShowcaseSeeder;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StorefrontCategoriesSeeder::class);
        $this->seed(StorefrontDemoShowcaseSeeder::class);
    }

    public function test_homepage_renders_successfully_with_client_branding_and_showroom_details(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Client branding & logo
        $response->assertSee('DEEN Electronics');
        $response->assertSee('/images/deen-logo.png');

        // Showroom address & contact verified from DEEN_Final.pdf
        $response->assertSee("Muhammad Amin's Plaza", false);
        $response->assertSee('Manawan');
        $response->assertSee('Bata Pur');
        $response->assertSee('Lahore');
        $response->assertSee('03007769932');
        $response->assertSee('deenselectronics1@gmail.com');
        $response->assertSee('10:00 AM', false);
        $response->assertSee('10:00 PM', false);

        // Delivery disclaimer per user instructions
        $response->assertSee('Nationwide Delivery');
        $response->assertSee('destination and product serviceability');
    }

    public function test_homepage_renders_chargehub_inspired_cinematic_hero_with_animated_phrases(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Cinematic background product image & 4-image reference slider
        $response->assertSee('/images/hero-cinematic.jpg');
        $response->assertSee('images/hero/hero-slide-1.webp');
        $response->assertSee('images/hero/hero-slide-2.webp');
        $response->assertSee('images/hero/hero-slide-3.webp');
        $response->assertSee('images/hero/hero-slide-4.webp');

        // Slider controls & indicator pills
        $response->assertSee('Previous Slide');
        $response->assertSee('Next Slide');
        $response->assertSee('Go to slide 1');
        $response->assertSee('Go to slide 4');

        // Fixed headline
        $response->assertSee('Upgrade Your Home With');

        // Animated rotating category phrases
        $response->assertSee('Premium Home Appliances');
        $response->assertSee('Modern Refrigerators');
        $response->assertSee('Powerful Air Conditioners');
        $response->assertSee('Smart LED TVs');
        $response->assertSee('Quality Washing Machines');
        $response->assertSee('Electric Scooters');

        // Exact description copy requested by user
        $response->assertSee('Explore quality home appliances, electronics and more from DEEN Electronics. Visit our Lahore showroom or enquire directly on WhatsApp.');

        // Buttons
        $response->assertSee('Browse Products');
        $response->assertSee('Enquire on WhatsApp');
        $response->assertSee('https://wa.me/923007769932', false);
    }

    public function test_homepage_displays_the_12_confirmed_categories(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        $expectedCategories = [
            'Home Appliances',
            'Refrigerators & Freezers',
            'Washing Machines',
            'Air Conditioners',
            'LED TVs',
            'Kitchen Appliances',
            'Fans',
            'Geysers & Water Dispensers',
            'Other Electronics',
            'Scooters',
            'Molded Foam / Multiform Products',
            'Furniture',
        ];

        foreach ($expectedCategories as $catName) {
            $response->assertSee($catName);
        }
    }

    public function test_homepage_displays_featured_demo_products_with_guard_tags(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Demo products in local/testing are marked with DEMO PREVIEW
        $response->assertSee('DEMO PREVIEW');

        // Enquiry action is via WhatsApp
        $response->assertSee('Enquire on WhatsApp');
        $response->assertSee('https://wa.me/923007769932', false);
    }

    public function test_catalog_page_renders_with_filtering(): void
    {
        $response = $this->get('/catalog');

        $response->assertStatus(200);
        $response->assertSee('All Products');
        $response->assertSee('Showing');

        // Test category filtering
        $acCategory = Category::where('slug', 'air-conditioners')->first();
        if ($acCategory) {
            $catResponse = $this->get('/catalog?category=' . $acCategory->slug);
            $catResponse->assertStatus(200);
            $catResponse->assertSee($acCategory->name);
        }
    }

    public function test_catalog_search_filters_matching_products(): void
    {
        $response = $this->get('/catalog?search=Haier');

        $response->assertStatus(200);
        $response->assertSee('matching', false);
        $response->assertSee('Haier');

        // Test non-matching query
        $emptyResponse = $this->get('/catalog?search=NonExistentDevice12345XYZ');
        $emptyResponse->assertStatus(200);
        $emptyResponse->assertSee('No products match your filter criteria');
        $emptyResponse->assertSee('Reset All Filters');
    }

    public function test_catalog_left_side_filters_render_and_filter_accurately(): void
    {
        $response = $this->get('/catalog');

        $response->assertStatus(200);

        // Sidebar filter headers
        $response->assertSee('Product Categories');
        $response->assertSee('Official Brands');
        $response->assertSee('Price Range (PKR)');
        $response->assertSee('Assurance &amp; Availability', false);
        $response->assertSee('Delivery Type');
        $response->assertSee('Showroom Assistance');

        // Brands are displayed in sidebar
        $response->assertSee('Haier');
        $response->assertSee('Dawlance');
        $response->assertSee('TCL');
        $response->assertSee('Crown');

        // Test brand filtering
        $brandResponse = $this->get('/catalog?brand=haier');
        $brandResponse->assertStatus(200);
        $brandResponse->assertSee('Haier 1.5 Ton');
        $brandResponse->assertDontSee('Dawlance Double Door');

        // Test price range filtering (Rs 150,000 to Rs 200,000)
        $priceResponse = $this->get('/catalog?min_price=150000&max_price=200000');
        $priceResponse->assertStatus(200);
        $priceResponse->assertSee('DEMO-AC-H15T3'); // Haier AC: Rs 185,000
        $priceResponse->assertDontSee('DEMO-FAN-PF56'); // Pak Fan: Rs 14,500 (filtered out)
        $priceResponse->assertSee('Active Filters (1)');
        $priceResponse->assertSee('Clear All');

        // Test stock filtering
        $stockResponse = $this->get('/catalog?in_stock=1');
        $stockResponse->assertStatus(200);
        $stockResponse->assertSee('In Stock Only');

        // Test bulky freight delivery filtering
        $bulkyResponse = $this->get('/catalog?delivery_type=bulky');
        $bulkyResponse->assertStatus(200);
        $bulkyResponse->assertSee('DEMO-SCOOT-1200'); // Scooter is bulky
        $bulkyResponse->assertDontSee('DEMO-FAN-PF56'); // Ceiling Fan is standard parcel delivery
    }

    public function test_product_detail_page_renders_specifications_and_whatsapp_action(): void
    {
        $product = Product::with(['variants', 'specifications', 'media'])->where('is_active', true)->first();

        $this->assertNotNull($product, 'At least one active product must exist for testing.');

        $response = $this->get('/product/' . $product->slug);

        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee($product->sku);

        // Delivery handling notice
        $response->assertSee('Nationwide Delivery Terms');
        $response->assertSee('Delivery across Pakistan');
        $response->assertSee('Scooters, furniture');

        // Pre-filled WhatsApp button
        $response->assertSee('https://wa.me/923007769932?text=', false);
        $response->assertSee('Enquire & Order on WhatsApp', false);

        // Check specifications section
        $response->assertSee('Technical Specifications');
        if ($product->specifications->isNotEmpty()) {
            $response->assertSee($product->specifications->first()->spec_key);
            $response->assertSee($product->specifications->first()->spec_value);
        }
    }

    public function test_functional_cart_and_add_to_cart_controls_are_present(): void
    {
        // Check Homepage has Cart link in header
        $homeResponse = $this->get('/');
        $homeResponse->assertSee('/cart');

        // Check Catalog has Cart link
        $catalogResponse = $this->get('/catalog');
        $catalogResponse->assertSee('/cart');

        // Check Product Detail has Add to Cart form
        $product = Product::where('is_active', true)->first();
        if ($product) {
            $productResponse = $this->get('/product/' . $product->slug);
            $productResponse->assertSee('Add to Cart');
            $productResponse->assertSee('/cart');
        }
    }

    public function test_homepage_renders_category_images_and_promotional_banner(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Category images rendered for all 12 categories
        $categories = Category::all();
        $this->assertCount(12, $categories);

        foreach ($categories as $cat) {
            $response->assertSee('images/categories/cat_' . $cat->slug . '.webp');
        }

        // Promotional banner rendered
        $response->assertSee('images/promotions/promo_showroom_cooling.webp');

        // Hero WebP rendered
        $response->assertSee('images/hero/hero-cinematic.webp');
    }

    public function test_product_detail_page_renders_multi_angle_gallery_images(): void
    {
        $product = Product::with('media')->where('is_active', true)->first();
        $this->assertNotNull($product);
        $this->assertGreaterThanOrEqual(2, $product->media->count());

        $response = $this->get('/product/' . $product->slug);
        $response->assertStatus(200);

        foreach ($product->media as $media) {
            $response->assertSee($media->url);
        }
    }

    public function test_search_autocomplete_endpoint_returns_products_with_images_and_pricing(): void
    {
        $response = $this->getJson('/search/autocomplete?q=Haier');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'query',
            'results' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'url',
                    'sku',
                    'category',
                    'brand',
                    'thumbnail_url',
                    'price',
                    'original_price',
                    'badge',
                    'in_stock',
                    'warranty',
                ],
            ],
            'total',
        ]);

        $data = $response->json();
        $this->assertGreaterThanOrEqual(1, count($data['results']));
        $first = $data['results'][0];
        $this->assertStringContainsString('Haier', $first['name']);
        $this->assertNotEmpty($first['thumbnail_url']);
        $this->assertNotEmpty($first['url']);
        $this->assertNotNull($first['price']);
    }

    public function test_search_autocomplete_handles_short_query_and_empty_results(): void
    {
        // 1-character query returns empty array
        $shortResponse = $this->getJson('/search/autocomplete?q=H');
        $shortResponse->assertStatus(200);
        $shortResponse->assertJson(['results' => [], 'total' => 0]);

        // Non-existent search term returns empty array
        $emptyResponse = $this->getJson('/search/autocomplete?q=NonExistentDevice999');
        $emptyResponse->assertStatus(200);
        $emptyResponse->assertJson(['results' => [], 'total' => 0]);
    }

    public function test_ai_faq_json_file_exists_and_has_valid_knowledgebase_structure(): void
    {
        $faqPath = public_path('data/deen-ai-faq.json');
        $this->assertFileExists($faqPath);

        $jsonContent = file_get_contents($faqPath);
        $this->assertJson($jsonContent);

        $data = json_decode($jsonContent, true);

        // Verify Bot Profile
        $this->assertArrayHasKey('bot_profile', $data);
        $this->assertEquals('DEEN AI Assistant', $data['bot_profile']['name']);
        $this->assertEquals('Muhammad Aslam', $data['bot_profile']['showroom_contact']);
        $this->assertEquals('0300-7769932', $data['bot_profile']['showroom_phone']);
        $this->assertEquals('03007769932', $data['bot_profile']['whatsapp_number']);
        $this->assertStringContainsString('https://wa.me/923007769932', $data['bot_profile']['whatsapp_url']);

        // Verify Greeting & Suggested Questions
        $this->assertArrayHasKey('greeting', $data);
        $this->assertNotEmpty($data['greeting']['message']);
        $this->assertIsArray($data['greeting']['suggested_questions']);
        $this->assertGreaterThanOrEqual(4, count($data['greeting']['suggested_questions']));

        // Verify QA Pairs
        $this->assertArrayHasKey('qa_pairs', $data);
        $this->assertIsArray($data['qa_pairs']);
        $this->assertGreaterThanOrEqual(6, count($data['qa_pairs']));

        // Verify Showroom Location and Human Handoff QA presence
        $qaIds = array_column($data['qa_pairs'], 'id');
        $this->assertContains('showroom_location', $qaIds);
        $this->assertContains('delivery_policy', $qaIds);
        $this->assertContains('payment_methods', $qaIds);
        $this->assertContains('official_warranty', $qaIds);
        $this->assertContains('talk_to_human', $qaIds);

        // Verify human handoff action
        $talkToHuman = collect($data['qa_pairs'])->firstWhere('id', 'talk_to_human');
        $this->assertEquals('human_handoff', $talkToHuman['action']);
        $this->assertContains('human', $talkToHuman['keywords']);
        $this->assertContains('whatsapp', $talkToHuman['keywords']);
    }

    public function test_storefront_renders_interactive_ai_chat_modal_and_order_desk(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // AI Chat Widget & Alpine controller
        $response->assertSee('deenAiChat()', false);
        $response->assertSee('open-deen-chat', false);
        $response->assertSee('DEEN AI Assistant');
        $response->assertSee('Virtual Order & Sales Desk', false);
        $response->assertSee('/data/deen-ai-faq.json');

        // Human handoff & WhatsApp redirect
        $response->assertSee('Muhammad Aslam');
        $response->assertSee('0300-7769932');
        $response->assertSee('https://wa.me/923007769932', false);
        $response->assertSee('Redirect to WhatsApp');
    }

    public function test_mobile_navigation_sidebar_renders_with_drawer_and_all_twelve_categories(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Hamburger trigger button for small devices
        $response->assertSee('aria-label="Open mobile navigation sidebar"', false);
        $response->assertSee('mobileMenuOpen = true', false);

        // Body scroll locking watcher
        $response->assertSee('$watch(\'mobileMenuOpen\', value => document.body.classList.toggle(\'overflow-hidden\', value))', false);

        // Off-canvas mobile sidebar drawer
        $response->assertSee('aria-label="Mobile Navigation Sidebar"', false);
        $response->assertSee('Close navigation sidebar');
        $response->assertSee('-translate-x-full');
        $response->assertSee('translate-x-0');

        // All 12 categories in the mobile sidebar
        $mobileCategories = [
            'Home Appliances',
            'Refrigerators & Freezers',
            'Washing Machines',
            'Air Conditioners',
            'Smart LED TVs',
            'Kitchen Appliances',
            'Fans & Inverter Fans',
            'Geysers & Water Dispensers',
            'Electric Scooters',
            'Furniture (Home & Office)',
            'Molded Foam Products',
            'Other Electronics',
        ];

        foreach ($mobileCategories as $categoryName) {
            $response->assertSee($categoryName);
        }

        // In-sidebar search bar
        $response->assertSee('Search products...');

        // In-sidebar Showroom contact actions
        $response->assertSee('Call Showroom: 0300-7769932');
        $response->assertSee('WhatsApp Order Desk &amp; AI Assistant', false);
    }
}
