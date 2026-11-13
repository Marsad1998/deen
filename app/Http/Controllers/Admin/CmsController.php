<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\AuditLogService;
use App\Services\HeroCmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CmsController extends Controller
{
    public function __construct(
        protected HeroCmsService $heroCmsService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Unified CMS Dashboard Hub.
     */
    public function index(): View
    {
        $heroConfig = $this->heroCmsService->getConfig();
        $promoConfig = $this->heroCmsService->getPromoConfig();
        $trustConfig = $this->heroCmsService->getTrustConfig();
        $aboutConfig = $this->heroCmsService->getAboutConfig();

        return view('admin.cms.index', compact('heroConfig', 'promoConfig', 'trustConfig', 'aboutConfig'));
    }

    /**
     * Display the CMS Hero Section management form.
     */
    public function hero(): View
    {
        $heroConfig = $this->heroCmsService->getConfig();

        $products = Product::active()
            ->with('category')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'sku', 'category_id']);

        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $presetImages = [
            'images/hero/hero-slide-1.webp' => 'Slide 1: Inverters & Refrigerators',
            'images/hero/hero-slide-2.webp' => 'Slide 2: Smart 4K LED TVs',
            'images/hero/hero-slide-3.webp' => 'Slide 3: Automatic Washing Machines',
            'images/hero/hero-slide-4.webp' => 'Slide 4: Kitchen Appliances & Scooters',
            'images/hero/hero-cinematic.webp' => 'Cinematic: Home Appliances Plate',
        ];

        return view('admin.cms.hero', compact('heroConfig', 'products', 'categories', 'presetImages'));
    }

    /**
     * Update CMS Hero Section settings, per-slide content, CTA buttons, and images.
     */
    public function updateHero(Request $request): RedirectResponse
    {
        $rules = [
            'badge_text'             => ['nullable', 'string', 'max:100'],
            'headline'               => ['nullable', 'string', 'max:150'],
            'animated_phrases'       => ['nullable', 'string'],
            'description'            => ['nullable', 'string', 'max:600'],
            'button_text'            => ['nullable', 'string', 'max:50'],
            'button_type'            => ['nullable', 'in:catalog,product,custom'],
            'button_product_id'      => ['nullable', 'exists:products,id'],
            'button_custom_url'      => ['nullable', 'string', 'max:255'],
            'secondary_button_text'  => ['nullable', 'string', 'max:50'],
        ];

        for ($i = 1; $i <= 4; $i++) {
            $rules["slide_{$i}_badge"]                 = ['nullable', 'string', 'max:100'];
            $rules["hero_slide_{$i}_badge"]            = ['nullable', 'string', 'max:100'];
            $rules["slide_{$i}_title"]                 = ['nullable', 'string', 'max:150'];
            $rules["hero_slide_{$i}_title"]            = ['nullable', 'string', 'max:150'];
            $rules["slide_{$i}_subtitle"]              = ['nullable', 'string', 'max:500'];
            $rules["hero_slide_{$i}_subtitle"]         = ['nullable', 'string', 'max:500'];
            $rules["slide_{$i}_button_text"]           = ['nullable', 'string', 'max:60'];
            $rules["hero_slide_{$i}_button_text"]      = ['nullable', 'string', 'max:60'];
            $rules["slide_{$i}_button_type"]           = ['nullable', 'in:catalog,product,custom'];
            $rules["hero_slide_{$i}_button_type"]      = ['nullable', 'in:catalog,product,custom'];
            $rules["slide_{$i}_button_product_id"]     = ['nullable', 'exists:products,id'];
            $rules["hero_slide_{$i}_button_product_id"] = ['nullable', 'exists:products,id'];
            $rules["slide_{$i}_button_custom_url"]     = ['nullable', 'string', 'max:255'];
            $rules["hero_slide_{$i}_button_custom_url"] = ['nullable', 'string', 'max:255'];
            $rules["slide_{$i}_secondary_button_text"] = ['nullable', 'string', 'max:60'];
            $rules["hero_slide_{$i}_secondary_button_text"] = ['nullable', 'string', 'max:60'];
            $rules["slide_{$i}_secondary_button_url"]  = ['nullable', 'string', 'max:255'];
            $rules["hero_slide_{$i}_secondary_button_url"] = ['nullable', 'string', 'max:255'];
            $rules["slide_{$i}_file"]                  = ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'];
            $rules["slide_{$i}_image_preset"]          = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        $admin = Auth::guard('admin')->user();

        $updatedConfig = $this->heroCmsService->updateConfig($validated, $request->allFiles());

        $this->auditLogService->log(
            action: 'cms.hero_updated',
            entity: 'StoreSetting',
            entityId: null,
            changes: [
                'headline' => $validated['headline'] ?? 'Updated Hero',
            ],
            adminId: $admin?->id
        );

        return redirect()->route('admin.cms.hero')
            ->with('status', 'Homepage Hero Section CMS content, per-slide CTA buttons, and images updated successfully!');
    }

    /**
     * Display the CMS Promotional Banner management form.
     */
    public function promo(): View
    {
        $promoConfig = $this->heroCmsService->getPromoConfig();

        $presetImages = [
            'images/promotions/promo_showroom_cooling.webp' => 'Preset: Showroom Cooling & Inverters',
        ];

        return view('admin.cms.promo', compact('promoConfig', 'presetImages'));
    }

    /**
     * Update CMS Promotional Banner.
     */
    public function updatePromo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'is_enabled'            => ['nullable', 'boolean'],
            'badge_text'            => ['required', 'string', 'max:100'],
            'headline'              => ['required', 'string', 'max:200'],
            'description'           => ['required', 'string', 'max:600'],
            'button_text'           => ['required', 'string', 'max:60'],
            'button_url'            => ['required', 'string', 'max:255'],
            'secondary_button_text' => ['nullable', 'string', 'max:60'],
            'secondary_button_url'  => ['nullable', 'string', 'max:255'],
            'promo_image_file'      => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'promo_image_preset'    => ['nullable', 'string', 'max:255'],
        ]);

        $admin = Auth::guard('admin')->user();

        $this->heroCmsService->updatePromoConfig($validated, $request->allFiles());

        $this->auditLogService->log(
            action: 'cms.promo_updated',
            entity: 'StoreSetting',
            entityId: null,
            changes: [
                'headline' => $validated['headline'],
            ],
            adminId: $admin?->id
        );

        return redirect()->route('admin.cms.promo')
            ->with('status', 'Promotional Banner configuration updated successfully!');
    }

    /**
     * Display Trust & Value Highlights management form.
     */
    public function trustFeatures(): View
    {
        $trustConfig = $this->heroCmsService->getTrustConfig();

        return view('admin.cms.trust', compact('trustConfig'));
    }

    /**
     * Update Trust & Value Highlights.
     */
    public function updateTrustFeatures(Request $request): RedirectResponse
    {
        $rules = [
            'section_title' => ['required', 'string', 'max:150'],
        ];

        for ($i = 1; $i <= 4; $i++) {
            $rules["trust_{$i}_title"]       = ['required', 'string', 'max:100'];
            $rules["trust_{$i}_description"] = ['required', 'string', 'max:300'];
        }

        $validated = $request->validate($rules);

        $admin = Auth::guard('admin')->user();

        $this->heroCmsService->updateTrustConfig($validated);

        $this->auditLogService->log(
            action: 'cms.trust_updated',
            entity: 'StoreSetting',
            entityId: null,
            changes: [
                'section_title' => $validated['section_title'],
            ],
            adminId: $admin?->id
        );

        return redirect()->route('admin.cms.trust')
            ->with('status', 'Showroom Trust & Value Highlights updated successfully!');
    }

    /**
     * Display Showroom About & Timings management form.
     */
    public function showroomAbout(): View
    {
        $aboutConfig = $this->heroCmsService->getAboutConfig();

        return view('admin.cms.about', compact('aboutConfig'));
    }

    /**
     * Update Showroom About & Timings.
     */
    public function updateShowroomAbout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'badge_text'     => ['required', 'string', 'max:100'],
            'headline'       => ['required', 'string', 'max:200'],
            'narrative'      => ['required', 'string', 'max:1000'],
            'daily_hours'    => ['required', 'string', 'max:100'],
            'phone'          => ['required', 'string', 'max:50'],
            'business_owner' => ['required', 'string', 'max:100'],
            'address'        => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:150'],
            'whatsapp_url'   => ['nullable', 'string', 'max:255'],
        ]);

        $admin = Auth::guard('admin')->user();

        $this->heroCmsService->updateAboutConfig($validated);

        $this->auditLogService->log(
            action: 'cms.about_updated',
            entity: 'StoreSetting',
            entityId: null,
            changes: [
                'headline' => $validated['headline'],
            ],
            adminId: $admin?->id
        );

        return redirect()->route('admin.cms.about')
            ->with('status', 'Showroom Context, Timings, and Location Details updated successfully!');
    }

    /**
     * Display Homepage Categories & Brands display management form.
     */
    public function displaySections(): View
    {
        $displayConfig = $this->heroCmsService->getDisplaySectionsConfig();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $brands = Brand::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.cms.display', compact('displayConfig', 'categories', 'brands'));
    }

    /**
     * Update Homepage Categories & Brands display configuration.
     */
    public function updateDisplaySections(Request $request): RedirectResponse
    {
        $rules = [
            'categories_title'    => ['nullable', 'string', 'max:150'],
            'categories_subtitle' => ['nullable', 'string', 'max:500'],
            'categories_enabled'  => ['nullable', 'boolean'],
            'brands_title'        => ['nullable', 'string', 'max:150'],
            'brands_subtitle'     => ['nullable', 'string', 'max:500'],
            'brands_enabled'      => ['nullable', 'boolean'],
            'nav_category_ids'    => ['nullable', 'array'],
            'nav_category_ids.*'  => ['integer', 'exists:categories,id'],
            'home_category_ids'   => ['nullable', 'array'],
            'home_category_ids.*' => ['integer', 'exists:categories,id'],
            'home_brand_ids'      => ['nullable', 'array'],
            'home_brand_ids.*'    => ['integer', 'exists:brands,id'],
        ];

        $validated = $request->validate($rules);
        $admin = Auth::guard('admin')->user();

        $this->heroCmsService->updateDisplaySectionsConfig($validated);

        $this->auditLogService->log(
            action: 'cms.display_sections_updated',
            entity: 'StoreSetting',
            entityId: null,
            changes: [
                'nav_categories_count' => count($validated['nav_category_ids'] ?? []),
                'home_categories_count' => count($validated['home_category_ids'] ?? []),
                'home_brands_count' => count($validated['home_brand_ids'] ?? []),
            ],
            adminId: $admin?->id
        );

        return redirect()->route('admin.cms.display')
            ->with('status', 'Homepage Categories, Brands & Navigation display settings updated successfully!');
    }
}
