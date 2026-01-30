<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $activities = Activity::with('causer', 'subject')->latest()->paginate(20);

        return view('tenant.activity.index', compact('activities'));
    }
}
