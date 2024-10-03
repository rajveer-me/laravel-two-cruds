<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('product.index', compact('products'));
    }

    public function create()
    {
        // return view('product.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'image|nullable|max:1999',
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->description = $request->description;

        // Handle image upload
        if ($request->hasFile('image')) {
            $img = $request->file('image');
            $filename = time() . '.' . $img->getClientOriginalExtension();
            $path = public_path('images');
            $img->move($path, $filename); // Move to public/images
            $product->image = $filename;  
        }

        $product->save();
        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        // return view('product.show', compact('product'));
    }

    public function edit(Product $product)
    {
        // return view('product.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'image|nullable|max:1999',
        ]);

        $product->name = $request->name;
        $product->description = $request->description;

        if ($request->hasFile('image')) {
            // Delete the old image if exists
            if ($product->image) {
                $oldImagePath = public_path('images/' . $product->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Upload new image
            $img = $request->file('image');
            $filename = time() . '.' . $img->getClientOriginalExtension();
            $img->move(public_path('images'), $filename);
            $product->image = $filename; // Update the image field
        }

        $product->save();
        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Delete the image before deleting the product
        if ($product->image) {
            $oldImagePath = public_path('images/' . $product->image);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
