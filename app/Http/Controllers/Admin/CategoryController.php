<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Display a paginated, searchable list of categories with product counts.
     */
    public function index(Request $request): View
    {
        $query = Category::withCount('products')
            ->with('parent');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', $request->boolean('status'));
        }

        $categories = $query->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show form to create a new category.
     */
    public function create(): View
    {
        $parentCategories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:150'],
            'slug'              => ['nullable', 'string', 'max:150', 'unique:categories,slug'],
            'parent_id'         => ['nullable', 'exists:categories,id'],
            'badge_text'        => ['nullable', 'string', 'max:50'],
            'models_count_text' => ['nullable', 'string', 'max:50'],
            'bg_color'          => ['nullable', 'string', 'max:50'],
            'description'       => ['nullable', 'string'],
            'meta_title'        => ['nullable', 'string', 'max:255'],
            'meta_description'  => ['nullable', 'string'],
            'sort_order'        => ['nullable', 'integer', 'min:0'],
            'is_active'         => ['nullable', 'boolean'],
            'image'             => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
            'image_url'         => ['nullable', 'string', 'max:500'],
        ]);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        // Handle unique collision fallback
        $originalSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        $imageUrl = $validated['image_url'] ?? null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = 'cat_' . $slug . '_' . time() . '.' . $ext;
            $file->move(public_path('images/categories'), $filename);
            $imageUrl = 'images/categories/' . $filename;
        }

        $category = Category::create([
            'parent_id'         => $validated['parent_id'] ?? null,
            'name'              => $validated['name'],
            'slug'              => $slug,
            'image_url'         => $imageUrl,
            'badge_text'        => $validated['badge_text'] ?? null,
            'models_count_text' => $validated['models_count_text'] ?? null,
            'bg_color'          => $validated['bg_color'] ?? null,
            'description'       => $validated['description'] ?? null,
            'meta_title'        => $validated['meta_title'] ?? null,
            'meta_description'  => $validated['meta_description'] ?? null,
            'sort_order'        => $validated['sort_order'] ?? 0,
            'is_active'         => $request->boolean('is_active', true),
        ]);

        $this->auditLogService->log(
            action: 'category.created',
            entity: 'Category',
            entityId: $category->id,
            changes: ['name' => $category->name, 'slug' => $category->slug],
            adminId: Auth::guard('admin')->id()
        );

        return redirect()->route('admin.categories.index')
            ->with('status', "Category '{$category->name}' has been created successfully!");
    }

    /**
     * Show form to edit an existing category.
     */
    public function edit(Category $category): View
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update an existing category.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:150'],
            'slug'              => ['nullable', 'string', 'max:150', 'unique:categories,slug,' . $category->id],
            'parent_id'         => ['nullable', 'exists:categories,id', 'not_in:' . $category->id],
            'badge_text'        => ['nullable', 'string', 'max:50'],
            'models_count_text' => ['nullable', 'string', 'max:50'],
            'bg_color'          => ['nullable', 'string', 'max:50'],
            'description'       => ['nullable', 'string'],
            'meta_title'        => ['nullable', 'string', 'max:255'],
            'meta_description'  => ['nullable', 'string'],
            'sort_order'        => ['nullable', 'integer', 'min:0'],
            'is_active'         => ['nullable', 'boolean'],
            'image'             => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
            'image_url'         => ['nullable', 'string', 'max:500'],
        ]);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        $imageUrl = $validated['image_url'] ?? $category->image_url;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = 'cat_' . $slug . '_' . time() . '.' . $ext;
            $file->move(public_path('images/categories'), $filename);
            $imageUrl = 'images/categories/' . $filename;
        }

        $oldChanges = $category->only(['name', 'slug', 'is_active', 'sort_order']);

        $category->update([
            'parent_id'         => $validated['parent_id'] ?? null,
            'name'              => $validated['name'],
            'slug'              => $slug,
            'image_url'         => $imageUrl,
            'badge_text'        => $validated['badge_text'] ?? null,
            'models_count_text' => $validated['models_count_text'] ?? null,
            'bg_color'          => $validated['bg_color'] ?? null,
            'description'       => $validated['description'] ?? null,
            'meta_title'        => $validated['meta_title'] ?? null,
            'meta_description'  => $validated['meta_description'] ?? null,
            'sort_order'        => $validated['sort_order'] ?? 0,
            'is_active'         => $request->boolean('is_active', true),
        ]);

        $this->auditLogService->log(
            action: 'category.updated',
            entity: 'Category',
            entityId: $category->id,
            changes: ['before' => $oldChanges, 'after' => $category->only(['name', 'slug', 'is_active', 'sort_order'])],
            adminId: Auth::guard('admin')->id()
        );

        return redirect()->route('admin.categories.index')
            ->with('status', "Category '{$category->name}' has been updated successfully!");
    }

    /**
     * Delete a category safely.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $productsCount = $category->products()->count();
        if ($productsCount > 0) {
            return redirect()->route('admin.categories.index')
                ->withErrors([
                    'error' => "Cannot delete category '{$category->name}' because {$productsCount} product(s) are currently attached to it. Please reassign the products or set the category to inactive instead.",
                ]);
        }

        $name = $category->name;
        $id = $category->id;
        $category->delete();

        $this->auditLogService->log(
            action: 'category.deleted',
            entity: 'Category',
            entityId: $id,
            changes: ['name' => $name],
            adminId: Auth::guard('admin')->id()
        );

        return redirect()->route('admin.categories.index')
            ->with('status', "Category '{$name}' has been deleted.");
    }

    /**
     * Quick toggle category active status.
     */
    public function toggleActive(Category $category): RedirectResponse
    {
        $category->is_active = ! $category->is_active;
        $category->save();

        $statusText = $category->is_active ? 'activated' : 'deactivated';

        $this->auditLogService->log(
            action: 'category.status_toggled',
            entity: 'Category',
            entityId: $category->id,
            changes: ['is_active' => $category->is_active],
            adminId: Auth::guard('admin')->id()
        );

        return redirect()->back()
            ->with('status', "Category '{$category->name}' has been {$statusText}.");
    }
}
