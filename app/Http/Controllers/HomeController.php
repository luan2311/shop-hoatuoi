<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // Mỗi khối trên trang chủ lấy 8 sản phẩm theo danh mục (như Trang_chu của đồ án cũ)
        $byCategory = fn (string $slug) => Product::inCategory($slug)->take(8)->get();

        return view('home', [
            'banChay'   => $this->banChay(8),
            'boHoaTuoi' => $byCategory('bo-hoa-tuoi'),
            'keHoa'     => $byCategory('ke-chuc-mung'),
            'gioHoa'    => $byCategory('gio-hoa-chuc-mung'),
            'hoaSap'    => $byCategory('hoa-sap'),
            'hoaCuoi'   => $byCategory('hoa-cuoi'),
            'tinTucMoi' => Post::orderBy('published_at', 'desc')->orderBy('_id', 'desc')->take(3)->get(),
        ]);
    }

    // Bán chạy: cộng số lượng theo product_code trong orders.items (bỏ đơn đã hủy),
    // thiếu thì bù bằng sản phẩm mới nhất
    private function banChay(int $limit)
    {
        $sold = [];
        $rows = DB::connection('mongodb')->getDatabase()->selectCollection('orders')->aggregate([
            ['$match' => ['status' => ['$ne' => 'Cancelled']]],
            ['$unwind' => '$items'],
            ['$group' => ['_id' => '$items.product_code', 'sold' => ['$sum' => '$items.quantity']]],
            ['$sort' => ['sold' => -1]],
            ['$limit' => $limit],
        ]);
        foreach ($rows as $row) {
            $sold[$row['_id']] = $row['sold'];
        }

        $codes = array_keys($sold);
        $products = Product::whereIn('code', $codes)->get()
            ->sortBy(fn ($product) => array_search($product->code, $codes))
            ->values();

        if ($products->count() < $limit) {
            $products = $products->concat(
                Product::whereNotIn('code', $codes)->orderBy('_id', 'desc')->take($limit - $products->count())->get()
            );
        }

        return $products;
    }
}
