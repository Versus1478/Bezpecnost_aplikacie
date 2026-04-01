<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()->get();

        return response()->json(['categories' => $categories], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/']
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#808080'
        ]);

        return response()->json([
            'message' => 'Kategória bola vytvorená.',
            'category' => $category
        ], Response::HTTP_CREATED);
    }

    public function show(string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Kategória nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['category' => $category], Response::HTTP_OK);
    }

    public function update(Request $request, string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Kategória nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id)
            ],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/']
        ]);

        $category->update($validated);

        return response()->json([
            'category' => $category
        ], Response::HTTP_OK);
    }

    public function destroy(string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Kategória nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        $category->delete();

        return response()->json([
            'message' => 'Kategória bola odstránená.'
        ], Response::HTTP_OK);
    }
}
