<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Profession;
use Illuminate\Http\Request;

class AdminProfessionController extends Controller
{
    public function index()
    {
        $professions = Profession::with('category')->paginate(10);
        return view('professions.index', compact('professions'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('professions.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:professions',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|boolean'
        ]);
        // dd($validated['category_id']);
        Profession::create($validated);

        return redirect()->route('professions.index')
            ->with('success', 'Profession créée avec succès');
    }

    public function edit(Profession $profession)
    {
        $categories = Category::all();
        return view('professions.form', compact('profession', 'categories'));
    }

    public function update(Request $request, Profession $profession)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:professions,name,' . $profession->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|boolean'
        ]);

        $profession->update($validated);

        return redirect()->route('professions.index')
            ->with('success', 'Profession mise à jour avec succès');
    }

    public function destroy(Profession $profession)
    {
        try {
            $profession->delete();
            return redirect()->route('professions.index')
                ->with('success', 'Profession supprimée avec succès');
        } catch (\Exception $e) {
            return redirect()->route('professions.index')
                ->with('error', 'Impossible de supprimer cette profession car elle est utilisée par des artisans');
        }
    }
}
