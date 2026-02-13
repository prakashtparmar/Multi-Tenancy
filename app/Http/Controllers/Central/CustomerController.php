<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\CustomerInteraction;
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

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        if ($request->query('trashed') === 'only') {
            $query->onlyTrashed();
        }

        $perPage = (int) $request->input('per_page', 10);

        $customers = $query->with([
            'addresses' => function ($q) {
                $q->where('is_default', true);
            }
        ])->latest()->paginate($perPage)->withQueryString();

        return view('central.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        $this->authorize('customers manage');
        return view('central.customers.create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $this->authorize('customers manage');

        $data = $request->validated();

        $crops = [
            'primary' => array_filter(array_map('trim', explode(',', (string) $request->input('primary_crops', '')))),
            'secondary' => array_filter(array_map('trim', explode(',', (string) $request->input('secondary_crops', '')))),
        ];

        $data['crops'] = $crops;

        try {
            DB::transaction(function () use ($data, $request) {
                $customer = Customer::create(
                    collect($data)->except([
                        'address_line1',
                        'address_line2',
                        'village',
                        'taluka',
                        'district',
                        'state',
                        'pincode',
                        'country',
                        'post_office',
                        'latitude',
                        'longitude',
                        'primary_crops',
                        'secondary_crops',
                        'tags'
                    ])->all()
                );

                if ($request->filled('tags')) {
                    $customer->tags = array_filter(
                        array_map('trim', explode(',', (string) $request->input('tags', '')))
                    );
                    $customer->save();
                }

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

            return redirect()->route('central.customers.index')
                ->with('success', 'Customer created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to create customer: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): View
    {
        $this->authorize('customers view');

        $customer->loadCount(['orders', 'interactions']);
        $customer->load(['addresses']);

        $orders = $customer->orders()
            ->with(['items.product', 'warehouse', 'addresses'])
            ->latest()
            ->paginate(10, ['*'], 'orders_page');

        $interactions = $customer->interactions()
            ->with('user')
            ->latest()
            ->paginate(10, ['*'], 'interactions_page');

        return view('central.customers.show', compact('customer', 'orders', 'interactions'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): View
    {
        $this->authorize('customers manage');

        $customer->load([
            'addresses' => function ($q) {
                $q->where('is_default', true);
            }
        ]);

        return view('central.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorize('customers manage');

        $data = $request->validated();

        $crops = $customer->crops ?? [];

        if ($request->filled('primary_crops')) {
            $crops['primary'] = array_filter(
                array_map('trim', explode(',', (string) $request->input('primary_crops')))
            );
        }

        if ($request->filled('secondary_crops')) {
            $crops['secondary'] = array_filter(
                array_map('trim', explode(',', (string) $request->input('secondary_crops')))
            );
        }

        $data['crops'] = $crops;

        try {
            DB::transaction(function () use ($customer, $data, $request) {
                $customer->update(
                    collect($data)->except([
                        'address_line1',
                        'address_line2',
                        'village',
                        'taluka',
                        'district',
                        'state',
                        'pincode',
                        'country',
                        'post_office',
                        'latitude',
                        'longitude',
                        'primary_crops',
                        'secondary_crops',
                        'tags'
                    ])->all()
                );

                if ($request->filled('tags')) {
                    $customer->tags = array_filter(
                        array_map('trim', explode(',', (string) $request->input('tags')))
                    );
                    $customer->save();
                }

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

            return redirect()->route('central.customers.index')
                ->with('success', 'Customer updated successfully');

        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Failed to update customer: ' . $e->getMessage()]);
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
            return redirect()->route('central.customers.index', ['trashed' => 'only'])
                ->with('success', 'Customer permanently deleted.');
        }

        $customer->delete();
        return redirect()->route('central.customers.index')
            ->with('success', 'Customer moved to trash.');
    }

    /**
     * Restore the specified customer from trash.
     */
    public function restore($id): RedirectResponse
    {
        $this->authorize('customers manage');

        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->restore();

        return redirect()->route('central.customers.index')
            ->with('success', 'Customer restored successfully.');
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
                Customer::whereIn('id', $ids)
                    ->update(['is_active' => $action === 'active' ? 1 : 0]);
                $message = "Selected customers marked as $action.";
                break;

            default:
                $message = 'Invalid action.';
        }

        return back()->with('success', $message);
    }

    /**
     * Store a customer interaction outcome.
     */
    public function storeInteraction(Request $request, Customer $customer)
    {
        // Allow interaction logging if user has 'customers manage' OR 'orders manage'
        if (!$request->user()->can('customers manage') && !$request->user()->can('orders manage')) {
            $this->authorize('customers manage');
        }

        $validated = $request->validate([
            'outcome' => 'required|string',
            'notes' => 'nullable|string',
            'type' => 'nullable|string',
            'close_session' => 'nullable|boolean'
        ]);

        $interaction = CustomerInteraction::create([
            'customer_id' => $customer->id,
            'user_id' => auth()->id(),
            'type' => $validated['type'] ?? 'enquiry',
            'outcome' => $validated['outcome'],
            'notes' => $validated['notes'],
            'metadata' => $request->input('metadata', [])
        ]);

        if ($request->boolean('close_session')) {
            session()->forget('active_customer_id');
        }

        return back()->with('success', 'Interaction tagged successfully.');
    }
}
