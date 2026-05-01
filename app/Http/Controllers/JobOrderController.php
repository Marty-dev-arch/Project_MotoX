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

class JobOrderController extends Controller
{
    use BuildsPageData;

    public function index(Request $request): View
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');

        $orders = JobOrder::query()
            ->forShop($shop)
            ->with('customer')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        $customers = Customer::query()
            ->forShop($shop)
            ->orderBy('name')
            ->get(['id', 'name']);

$selectedOrder = $orders->firstWhere('id', (int) $request->query('order'))
            ?? $orders->first();
        $editingOrder = $orders->firstWhere('id', (int) $request->query('edit'));

        // Support create=1 query parameter to show blank create form
        $isCreating = $request->integer('create', 0) === 1;
        if ($isCreating) {
            $editingOrder = null;
        }

        $statusCounts = [
            JobOrder::STATUS_PENDING => $orders->where('status', JobOrder::STATUS_PENDING)->count(),
            JobOrder::STATUS_IN_PROGRESS => $orders->where('status', JobOrder::STATUS_IN_PROGRESS)->count(),
            JobOrder::STATUS_COMPLETED => $orders->where('status', JobOrder::STATUS_COMPLETED)->count(),
            JobOrder::STATUS_CANCELLED => $orders->where('status', JobOrder::STATUS_CANCELLED)->count(),
        ];

return view('pages.job-orders', $this->buildPageData('job-orders', [
            'heading' => 'Job Orders',
            'subheading' => 'Track live repair jobs and update progress from intake to release.',
            'searchPlaceholder' => 'Search job order, vehicle, customer...',
            'orders' => $orders,
            'customers' => $customers,
            'selectedOrder' => $selectedOrder,
            'editingOrder' => $editingOrder,
            'isCreating' => $isCreating,
            'statusCounts' => $statusCounts,
            'statusOptions' => JobOrder::statuses(),
            'totalEstimated' => $orders->sum(fn (JobOrder $order): float => (float) $order->estimated_cost),
            'jobOrdersMetricsUrl' => route('job-orders.metrics'),
        ]));
    }

    public function metrics(Request $request): JsonResponse
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');

        $orders = JobOrder::query()
            ->forShop($shop)
            ->get(['status', 'estimated_cost']);

        return response()->json([
            'stats' => [
                'total_orders' => $orders->count(),
                'pending' => $orders->where('status', JobOrder::STATUS_PENDING)->count(),
                'in_progress' => $orders->where('status', JobOrder::STATUS_IN_PROGRESS)->count(),
                'completed' => $orders->where('status', JobOrder::STATUS_COMPLETED)->count(),
                'estimated_value' => (float) $orders->sum(fn (JobOrder $order): float => (float) $order->estimated_cost),
            ],
            'updated_at' => now('Asia/Manila')->toIso8601String(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');

        $validated = $this->validatePayload($request, $shop->id);
        $orderNumber = $this->nextOrderNumber($shop->id);

        $order = JobOrder::query()->create([
            'shop_id' => $shop->id,
            'customer_id' => $validated['customer_id'],
            'order_number' => $orderNumber,
            'vehicle' => trim($validated['vehicle']),
            'concern' => trim($validated['concern']),
            'status' => $validated['status'],
            'estimated_cost' => $validated['estimated_cost'],
            'scheduled_for' => $validated['scheduled_for'],
            'completed_at' => $validated['status'] === JobOrder::STATUS_COMPLETED ? now() : null,
            'notes' => $validated['notes'] ? trim($validated['notes']) : null,
        ]);

        return redirect()
            ->route('job-orders', ['order' => $order->id])
            ->with('status', 'Job order created.');
    }

    public function update(Request $request, JobOrder $jobOrder): RedirectResponse
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');
        $jobOrder = $this->shopOrder($jobOrder, $shop->id);

        $validated = $this->validatePayload($request, $shop->id);
        $isCompleting = $validated['status'] === JobOrder::STATUS_COMPLETED;

        $jobOrder->update([
            'customer_id' => $validated['customer_id'],
            'vehicle' => trim($validated['vehicle']),
            'concern' => trim($validated['concern']),
            'status' => $validated['status'],
            'estimated_cost' => $validated['estimated_cost'],
            'scheduled_for' => $validated['scheduled_for'],
            'completed_at' => $isCompleting ? ($jobOrder->completed_at ?: now()) : null,
            'notes' => $validated['notes'] ? trim($validated['notes']) : null,
        ]);

        return redirect()
            ->route('job-orders', ['order' => $jobOrder->id])
            ->with('status', 'Job order updated.');
    }

    public function destroy(Request $request, JobOrder $jobOrder): RedirectResponse
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');
        $jobOrder = $this->shopOrder($jobOrder, $shop->id);

        $jobOrder->delete();

        return redirect()
            ->route('job-orders')
            ->with('status', 'Job order deleted.');
    }

    
    private function validatePayload(Request $request, int $shopId): array
    {
        return $request->validate([
            'customer_id' => [
                'nullable',
                'integer',
                Rule::exists('customers', 'id')->where(fn ($query) => $query->where('shop_id', $shopId)),
            ],
            'vehicle' => ['required', 'string', 'max:140'],
            'concern' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(JobOrder::statuses())],
            'estimated_cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'scheduled_for' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function shopOrder(JobOrder $order, int $shopId): JobOrder
    {
        abort_if($order->shop_id !== $shopId, 404);

        return $order;
    }

    private function nextOrderNumber(int $shopId): string
    {
        $latest = JobOrder::query()
            ->forShop($shopId)
            ->latest('id')
            ->value('order_number');

        if (! is_string($latest)) {
            return 'JO-00001';
        }

        $numeric = (int) preg_replace('/\D+/', '', $latest);
        $next = max(1, $numeric + 1);

        return 'JO-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
