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
        $searchKey = $request->input('searchString') ?? $request->input('q');
        $status = $request->input('status');

        $query = Order::query()->with('user');

        if ($status && in_array($status, Order::STATUSES, true)) {
            $query->where('status', $status);
        }

        if (!empty($searchKey)) {
            $escaped = '%' . addcslashes($searchKey, '\\%_') . '%';
            $query->where(function ($q) use ($escaped) {
                $q->where('order_code', 'like', $escaped)
                  ->orWhere('customer.name', 'like', $escaped)
                  ->orWhere('customer.email', 'like', $escaped)
                  ->orWhere('customer.phone', 'like', $escaped);
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->orderBy('_id', 'desc')->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(string $order): View
    {
        return view('admin.orders.show', ['order' => Order::with('user')->findOrFail($order)]);
    }

    public function updateStatus(OrderStatusRequest $request, string $order, SalesService $sales): RedirectResponse
    {
        $sales->transition($order, $request->validated('status'));

        return to_route('admin.orders.show', $order)->with('status', 'Đã cập nhật trạng thái đơn hàng.');
    }

    public function markCompleted(Order $order): RedirectResponse
    {
        $order->status = 'Completed';
        $order->save();

        return redirect()->back()->with('status', 'Đơn hàng #' . $order->order_code . ' đã được đánh dấu hoàn thành (Completed).');
    }
}
