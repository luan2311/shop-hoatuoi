# Bàn giao phần Tiến — giỏ hàng, checkout, đơn hàng

> Đây là bản bàn giao TIEN-01. [Nghiệm thu TIEN-02](TIEN-02-final-audit.md) và [hợp đồng tích hợp](TIEN-02-integration-contract.md) mô tả trạng thái mới nhất, gồm checkout_token bắt buộc, Decimal128, kiểm thử trình duyệt và startup Windows. Các số 44 tests/292 assertions bên dưới là lượt kiểm chứng TIEN-01.

Ngày kiểm chứng: 26/10/2026, Asia/Bangkok. Không push, merge, tạo PR hoặc commit.

## A. Git và audit repository

- Repository đích: https://github.com/luan2311/shop-hoatuoi, checkout tại `D:\Learning\OpenSourceProgramming\DA\shop-hoatuoi`.
- Workspace ban đầu chỉ có `mongodb_init.js`, không có Git repository; đã clone mới, không có thay đổi sẵn có bị ghi đè.
- Đã kiểm tra status, remote, các nhánh, HEAD và fetch `origin/dev`.
- Nhánh làm việc: `feature/cart-checkout-orders`, tạo từ `origin/dev`, commit gốc `3ffe4cb0a09be7b7372dee98bf5b0580c83d350f`. HEAD vẫn ở commit này; thay đổi để unstaged cho review.
- Nhánh remote tại audit: `main`, `dev`, `feature/auth-category-admin`; chưa có nhánh Tiến để tái sử dụng.
- Nhánh Luân đã có Category CRUD, Dashboard, admin layout, routes admin/user và tests, nhưng chưa merge vào dev. Không cherry-pick hoặc sửa nhánh đó. Không có nhánh remote công việc An/Huy/Dũng tại thời điểm audit; chưa thể kết luận về công việc chưa push trên máy đồng đội.
- Repository tham khảo clone riêng tại `D:\Learning\OpenSourceProgramming\DA\Shop-Flower-reference`, HEAD `b4ed340f35831f829bc43e585e2bcb5595717511`; chỉ đọc.

Môi trường thực tế: PHP 8.3.30 ZTS x64, Composer 2.9.4, Laravel **13.37.0** theo lockfile (yêu cầu `^13.17`), MongoDB Server 8.2.3, mongosh 2.10.0, Node 24.12.0. File kế hoạch vẫn ghi Laravel 12/MySQL ở phần mở đầu, không phản ánh cấu hình source hiện tại.

Trước triển khai: Breeze auth/profile và middleware role đã có; Cart, CartItem, Order, OrderDetail, Product, User dùng SQL Eloquent; chưa có controller, request, route hoặc view nghiệp vụ Tiến; chưa có package/kết nối MongoDB. Migrations và DatabaseSeeder hiện hữu là SQL. PHP chưa bật MongoDB extension; server cổng 27017 là standalone và có nhiều database khác.

Đã cài extension 2.5.5 vào `.runtime/php` riêng, không sửa PHP hệ thống; package `mongodb/laravel-mongodb` 5.7.0 + `mongodb/mongodb` 2.5.0. Không cài Boost vì yêu cầu người dùng giới hạn package mới vào dependency cần cho chức năng. Nội dung tài liệu phân công, gồm lời nhắc dành cho Claude và các lệnh commit, được dùng làm tài liệu nghiệp vụ, không coi là yêu cầu thao tác bổ sung của người dùng.

## B. Phạm vi chính xác

Nguồn: [file phân công](plans/2026-10-19-phan-chia-cong-viec-5-nguoi.md), hàng Member 3 trong ma trận, Tasks 3.1–3.3 và checklist Member 3. Người dùng xác nhận Tiến là Member 3.

Hàng Member 3 của **origin/dev** ghi: “Giỏ hàng MongoDB (Embedded items), Đặt hàng NoSQL Checkout (Embedded Order Items, Atomic Operations $push/$inc), Quản lý đơn hàng”. Các ví dụ bên dưới vẫn dùng bảng SQL `cart_items/order_details`; triển khai theo ma trận NoSQL mới và schema người dùng cung cấp: `carts.items[]`, `orders.items[]`. Không tạo hai collection chi tiết rời chỉ để khớp ví dụ SQL.

| Nhiệm vụ Tiến | MVC tham khảo | Laravel trước triển khai | Đã triển khai | Tiêu chí PASS và trạng thái |
|---|---|---|---|---|
| 3.1 Giỏ DB: thêm/cộng dồn, đổi số lượng theo stock, xóa item, clear; hiển thị ảnh, giá, tổng | CartController, Cart.cs, Views/Cart | Chỉ model SQL Cart/CartItem | Mongo Cart, SalesService, CartController, CartRequest, Blade | PASS: lưu/truy vấn DB, tách user, validation, giỏ rỗng, sản phẩm mất/hết hàng |
| 3.2 Checkout: thông tin nhận, phone/address validation, COD/Banking, transaction tạo đơn/chi tiết/trừ kho/xóa giỏ, trang thành công | CheckoutController, ThanhToanViewModel, API HoaDon | Chỉ model SQL Order/OrderDetail | OrderRequest, OrderController, snapshot embedded và transaction | PASS: giá từ DB, snapshot, stock, rollback khi thiếu hàng hoặc DB lỗi, CSRF, chống checkout trùng |
| 3.3 Lịch sử/chi tiết cá nhân `/account/orders` | AccountController.Orders, OrderViewModel, Account/Orders | Chưa có | Danh sách 10 đơn/trang, chi tiết, lọc theo owner | PASS: 404 với đơn người khác/không có, xử lý user_id ObjectId và string |
| 3.3 Admin xem danh sách/chi tiết/lọc trạng thái/cập nhật | DashboardController.QL_HoaDon, DetailsHoaDon, UpdateOrderStatus | Chỉ RoleMiddleware | Admin OrderController, OrderStatusRequest, Blade, tìm mã/người nhận và phân trang | PASS: Admin/User, chuyển trạng thái hợp lệ, hủy hoàn kho đúng một lần |

Luân: kiến trúc, MongoDB tổng thể, auth/role, category, dashboard/admin layout. An: sản phẩm, upload, bộ lọc và search AJAX. Huy: frontend layout/theme, wishlist, contact/Maps, posts. Dũng: dữ liệu đầy đủ/ảnh, QA toàn dự án, sơ đồ/báo cáo/slide. Thay đổi hạ tầng trong nhánh Tiến chỉ đủ để chạy và kiểm chứng nghiệp vụ bán hàng trên MongoDB; cần Luân review khi tích hợp.

## C. Mapping MVC và hành vi

Nguồn .NET: https://github.com/luan2311/Shop-Flower, thư mục `ShopFlower/`.

| ASP.NET MVC | Laravel |
|---|---|
| CartController.ThemGioHang, CapNhatGioHang, XoaGioHang, XoaGioHang_All và sp_GetCartItems/sp_AddToCart/sp_UpdateCartItem/sp_RemoveFromCart/sp_ClearCart | CartController + SalesService.change/summary, Mongo Eloquent |
| Cart.cs và Views/Cart/Cart.cshtml, empty_cart.cshtml, CartPartial.cshtml | Cart document, `cart/index.blade.php`, add-to-cart component |
| CheckoutController.ThanhToan/PlaceOrder/ClearCart; Views/Checkout/ThanhToan.cshtml | OrderController.checkout/store, `cart/checkout.blade.php`, `orders/success.blade.php` |
| ThanhToanViewModel data annotations | OrderRequest; nhận địa chỉ đầy đủ thay cho 4 trường địa chỉ phụ thuộc UI cũ |
| HOADON + CTHD; MaTK → TAIKHOAN; MaSP → SANPHAM | orders.user_id → users._id; orders.items[].product_code → products.code, snapshot giá/tên/ảnh |
| AccountController.Orders, OrderViewModel/OrderDetailViewModel | OrderController.index/show, orders views |
| Admin DashboardController.QL_HoaDon/DetailsHoaDon/UpdateOrderStatus | Admin OrderController.index/show/updateStatus |
| APIController/HoaDonController.PlaceOrder transaction | Mongo transaction; không sao chép việc tin giá gửi từ client |
| Authorize, BaseAdminController, AntiForgeryToken; RouteConfig `{controller}/{action}/{id}` | auth + role:Admin middleware có sẵn, FormRequest, CSRF web middleware, routes/sales.php |

Đã đọc model HOADON/CTHD/SANPHAM và mapping EF trong `Models/QL_ShopFlower.edmx`; clone không có file SQL độc lập. Stored procedure được xác định qua call sites; không khẳng định đã kiểm chứng phần SQL triển khai của chúng. Dự án cũ dùng Entity Framework/ADO.NET trong controller, không có service/repository riêng cho các luồng này.

Giữ các nghiệp vụ nhận hàng, giá tại lúc mua, COD/Banking, thông tin đơn, lịch sử theo tài khoản và quản lý Admin. Không chuyển session cart cho khách chưa đăng nhập vì phân công yêu cầu giỏ DB theo user. Không chuyển Forms Authentication, Razor, EF, stored procedure hoặc API cũ thiếu phân quyền. Không sao chép CSS/JS/ảnh toàn dự án .NET và không redesign theme; các view mới dùng layout Breeze/Tailwind sẵn có. Admin tạm dùng cùng layout vì admin layout chưa có trên dev. Huy/Luân đồng bộ layout sau khi tích hợp.

Luồng trạng thái dùng checklist: `Pending → Processing → Shipped → Completed`; chỉ Pending/Processing được Cancelled. Trạng thái cuối không sửa lại. Một ví dụ test cũ cho phép Pending → Shipped trực tiếp, trái với checklist; nhánh này chủ động từ chối bước nhảy. Hủy đơn hoàn kho trong transaction; nếu sản phẩm đã bị xóa thì chặn hủy và giữ nguyên đơn để Admin xử lý nghiệp vụ, không bỏ qua việc hoàn kho.

Banking chỉ ghi nhận lựa chọn thanh toán/chờ cửa hàng xác nhận, không phải tích hợp cổng thanh toán.

## D. MongoDB và schema

Database ứng dụng: `shop_hoatuoi`. Không dùng SQL/SQLite làm fallback. Default connection, User/Cart/Order/Product đều trỏ MongoDB. Session/cache dùng file; queue sync để không đòi SQL tables.

`D:\Learning\OpenSourceProgramming\DA\mongodb_init.js` giống `database/mongodb_init.js` sau chuẩn hóa CRLF/LF. **Không chạy cả hai script**: script cố định `getSiblingDB('shop_hoatuoi')` và drop 9 collections, dù URI ban đầu chỉ database khác. Chưa nạp script vào bất kỳ database đang có dữ liệu nào.

| Collection/field | Cách sử dụng |
|---|---|
| users._id, username, email, password, roles[] | Mongo Auth User; roles là mảng chuỗi Admin/User, không pivot user_roles. Registration được bổ sung username tự sinh và role User mặc định trong model; không cho client mass-assign roles |
| products._id, code, slug, name, price, image, stock, status, category{} | product_id nhận từ form là chuỗi 24 hex của ObjectId; cart/order tham chiếu code. Không đổi code khi đã được dùng trong đơn. `category` là snapshot `{code,name,slug}` theo initializer |
| carts._id, user_id, items[] | Một cart/user; item `{product_code,quantity}`; đọc giá sản phẩm hiện tại khi hiển thị/checkout |
| orders._id, order_code, user_id, customer{}, items[], total_amount, payment_method, status, order_date, timestamps | user_id mới lưu BSON ObjectId; dữ liệu legacy string vẫn được các query owner chấp nhận. customer `{name,phone,address,email,note}`; item `{product_code,product_name,product_slug,image,price,quantity,subtotal}` |

Eloquent: `Order::user()`/`Cart::user()` belongsTo User; User.cart hasOne, User.orders hasMany dùng accessor ObjectId làm local key để tránh so sánh chuỗi với BSON ObjectId. Đã test đọc hai chiều. Với dữ liệu legacy user_id string, lịch sử/giỏ dùng SalesService.owned whereIn cả hai kiểu; quan hệ User.cart/orders định hướng dữ liệu mới ObjectId. Embedded items được truy cập dưới dạng mảng document, không phải các model CartItem/OrderDetail SQL. Không có foreign key cưỡng chế giữa collection; kiểm tra tồn tại/quyền sở hữu/tồn kho tại service. Đơn vẫn xem được khi sản phẩm bị đổi tên, đổi giá hoặc xóa nhờ snapshot.

Giá dùng số nguyên VND, không tin giá/total/user_id gửi từ browser; từ chối giá âm, giá lẻ hoặc trên 1.000.000.000đ. Quantity 1–999 và không vượt stock. Tổng và subtotal tính phía server. Không có VAT/phí giao hàng do phân công không yêu cầu.

`SalesService` dùng Eloquent/query builder, không raw query. Stock giảm bằng decrement có điều kiện `stock >= quantity` và status còn hàng. Transaction bao trùm kiểm tra giỏ → stock → insert order → clear cart. Mọi lần sửa giỏ và chuyển trạng thái cũng dùng transaction, retry tối đa 5 lần; không chạy operations song song trong một transaction. Ba tests dùng hai PHP process độc lập kiểm chứng conflict/retry thực tế. Lỗi Mongo trên các route bán hàng trả 503 chung, không hiển thị chi tiết kết nối.

Hai migration trong `database/migrations/mongodb/`:

1. Tạo carts/orders nếu thiếu; unique carts.user_id, orders.order_code; index orders.user_id và compound `(user_id, created_at desc, _id desc)`, `(status, created_at desc, _id desc)`.
2. Tạo users/products nếu thiếu; unique users.email/username, products.code/slug cần cho auth và lookup bán hàng.

Không drop hoặc sửa dữ liệu. Chạy lại migration/index định nghĩa giống nhau an toàn; dữ liệu trùng đã có sẽ gây lỗi index để người phụ trách xử lý, không tự xóa. Rollback giữ dữ liệu và các index định danh dùng chung, chỉ bỏ hai compound index bổ sung. Không sử dụng migrate:fresh/reset. Validators gốc được trích offline vào `tests/Fixtures/mongodb-validators.json`, áp dụng lên MongoDB thật trong tests. Migration ứng dụng không tự thay validators đang có; FormRequest/service kiểm tra dữ liệu nghiệp vụ.

Đã password_verify cả hai mật khẩu trong comment script: Admin@123 **false**, User@123 **false**; hai tài khoản dùng cùng một hash. Không dùng chúng để cam kết đăng nhập. `SalesDemoSeeder` tạo riêng user.sales@example.test và admin.sales@example.test, yêu cầu hai biến SALES_DEMO_*_PASSWORD >=12 ký tự; chỉ local/testing, dùng Hash::make. Chạy lại không ghi đè mật khẩu, role, tồn kho, tài khoản hoặc sản phẩm đã tồn tại. Đây là dữ liệu tối thiểu một sản phẩm kiểm thử; bộ dữ liệu đầy đủ vẫn thuộc Dũng. Không thêm mật khẩu hoặc URI Atlas vào Git.

## E. Kiểm thử và bằng chứng

[Kết quả PHPUnit mới nhất](evidence/tien02/phpunit.json) và [HTTP audit mới nhất](evidence/tien02/http-audit.json) là dữ liệu chạy thực tế, không chứa cookie, CSRF token hoặc mật khẩu. Danh sách file đề xuất commit nằm trong [manifest TIEN-02](evidence/tien02/commit-files.txt).

Tests tạo database ngẫu nhiên `shop_hoatuoi_test_suite_<pid>_<random>`, yêu cầu APP_ENV=testing và tên prefix test trước khi thao tác. Từ TIEN-02, tests giữ lại database riêng để audit, không drop database. Không dùng RefreshDatabase/DatabaseTransactions vì MongoDB package không hỗ trợ chúng; các test auth/profile cũ chỉ đổi trait chuẩn bị DB sang UsesMongoDatabase. phpunit.xml dùng MongoDB, không SQLite in-memory.

| Kiểm tra | Kết quả |
|---|---|
| PHP/Composer, extension, composer check-platform-reqs | PASS |
| composer validate --strict; composer audit | PASS; không có advisory Composer |
| Migration trên replica set riêng; chạy lại migration và seeder | PASS |
| Route list: không trùng tên route | PASS |
| CartOperationsTest, OrderCheckoutTest, AdminOrderManagementTest, SalesConcurrencyTest, SalesSetupTest | PASS; xem JSON để lấy số assertions cuối cùng |
| Toàn bộ php artisan test, gồm auth/password reset/profile có sẵn | PASS: 44 tests, 292 assertions; kết quả cuối lưu ở evidence |
| Rollback khi item thứ hai thiếu stock; MongoDB validator từ chối insert | PASS; stock, cart và orders được đọc lại |
| Chạy đồng thời: first add vào cart mới, hai checkout cùng cart, hai khách mua item cuối | PASS |
| Ownership, role, validation, 404, CSRF | PASS |
| Browser-server HTTP với cookie login/CSRF thật | PASS 21 checks; không dùng actingAs cho smoke này |
| Mongosh trực tiếp sau HTTP | PASS: đơn HD-01M5VF8KV1CA2KZHRS11YYKCRV, total 700000, Completed, một item quantity 2, stock 20→18, cart.items rỗng |
| npm ci bằng npm runtime riêng, npm run build, Blade view:cache, Pint, git diff --check | PASS |
| npm audit | FAIL: 3 mục moderate đã có trong dependency frontend (tailwindcss/postcss-nested/postcss-selector-parser); không nâng major UI ngoài phạm vi |
| Đánh giá trực quan bằng browser/mobile 375px, ảnh sản phẩm thật, Laragon Apache/Nginx | NOT TESTED; đã kiểm tra HTTP/render Blade và build assets, chưa đánh dấu PASS giao diện/responsive |
| Toàn bộ category/product/wishlist/contact/posts từ các nhánh đồng đội | NOT TESTED; chưa tích hợp |

Môi trường DB kiểm thử là instance mới cổng 27018, replica set tienRS một node, chỉ dùng local. Không đổi cấu hình hoặc dữ liệu MongoDB cổng 27017. Standalone không hỗ trợ transaction; không có cơ chế âm thầm chạy checkout không transaction. Deployment thực tế cần replica set/Atlas và cấu hình bảo mật do Lead quản lý.

## F. Đối chiếu rubric và phối hợp

| Rubric | Phần Tiến | Phụ thuộc toàn dự án |
|---|---|---|
| CRUD ≥2 collection có quan hệ | carts và orders liên kết users/products; cart đủ thêm/đọc/sửa/xóa/clear; order tạo/đọc/cập nhật trạng thái, không xóa lịch sử | Luân/An: category/product CRUD; cần giảng viên chấp nhận mô hình embedded thay bảng chi tiết |
| Login/logout/Admin/User | Tái sử dụng Breeze/RoleMiddleware, bridge Mongo User, test auth hồi quy PASS, chặn Admin routes | Luân duyệt cơ chế roles[] và hợp nhất nhánh auth |
| Tìm kiếm/phân trang | Admin search mã/người nhận, filter status; User/Admin paginate 10 trên DB | An phụ trách catalog/search AJAX |
| Validation/Eloquent/MVC/Middleware/Blade | FormRequests, SalesService, Mongo Eloquent, auth/role/CSRF và views | Cả nhóm đồng bộ schema |
| Migration/Seeder | Migration indexes không phá dữ liệu, SalesDemoSeeder chạy lặp | Dũng làm dữ liệu đầy đủ và báo cáo NoSQL |
| Bootstrap/Tailwind/Responsive | Dùng Tailwind/Breeze hiện có; bảng overflow-x-auto | Huy/Luân đồng bộ UI và kiểm thử trực quan |
| Upload | Chỉ hiển thị đường dẫn ảnh sản phẩm, không upload | An; cần thống nhất image filename/path và ảnh thật |
| AJAX | Không phải yêu cầu cart trong phân công, form CSRF server-rendered đủ chức năng | An/Huy: search/wishlist AJAX |
| API bên ngoài | Không thuộc Tiến; Banking chưa tích hợp payment API | Huy: Google Maps |
| Tối ưu | Batch load sản phẩm khi hiển thị cart, compound indexes, DB pagination, transaction/stock guards | An/Dũng kiểm thử tải/toàn hệ thống |

Các điểm tích hợp cần review: User/Product đổi base class MongoDB; roles không còn pivot. Model CartItem/OrderDetail, các model SQL khác, migrations SQL và DatabaseSeeder cũ được giữ để tránh tự chuyển module đồng đội, **không dùng cho nhánh bán hàng MongoDB**. Product.category()/category_id và các quan hệ wishlist/orderDetails/cartItems SQL cũ chưa được chuyển thành API quan hệ MongoDB; An/Luân phải thống nhất lại trước khi dùng những API đó. Module Tiến không gọi chúng. Code mới đọc items[]/customer/total_amount, không các cột order SQL cũ.

Chưa có catalog của An trên dev nên không có nút thêm giỏ từ trang sản phẩm thật trong nhánh này. Đã cung cấp `<x-add-to-cart :product="$product" />` để An gắn vào view do An quản lý; endpoint đã test cả Feature và HTTP. Không tạo catalog hoặc sửa homepage ngoài phạm vi. Product.code phải ổn định; nếu cần đổi code đã có trong giỏ/đơn, An cần phối hợp kế hoạch chuyển dữ liệu. Layout admin cuối cùng chờ Luân; không nhận công việc đó đã hoàn thành trong nhánh Tiến.

npm hệ thống 11.6.2 bị SyntaxError trong `@tufjs/models/dist/signature.js`. Đã tải cùng phiên bản từ registry vào `.runtime/npm` để build; không sửa bộ cài hệ thống hoặc package-lock. Ba advisory frontend cần Huy/Lead xử lý riêng.

## G. Cách chạy lại trên Windows/Laragon

Chạy từ root repository. PHP phải là 8.3+ có extension MongoDB tương thích TS/NTS/x64. Trên máy hiện tại dùng bản DLL riêng đã chuẩn bị:

```powershell
Set-Location D:\Learning\OpenSourceProgramming\DA\shop-hoatuoi
$env:PHP_INI_SCAN_DIR = (Resolve-Path .runtime\php).Path
php --ri mongodb
composer install
composer check-platform-reqs
```

Máy khác: tải DLL từ [PECL Windows](https://pecl.php.net/package/mongodb/2.5.5/windows) phù hợp PHP của máy, cấu hình extension ở php.ini hoặc thư mục ini riêng; runtime/binary không được commit. Nếu dùng Laragon web server, cấu hình extension cho PHP mà server đó sử dụng rồi restart server. HTTP thực tế của lần bàn giao dùng php artisan serve.

Replica set local hiện đã chạy ở 27018, data/log/pid trong `.runtime`. Nếu cần khởi động lại instance này khi chưa có process nghe 27018:

```powershell
$salesData = (Resolve-Path .runtime\mongo-data).Path
$salesLog = Join-Path (Resolve-Path .runtime).Path 'mongod.log'
Start-Process -FilePath 'C:\Program Files\MongoDB\Server\8.2\bin\mongod.exe' -ArgumentList @('--dbpath', $salesData, '--port', '27018', '--bind_ip', '127.0.0.1', '--replSet', 'tienRS', '--logpath', $salesLog) -WindowStyle Hidden
mongosh 'mongodb://127.0.0.1:27018/?directConnection=true' --quiet --eval 'rs.status().ok'
```

Máy mới tạo thư mục data riêng trước; chỉ lần đầu initialize:

```powershell
New-Item -ItemType Directory -Path .runtime\mongo-data -Force
# Chạy mongod với lệnh trên, rồi initialize khi replica set chưa được khởi tạo:
mongosh 'mongodb://127.0.0.1:27018/?directConnection=true' --eval 'rs.initiate({_id:"tienRS",members:[{_id:0,host:"127.0.0.1:27018"}]})'
```

Nếu `.env` chưa có, copy `.env.example`; không copy đè `.env` sẵn có. Cấu hình DB_CONNECTION=mongodb, MONGODB_DATABASE=shop_hoatuoi, MONGODB_URI theo replica set local trong example hoặc giá trị riêng của máy (không commit URI thật). Đặt hai mật khẩu riêng >=12 ký tự vào SALES_DEMO_USER_PASSWORD và SALES_DEMO_ADMIN_PASSWORD trong `.env`. Không dùng mật khẩu comment của initializer.

```powershell
php artisan key:generate # chỉ khi APP_KEY còn trống
php artisan config:clear
php artisan migrate --path=database/migrations/mongodb
php artisan db:seed --class=SalesDemoSeeder
npm.cmd ci --ignore-scripts
npm.cmd run build
php artisan storage:link # khi đã có ảnh ở storage/app/public/products
php artisan serve --host=127.0.0.1 --port=8000
```

Trong môi trường hiện tại npm hệ thống lỗi, thay hai lệnh npm bằng:

```powershell
node .runtime/npm/package/bin/npm-cli.js ci --ignore-scripts
node .runtime/npm/package/bin/npm-cli.js run build
```

**Chỉ chạy migrations trong path mongodb.** Migrations SQL và DatabaseSeeder mặc định chưa được chuyển cho toàn nhóm; không chạy `migrate:fresh`, `migrate --seed` hoặc `db:seed` không chỉ rõ class. Composer setup/post-create đã đổi sang path MongoDB để không tự chạy migrations SQL/khởi tạo SQLite.

Nạp mẫu an toàn: SalesDemoSeeder không drop và không chạy mongodb_init.js. Bộ 80 sản phẩm/5 danh mục theo kế hoạch để Dũng xây seeder/import idempotent; nhánh này không tự nạp toàn bộ script phá dữ liệu. Nếu review index trên dữ liệu có sẵn, sao lưu và kiểm tra trùng username/email/code/slug/user_id trước; không tự xóa bản ghi gây lỗi unique.

### Các route của Tiến

| Method | URI | Route name / quyền |
|---|---|---|
| GET | /cart | cart.index / auth |
| POST | /cart/add | cart.add / auth; product_id ObjectId string, quantity |
| PATCH | /cart/items/{code} | cart.update / auth; quantity |
| DELETE | /cart/items/{code} | cart.remove / auth |
| DELETE | /cart | cart.clear / auth |
| GET, POST | /checkout | checkout, checkout.store / auth |
| GET | /orders/success | orders.success / auth; chỉ đơn vừa checkout trong session |
| GET | /account/orders | orders.index / auth |
| GET | /account/orders/{order} | orders.show / auth + owner; order là ObjectId 24 hex |
| GET | /admin/orders | admin.orders.index / auth + role:Admin; status,q,page |
| GET | /admin/orders/{order} | admin.orders.show / auth + role:Admin |
| PATCH | /admin/orders/{order}/status | admin.orders.status / auth + role:Admin; status |

Tất cả POST/PATCH/DELETE nằm trong web middleware có CSRF. UI dùng POST + @method để gửi PATCH/DELETE. File sales.php được require đúng một dòng từ web.php, không đụng routes/admin.php hay routes/user.php của Luân.

### Kiểm tra trực tiếp trên browser

1. Mở http://127.0.0.1:8000/login, đăng nhập user.sales@example.test bằng mật khẩu tự đặt ở `.env`.
2. Trong khi catalog chưa tích hợp, dùng Tinker để thêm mẫu vào giỏ, rồi mở `/cart`:

```powershell
php artisan tinker
```

```php
$user = App\Models\User::where('email', 'user.sales@example.test')->firstOrFail();
$product = App\Models\Product::where('code', 'TIEN-DEMO-01')->firstOrFail();
app(App\Services\SalesService::class)->change($user, 'add', $product->id, 2);
```

3. `/cart`: sửa số lượng, xóa, clear; thêm lại mẫu bằng bước 2 nếu cần. Số lượng vượt tồn phải báo lỗi; tạm tính hai sản phẩm là 700.000đ với dữ liệu mới.
4. `/checkout`: thử thiếu thông tin/sai phone, sau đó điền người nhận, số 0 + 9–10 chữ số, địa chỉ, COD/Banking rồi đặt hàng. Kết quả `/orders/success`; `/account/orders` có đơn. Reload/submit lại khi giỏ rỗng không tạo thêm đơn.
5. Logout, login admin.sales@example.test; vào `/admin/orders`, lọc Pending, tìm mã/người nhận, mở chi tiết; chuyển Processing → Shipped → Completed. Đơn Pending khác có thể Cancelled để kiểm tra hoàn kho.
6. Login User thử `/admin/orders` phải 403; dùng user khác truy cập URL chi tiết đơn phải 404. Đường dẫn quay lại giỏ/đơn/Admin nằm trong component nội bộ các trang bán hàng; không sửa navbar chung.

Chạy kiểm thử lại (MongoDB replica set 27018 phải đang hoạt động):

```powershell
php artisan test
php artisan test --filter='CartOperationsTest|OrderCheckoutTest|AdminOrderManagementTest|SalesConcurrencyTest|SalesSetupTest'
php artisan route:list
php artisan view:cache
php vendor/bin/pint --dirty --test
git diff --check
```

Máy dùng replica set khác: đặt MONGODB_URI trong môi trường trước lệnh test; phpunit.xml giữ nguyên tên database test an toàn. Không đặt database test thành shop_hoatuoi để né guard. Evidence HTTP là lượt chạy trên database riêng shop_hoatuoi_test_http_review; tài khoản test/mật khẩu/cookie runtime không đưa vào Git. Server HTTP của lượt smoke được dừng sau khi kiểm chứng; instance Mongo riêng được giữ để có thể chạy lại tests.

Tài liệu chính thức đã đối chiếu: [Laravel MongoDB](https://www.mongodb.com/docs/drivers/php/laravel-mongodb/current/), [transactions và hạn chế testing traits](https://www.mongodb.com/docs/drivers/php/laravel-mongodb/current/interact-data/transactions/), [Laravel 13 MongoDB](https://laravel.com/framework/docs/13.x/mongodb). Khả năng tương thích còn được kiểm chứng qua composer constraints trong package và bộ test chạy thực tế, không chỉ dựa vào kế hoạch cũ.
