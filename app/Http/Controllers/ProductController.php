<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $query = Product::query()->with('category');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->integer('category')) {
            $query->where('category_id', $categoryId);
        }

        match ($request->string('stock')->toString()) {
            'in' => $query->whereColumn('stock_quantity', '>', 'min_stock_alert'),
            'low' => $query->whereColumn('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'min_stock_alert'),
            'out' => $query->where('stock_quantity', 0),
            default => null,
        };

        match ($request->string('sort')->toString()) {
            'price' => $query->orderBy('price'),
            'stock' => $query->orderBy('stock_quantity'),
            default => $query->orderBy('name'),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('pages.products.index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'stock', 'sort']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        Product::create($this->withImage($request, $validated));

        return redirect()->route('products')->with('status', 'Товар успішно додано.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validated($request);

        $product->update($this->withImage($request, $validated, $product));

        return redirect()->route('products')->with('status', 'Товар успішно оновлено.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path && str_starts_with($product->image_path, 'storage/')) {
            Storage::disk('public')->delete(substr($product->image_path, strlen('storage/')));
        }

        $product->delete();

        return redirect()->route('products')->with('status', 'Товар видалено.');
    }

    public function duplicate(Product $product): RedirectResponse
    {
        $copy = $product->replicate();
        $copy->name = $product->name.' (копія)';
        $copy->save();

        return redirect()->route('products')->with('status', 'Товар продубльовано.');
    }

    public function toggleActive(Product $product): JsonResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return response()->json(['is_active' => $product->is_active]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'new_category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock_alert' => ['required', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:4096'],
        ], [
            'name.required' => 'Введіть назву товару.',
            'price.required' => 'Введіть ціну товару.',
            'price.numeric' => 'Ціна має бути числом.',
            'unit.required' => 'Оберіть одиницю виміру.',
            'stock_quantity.required' => 'Введіть залишок на складі.',
            'image.image' => 'Файл має бути зображенням.',
            'image.max' => 'Розмір зображення не має перевищувати 4 МБ.',
        ]);
    }

    private function withImage(Request $request, array $validated, ?Product $product = null): array
    {
        if (! empty($validated['new_category'])) {
            $category = Category::firstOrCreate(['name' => $validated['new_category']]);
            $validated['category_id'] = $category->id;
        }
        unset($validated['new_category']);

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($product?->image_path && str_starts_with($product->image_path, 'storage/')) {
                Storage::disk('public')->delete(substr($product->image_path, strlen('storage/')));
            }

            $path = $request->file('image')->store('products', 'public');
            $validated['image_path'] = 'storage/'.$path;
        }

        return $validated;
    }
}
