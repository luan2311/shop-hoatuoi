<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private SalesService $sales) {}

    public function index(Request $request): View
    {
        return view('cart.index', $this->sales->summary($this->sales->cart($request->user())));
    }

    public function add(CartRequest $request): RedirectResponse
    {
        $this->sales->change($request->user(), 'add', $request->validated('product_id'), (int) $request->validated('quantity'));

        return to_route('cart.index')->with('status', 'Đã thêm sản phẩm vào giỏ.');
    }

    public function update(CartRequest $request, string $code): RedirectResponse
    {
        $this->sales->change($request->user(), 'update', $code, (int) $request->validated('quantity'));

        return to_route('cart.index')->with('status', 'Đã cập nhật giỏ hàng.');
    }

    public function remove(Request $request, string $code): RedirectResponse
    {
        $this->sales->change($request->user(), 'remove', $code);

        return to_route('cart.index')->with('status', 'Đã xóa sản phẩm.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $this->sales->change($request->user(), 'clear');

        return to_route('cart.index')->with('status', 'Đã làm trống giỏ hàng.');
    }
}
