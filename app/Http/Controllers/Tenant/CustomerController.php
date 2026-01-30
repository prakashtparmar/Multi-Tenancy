<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private function getRoutePrefix()
    {
        // If tenancy is initialized, use tenant routes
        if (tenant()) {
            return 'tenant';
        }

        // Fallback: If we are on a subdomain that is not localhost/127.0.0.1, we are likely in a tenant context
        // regardless of whether the helper returns true (e.g. during some edge cases)
        $host = request()->getHost();
        if ($host !== 'localhost' && $host !== '127.0.0.1' && !str_contains($host, 'central')) {
             return 'tenant';
        }

        return 'central';
    }

    public function index(Request $request)
    {
        $query = Customer::query();

        // Search Filter
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Filter by Status
        if ($request->has('status') && in_array($request->status, ['active', 'inactive'])) {
            $status = $request->status === 'active' ? 1 : 0;
            $query->where('is_active', $status);
        }

        // Filter by Trashed
        if ($request->has('trashed') && $request->trashed === 'only') {
            $query->onlyTrashed();
        }

        $perPage = $request->input('per_page', 10);
        $customers = $query->with(['addresses' => function($q) {
            $q->where('is_default', true);
        }])->latest()->paginate($perPage)->withQueryString();

        return view('tenant.customers.index', [
            'customers' => $customers,
            'routePrefix' => $this->getRoutePrefix(),
        ]);
    }

    public function create()
    {
        return view('tenant.customers.create', [
            'routePrefix' => $this->getRoutePrefix(),
        ]);
    }

    public function store(StoreCustomerRequest $request)
    {
        $data = $request->validated();
        
        $crops = [];
        if ($request->has('primary_crops')) {
            $crops['primary'] = array_filter(array_map('trim', explode(',', $request->primary_crops)));
        }
        if ($request->has('secondary_crops')) {
            $crops['secondary'] = array_filter(array_map('trim', explode(',', $request->secondary_crops)));
        }
        $data['crops'] = $crops;

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
        
        return redirect(request()->root() . '/customers')->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['addresses' => function($q) {
            $q->where('is_default', true);
        }]);

        return view('tenant.customers.show', [
            'customer' => $customer,
            'routePrefix' => $this->getRoutePrefix(),
        ]);
    }

    public function edit(Customer $customer)
    {
        $customer->load(['addresses' => function($q) {
            $q->where('is_default', true);
        }]);

        return view('tenant.customers.edit', [
            'customer' => $customer,
            'routePrefix' => $this->getRoutePrefix(),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $data = $request->validated();

        $crops = $customer->crops ?? [];
        if ($request->has('primary_crops')) {
            $crops['primary'] = array_filter(array_map('trim', explode(',', $request->primary_crops)));
        }
        if ($request->has('secondary_crops')) {
            $crops['secondary'] = array_filter(array_map('trim', explode(',', $request->secondary_crops)));
        }
        $data['crops'] = $crops;

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

        return redirect(request()->root() . '/customers')->with('success', 'Customer updated successfully');
    }

    public function destroy($id)
    {
        $customer = Customer::withTrashed()->findOrFail($id);

        if ($customer->trashed()) {
            $customer->forceDelete();
            return redirect(request()->root() . '/customers?trashed=only')->with('success', 'Customer permanently deleted.');
        } else {
            $customer->delete();
            return redirect(request()->root() . '/customers')->with('success', 'Customer moved to trash.');
        }
    }

    public function restore($id)
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->restore();

        return redirect(request()->root() . '/customers')->with('success', 'Customer restored successfully.');
    }

    public function bulk(Request $request)
    {
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
        }

        return back()->with('success', $message);
    }
}
