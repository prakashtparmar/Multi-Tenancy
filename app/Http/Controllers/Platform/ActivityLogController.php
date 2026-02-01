<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ActivityLogController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // Require specific permission or admin role
        if (!auth()->user()->hasRole('admin') && !auth()->user()->can('system view')) {
            abort(403);
        }

        $activities = Activity::with(['causer', 'subject'])->latest()->paginate(20);

        return view('tenant.activity.index', compact('activities'));
    }
}
