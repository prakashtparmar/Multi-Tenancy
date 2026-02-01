<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the customers.
     */
    public function index(Request $request): View
    {
        $this->authorize('customers view');

        $query = Customer::query();

        // Search Filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by Status
        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        // Filter by Trashed
        if ($request->query('trashed') === 'only') {
            $query->onlyTrashed();
        }

        $perPage = (int) $request->input('per_page', 10);
        $customers = $query->with(['addresses' => function ($q) {
            $q->where('is_default', true);
        }])->latest()->paginate($perPage)->withQueryString();

        return view('tenant.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        $this->authorize('customers manage');
        return view('tenant.customers.create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $this->authorize('customers manage');

        $data = $request->validated();
        
        $crops = [
            'primary' => array_filter(array_map('trim', explode(',', $request->input('primary_crops', '')))),
            'secondary' => array_filter(array_map('trim', explode(',', $request->input('secondary_crops', '')))),
        ];
        $data['crops'] = $crops;

        try {
            DB::transaction(function () use ($data, $request) {
                $customer = Customer::create(collect($data)->except([
                    'address_line1', 'address_line2', 'village', 'taluka', 'district', 'state', 'pincode', 'country', 'post_office', 'latitude', 'longitude',
                    'primary_crops', 'secondary_crops'
                ])->all());
                
                // Create primary address
                $customer->addresses()->create([
                    'address_line1' => $request->address_line1 ?? '',
                    'address_line2' => $request->address_line2,
                    'village' => $request->village,
                    'taluka' => $request->taluka,
                    'district' => $request->district,
                    'state' => $request->state,
                    'pincode' => $request->pincode,
                    'country' => $request->country ?? 'India',
                    'post_office' => $request->post_office,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'is_default' => true,
                    'type' => 'both',
                ]);
            });

            return redirect()->route('tenant.customers.index')->with('success', 'Customer created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to create customer: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): View
    {
        $this->authorize('customers view');

        $customer->load(['addresses' => function ($q) {
            $q->where('is_default', true);
        }]);

        return view('tenant.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): View
    {
        $this->authorize('customers manage');

        $customer->load(['addresses' => function ($q) {
            $q->where('is_default', true);
        }]);

        return view('tenant.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorize('customers manage');

        $data = $request->validated();

        $crops = $customer->crops ?? [];
        if ($request->has('primary_crops')) {
            $crops['primary'] = array_filter(array_map('trim', explode(',', $request->primary_crops)));
        }
        if ($request->has('secondary_crops')) {
            $crops['secondary'] = array_filter(array_map('trim', explode(',', $request->secondary_crops)));
        }
        $data['crops'] = $crops;

        try {
            DB::transaction(function () use ($customer, $data, $request) {
                $customer->update(collect($data)->except([
                    'address_line1', 'address_line2', 'village', 'taluka', 'district', 'state', 'pincode', 'country', 'post_office', 'latitude', 'longitude',
                    'primary_crops', 'secondary_crops'
                ])->all());

                // Update default address
                $customer->addresses()->where('is_default', true)->update([
                    'address_line1' => $request->address_line1 ?? '',
                    'address_line2' => $request->address_line2,
                    'village' => $request->village,
                    'taluka' => $request->taluka,
                    'district' => $request->district,
                    'state' => $request->state,
                    'pincode' => $request->pincode,
                    'country' => $request->country ?? 'India',
                    'post_office' => $request->post_office,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]);
            });

            return redirect()->route('tenant.customers.index')->with('success', 'Customer updated successfully');
            
        } catch (\Exception $e) {
             return back()->withInput()->withErrors(['error' => 'Failed to update customer: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $this->authorize('customers manage');

        $customer = Customer::withTrashed()->findOrFail($id);

        if ($customer->trashed()) {
            $customer->forceDelete();
            return redirect()->route('tenant.customers.index', ['trashed' => 'only'])->with('success', 'Customer permanently deleted.');
        } else {
            $customer->delete();
            return redirect()->route('tenant.customers.index')->with('success', 'Customer moved to trash.');
        }
    }

    /**
     * Restore the specified customer from trash.
     */
    public function restore($id): RedirectResponse
    {
        $this->authorize('customers manage');

        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->restore();

        return redirect()->route('tenant.customers.index')->with('success', 'Customer restored successfully.');
    }

    /**
     * Perform bulk actions on customers.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $this->authorize('customers manage');

        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:customers,id'],
            'action' => ['required', 'string', 'in:delete,restore,active,inactive,force_delete'],
        ]);

        $ids = $validated['ids'];
        $action = $validated['action'];

        switch ($action) {
            case 'delete':
                Customer::whereIn('id', $ids)->delete();
                $message = 'Selected customers moved to trash.';
                break;
            
            case 'force_delete':
                Customer::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                $message = 'Selected customers permanently deleted.';
                break;

            case 'restore':
                Customer::onlyTrashed()->whereIn('id', $ids)->restore();
                $message = 'Selected customers restored.';
                break;

            case 'active':
            case 'inactive':
                $status = $action === 'active' ? 1 : 0;
                Customer::whereIn('id', $ids)->update(['is_active' => $status]);
                $message = "Selected customers marked as $action.";
                break;
            default:
                $message = 'Invalid action.';
        }

        return back()->with('success', $message);
    }
}
