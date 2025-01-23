<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentPage = $request->get('page', 1);
        $perPage = 5;
        $cacheKey = "products_page_{$currentPage}_{$perPage}";

        $products = Redis::get($cacheKey);

        if (!$products) {
            $products = Product::paginate($perPage);
            // dd($products, 'if');
            Redis::set($cacheKey, serialize($products));
            Redis::expire($cacheKey, 60);
        } else {
            $products = unserialize($products);
            // dd($products, 'else');
        }

        return view('index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Product::create($request->only('name'));

        Redis::flushAll();

        return redirect()->route('product.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('edit', ['product' => $product]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Product::findOrFail($id);
        $category->update($request->only('name'));

        Redis::flushAll();

        return redirect()->route('product.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        Redis::flushAll();

        return redirect()->route('categories.index');
    }
}
