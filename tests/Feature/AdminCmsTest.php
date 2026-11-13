<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Role;
use App\Models\StoreSetting;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StorefrontCategoriesSeeder;
use Database\Seeders\StorefrontDemoShowcaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCmsTest extends TestCase
{
    protected Admin $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(StorefrontCategoriesSeeder::class);
        $this->seed(StorefrontDemoShowcaseSeeder::class);

        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();

        $this->superAdmin = Admin::create([
            'role_id'   => $superAdminRole->id,
            'username'  => 'cms_admin_' . uniqid(),
            'email'     => 'cms_admin_' . uniqid() . '@example.com',
            'password'  => Hash::make('SecretPass123!'),
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Admin::where('username', 'LIKE', 'cms_admin_%')->delete();
        StoreSetting::whereIn('setting_group', ['cms_hero', 'cms_promo', 'cms_trust', 'cms_about'])->delete();

        parent::tearDown();
    }

    public function test_guest_is_redirected_to_admin_login_when_accessing_cms_hero(): void
    {
        $response = $this->get('/admin/cms/hero');

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_admin_can_view_cms_hero_management_form(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->get('/admin/cms/hero');

        $response->assertStatus(200);
        $response->assertSee('Homepage Hero Slider Management');
        $response->assertSee('Global Header Brand Identity');
        $response->assertSee('Per-Slide Content, Showroom Pill Badges &amp; CTA Buttons', false);
        $response->assertSee('Save &amp; Publish Hero Changes', false);
    }

    public function test_admin_can_update_hero_content_and_see_it_reflected_on_storefront(): void
    {
        $payload = [
            'badge_text'            => 'Grand Winter Mega Sale 2026',
            'headline'              => 'Discover Smart Living With',
            'animated_phrases'      => "Inverter Air Conditioners\nFrench Door Refrigerators\nOLED 4K Televisions",
            'description'           => 'Special showroom discounts and official brand warranty on all electronics.',
            'button_text'           => 'Explore Winter Deals',
            'button_type'           => 'catalog',
            'secondary_button_text' => 'WhatsApp Order Desk',
            'slide_1_title'         => 'Winter Chill Inverters',
            'slide_2_title'         => 'Cinematic OLED',
            'slide_3_title'         => 'Eco Washing',
            'slide_4_title'         => 'City Commuter Scooters',
            'slide_1_image_preset'  => 'images/hero/hero-slide-1.webp',
            'slide_2_image_preset'  => 'images/hero/hero-slide-2.webp',
            'slide_3_image_preset'  => 'images/hero/hero-slide-3.webp',
            'slide_4_image_preset'  => 'images/hero/hero-slide-4.webp',
        ];

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/cms/hero', $payload);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/cms/hero');
        $response->assertSessionHas('status');

        // Verify stored in database
        $this->assertEquals('Grand Winter Mega Sale 2026', StoreSetting::get('hero_badge_text'));
        $this->assertEquals('Discover Smart Living With', StoreSetting::get('hero_headline'));
        $this->assertEquals('Explore Winter Deals', StoreSetting::get('hero_button_text'));

        // Verify audit log
        $log = AuditLog::where('action', 'cms.hero_updated')->latest()->first();
        $this->assertNotNull($log);
        $this->assertEquals($this->superAdmin->id, $log->admin_id);

        // Verify storefront homepage reflects the CMS update
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Grand Winter Mega Sale 2026');
        $homeResponse->assertSee('Discover Smart Living With');
        $homeResponse->assertSee('Explore Winter Deals');
        $homeResponse->assertSee('Special showroom discounts and official brand warranty on all electronics.');
    }

    public function test_admin_can_link_hero_primary_button_directly_to_a_specific_product(): void
    {
        $product = Product::active()->firstOrFail();

        $payload = [
            'badge_text'            => 'Featured Product Spotlight',
            'headline'              => 'Experience The Future Of',
            'animated_phrases'      => "Flagship Quality\nUnmatched Performance",
            'description'           => 'Shop our highest rated product directly with full official warranty.',
            'button_text'           => 'Shop Featured SKU',
            'button_type'           => 'product',
            'button_product_id'     => $product->id,
            'secondary_button_text' => 'Enquire on WhatsApp',
        ];

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/cms/hero', $payload);

        $response->assertStatus(302);

        // Verify database settings
        $this->assertEquals('product', StoreSetting::get('hero_button_type'));
        $this->assertEquals((string) $product->id, StoreSetting::get('hero_button_product_id'));

        // Verify storefront homepage links directly to this product
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Shop Featured SKU');
        $homeResponse->assertSee(route('storefront.product', $product->slug));
    }

    public function test_admin_can_link_hero_button_to_custom_url(): void
    {
        $payload = [
            'badge_text'            => 'Showroom Offer',
            'headline'              => 'Upgrade Your Home With',
            'animated_phrases'      => 'Premium Appliances',
            'description'           => 'Check our special campaign page.',
            'button_text'           => 'View Campaign',
            'button_type'           => 'custom',
            'button_custom_url'     => 'https://deenelectronics.com/promo-2026',
            'secondary_button_text' => 'WhatsApp Desk',
        ];

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/cms/hero', $payload);

        $response->assertStatus(302);

        // Verify storefront links to custom URL
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('https://deenelectronics.com/promo-2026');
        $homeResponse->assertSee('View Campaign');
    }

    public function test_admin_can_upload_custom_hero_slide_image(): void
    {
        $fakeImage = UploadedFile::fake()->image('custom_slide.webp', 1920, 850);

        $payload = [
            'badge_text'            => 'Custom Slide Test',
            'headline'              => 'Upgrade Your Home With',
            'animated_phrases'      => 'Quality Washing Machines',
            'description'           => 'Testing custom slide upload.',
            'button_text'           => 'Browse Products',
            'button_type'           => 'catalog',
            'secondary_button_text' => 'Enquire on WhatsApp',
            'slide_1_title'         => 'Custom Upload Slide',
            'slide_1_file'          => $fakeImage,
        ];

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/cms/hero', $payload);

        $response->assertStatus(302);

        $savedImagePath = StoreSetting::get('hero_slide_1_image');
        $this->assertNotNull($savedImagePath);
        $this->assertStringStartsWith('images/hero/hero-cms-slide-1-', $savedImagePath);
        $this->assertFileExists(public_path($savedImagePath));

        // Clean up uploaded test file
        if (file_exists(public_path($savedImagePath))) {
            unlink(public_path($savedImagePath));
        }
    }

    public function test_admin_can_view_cms_hub_overview(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->get('/admin/cms');

        $response->assertStatus(200);
        $response->assertSee('Storefront CMS Management');
        $response->assertSee('CMS Overview Hub');
        $response->assertSee('Hero Slider (4 Slides &amp; CTAs)', false);
        $response->assertSee('Promotional Banner');
        $response->assertSee('Trust Highlights (4 Cards)');
        $response->assertSee('Showroom About &amp; Timings', false);
    }

    public function test_admin_can_update_per_slide_hero_content(): void
    {
        $payload = [
            'badge_text'                 => 'Global Hero Badge',
            'headline'                   => 'Headline Test',
            'animated_phrases'           => "Cooling\nEntertainment",
            'description'                => 'Global Description Test',
            'button_text'                => 'Explore',
            'button_type'                => 'catalog',
            'secondary_button_text'      => 'Chat',

            // Slide 1
            'hero_slide_1_badge'         => 'Lahore Exclusive Deal',
            'slide_1_title'              => 'Haier DC Inverter In Lahore',
            'hero_slide_1_subtitle'      => 'Save up to 66% on your electricity bills.',
            'hero_slide_1_button_text'   => 'Shop Air Conditioners',
            'hero_slide_1_button_type'   => 'catalog',
            'hero_slide_1_secondary_button_text' => 'Direct WhatsApp',

            // Slide 2
            'hero_slide_2_badge'         => 'Cinema At Home',
            'slide_2_title'              => 'TCL 4K UHD Smart Television',
            'hero_slide_2_subtitle'      => 'Stunning Dolby Vision and HDR10+ visuals.',
            'hero_slide_2_button_text'   => 'Shop Televisions',
            'hero_slide_2_button_type'   => 'custom',
            'hero_slide_2_button_custom_url' => '/catalog?category=led-tvs',
            'hero_slide_2_secondary_button_text' => 'Showroom Demo',

            'slide_1_image_preset'       => 'images/hero/hero-slide-1.webp',
            'slide_2_image_preset'       => 'images/hero/hero-slide-2.webp',
            'slide_3_image_preset'       => 'images/hero/hero-slide-3.webp',
            'slide_4_image_preset'       => 'images/hero/hero-slide-4.webp',
        ];

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/cms/hero', $payload);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/cms/hero');

        // Check StoreSetting values
        $this->assertEquals('Lahore Exclusive Deal', StoreSetting::get('hero_slide_1_badge'));
        $this->assertEquals('Haier DC Inverter In Lahore', StoreSetting::get('hero_slide_1_title'));
        $this->assertEquals('Cinema At Home', StoreSetting::get('hero_slide_2_badge'));
        $this->assertEquals('TCL 4K UHD Smart Television', StoreSetting::get('hero_slide_2_title'));

        // Check storefront
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Lahore Exclusive Deal');
        $homeResponse->assertSee('Haier DC Inverter In Lahore');
        $homeResponse->assertSee('Cinema At Home');
        $homeResponse->assertSee('TCL 4K UHD Smart Television');
    }

    public function test_admin_can_manage_promotional_banner(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->get('/admin/cms/promo');

        $response->assertStatus(200);
        $response->assertSee('Promotional Banner Content &amp; Buttons', false);

        $payload = [
            'is_enabled'    => '1',
            'badge_text'    => 'Mid-Season Flash Sale',
            'headline'      => 'Save up to 40% on Selected Inverters',
            'description'   => 'Valid this weekend only at DEEN Electronics Lahore.',
            'button_text'   => 'Grab The Deals',
            'button_url'    => '/catalog?discount=true',
            'secondary_text'=> 'Call Showroom',
            'secondary_url' => 'https://wa.me/923007769932',
        ];

        $postResponse = $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/cms/promo', $payload);

        $postResponse->assertStatus(302);
        $postResponse->assertRedirect('/admin/cms/promo');

        $this->assertEquals('Mid-Season Flash Sale', StoreSetting::get('promo_badge_text'));
        $this->assertEquals('Save up to 40% on Selected Inverters', StoreSetting::get('promo_headline'));

        // Check storefront reflects promotional banner
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Mid-Season Flash Sale');
        $homeResponse->assertSee('Save up to 40% on Selected Inverters');
        $homeResponse->assertSee('Valid this weekend only at DEEN Electronics Lahore.');
        $homeResponse->assertSee('Grab The Deals');
    }

    public function test_admin_can_manage_trust_highlights(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->get('/admin/cms/trust');

        $response->assertStatus(200);
        $response->assertSee('Showroom Trust &amp; Value Highlights', false);

        $payload = [
            'section_title'       => 'Why Choose DEEN Electronics',
            'trust_1_title'       => 'Verified Lahore Showroom',
            'trust_1_description' => 'Visit us on Main GT Road near Bata Pur.',
            'trust_2_title'       => 'Fast Nationwide Shipping',
            'trust_2_description' => 'Reliable logistics with careful packaging.',
            'trust_3_title'       => '100% Genuine Warranty',
            'trust_3_description' => 'Direct from official Pakistani brand distributors.',
            'trust_4_title'       => 'Flexible Cash & Bank Transfer',
            'trust_4_description' => 'JazzCash, EasyPaisa, or Cash on Delivery.',
        ];

        $postResponse = $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/cms/trust', $payload);

        $postResponse->assertStatus(302);
        $postResponse->assertRedirect('/admin/cms/trust');

        $this->assertEquals('Verified Lahore Showroom', StoreSetting::get('trust_1_title'));
        $this->assertEquals('Fast Nationwide Shipping', StoreSetting::get('trust_2_title'));

        // Check storefront
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Verified Lahore Showroom');
        $homeResponse->assertSee('Visit us on Main GT Road near Bata Pur.');
        $homeResponse->assertSee('100% Genuine Warranty');
    }

    public function test_admin_can_manage_showroom_about_and_timings(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->get('/admin/cms/about');

        $response->assertStatus(200);
        $response->assertSee('Showroom About, Location &amp; Timings', false);

        $payload = [
            'badge_text'     => 'Authentic Showroom',
            'headline'       => 'DEEN Electronics Lahore Mega Store',
            'narrative'      => 'A proud family-owned appliance and electronics showroom serving Lahore since decades.',
            'daily_hours'    => '09:30 AM – 10:30 PM (7 Days a Week)',
            'phone'          => '0300-7769932',
            'business_owner' => 'Muhammad Aslam',
            'address'        => 'Muhammad Amin Plaza, Main GT Road, Manawan, Bata Pur, Lahore',
            'email'          => 'deenselectronics1@gmail.com',
            'whatsapp_url'   => 'https://wa.me/923007769932',
        ];

        $postResponse = $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/cms/about', $payload);

        $postResponse->assertStatus(302);
        $postResponse->assertRedirect('/admin/cms/about');

        $this->assertEquals('Authentic Showroom', StoreSetting::get('about_badge_text'));
        $this->assertEquals('DEEN Electronics Lahore Mega Store', StoreSetting::get('about_headline'));
        $this->assertEquals('09:30 AM – 10:30 PM (7 Days a Week)', StoreSetting::get('about_daily_hours'));

        // Check storefront reflects updated showroom details
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Authentic Showroom');
        $homeResponse->assertSee('DEEN Electronics Lahore Mega Store');
        $homeResponse->assertSee('09:30 AM – 10:30 PM (7 Days a Week)');
    }
}
