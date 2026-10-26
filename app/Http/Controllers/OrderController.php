<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private SalesService $sales) {}

    public function checkout(Request $request): View|RedirectResponse
    {
        $summary = $this->sales->summary($this->sales->cart($request->user()));
        if (empty($summary['lines'])) {
            return to_route('cart.index')->withErrors(['cart' => 'Giỏ hàng đang trống.']);
        }

        return view('cart.checkout', $summary + ['checkoutToken' => (string) Str::uuid()]);
    }

    public function store(OrderRequest $request): RedirectResponse
    {
        $order = $this->sales->checkout($request->user(), $request->validated());
        $request->session()->put('sales.last_order_id', $order->id);

        return to_route('orders.success');
    }

    public function success(Request $request): View
    {
        $order = $this->sales->owned(Order::query(), $request->user())
            ->findOrFail($request->session()->get('sales.last_order_id', ''));

        return view('orders.success', compact('order'));
    }

    public function index(Request $request): View
    {
        $orders = $this->sales->owned(Order::query(), $request->user())
            ->orderBy('created_at', 'desc')->orderBy('_id', 'desc')->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, string $order): View
    {
        $order = $this->sales->owned(Order::query(), $request->user())->findOrFail($order);

        return view('orders.show', compact('order'));
    }
}
