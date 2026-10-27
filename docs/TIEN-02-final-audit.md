# TIEN-02 — Nghiệm thu cuối và khả năng tích hợp

Ngày 26/10/2026, múi giờ UTC+7. Repository `D:\Learning\OpenSourceProgramming\DA\shop-hoatuoi`, nhánh `feature/cart-checkout-orders`, HEAD `3ffe4cb0a09be7b7372dee98bf5b0580c83d350f`. Không commit, push, merge, tạo PR, reset, stash hoặc ghi đè công việc đồng đội.

## 1. Kết luận độc lập

| Mức sẵn sàng | Kết quả | Phạm vi và lý do |
|---|---|---|
| READY_TO_COMMIT | **PASS** cho nhánh Tiến | Source, tests, hợp đồng và bằng chứng đã kiểm tra; có danh sách file rõ ràng, loại secrets/runtime/build. Các thay đổi dùng chung được đánh dấu để Lead review; chưa tự động được phép merge |
| READY_TO_INTEGRATE | **BLOCKED** cho website toàn nhóm | Catalog An chưa có trên remote; nhánh Luân còn dùng SQL Category/Role, pivot roles, field order cũ và SQL testing trait. Cần đồng bộ các API/model trước khi tích hợp chạy đúng |
| READY_TO_DEMO | **PASS** cho module Tiến độc lập | Checkout thật trên desktop/mobile và HTTP, MongoDB transaction và restart Windows đã được kiểm chứng. Demo toàn website vẫn BLOCKED bởi các phụ thuộc tích hợp |

Không có lỗi nghiệp vụ Tiến đã xác minh còn chưa sửa trong phạm vi kiểm tra này. Chưa có luồng từ Catalog thật, ảnh upload thật hoặc layout shop/admin cuối để nghiệm thu toàn nhóm. Default development DB chưa có dữ liệu; demo đã kiểm chứng dùng database audit riêng có fixture.

## 2. Môi trường MongoDB thực tế

| Hạng mục | Kết quả |
|---|---|
| PHP / Composer | PHP 8.3.30 ZTS x64, Composer 2.9.4; Laravel 13.37.0, mongodb/laravel-mongodb 5.7.0, library 2.5.0, extension 2.5.5 |
| MongoDB 127.0.0.1:27017 | Server 8.2.3 **standalone**, hello không có setName; không hỗ trợ transaction của checkout. Không sửa, restart hoặc thao tác dữ liệu ở instance này |
| MongoDB 127.0.0.1:27018 | Server 8.2.3, replica set **tienRS**, một node writable primary, bind loopback. Phù hợp development local và test transaction; không phải triển khai production có dự phòng |
| `.env` | APP_ENV=local, DB_CONNECTION=mongodb; kết nối loopback tới 27018/tienRS, MONGODB_DATABASE=shop_hoatuoi; session/cache=file, APP_KEY đã có. Không xuất key/password và không sửa `.env` trong TIEN-02 |
| `.env.testing` | Không tồn tại. phpunit.xml cấp cấu hình MongoDB và database test riêng; không dùng SQLite. Đã bổ sung ignore cho `.env.testing` để tránh commit credentials về sau |
| `config/database.php` | Mongo connection lấy URI/database từ env. Fallback URI 27017 chỉ áp dụng khi thiếu env; preflight chặn standalone, không tự bỏ transaction |
| Development DB ở 27018 | `shop_hoatuoi`: trước migration users/products/orders/carts đều có 0 bản ghi. Chỉ chạy migration additive thứ ba để tạo unique sparse index checkout_key; không seed, drop hoặc reset |
| Database ứng dụng chạy thật khi audit | `shop_hoatuoi_test_tien02_20261026140353`, độc lập với development DB và dữ liệu cũ. Có validators từ initializer, ba tài khoản giả lập và một sản phẩm fixture |

Không thực thi mongodb_init.js. Không có database nào bị drop trong TIEN-02. Đã bỏ teardown drop DB khỏi UsesMongoDatabase: mỗi test tạo DB ngẫu nhiên riêng và giữ lại cho audit. Các DB test có thể tích lũy; chưa tự dọn chúng. Việc sửa/xóa sản phẩm hoặc cart item trong test là nghiệp vụ trên fixture, không phải drop database.

Đã shutdown có kiểm soát **instance riêng 27018**, chạy lại `scripts/start-tien-mongodb.ps1`, rồi so sánh hash JSON đọc trực tiếp orders/stock/carts trước và sau: giống nhau. ECONNRESET khi mongosh shutdown là do server đóng kết nối khi dừng. Xem [restart evidence](evidence/tien02/restart-check.json).

Đã khởi động website bằng `scripts/start-tien-sales.ps1`; GET /login trả 200. Preflight của default development DB cũng PASS sau khi thêm index. Không chỉ dựa vào Artisan trong test suite để kết luận ứng dụng chạy được.

## 3. Những lỗi đã xác minh và sửa

1. **Replay checkout sau khi giỏ được thêm mới:** trước sửa, một request cũ có thể tạo hai đơn thay vì một. Thêm checkout_token UUID bắt buộc, hash theo user vào orders.checkout_key và unique sparse index. Retry trả đơn ban đầu, giữ nguyên giỏ mới và tồn kho. Có test token theo tài khoản và retry đồng thời. [Regression trước sửa](evidence/tien02/regression-before.json).
2. **BSON Decimal128:** hai regression tests trả 500 khi render giỏ/đơn legacy do cast object sang float. SalesService chuẩn hóa đơn giá cart; các Blade order chuyển Decimal128 qua string trước khi format. Checkout mới vẫn lưu integer VND và chỉ lấy giá từ server. Không sửa Catalog của An. [Regression trước sửa](evidence/tien02/decimal-regression-before.json).
3. **Bảng trên mobile:** tên sản phẩm bị ép xuống từng từ. Thêm min-width 640px và cuộn ngang trong bảng của Tiến. Ancestor `relative` giữ label sr-only trong vùng cuộn, tránh tràn ngang toàn trang. Kết quả cuối cart/admin list: documentWidth 375 tại viewport 375, bảng 640 cuộn bên trong. Không đổi theme/layout chung.
4. **Ngày đặt:** trước hiển thị UTC không có nhãn; đổi phần hiển thị của Tiến sang UTC+7/Asia/Ho_Chi_Minh. BSON datetime trong DB vẫn giữ UTC.
5. **Khởi động lặp lại:** thêm hai script PowerShell và preflight PHP. Launcher website không tự cài package, migrate hoặc seed; kiểm tra extension, topology và index idempotency trước khi chạy.

## 4. Nghiệp vụ và bằng chứng kiểm thử

| Nghiệp vụ | Tự động trên MongoDB thật | Ứng dụng chạy thật | Kết luận |
|---|---|---|---|
| Thêm nhiều lần cộng dồn | CartOperationsTest; concurrent first-add | HTTP 2+1 thành 3; browser cập nhật 1→2 ở desktop và 2→3 ở mobile | PASS |
| Không sửa giỏ/xem đơn người khác | Injection user_id/cart_id; cart isolation và owner tests | HTTP Other không xem được đơn User; User bị 403 ở Admin | PASS |
| Không đặt vượt tồn | Validation và hai process mua sản phẩm cuối | HTTP add 999 bị 422, stock không đổi | PASS |
| Giá/tổng tiền từ server, snapshot | Giả price/total/user_id; đổi/xóa sản phẩm sau mua | HTTP gửi price=1,total=1 vẫn lưu 1.050.000; browser đọc đúng giá/tổng | PASS |
| Tiền BSON Decimal128 | Hai regression tests và schema Mongo thật | Smoke browser/HTTP dùng giá integer; không ghi browser PASS cho Decimal128 | PASS tự động |
| Checkout đồng thời không oversell | Hai PHP process độc lập mua sản phẩm cuối | Không tái tạo race bằng UI | PASS tự động |
| Checkout lỗi rollback | Item thứ hai hết stock; Mongo validator từ chối insert; đọc lại stock/cart/orders | Không sửa validator của ứng dụng đang mở browser để giả lập lỗi | PASS tự động |
| Retry không tạo đơn ngoài ý muốn | Retry cùng token, account scope, replay sau refill | HTTP retry ngay và sau refill: vẫn một đơn, giỏ mới giữ một item, không trừ stock thêm | PASS |
| Hủy chỉ hoàn kho một lần | Admin tests và concurrent cancellation | HTTP hủy lần đầu thành công, lần hai 422; browser hủy đơn desktop và Mongo xác nhận hoàn kho | PASS |
| Chặn hủy trạng thái cuối/nhảy bước | Completed/Cancelled bất biến; từ chối skipped transition | HTTP Completed→Cancelled bị 422; UI trạng thái cuối không còn form | PASS |
| Luồng trạng thái | Pending→Processing→Shipped→Completed | Admin desktop chuyển Processing, mobile chuyển Shipped/Completed; search/filter có thao tác thật | PASS |
| MongoDB thật | Model Mongo, validators/indexes và direct-driver assertions | Đọc lại trực tiếp; restart không đổi dữ liệu | PASS |
| CSRF/auth/validation | Auth/profile suite, CSRF 419 và FormRequests | Cookie login thật, HTTP 419; browser chặn phone 123 | PASS |

Suite cuối: **51 tests / 355 assertions PASS**, bao gồm auth/profile cũ và các test bán hàng. [PHPUnit JSON](evidence/tien02/phpunit.json). So với TIEN-01, thêm năm audit tests và hai concurrency tests. Không diễn giải 355 assertions thành 355 trường hợp UI.

Lượt HTTP TIEN-02: **21 checks PASS**, cookie sessions và CSRF thật, không actingAs. [HTTP JSON](evidence/tien02/http-audit.json). Chỉ gọi ứng dụng local tại 127.0.0.1:8001.

Trình duyệt Chromium trong app: **desktop 1280×900 và mobile 375×812**. Đã thao tác form/nút thực tế trên cả `/cart`, `/checkout`, `/account/orders`, `/admin/orders`, không chỉ kiểm tra HTTP 200. [Biên bản browser](evidence/tien02/browser-audit.json).

- Mobile User: tăng quantity/update, validation phone, checkout ba items 1.050.000đ, mở lịch sử và chi tiết.
- Desktop Admin: search/filter, checkout một item 350.000đ, xem lịch sử cá nhân, hủy qua UI.
- Admin desktop/mobile: chuyển Processing/Shipped/Completed, lọc Completed để loại đơn Cancelled, kiểm tra form biến mất ở trạng thái cuối.
- Không tràn ngang toàn trang sau sửa; bảng cần cuộn ngang có chủ đích. Chưa kiểm tra thiết bị vật lý hoặc Safari.
- Layout vẫn là Breeze với tên Laravel và navbar Dashboard. Đây là phần chung chờ Luân/Huy, không được redesign trong audit.
- Ảnh sản phẩm thật/upload chưa được kiểm chứng: fixture image rỗng và chưa có module An. Không ghi PASS cho ảnh thật hoặc demo UI toàn nhóm.

Ảnh: [cart desktop](evidence/tien02/cart-desktop.png), [cart mobile](evidence/tien02/cart-mobile.png), [checkout desktop](evidence/tien02/checkout-desktop.png), [validation mobile](evidence/tien02/checkout-mobile-validation.png), [history mobile](evidence/tien02/history-mobile.png), [Admin desktop](evidence/tien02/admin-desktop.png), [Completed mobile](evidence/tien02/order-completed-mobile.png). Các ảnh ở những bước khác nhau; JSON readback là căn cứ trạng thái cuối.

Mongo readback cuối có ba đơn:

- User: Completed, 1.050.000đ, quantity 3.
- HTTP Other: Cancelled, 1.050.000đ, quantity 3.
- Admin: Cancelled, 350.000đ, quantity 1.

Stock 30→27 do chỉ đơn User Completed còn hiệu lực; không còn giỏ chứa items. Cả ba đơn có checkout_key. [Readback JSON](evidence/tien02/mongodb-readback.json).

Composer validate/platform, Pint, Blade view cache, Vite build và git diff --check đều PASS. Không đổi dependencies trong TIEN-02. npm hệ thống vẫn lỗi bộ cài; build bằng npm runtime riêng như TIEN-01. Ba advisory moderate frontend cũ chưa sửa ngoài phạm vi Tiến; không ghi npm audit PASS. **Laragon Apache/Nginx chưa được chạy trực tiếp**; chỉ Artisan/Windows launcher và Chromium đã PASS.

## 5. Khả năng tích hợp và Git

Đã đọc lại file phân công, đối chiếu Member 3/Tasks 3.1–3.3. Sau fetch, origin/dev vẫn tại 3ffe4cb và chưa có nhánh An/Huy/Dũng. Không suy đoán phần chưa push của đồng đội. [Hợp đồng tích hợp](TIEN-02-integration-contract.md) mô tả endpoint/model/response, form Blade và checkout_token mới.

Chồng lấn đường dẫn với origin/feature/auth-category-admin: **routes/web.php**. Chưa thử merge, nên không khẳng định đã giải quyết conflict thực tế. Khi hợp nhất phải giữ require auth/admin/user/sales và không include hai lần.

Các điểm không tương thích tìm thấy trong code nhánh Luân:

- Tests còn dùng roles()->attach() và RefreshDatabase, không tương thích roles[] và MongoDB testing trait hiện tại.
- Category/Role vẫn là SQL; Product.category()/scopeSearch()/with(category) cũ chưa là API quan hệ MongoDB.
- Dashboard view dùng receiver_name/total; order mới dùng customer.name/total_amount. Fallback hiện tại có thể sai người nhận hoặc tổng tiền.
- Dashboard controller dùng latest()/with(category). Luân cần kiểm tra sort với MongoDB/Laravel 13; phần Tiến đã dùng orderBy(..., 'desc') được kiểm chứng.

Không sửa các file của Luân để xử lý thay. Đây là lý do READY_TO_INTEGRATE toàn nhóm BLOCKED dù suite nhánh Tiến PASS.

### Phân loại file và danh sách commit

[commit-files.txt](evidence/tien02/commit-files.txt) liệt kê từng file đề xuất commit. [file-classification.json](evidence/tien02/file-classification.json) phân loại và nêu các snapshot không cần commit. Không tự git add.

Tổng cộng **74 file đề xuất**: 23 file code Tiến, 13 file dependency/config chung cần thiết, 3 tài liệu và 35 file tests/evidence. Ba snapshot TIEN-01 cũ được giữ local nhưng loại khỏi danh sách. Kiểm tra Git: không có file staged, không có secrets được tracked hoặc password runtime xuất hiện trong các file đề xuất; không trùng tên route và PHP lint không có lỗi. Xem [Git checks](evidence/tien02/git-checks.json).

- **Code Tiến:** Cart/Order, SalesService, controllers/requests, routes sales, Blade/components, sales migrations/seeder.
- **Dependency/config chung cần thiết:** bridge User/Product, MongoDB config, bootstrap error handler, composer json/lock, env example/gitignore, require trong web.php, lookup indexes và startup/preflight. Cần Lead review; không có nghĩa module Catalog/Auth đã được chuyển xong.
- **Tests/evidence:** MongoDB traits/fixtures, các test bán hàng, thay trait auth/profile, phpunit.xml, JSON và ảnh kiểm chứng có chủ đích.
- **Tài liệu:** bàn giao TIEN-01 có dẫn tới TIEN-02, hợp đồng và báo cáo này.
- Không có source Catalog/Category/Wishlist/Contact/Posts/layout chung bị sửa thêm trong audit. Không có thay đổi ngoài phạm vi được đề xuất commit.
- Không commit `.env`, `.env.testing`, `.runtime/**`, vendor, node_modules, public/build, public/storage, caches/logs. Runtime chứa DLL, DB data/log/pid, password/cookies, HTTP scratch scripts và npm archives; git check-ignore đã xác nhận.
- Ba snapshot cũ `docs/evidence/2026-10-26-tien-files.txt`, `...-http.json`, `...-tests.json` đã được evidence TIEN-02 thay thế. Giữ file trên máy, không xóa; loại khỏi commit-files.txt. Tài liệu đề xuất commit không còn cần link tới chúng.
- Screenshots TIEN-02 là bằng chứng desktop/mobile theo yêu cầu, không phải build artifacts. Chúng chỉ chứa thông tin fixture giả lập và không có credentials.

## 6. Khởi động lặp lại trên Windows/Laragon

```powershell
Set-Location D:\Learning\OpenSourceProgramming\DA\shop-hoatuoi
.\scripts\start-tien-mongodb.ps1
# Trên máy hiện tại dùng extension riêng, không sửa PHP hệ thống:
$env:PHP_INI_SCAN_DIR = (Resolve-Path .runtime\php).Path
composer install
composer check-platform-reqs
php artisan migrate --path=database/migrations/mongodb
.\scripts\start-tien-sales.ps1 -CheckOnly
.\scripts\start-tien-sales.ps1 -Port 8000
```

Script MongoDB chỉ sử dụng 27018, initialize khi node chưa được khởi tạo; không reconfigure server khác và không drop/reset. Cung cấp -MongoBin nếu đường dẫn mongod khác. Launcher website không tự migrate/seed/cài đặt; chỉ preflight rồi Artisan serve. Máy mới cần bật extension MongoDB tương thích PHP và cài mongosh/vendor. DLL runtime không được commit.

`.env` phải trỏ replica set/Atlas hỗ trợ transaction; không dùng standalone 27017 cho checkout. Default dev DB chưa có tài khoản/sản phẩm. Để chuẩn bị demo mới, tự đặt SALES_DEMO_USER_PASSWORD/SALES_DEMO_ADMIN_PASSWORD tối thiểu 12 ký tự trong env local rồi chạy `php artisan db:seed --class=SalesDemoSeeder`. Seeder không ghi đè password/role/stock đã có. Không chạy DatabaseSeeder mặc định hoặc mongodb_init.js.

Để tiếp tục xem đúng fixture đã audit trên máy hiện tại:

```powershell
$auditRun = Get-Content .runtime\tien02\run.json -Raw | ConvertFrom-Json
.\scripts\start-tien-sales.ps1 -Database $auditRun.database -Port 8001
```

Email fixture: user.tien02@example.test và admin.tien02@example.test. Password nằm trong file run.json ignored local, không đưa vào Git/evidence. Audit server đã dừng sau kiểm chứng; replica set riêng 27018 được giữ chạy cho tests. Chạy lại tests bằng `php artisan test`; database test được giữ lại, không drop.

Với Laragon Apache/Nginx: chọn PHP 8.3+ đã bật extension MongoDB, restart web server, dùng cùng .env, document root public và build assets. Đây là hướng dẫn tích hợp, không phải kết quả đã chạy thử Apache/Nginx.

## 7. Việc cần phối hợp và phần còn thiếu

- **An:** Catalog/nút thêm giỏ, code sản phẩm ổn định, status, price, ảnh và quan hệ/scopes MongoDB.
- **Luân:** shared bridge, roles array, Category/Role SQL→MongoDB, field/sort của Dashboard, hợp nhất routes và migrations.
- **Huy:** layout navbar/Admin/User cuối và kiểm tra responsive với ảnh thật.
- **Dũng:** dữ liệu đầy đủ, hash mật khẩu, ảnh thật và tests tích hợp nhiều module.
- **Tiến:** không còn lỗi core đã xác minh chưa sửa; còn nghiệm thu tích hợp từ Catalog/layout hoàn chỉnh, ảnh thật và server Laragon thực tế khi các phụ thuộc được cung cấp. Không tự triển khai upload/AJAX search/wishlist/Maps để hoàn rubric. Banking chưa là payment API; guest cart không thuộc phân công.
