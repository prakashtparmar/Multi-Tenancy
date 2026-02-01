<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class CollectionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the collections.
     */
    public function index(): View
    {
        $this->authorize('products view');
        
        $collections = Collection::orderBy('name')->paginate(10);
        return view('tenant.collections.index', compact('collections'));
    }

    /**
     * Show the form for creating a new collection.
     */
    public function create(): View
    {
        $this->authorize('products create');
        return view('tenant.collections.create');
    }

    /**
     * Store a newly created collection in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('products create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:collections,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                Collection::create($validated);
            });

            return redirect()->route('tenant.collections.index')
                ->with('success', 'Collection created successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to create collection.');
        }
    }

    /**
     * Show the form for editing the specified collection.
     */
    public function edit(Collection $collection): View
    {
        $this->authorize('products edit');
        return view('tenant.collections.edit', compact('collection'));
    }

    /**
     * Update the specified collection in storage.
     */
    public function update(Request $request, Collection $collection): RedirectResponse
    {
        $this->authorize('products edit');

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

        try {
            DB::transaction(function () use ($collection, $validated) {
                $collection->update($validated);
            });

            return redirect()->route('tenant.collections.index')
                ->with('success', 'Collection updated successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Failed to update collection.');
        }
    }

    /**
     * Remove the specified collection from storage.
     */
    public function destroy(Collection $collection): RedirectResponse
    {
        $this->authorize('products delete');
        
        $collection->delete();

        return redirect()->route('tenant.collections.index')
            ->with('success', 'Collection deleted successfully.');
    }
}
