<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\InteractionOutcome;
use Illuminate\Http\Request;

class InteractionOutcomeController extends Controller
{
    public function index()
    {
        return response()->json(InteractionOutcome::where('is_active', true)->get());
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
        ]);

        $outcome = InteractionOutcome::create($validated);
        return response()->json($outcome);
    }

    public function update(Request $request, InteractionOutcome $outcome)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $outcome->update($validated);
        return response()->json($outcome);
    }

    public function destroy(InteractionOutcome $outcome)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $outcome->delete(); // Or soft delete if you prefer
        // Or just mark inactive: $outcome->update(['is_active' => false]);
        return response()->json(['success' => true]);
    }
}
