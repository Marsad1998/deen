<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Display a paginated, searchable list of brands with product counts.
     */
    public function index(Request $request): View
    {
        $query = Brand::withCount('products');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%")
                  ->orWhere('website', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', $request->boolean('status'));
        }

        $brands = $query->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.brands.index', compact('brands'));
    }

    /**
     * Show form to create a new brand.
     */
    public function create(): View
    {
        return view('admin.brands.create');
    }

    /**
     * Store a newly created brand.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:150'],
            'slug'             => ['nullable', 'string', 'max:150', 'unique:brands,slug'],
            'website'          => ['nullable', 'url', 'max:255'],
            'description'      => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'is_active'        => ['nullable', 'boolean'],
            'logo'             => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
            'logo_url'         => ['nullable', 'string', 'max:500'],
        ]);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        $originalSlug = $slug;
        $counter = 1;
        while (Brand::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        $logoUrl = $validated['logo_url'] ?? null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = 'brand_' . $slug . '_' . time() . '.' . $ext;
            $file->move(public_path('images/brands'), $filename);
            $logoUrl = 'images/brands/' . $filename;
        }

        $brand = Brand::create([
            'name'             => $validated['name'],
            'slug'             => $slug,
            'logo_url'         => $logoUrl,
            'website'          => $validated['website'] ?? null,
            'description'      => $validated['description'] ?? null,
            'meta_title'       => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'sort_order'       => $validated['sort_order'] ?? 0,
            'is_active'        => $request->boolean('is_active', true),
        ]);

        $this->auditLogService->log(
            action: 'brand.created',
            entity: 'Brand',
            entityId: $brand->id,
            changes: ['name' => $brand->name, 'slug' => $brand->slug],
            adminId: Auth::guard('admin')->id()
        );

        return redirect()->route('admin.brands.index')
            ->with('status', "Brand '{$brand->name}' has been created successfully!");
    }

    /**
     * Show form to edit an existing brand.
     */
    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update an existing brand.
     */
    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:150'],
            'slug'             => ['nullable', 'string', 'max:150', 'unique:brands,slug,' . $brand->id],
            'website'          => ['nullable', 'url', 'max:255'],
            'description'      => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'is_active'        => ['nullable', 'boolean'],
            'logo'             => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
            'logo_url'         => ['nullable', 'string', 'max:500'],
        ]);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        $logoUrl = $validated['logo_url'] ?? $brand->logo_url;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = 'brand_' . $slug . '_' . time() . '.' . $ext;
            $file->move(public_path('images/brands'), $filename);
            $logoUrl = 'images/brands/' . $filename;
        }

        $oldChanges = $brand->only(['name', 'slug', 'is_active', 'sort_order']);

        $brand->update([
            'name'             => $validated['name'],
            'slug'             => $slug,
            'logo_url'         => $logoUrl,
            'website'          => $validated['website'] ?? null,
            'description'      => $validated['description'] ?? null,
            'meta_title'       => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'sort_order'       => $validated['sort_order'] ?? 0,
            'is_active'        => $request->boolean('is_active', true),
        ]);

        $this->auditLogService->log(
            action: 'brand.updated',
            entity: 'Brand',
            entityId: $brand->id,
            changes: ['before' => $oldChanges, 'after' => $brand->only(['name', 'slug', 'is_active', 'sort_order'])],
            adminId: Auth::guard('admin')->id()
        );

        return redirect()->route('admin.brands.index')
            ->with('status', "Brand '{$brand->name}' has been updated successfully!");
    }

    /**
     * Delete a brand safely.
     */
    public function destroy(Brand $brand): RedirectResponse
    {
        $productsCount = $brand->products()->count();
        if ($productsCount > 0) {
            return redirect()->route('admin.brands.index')
                ->withErrors([
                    'error' => "Cannot delete brand '{$brand->name}' because {$productsCount} product(s) are associated with it. Please reassign the products or deactivate the brand instead.",
                ]);
        }

        $name = $brand->name;
        $id = $brand->id;
        $brand->delete();

        $this->auditLogService->log(
            action: 'brand.deleted',
            entity: 'Brand',
            entityId: $id,
            changes: ['name' => $name],
            adminId: Auth::guard('admin')->id()
        );

        return redirect()->route('admin.brands.index')
            ->with('status', "Brand '{$name}' has been deleted.");
    }

    /**
     * Quick toggle brand active status.
     */
    public function toggleActive(Brand $brand): RedirectResponse
    {
        $brand->is_active = ! $brand->is_active;
        $brand->save();

        $statusText = $brand->is_active ? 'activated' : 'deactivated';

        $this->auditLogService->log(
            action: 'brand.status_toggled',
            entity: 'Brand',
            entityId: $brand->id,
            changes: ['is_active' => $brand->is_active],
            adminId: Auth::guard('admin')->id()
        );

        return redirect()->back()
            ->with('status', "Brand '{$brand->name}' has been {$statusText}.");
    }
}
