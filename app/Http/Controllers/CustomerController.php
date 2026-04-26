<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPageData;
use App\Models\Customer;
use App\Models\JobOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    use BuildsPageData;

    public function index(Request $request): View
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');

        $customers = Customer::query()
            ->forShop($shop)
            ->withCount('jobOrders')
            ->withCount([
                'jobOrders as active_job_orders_count' => fn ($query) => $query->whereIn('status', [
                    JobOrder::STATUS_PENDING,
                    JobOrder::STATUS_IN_PROGRESS,
                ]),
            ])
            ->orderByDesc('updated_at')
            ->get();

        $selectedCustomer = $customers->firstWhere('id', (int) $request->query('customer'))
            ?? $customers->first();
        $editingCustomer = $customers->firstWhere('id', (int) $request->query('edit'));

        $recentJobs = $selectedCustomer
            ? JobOrder::query()
                ->forShop($shop)
                ->with('customer')
                ->where('customer_id', $selectedCustomer->id)
                ->latest()
                ->take(8)
                ->get()
            : collect();

        $activeJobs = JobOrder::query()
            ->forShop($shop)
            ->whereIn('status', [JobOrder::STATUS_PENDING, JobOrder::STATUS_IN_PROGRESS])
            ->count();

        return view('pages.customers', $this->buildPageData('customers', [
            'heading' => 'Customers',
            'subheading' => 'Manage your real customer records and keep service history linked to every profile.',
            'searchPlaceholder' => 'Search customer, email, phone...',
            'customers' => $customers,
            'selectedCustomer' => $selectedCustomer,
            'editingCustomer' => $editingCustomer,
            'recentJobs' => $recentJobs,
            'stats' => [
                'total' => $customers->count(),
                'active_jobs' => $activeJobs,
                'new_this_month' => $customers->where('created_at', '>=', now()->startOfMonth())->count(),
            ],
            'customersMetricsUrl' => route('customers.metrics'),
        ]));
    }

    public function metrics(Request $request): JsonResponse
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');

        $customers = Customer::query()
            ->forShop($shop)
            ->get(['id', 'created_at']);

        $activeJobs = JobOrder::query()
            ->forShop($shop)
            ->whereIn('status', [JobOrder::STATUS_PENDING, JobOrder::STATUS_IN_PROGRESS])
            ->count();

        return response()->json([
            'stats' => [
                'total' => $customers->count(),
                'active_jobs' => $activeJobs,
                'new_this_month' => $customers->where('created_at', '>=', now()->startOfMonth())->count(),
            ],
            'updated_at' => now('Asia/Manila')->toIso8601String(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');

        $validated = $this->validatePayload($request, $shop->id);

        $customer = Customer::query()->create([
            'shop_id' => $shop->id,
            'name' => trim($validated['name']),
            'email' => $validated['email'] ? strtolower(trim($validated['email'])) : null,
            'phone' => $validated['phone'] ? trim($validated['phone']) : null,
            'address' => $validated['address'] ? trim($validated['address']) : null,
            'notes' => $validated['notes'] ? trim($validated['notes']) : null,
        ]);

        return redirect()
            ->route('customers', ['customer' => $customer->id])
            ->with('status', 'Customer added successfully.');
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');
        $customer = $this->shopCustomer($customer, $shop->id);

        $validated = $this->validatePayload($request, $shop->id, $customer->id);

        $customer->update([
            'name' => trim($validated['name']),
            'email' => $validated['email'] ? strtolower(trim($validated['email'])) : null,
            'phone' => $validated['phone'] ? trim($validated['phone']) : null,
            'address' => $validated['address'] ? trim($validated['address']) : null,
            'notes' => $validated['notes'] ? trim($validated['notes']) : null,
        ]);

        return redirect()
            ->route('customers', ['customer' => $customer->id])
            ->with('status', 'Customer updated.');
    }

    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');
        $customer = $this->shopCustomer($customer, $shop->id);

        $customer->delete();

        return redirect()
            ->route('customers')
            ->with('status', 'Customer deleted.');
    }

    
    private function validatePayload(Request $request, int $shopId, ?int $ignoreCustomerId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')
                    ->where(fn ($query) => $query->where('shop_id', $shopId))
                    ->ignore($ignoreCustomerId),
            ],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function shopCustomer(Customer $customer, int $shopId): Customer
    {
        abort_if($customer->shop_id !== $shopId, 404);

        return $customer;
    }
}
