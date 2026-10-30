<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the main admin dashboard view.
     */
    public function index()
    {
        $totalCategories = Category::count();
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalUsers = User::count();

        // Thống kê doanh thu và đơn hoàn thành (tương tự Dashboard C# ASP.NET MVC 5)
        $completedOrders = Order::where('status', 'Completed')->count();
        $totalRevenue = (float) (Order::where('status', 'Completed')->sum('total_amount') ?: Order::where('status', 'Completed')->sum('total'));

        // Đơn hàng gần đây
        $recentOrders = Order::with('user')->latest()->take(10)->get();
        $recentProducts = Product::with('categoryRecord')->latest()->take(6)->get();

        // Top sản phẩm bán chạy nhất (Tính từ embedded items của orders hoặc CTHD)
        $topSellingProducts = collect();
        try {
            $ordersForStats = Order::where('status', 'Completed')->get();
            if ($ordersForStats->isEmpty()) {
                $ordersForStats = Order::all();
            }

            $productSales = [];
            foreach ($ordersForStats as $ord) {
                foreach ($ord->items ?? [] as $it) {
                    $pName = $it['product_name'] ?? $it['product_code'] ?? 'Sản phẩm';
                    $qty = (int) ($it['quantity'] ?? 1);
                    $price = (float) ($it['subtotal'] ?? (($it['price'] ?? 0) * $qty));

                    if (!isset($productSales[$pName])) {
                        $productSales[$pName] = [
                            'product_name' => $pName,
                            'quantity' => 0,
                            'total_amount' => 0,
                        ];
                    }
                    $productSales[$pName]['quantity'] += $qty;
                    $productSales[$pName]['total_amount'] += $price;
                }
            }

            $topSellingProducts = collect($productSales)
                ->sortByDesc('total_amount')
                ->take(3)
                ->map(fn ($p) => (object)$p)
                ->values();
        } catch (\Throwable $e) {
            $topSellingProducts = collect();
        }

        // Nếu chưa có đơn hàng, lấy các sản phẩm tiêu biểu mẫu
        if ($topSellingProducts->isEmpty() && $totalProducts > 0) {
            $topSellingProducts = Product::take(3)->get()->map(function ($prod) {
                return (object) [
                    'product_name' => $prod->name,
                    'quantity' => 5,
                    'total_amount' => (float) $prod->price * 5,
                ];
            });
        }

        $categoriesWithCounts = Category::take(6)->get()->map(function ($cat) {
            $cat->products_count = Product::where('category.code', $cat->code)
                ->orWhere('category.slug', $cat->slug)
                ->orWhere('category_id', (string) $cat->id)
                ->count();

            return $cat;
        });

        return view('admin.dashboard', compact(
            'totalCategories',
            'totalProducts',
            'totalOrders',
            'totalUsers',
            'totalRevenue',
            'completedOrders',
            'recentOrders',
            'recentProducts',
            'topSellingProducts',
            'categoriesWithCounts'
        ));
    }

    /**
     * Get revenue chart data by date range for Chart.js.
     * Ported from C# DashboardController.GetRevenueData
     */
    public function getRevenueData(Request $request): JsonResponse
    {
        try {
            $startDate = $request->filled('fromDate')
                ? Carbon::parse($request->input('fromDate'))->startOfDay()
                : Carbon::today()->subDays(30)->startOfDay();

            $endDate = $request->filled('toDate')
                ? Carbon::parse($request->input('toDate'))->endOfDay()
                : Carbon::today()->endOfDay();

            if ($startDate->gt($endDate)) {
                $temp = $startDate;
                $startDate = $endDate->copy()->startOfDay();
                $endDate = $temp->copy()->endOfDay();
            }

            // Lấy tất cả đơn hàng completed trong khoảng ngày
            $orders = Order::where('status', 'Completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(['created_at', 'total', 'total_amount']);

            // Nhóm theo ngày dd/MM/yyyy
            $grouped = $orders->groupBy(function ($order) {
                return $order->created_at->format('d/m/Y');
            });

            $chartData = [];
            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                $dateKey = $current->format('d/m/Y');
                $ordersOnDate = $grouped->get($dateKey, collect());
                $chartData[] = [
                    'date' => $dateKey,
                    'revenue' => (float) $ordersOnDate->sum(fn ($o) => (float) ($o->total ?? $o->total_amount ?? 0)),
                ];
                $current->addDay();
            }

            return response()->json([
                'success' => true,
                'data' => $chartData,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
