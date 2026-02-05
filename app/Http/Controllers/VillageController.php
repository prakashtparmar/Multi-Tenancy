<?php

namespace App\Http\Controllers;

use App\Models\Village;
use Illuminate\Http\Request;

class VillageController extends Controller
{
    public function lookup(Request $request)
    {
        $query = Village::query();

        /*
        |--------------------------------------------------------------------------
        | Dynamic Filters (priority based)
        |--------------------------------------------------------------------------
        | pincode → fastest & most accurate
        | then village → taluka → district
        */

        if ($request->filled('pincode')) {
            $query->where('pincode', $request->pincode);
        } elseif ($request->filled('village')) {
            $query->where('village_name', 'like', $request->village . '%');
        } elseif ($request->filled('taluka')) {
            $query->where('taluka_name', 'like', $request->taluka . '%');
        } elseif ($request->filled('district')) {
            $query->where('district_name', 'like', $request->district . '%');
        } else {
            return response()->json(['found' => false]);
        }

        // Limit results (UI + performance)
        $results = $query
            ->orderBy('village_name')
            ->limit(10)
            ->get();

        if ($results->isEmpty()) {
            return response()->json(['found' => false]);
        }

        /*
        |--------------------------------------------------------------------------
        | Single result → auto-fill
        |--------------------------------------------------------------------------
        */
        if ($results->count() === 1) {
            $v = $results->first();

            return response()->json([
                'found' => true,
                'mode'  => 'single',
                'data'  => $this->formatVillage($v),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Multiple results → dropdown
        |--------------------------------------------------------------------------
        */
        return response()->json([
            'found' => true,
            'mode'  => 'multiple',
            'list'  => $results->map(fn ($v) => [
                'label' => sprintf(
    '%s – %s, %s, %s (%s)',
    $v->post_so_name ?? 'Post Office',
    $v->village_name,
    $v->taluka_name ?? '-',
    $v->district_name ?? '-',
    $v->pincode
),

                'data'  => $this->formatVillage($v),
            ])->values(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: normalize response data
    |--------------------------------------------------------------------------
    */
    private function formatVillage(Village $v): array
    {
        return [
            'village'     => $v->village_name,
            'pincode'     => $v->pincode,
            'taluka'      => $v->taluka_name,
            'district'    => $v->district_name,
            'state'       => $v->state_name,
            'post_office' => $v->post_so_name,
        ];
    }
}
