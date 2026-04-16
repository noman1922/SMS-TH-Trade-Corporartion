<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Http\Requests\Admin\CustomerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $customers = Customer::query()
            ->when($search, function ($query, $search) {
                return $query->where('customer_name', 'like', "%{$search}%")
                             ->orWhere('mobile', 'like', "%{$search}%");
            })
            ->withCount('invoices')
            ->withSum('invoices', 'net_payable')
            ->withSum('payments', 'amount')
            ->orderBy('customer_name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.customers.index', compact('customers', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                Customer::create($request->validated());
            });

            return redirect()->route('customers.index')
                ->with('success', 'Customer created successfully.');
        } catch (Exception $e) {
            Log::error('Customer creation failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Error creating customer. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        // Calculate financial summaries (handled by model attributes)
        $invoices = $customer->invoices()
            ->with('user')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.customers.show', compact('customer', 'invoices'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, Customer $customer)
    {
        try {
            DB::transaction(function () use ($request, $customer) {
                $customer->update($request->validated());
            });

            return redirect()->route('customers.index')
                ->with('success', 'Customer updated successfully.');
        } catch (Exception $e) {
            Log::error('Customer update failed', ['customer_id' => $customer->id, 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Error updating customer. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        // Security: Only admin can delete
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Unauthorized! Only Admins can delete customers.');
        }

        try {
            // Check if customer has invoices before deleting (optional but safer)
            if ($customer->invoices()->exists()) {
                return back()->with('error', 'Cannot delete customer with existing invoices.');
            }

            $customer->delete();
            return redirect()->route('customers.index')
                ->with('success', 'Customer deleted successfully.');
        } catch (Exception $e) {
            Log::error('Customer deletion failed', ['customer_id' => $customer->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Error deleting customer. Please try again.');
        }
    }
}
