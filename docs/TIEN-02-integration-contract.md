# TIEN-02 — Hợp đồng tích hợp Catalog/Auth/UI với bán hàng

Ngày 26/10/2026. Nhánh `feature/cart-checkout-orders`, dựa trên `origin/dev` tại `3ffe4cb`. Không triển khai Catalog thay An. Sau fetch, remote chỉ có main/dev/feature/auth-category-admin; không có controller, route hoặc view Catalog của An trong checkout hoặc nhánh remote. Vì vậy hợp đồng này đã kiểm chứng endpoint và model thực tế, chưa khẳng định luồng Catalog tích hợp đã PASS.

## 1. Thêm sản phẩm vào giỏ

`POST /cart/add`, route `cart.add`, web session + middleware `auth`, CSRF bắt buộc.

| Tham số | Định dạng | Quy tắc |
|---|---|---|
| `_token` hoặc `X-CSRF-TOKEN` | Laravel CSRF token của session hiện tại | Không phải checkout_token |
| `product_id` | Chuỗi 24 ký tự hex, BSON ObjectId của products._id | Bắt buộc, không phải product.code hoặc slug |
| `quantity` | Số nguyên 1–999 | Bắt buộc; tổng quantity cũ + mới không vượt stock/999 |

Giỏ thuộc tài khoản đăng nhập; user_id/cart_id/price/total từ client không được dùng. Thêm nhiều lần cộng dồn theo products.code; stock chỉ giảm khi checkout, không giảm khi thêm giỏ. Không có guest/session cart trong phạm vi phân công.

Response thực tế:

| Tình huống | Form HTML | Request `Accept: application/json` |
|---|---|---|
| Thành công | 302 Location `/cart`, flash status | Vẫn 302 `/cart`; controller chưa trả JSON success |
| Chưa đăng nhập | 302 `/login` | 401 JSON |
| Thiếu CSRF | 419 | 419 |
| ID sai định dạng/quantity sai/stock không đủ/status không còn hàng | 302 về trang trước, errors trong session | 422 `{message, errors}` |
| ID hợp lệ nhưng sản phẩm không tồn tại | 404 | 404 |
| MongoDB không sẵn sàng/transaction thất bại do DB | 503 thông báo chung | 503 `{message}`; không có URI/credentials trong response |

Không giả định endpoint trả `{success:true,count:...}`. Muốn AJAX cart/count là thay đổi hợp đồng cần phối hợp Tiến; AJAX wishlist và product search vẫn thuộc An/Huy.

Gắn vào view sản phẩm của An khi đã có `$product` là Mongo Product:

```blade
<x-add-to-cart :product="$product" />
```

Hoặc form tối thiểu:

```blade
<form method="POST" action="{{ route('cart.add') }}">
    @csrf
    <input type="hidden" name="product_id" value="{{ $product->id }}">
    <input type="number" name="quantity" value="1" min="1" max="999" required>
    <button type="submit">Thêm vào giỏ</button>
</form>
```

Quantity max trên client chỉ là hỗ trợ UI; server kiểm tra lại stock/status. Component có sẵn disable khi hết hàng. Đừng truyền `$product->code` vào product_id.

## 2. Contract dữ liệu sản phẩm

| Field | Hợp đồng với An/Dũng |
|---|---|
| `_id` | BSON ObjectId; dùng `$product->id` trong Blade để lấy chuỗi hex |
| `code` | Mã nghiệp vụ unique, ổn định, không rỗng và không chứa `/` để dùng trong URI sửa/xóa cart; giữ nguyên khi đã được cart/order tham chiếu |
| `slug` | Unique; Tiến giữ slug được cung cấp, chỉ tự sinh nếu thiếu |
| `name` | Tên hiện tại trong cart, snapshot trong order; Blade escape nội dung |
| `price` | BSON int/long/double/decimal được schema cho phép; nghiệp vụ bán hàng chấp nhận giá trị nguyên VND 0–1.000.000.000; Decimal128 đã có test; không gửi formatted string `350.000 đ` vào DB |
| `stock` | BSON int/long, không âm; cùng một field dùng cho CRUD An và decrement/increment trong transaction Tiến |
| `status` | So sánh chính xác `Còn hàng` để được mua; `Hết hàng` hoặc null không được mua. Không đổi sang boolean/enum khác khi chưa thống nhất |
| `image` | Hiện cart hỗ trợ filename hoặc `products/filename`; render tại `/storage/products/{basename(image)}`. File thực phải ở storage/app/public/products và storage:link đã tạo. Chưa hỗ trợ URL CDN/nested directory; An cần đề xuất contract nếu dùng |
| `category` | Embedded `{code,name,slug}` theo mongodb_init.js; category_id SQL và category() cũ chưa được chuyển bởi Tiến |

Không có ảnh thật trong fixture TIEN-02; kiểm tra đường dẫn/schema và trạng thái đã thực hiện, hiển thị ảnh thật sau upload còn phụ thuộc An/Dũng. Không khẳng định upload hoặc catalog đã hoạt động.

Tiến dùng Eloquent Product::find theo _id khi add, rồi products.code để đọc/cart/order; không gọi Product.category(), cartItems(), orderDetails(), wishlists() hoặc scopeSearch() SQL cũ. An cần sửa các API đó trong nhánh sản phẩm; accessor formatted_price cũ cũng cần hỗ trợ Decimal128 nếu Catalog dùng. Tiến chỉ sửa việc render tiền của cart/orders, không tự chuyển Catalog.

## 3. Các thao tác cart và checkout

| Method/URI | Route name | Dữ liệu |
|---|---|---|
| GET /cart | cart.index | Tên/giá hiện tại từ DB; sản phẩm đã xóa hiện thông báo và vẫn xóa khỏi giỏ được |
| PATCH /cart/items/{code} | cart.update | quantity là số lượng mới tuyệt đối, không cộng dồn; scope theo tài khoản |
| DELETE /cart/items/{code} | cart.remove | Xóa item trong giỏ của tài khoản; không có item trả 404 |
| DELETE /cart | cart.clear | Làm trống giỏ tài khoản |
| GET /checkout | checkout | Giỏ rỗng redirect /cart; trang trả hidden checkout_token UUID mới |
| POST /checkout | checkout.store | Các trường bên dưới; tạo đơn trong Mongo transaction hoặc trả đơn cũ khi replay cùng token |
| GET /orders/success | orders.success | Chỉ đơn cuối cùng thuộc tài khoản trong session, không có thì 404 |

POST checkout: checkout_token **required UUID**, receiver_name required string max100; receiver_phone regex `^0[0-9]{9,10}$`; receiver_address required max500; email nullable email max255; note nullable max1000; payment_method COD hoặc Banking. Auth/CSRF bắt buộc. Thành công luôn 302 `/orders/success`; validation HTML 302 errors hoặc JSON 422.

**Idempotency:** giữ nguyên checkout_token khi retry một lần đặt hàng. Order lưu checkout_key SHA256(user id + token); index unique sparse, hỗ trợ đơn cũ chưa có key. Cùng tài khoản + token trả lại đơn đầu tiên, kể cả giỏ đã được thêm mới hoặc payload khác; không trừ kho/clear giỏ mới. Muốn đặt đơn mới: mở GET /checkout để lấy token mới. Token của hai tài khoản có phạm vi độc lập. Không gửi order.total/price/user_id từ client để quyết định giá hoặc người mua. Banking chỉ là phương thức chờ xác nhận, không phải payment gateway.

Fields order: order_code, checkout_key, user_id ObjectId, customer{name,phone,address,email,note}, items[{product_code,product_name,product_slug,image,price,quantity,subtotal}], total_amount, status, payment_method, order_date và timestamps. Items là snapshot để lịch sử không đổi khi sản phẩm đổi tên/giá hoặc xóa. Giá trị tiền lưu mới bằng integer VND; đơn legacy Decimal128 vẫn render được.

## 4. Đơn cá nhân/Admin

GET `/account/orders` và `/account/orders/{order}`: auth; list 10/trang, detail kiểm tra owner. `{order}` là ObjectId hex24, không phải order_code; đơn người khác/không có trả 404.

GET `/admin/orders?status=Pending&q=...&page=1`; GET `/admin/orders/{order}`; PATCH `/admin/orders/{order}/status` với status. Tất cả auth + role:Admin, write có CSRF; User nhận 403. Status filter là một trong Pending/Processing/Shipped/Completed/Cancelled; q max100, tìm literal theo mã/người nhận; paginate10 trên DB.

Luồng `Pending → Processing → Shipped → Completed`; chỉ Pending/Processing được Cancelled. Completed/Cancelled là cuối. Không nhảy bước; không reopen; hủy hoàn stock một lần bằng transaction. Nếu sản phẩm đã bị xóa và không thể hoàn kho, trả validation error, giữ nguyên đơn. Không có chức năng xóa lịch sử order trong phân công.

## 5. Phối hợp bắt buộc trước khi demo tích hợp

- **An:** đưa Catalog vào nhánh chung, gắn component, thống nhất field/status/image/code; chuyển các quan hệ/scopes Product khỏi SQL. Không có file Catalog của An bị sửa trong TIEN-02.
- **Luân:** duyệt bridge User/Product/config Mongo; roles là mảng chuỗi, không roles()->attach(); test seed dùng forceFill hoặc Seeder để set role, không cho request người dùng set role. Nhánh Luân hiện còn Category/Role SQL, RefreshDatabase, Dashboard receiver_name/total, Product::with('category')->latest(); cần chuyển sang Mongo/customer.name/total_amount và kiểm tra compatibility sort với Laravel13. Không tự sửa các file đó trong nhánh Tiến.
- **Luân:** hợp nhất thủ công `routes/web.php`: giữ require sales.php bên cạnh routes admin/user/auth, tránh include hai lần. Đây là file chồng lấn trực tiếp duy nhất với nhánh Luân theo diff sau fetch; không có nghĩa là đã thử merge.
- **Huy:** đồng bộ sales-panel với admin/user layout cuối, giữ route/forms/errors/CSRF và overflow table; hiện dùng Breeze, không redesign. Thêm đường dẫn giỏ/đơn vào navbar chung trong nhánh UI.
- **Dũng:** seed đầy đủ theo schema embedded, password hash thật, ảnh thật; dùng DB test riêng và migrations path mongodb. Default DatabaseSeeder/migrations SQL vẫn là legacy và không được chạy cho luồng này.

Eloquent User.cart/orders mới dùng local key ObjectId; owner queries của Tiến hỗ trợ cả user_id BSON ObjectId và string legacy. Không dùng giả định integer SQL id. Migrations path `database/migrations/mongodb`; không migrate:fresh/reset/drop. Contract mới cần migration thứ ba checkout_key trước khi chạy checkout; script preflight kiểm tra điều này.
