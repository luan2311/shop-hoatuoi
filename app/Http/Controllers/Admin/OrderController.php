<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStatusRequest;
use App\Models\Order;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'status' => ['nullable', Rule::in(Order::STATUSES)],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $orders = Order::query()
            ->when($filter['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filter['q'] ?? null, function ($query, $search) {
                // Escape LIKE wildcards so input remains literal search text.
                $search = '%'.addcslashes($search, '\\%_').'%';
                $query->where(fn ($query) => $query->where('order_code', 'like', $search)
                    ->orWhere('customer.name', 'like', $search));
            })
            ->orderBy('created_at', 'desc')->orderBy('_id', 'desc')->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(string $order): View
    {
        return view('admin.orders.show', ['order' => Order::findOrFail($order)]);
    }

    public function updateStatus(OrderStatusRequest $request, string $order, SalesService $sales): RedirectResponse
    {
        $sales->transition($order, $request->validated('status'));

        return to_route('admin.orders.show', $order)->with('status', 'Đã cập nhật trạng thái đơn hàng.');
    }
}
