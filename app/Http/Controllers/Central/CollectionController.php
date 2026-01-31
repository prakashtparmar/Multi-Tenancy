<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = Collection::orderBy('name')->paginate(10);
        return view('central.collections.index', compact('collections'));
    }

    public function create()
    {
        return view('central.collections.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:collections,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Collection::create($validated);

        return redirect()->route('central.collections.index')
            ->with('success', 'Collection created successfully.');
    }

    public function edit(Collection $collection)
    {
        return view('central.collections.edit', compact('collection'));
    }

    public function update(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('collections')->ignore($collection->id),
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $collection->update($validated);

        return redirect()->route('central.collections.index')
            ->with('success', 'Collection updated successfully.');
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();

        return redirect()->route('central.collections.index')
            ->with('success', 'Collection deleted successfully.');
    }
}
