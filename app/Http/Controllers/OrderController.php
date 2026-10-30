<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Services\SalesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private SalesService $sales) {}

    public function checkout(Request $request): View|RedirectResponse
    {
        $summary = $this->sales->summary($this->sales->cart($request->user()), $request->session()->get('sales.voucher_code'));
        if (empty($summary['lines'])) {
            return to_route('cart.index')->withErrors(['cart' => 'Giỏ hàng đang trống.']);
        }

        return view('cart.checkout', $summary + ['checkoutToken' => (string) Str::uuid()]);
    }

    public function voucher(Request $request): JsonResponse
    {
        $data = $request->validate(['voucher_code' => ['nullable', 'string', 'max:50']]);
        $cart = $this->sales->cart($request->user());
        if (! $cart || empty($cart->items)) {
            throw ValidationException::withMessages(['cart' => 'Giỏ hàng đang trống.']);
        }

        try {
            $summary = $this->sales->summary($cart, $data['voucher_code'] ?? null);
        } catch (ValidationException $e) {
            $request->session()->forget('sales.voucher_code');

            return response()->json($this->voucherQuote($this->sales->summary($cart)) + [
                'message' => $e->getMessage(), 'errors' => $e->errors(),
            ], 422);
        }
        $request->session()->put('sales.voucher_code', $summary['voucher_code']);

        return response()->json($this->voucherQuote($summary));
    }

    private function voucherQuote(array $summary): array
    {
        return array_intersect_key($summary, array_flip([
            'subtotal_amount', 'discount_amount', 'total_amount', 'voucher_code',
        ]));
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
