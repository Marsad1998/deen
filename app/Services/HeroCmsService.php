<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HeroCmsService
{
    public const GROUP = 'cms_hero';

    public const DEFAULT_PHRASES = [
        'Premium Home Appliances',
        'Modern Refrigerators',
        'Powerful Air Conditioners',
        'Smart LED TVs',
        'Quality Washing Machines',
        'Electric Scooters',
    ];

    public const DEFAULT_SLIDES = [
        [
            'badge_text'             => 'Lahore Showroom Exclusive',
            'title'                  => 'Inverters & Refrigerators',
            'subtitle'               => 'Energy-efficient DC Inverters & frost-free refrigerators with official manufacturer warranty and Lahore showroom pickup.',
            'button_text'            => 'Shop Inverters & Cooling',
            'button_type'            => 'custom',
            'button_product_id'      => null,
            'button_custom_url'      => '/catalog?category=air-conditioners',
            'secondary_button_text'  => 'Enquire on WhatsApp',
            'secondary_button_url'   => 'https://wa.me/923007769932',
            'image'                  => 'images/hero/hero-slide-1.webp',
        ],
        [
            'badge_text'             => 'Smart 4K Entertainment',
            'title'                  => 'Smart 4K LED Televisions',
            'subtitle'               => 'Immersive Android & Google TV displays with bezel-less design and crystal-clear audio for the whole family.',
            'button_text'            => 'Explore Smart TVs',
            'button_type'            => 'custom',
            'button_product_id'      => null,
            'button_custom_url'      => '/catalog?category=led-tvs',
            'secondary_button_text'  => 'Enquire on WhatsApp',
            'secondary_button_url'   => 'https://wa.me/923007769932',
            'image'                  => 'images/hero/hero-slide-2.webp',
        ],
        [
            'badge_text'             => 'Heavy Duty Performance',
            'title'                  => 'Automatic Washing Machines',
            'subtitle'               => 'Top-load and front-load washing machines with inverter motors and gentle fabric wash care technology.',
            'button_text'            => 'View Washing Machines',
            'button_type'            => 'custom',
            'button_product_id'      => null,
            'button_custom_url'      => '/catalog?category=washing-machines',
            'secondary_button_text'  => 'Enquire on WhatsApp',
            'secondary_button_url'   => 'https://wa.me/923007769932',
            'image'                  => 'images/hero/hero-slide-3.webp',
        ],
        [
            'badge_text'             => 'Modern Transit & Kitchen',
            'title'                  => 'Kitchen & Electric Scooters',
            'subtitle'               => 'High-efficiency electric commuter scooters and premium built-in kitchen appliances for modern lifestyles.',
            'button_text'            => 'Explore Scooters & Kitchen',
            'button_type'            => 'custom',
            'button_product_id'      => null,
            'button_custom_url'      => '/catalog?category=scooters',
            'secondary_button_text'  => 'Enquire on WhatsApp',
            'secondary_button_url'   => 'https://wa.me/923007769932',
            'image'                  => 'images/hero/hero-slide-4.webp',
        ],
    ];

    /**
     * Get the full hero section configuration, resolving defaults and linked products.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $badgeText = StoreSetting::get('hero_badge_text') ?: 'Lahore Showroom & Online Store';
        $headline = StoreSetting::get('hero_headline') ?: 'Upgrade Your Home With';
        $description = StoreSetting::get('hero_description') ?: 'Explore quality home appliances, electronics and more from DEEN Electronics. Visit our Lahore showroom or enquire directly on WhatsApp.';

        // Animated phrases
        $phrasesRaw = StoreSetting::get('hero_animated_phrases');
        if (! empty($phrasesRaw)) {
            $decoded = json_decode($phrasesRaw, true);
            if (is_array($decoded) && count($decoded) > 0) {
                $phrases = array_values(array_filter(array_map('trim', $decoded)));
            } else {
                $lines = array_filter(array_map('trim', explode("\n", $phrasesRaw)));
                $phrases = count($lines) > 0 ? array_values($lines) : self::DEFAULT_PHRASES;
            }
        } else {
            $phrases = self::DEFAULT_PHRASES;
        }

        // Global fallback button
        $buttonText = StoreSetting::get('hero_button_text') ?: 'Browse Products';
        $buttonType = StoreSetting::get('hero_button_type') ?: 'catalog';
        $buttonProductId = StoreSetting::get('hero_button_product_id');
        $buttonCustomUrl = StoreSetting::get('hero_button_custom_url');

        $linkedProduct = null;
        if ($buttonType === 'product' && ! empty($buttonProductId)) {
            $linkedProduct = Product::with(['category', 'defaultVariant'])->find($buttonProductId);
        }

        $buttonUrl = match ($buttonType) {
            'product' => $linkedProduct ? route('storefront.product', $linkedProduct->slug) : route('storefront.catalog'),
            'custom'  => ! empty($buttonCustomUrl) ? $buttonCustomUrl : route('storefront.catalog'),
            default   => route('storefront.catalog'),
        };

        $secondaryButtonText = StoreSetting::get('hero_secondary_button_text') ?: 'Enquire on WhatsApp';

        // Build 4 rich slides, each with its own badge, title, subtitle, CTA button, and image
        $slides = [];
        for ($i = 1; $i <= 4; $i++) {
            $defaultSlide = self::DEFAULT_SLIDES[$i - 1];

            $slideBadge = StoreSetting::get("hero_slide_{$i}_badge") 
                ?: ($i === 1 && StoreSetting::get('hero_badge_text') ? StoreSetting::get('hero_badge_text') : ($defaultSlide['badge_text'] ?? $badgeText));
            $slideTitle = StoreSetting::get("hero_slide_{$i}_title") ?: $defaultSlide['title'];
            $slideSubtitle = StoreSetting::get("hero_slide_{$i}_subtitle") 
                ?: ($i === 1 && StoreSetting::get('hero_description') ? StoreSetting::get('hero_description') : ($defaultSlide['subtitle'] ?? $description));

            $slideBtnText = StoreSetting::get("hero_slide_{$i}_button_text") 
                ?: ($i === 1 && StoreSetting::get('hero_button_text') ? StoreSetting::get('hero_button_text') : ($defaultSlide['button_text'] ?? $buttonText));
            $slideBtnType = StoreSetting::get("hero_slide_{$i}_button_type") 
                ?: ($i === 1 && StoreSetting::get('hero_button_type') ? StoreSetting::get('hero_button_type') : ($defaultSlide['button_type'] ?? 'catalog'));
            $slideBtnProductId = StoreSetting::get("hero_slide_{$i}_button_product_id") 
                ?: ($i === 1 ? StoreSetting::get('hero_button_product_id') : null);
            $slideBtnCustomUrl = StoreSetting::get("hero_slide_{$i}_button_custom_url") 
                ?: ($i === 1 && StoreSetting::get('hero_button_custom_url') ? StoreSetting::get('hero_button_custom_url') : ($defaultSlide['button_custom_url'] ?? null));

            $slideLinkedProduct = null;
            if ($slideBtnType === 'product' && ! empty($slideBtnProductId)) {
                $slideLinkedProduct = Product::with(['category', 'defaultVariant'])->find($slideBtnProductId);
            }

            $slideBtnUrl = match ($slideBtnType) {
                'product' => $slideLinkedProduct ? route('storefront.product', $slideLinkedProduct->slug) : route('storefront.catalog'),
                'custom'  => ! empty($slideBtnCustomUrl) ? $slideBtnCustomUrl : route('storefront.catalog'),
                default   => route('storefront.catalog'),
            };

            $slideSecBtnText = StoreSetting::get("hero_slide_{$i}_secondary_button_text") 
                ?: ($i === 1 && StoreSetting::get('hero_secondary_button_text') ? StoreSetting::get('hero_secondary_button_text') : ($defaultSlide['secondary_button_text'] ?? $secondaryButtonText));
            $slideSecBtnUrl = StoreSetting::get("hero_slide_{$i}_secondary_button_url") ?: ($defaultSlide['secondary_button_url'] ?? 'https://wa.me/923007769932');

            $customImage = StoreSetting::get("hero_slide_{$i}_image");
            $imagePath = $customImage ?: $defaultSlide['image'];

            $imageUrl = Str::startsWith($imagePath, ['http://', 'https://'])
                ? $imagePath
                : (Str::startsWith($imagePath, 'storage/') ? asset($imagePath) : asset($imagePath));

            $slides[] = [
                'index'                 => $i - 1,
                'slide_number'          => $i,
                'badge_text'            => $slideBadge,
                'title'                 => $slideTitle,
                'subtitle'              => $slideSubtitle,
                'description'           => $slideSubtitle,
                'button_text'           => $slideBtnText,
                'button_type'           => $slideBtnType,
                'button_product_id'     => $slideBtnProductId ? (int) $slideBtnProductId : null,
                'button_custom_url'     => $slideBtnCustomUrl,
                'button_url'            => $slideBtnUrl,
                'linked_product'        => $slideLinkedProduct,
                'secondary_button_text' => $slideSecBtnText,
                'secondary_button_url'  => $slideSecBtnUrl,
                'image'                 => $imagePath,
                'image_url'             => $imageUrl,
            ];
        }

        return [
            'badge_text'             => $badgeText,
            'headline'               => $headline,
            'animated_phrases'       => $phrases,
            'animated_phrases_lines' => implode("\n", $phrases),
            'description'            => $description,
            'button_text'            => $buttonText,
            'button_type'            => $buttonType,
            'button_product_id'      => $buttonProductId ? (int) $buttonProductId : null,
            'button_custom_url'      => $buttonCustomUrl,
            'button_url'             => $buttonUrl,
            'linked_product'         => $linkedProduct,
            'secondary_button_text'  => $secondaryButtonText,
            'slides'                 => $slides,
        ];
    }

    /**
     * Update the hero configuration in store_settings.
     *
     * @param array<string, mixed> $data
     * @param array<string, UploadedFile> $files
     * @return array<string, mixed>
     */
    public function updateConfig(array $data, array $files = []): array
    {
        if (isset($data['badge_text'])) {
            StoreSetting::set('hero_badge_text', trim($data['badge_text']), self::GROUP);
        }

        if (isset($data['headline'])) {
            StoreSetting::set('hero_headline', trim($data['headline']), self::GROUP);
        }

        if (isset($data['description'])) {
            StoreSetting::set('hero_description', trim($data['description']), self::GROUP);
        }

        if (isset($data['animated_phrases'])) {
            $raw = $data['animated_phrases'];
            if (is_array($raw)) {
                $phrases = array_values(array_filter(array_map('trim', $raw)));
            } else {
                $lines = array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", (string) $raw)));
                $phrases = count($lines) > 0 ? array_values($lines) : self::DEFAULT_PHRASES;
            }
            StoreSetting::set('hero_animated_phrases', json_encode($phrases, JSON_UNESCAPED_UNICODE), self::GROUP);
        }

        if (isset($data['button_text'])) {
            StoreSetting::set('hero_button_text', trim($data['button_text']), self::GROUP);
        }

        if (isset($data['button_type'])) {
            StoreSetting::set('hero_button_type', in_array($data['button_type'], ['catalog', 'product', 'custom'], true) ? $data['button_type'] : 'catalog', self::GROUP);
        }

        if (array_key_exists('button_product_id', $data)) {
            $prodId = ! empty($data['button_product_id']) ? (string) $data['button_product_id'] : null;
            StoreSetting::set('hero_button_product_id', $prodId, self::GROUP);
        }

        if (array_key_exists('button_custom_url', $data)) {
            StoreSetting::set('hero_button_custom_url', $data['button_custom_url'] ? trim($data['button_custom_url']) : null, self::GROUP);
        }

        if (isset($data['secondary_button_text'])) {
            StoreSetting::set('hero_secondary_button_text', trim($data['secondary_button_text']), self::GROUP);
        }

        // Handle per-slide content: badge, title, subtitle, CTA button, and image
        for ($i = 1; $i <= 4; $i++) {
            $badgeVal = $data["slide_{$i}_badge"] ?? $data["hero_slide_{$i}_badge"] ?? null;
            if ($badgeVal !== null) {
                StoreSetting::set("hero_slide_{$i}_badge", trim($badgeVal), self::GROUP);
            }

            $titleVal = $data["slide_{$i}_title"] ?? $data["hero_slide_{$i}_title"] ?? null;
            if ($titleVal !== null) {
                StoreSetting::set("hero_slide_{$i}_title", trim($titleVal), self::GROUP);
            }

            $subVal = $data["slide_{$i}_subtitle"] ?? $data["hero_slide_{$i}_subtitle"] ?? null;
            if ($subVal !== null) {
                StoreSetting::set("hero_slide_{$i}_subtitle", trim($subVal), self::GROUP);
            }

            $btnTextVal = $data["slide_{$i}_button_text"] ?? $data["hero_slide_{$i}_button_text"] ?? null;
            if ($btnTextVal !== null) {
                StoreSetting::set("hero_slide_{$i}_button_text", trim($btnTextVal), self::GROUP);
            }

            $btnTypeVal = $data["slide_{$i}_button_type"] ?? $data["hero_slide_{$i}_button_type"] ?? null;
            if ($btnTypeVal !== null) {
                StoreSetting::set("hero_slide_{$i}_button_type", in_array($btnTypeVal, ['catalog', 'product', 'custom'], true) ? $btnTypeVal : 'catalog', self::GROUP);
            }

            $hasProdId = array_key_exists("slide_{$i}_button_product_id", $data) || array_key_exists("hero_slide_{$i}_button_product_id", $data);
            if ($hasProdId) {
                $rawPId = $data["slide_{$i}_button_product_id"] ?? $data["hero_slide_{$i}_button_product_id"] ?? null;
                $pId = ! empty($rawPId) ? (string) $rawPId : null;
                StoreSetting::set("hero_slide_{$i}_button_product_id", $pId, self::GROUP);
            }

            $hasCustomUrl = array_key_exists("slide_{$i}_button_custom_url", $data) || array_key_exists("hero_slide_{$i}_button_custom_url", $data);
            if ($hasCustomUrl) {
                $rawUrl = $data["slide_{$i}_button_custom_url"] ?? $data["hero_slide_{$i}_button_custom_url"] ?? null;
                StoreSetting::set("hero_slide_{$i}_button_custom_url", $rawUrl ? trim($rawUrl) : null, self::GROUP);
            }

            $secTextVal = $data["slide_{$i}_secondary_button_text"] ?? $data["hero_slide_{$i}_secondary_button_text"] ?? null;
            if ($secTextVal !== null) {
                StoreSetting::set("hero_slide_{$i}_secondary_button_text", trim($secTextVal), self::GROUP);
            }

            $secUrlVal = $data["slide_{$i}_secondary_button_url"] ?? $data["hero_slide_{$i}_secondary_button_url"] ?? null;
            if ($secUrlVal !== null) {
                StoreSetting::set("hero_slide_{$i}_secondary_button_url", trim($secUrlVal), self::GROUP);
            }

            // File upload for this slide
            if (isset($files["slide_{$i}_file"]) && $files["slide_{$i}_file"] instanceof UploadedFile) {
                $uploaded = $files["slide_{$i}_file"];
                $ext = strtolower($uploaded->getClientOriginalExtension());
                if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    $ext = 'webp';
                }
                $filename = 'hero-cms-slide-' . $i . '-' . time() . '.' . $ext;
                $uploaded->move(public_path('images/hero'), $filename);
                StoreSetting::set("hero_slide_{$i}_image", 'images/hero/' . $filename, self::GROUP);
            } elseif (isset($data["slide_{$i}_image_preset"]) && ! empty($data["slide_{$i}_image_preset"])) {
                StoreSetting::set("hero_slide_{$i}_image", trim($data["slide_{$i}_image_preset"]), self::GROUP);
            }
        }

        return $this->getConfig();
    }

    /**
     * Get Promotional Banner configuration.
     *
     * @return array<string, mixed>
     */
    public function getPromoConfig(): array
    {
        $isEnabled = StoreSetting::get('promo_is_enabled', '1') === '1';
        $badgeText = StoreSetting::get('promo_badge_text', 'Showroom & Online Specials');
        $headline = StoreSetting::get('promo_headline', 'Beat the Heat with Inverter Air Conditioners & Cooling Solutions');
        $description = StoreSetting::get('promo_description', 'Explore energy-saving inverters, deep freezers, and electric scooters with direct showroom pickup in Lahore or nationwide delivery enquiry.');
        $buttonText = StoreSetting::get('promo_button_text', 'Explore Cooling');
        $buttonUrl = StoreSetting::get('promo_button_url', '/catalog?category=air-conditioners');
        $secondaryButtonText = StoreSetting::get('promo_secondary_button_text', 'WhatsApp Specials Desk');
        $secondaryButtonUrl = StoreSetting::get('promo_secondary_button_url', 'https://wa.me/923007769932?text=Assalam%20o%20Alaikum%20DEEN%20Electronics,%20I%20am%20inquiring%20about%20current%20cooling%20and%20appliance%20specials.');
        $image = StoreSetting::get('promo_image', 'images/promotions/promo_showroom_cooling.webp');

        $imageUrl = Str::startsWith($image, ['http://', 'https://'])
            ? $image
            : (Str::startsWith($image, 'storage/') ? asset($image) : asset($image));

        return [
            'is_enabled'             => $isEnabled,
            'badge_text'             => $badgeText,
            'headline'               => $headline,
            'description'            => $description,
            'button_text'            => $buttonText,
            'button_url'             => $buttonUrl,
            'secondary_button_text'  => $secondaryButtonText,
            'secondary_button_url'   => $secondaryButtonUrl,
            'image'                  => $image,
            'image_url'              => $imageUrl,
        ];
    }

    /**
     * Update Promotional Banner configuration.
     *
     * @param array<string, mixed> $data
     * @param array<string, UploadedFile> $files
     * @return array<string, mixed>
     */
    public function updatePromoConfig(array $data, array $files = []): array
    {
        StoreSetting::set('promo_is_enabled', ! empty($data['is_enabled']) ? '1' : '0', 'cms_promo');
        if (isset($data['badge_text'])) StoreSetting::set('promo_badge_text', trim($data['badge_text']), 'cms_promo');
        if (isset($data['headline'])) StoreSetting::set('promo_headline', trim($data['headline']), 'cms_promo');
        if (isset($data['description'])) StoreSetting::set('promo_description', trim($data['description']), 'cms_promo');
        if (isset($data['button_text'])) StoreSetting::set('promo_button_text', trim($data['button_text']), 'cms_promo');
        if (isset($data['button_url'])) StoreSetting::set('promo_button_url', trim($data['button_url']), 'cms_promo');
        if (isset($data['secondary_button_text'])) StoreSetting::set('promo_secondary_button_text', trim($data['secondary_button_text']), 'cms_promo');
        if (isset($data['secondary_button_url'])) StoreSetting::set('promo_secondary_button_url', trim($data['secondary_button_url']), 'cms_promo');

        if (isset($files['promo_image_file']) && $files['promo_image_file'] instanceof UploadedFile) {
            $path = $files['promo_image_file']->storeAs('promotions', 'promo_' . time() . '.' . $files['promo_image_file']->getClientOriginalExtension(), 'public');
            StoreSetting::set('promo_image', 'storage/' . $path, 'cms_promo');
        } elseif (! empty($data['promo_image_preset'])) {
            StoreSetting::set('promo_image', trim($data['promo_image_preset']), 'cms_promo');
        }

        return $this->getPromoConfig();
    }

    /**
     * Get Trust Highlights configuration.
     *
     * @return array<string, mixed>
     */
    public function getTrustConfig(): array
    {
        return [
            'section_title' => StoreSetting::get('trust_section_title', 'Showroom Guarantees & Service'),
            'items' => [
                1 => [
                    'title'       => StoreSetting::get('trust_1_title', 'Lahore Showroom'),
                    'description' => StoreSetting::get('trust_1_description', "Visit us at Muhammad Amin's Plaza, Main GT Road, Manawan, Bata Pur. (10:00 AM – 10:00 PM)."),
                ],
                2 => [
                    'title'       => StoreSetting::get('trust_2_title', 'Nationwide Delivery'),
                    'description' => StoreSetting::get('trust_2_description', 'Delivery intended across Pakistan, subject to destination and product serviceability.'),
                ],
                3 => [
                    'title'       => StoreSetting::get('trust_3_title', 'Warranty Terms'),
                    'description' => StoreSetting::get('trust_3_description', 'Handled strictly according to applicable manufacturer terms for eligible faults.'),
                ],
                4 => [
                    'title'       => StoreSetting::get('trust_4_title', 'Payment Options'),
                    'description' => StoreSetting::get('trust_4_description', 'Cash on Delivery (subject to order confirmation & serviceability), Bank Transfer, JazzCash & Easypaisa.'),
                ],
            ],
        ];
    }

    /**
     * Update Trust Highlights configuration.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function updateTrustConfig(array $data): array
    {
        if (isset($data['section_title'])) StoreSetting::set('trust_section_title', trim($data['section_title']), 'cms_trust');
        for ($i = 1; $i <= 4; $i++) {
            if (isset($data["trust_{$i}_title"])) StoreSetting::set("trust_{$i}_title", trim($data["trust_{$i}_title"]), 'cms_trust');
            if (isset($data["trust_{$i}_description"])) StoreSetting::set("trust_{$i}_description", trim($data["trust_{$i}_description"]), 'cms_trust');
        }
        return $this->getTrustConfig();
    }

    /**
     * Get Showroom About configuration.
     *
     * @return array<string, mixed>
     */
    public function getAboutConfig(): array
    {
        return [
            'badge_text'     => StoreSetting::get('about_badge_text', 'Physical Showroom'),
            'headline'       => StoreSetting::get('about_headline', 'Visit DEEN Electronics in Lahore'),
            'narrative'      => StoreSetting::get('about_narrative', "Located at Muhammad Amin's Plaza on Main Grand Trunk Road in Manawan (near Shell Petrol Pump, Bata Pur), DEEN Electronics is committed to offering a trusted retail destination for appliances, cooling systems, 4K LED televisions, kitchen gear, and electric commuter scooters."),
            'daily_hours'    => StoreSetting::get('about_daily_hours', '10:00 AM – 10:00 PM'),
            'phone'          => StoreSetting::get('about_phone', StoreSetting::get('whatsapp_number', '0300-7769932')),
            'business_owner' => StoreSetting::get('about_owner', StoreSetting::get('sales_rep_name', 'Muhammad Aslam')),
            'address'        => StoreSetting::get('about_address', StoreSetting::get('store_address', "Muhammad Amin's Plaza, Main GT Road, Manawan, Bata Pur, Lahore")),
            'email'          => StoreSetting::get('about_email', 'deenselectronics1@gmail.com'),
            'whatsapp_url'   => StoreSetting::get('about_whatsapp_url', 'https://wa.me/923007769932?text=Assalam%20o%20Alaikum%20DEEN%20Electronics,%20I%20want%20to%20visit%20your%20Lahore%20showroom.'),
        ];
    }

    /**
     * Update Showroom About configuration.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function updateAboutConfig(array $data): array
    {
        if (isset($data['badge_text'])) StoreSetting::set('about_badge_text', trim($data['badge_text']), 'cms_about');
        if (isset($data['headline'])) StoreSetting::set('about_headline', trim($data['headline']), 'cms_about');
        if (isset($data['narrative'])) StoreSetting::set('about_narrative', trim($data['narrative']), 'cms_about');
        if (isset($data['daily_hours'])) StoreSetting::set('about_daily_hours', trim($data['daily_hours']), 'cms_about');
        if (isset($data['phone'])) StoreSetting::set('about_phone', trim($data['phone']), 'cms_about');
        if (isset($data['business_owner'])) StoreSetting::set('about_owner', trim($data['business_owner']), 'cms_about');
        if (isset($data['address'])) StoreSetting::set('about_address', trim($data['address']), 'cms_about');
        if (isset($data['email'])) StoreSetting::set('about_email', trim($data['email']), 'cms_about');
        if (isset($data['whatsapp_url'])) StoreSetting::set('about_whatsapp_url', trim($data['whatsapp_url']), 'cms_about');

        return $this->getAboutConfig();
    }
}
