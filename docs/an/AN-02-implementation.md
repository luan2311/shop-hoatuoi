# AN-02 — Bàn giao module sản phẩm của An

## Kết quả

`PASS_AN_01` cho phạm vi Member 2 (Task 2.1–2.3): CRUD/upload sản phẩm, catalog/filter/sort/pagination/detail và AJAX search. Toàn bộ suite chạy 43 tests / 283 assertions, gồm 18 test sản phẩm dùng MongoDB thật riêng và 25 test có sẵn; thêm 4 JavaScript tests. Xem `AN-03-test-evidence.md` để phân biệt automated, browser và phần tích hợp nhóm chưa kiểm chứng.

Repository `https://github.com/luan2311/shop-hoatuoi`, thư mục local `D:/learning/MNM/shop-hoatuoi`, nhánh `feat/an-laravel-mongodb`, base `3ffe4cb0a09be7b7372dee98bf5b0580c83d350f` từ `origin/dev`. Chưa commit/push/merge/PR.

Source MVC chỉ đọc: `../Shop-Flower-reference`, commit `b4ed340f35831f829bc43e585e2bcb5595717511`. Mapping đầy đủ ở `AN-01-scope-and-mapping.md`: `DashboardController` CRUD/upload, `SanPhamController` catalog/detail/search, `SANPHAM`/`LOAIHANG` và các Razor views tương ứng.

## Thiết kế triển khai

- Laravel giữ nguyên 13.37.0, PHP 8.3.30. Chỉ thêm official `mongodb/laravel-mongodb` 5.11.0 và dependency `mongodb/mongodb` 2.5.0. PHP extension dùng 2.5.0; không bỏ qua platform requirements. [Tài liệu chính thức](https://www.mongodb.com/docs/drivers/php/laravel-mongodb/current/) và [Schema Builder](https://www.mongodb.com/docs/drivers/php/laravel-mongodb/current/model-data/schema-builder/).
- Product/Category sử dụng connection `mongodb` rõ ràng. Giữ default DB của dự án cho Auth/Role và các module SQL chưa chuyển. `_id` là ObjectId; `id` accessor dùng cho form/route. Không dùng business code thay `_id`.
- Product ghi `category {code,name,slug}` theo script và `category_id` string ObjectId để liên kết Eloquent. `category` property là snapshot; dùng `categoryRecord` khi cần Category model. Dữ liệu script chưa có category_id vẫn xem/lọc/related bằng snapshot; form edit chọn category theo code và bổ sung reference khi lưu. Không backfill dữ liệu cũ tự động.
- Giá numeric, stock integer; code/slug unique ở validation và database. Slug tự sinh khi tạo, giữ khi đổi tên nếu không nhập slug mới. Giá 0 được phép theo Task 2.1; khác rule >0 trong MVC cũ đã ghi rõ trong mapping.
- FormRequest giới hạn fields/length/types; category phải tồn tại và có code/slug. Ảnh bắt buộc khi tạo, optional khi sửa, JPEG/PNG/WebP <=2 MB. Không nhận đường dẫn ảnh từ client. Storage public tự tạo tên file.
- Ghi DB thành công mới dọn ảnh cũ. Nếu DB từ chối ghi, dọn ảnh vừa upload. Chỉ xóa đường dẫn `products/<filename>.<raster extension>` không còn document nào sử dụng; ảnh legacy không bị xóa. Dọn file là best effort và có log nếu filesystem/DB lỗi; không có distributed transaction giữa MongoDB và filesystem.
- Duplicate do race trả 422 hoặc flash error; lỗi MongoDB trả 503 chung, không lộ exception dù APP_DEBUG bật. Renderer chỉ áp dụng route module sản phẩm, gồm lỗi trong route binding.
- Search là regex literal, case-insensitive, theo tên/mô tả/tên danh mục; escape metacharacters và nhóm OR để không vượt bộ lọc category. Scope tên `matchingKeyword` tránh trùng `Builder::search` của MongoDB 5.11. Giới hạn 6 kết quả. Không cam kết accent-insensitive hoặc dùng text index cho regex substring.
- JS debounce 300 ms, hủy request cũ, bỏ response lỗi thời, xử lý empty/error/Escape/click ngoài. Render `textContent`, URL-encode slug. Partial search độc lập tại `products/partials/search.blade.php`, nhúng một lần trong layout app theo Task 2.3; Huy có thể di chuyển vào header khi hoàn thiện UI (tránh nhúng hai lần gây trùng id).
- Blade dùng Tailwind/layout hiện có, không dựng theme khác. Description HTML legacy hiển thị plain text escaped. Không có nút giỏ hàng/wishlist giả khi module nhóm chưa có routes.

## Collections và index

Phạm vi ghi: `products`; seeder fixture có thể thêm `categories`. Migration dùng thêm `migrations` trên connection MongoDB. Không ghi users/roles/orders/carts/wishlists bằng MongoDB trong task này.

Migration `database/migrations/mongodb/2026_10_26_000001_create_product_catalog.php` chỉ tạo collection nếu chưa có, validator cho collection mới, và index code/slug unique (products/categories), category.code, category.slug+price, price, status. Không thay validator collection đã có, không chạy script drop. Nếu dữ liệu có duplicate, migration sẽ báo lỗi để xử lý thủ công; không tự xóa bản ghi. `down()` cố ý từ chối rollback vì collection/index có thể có trước migration.

`ProductDemoSeeder` dùng firstOrCreate, tạo 15 sản phẩm `ANDEMO01`–`ANDEMO15`, không ghi đè dữ liệu đã có. Tái sử dụng category bo-hoa-tuoi/LH001, thêm fixture khi chưa có. Demo không kèm bộ ảnh gốc. Dũng phụ trách bộ ảnh đầy đủ. Seed slice product/category trong `DatabaseSeeder` cũng được chuyển tối thiểu sang schema mới; phần Auth/Post của seeder chung giữ nguyên, **seeder chung chưa đảm bảo idempotent** và không chạy như một seeder MongoDB toàn hệ thống.

## Routes và cách demo

| Method | Endpoint | Quyền / tác dụng |
|---|---|---|
| GET | `/products` | Guest: q, category_id hoặc category_slug, sort, page; 12/trang |
| GET | `/products/{slug}` | Guest: detail + tối đa 4 related |
| GET | `/api/products/search?q=hoa` | Guest: JSON `{products:[{id,name,price,formatted_price,slug,image_url,url}]}`; tối đa 6 |
| GET | `/admin/products` | auth + role:Admin; search q, 15/trang |
| GET | `/admin/products/create` | Form thêm |
| POST | `/admin/products` | Tạo; multipart + CSRF |
| GET | `/admin/products/{product}/edit` | Form sửa theo ObjectId |
| PUT/PATCH | `/admin/products/{product}` | Sửa; multipart form POST với `_method=PUT` |
| DELETE | `/admin/products/{product}` | Xóa; CSRF |

Sort hỗ trợ `price-asc`, `price-desc`, `alpha-asc`, `alpha-desc`. Pagination giữ query string. Detail/ID không tồn tại trả 404. Unknown category_slug trả catalog rỗng; category_id không tồn tại trả 404; query sai định dạng trả validation error.

## Setup từ checkout này

Máy đã có PHP/Composer/Node/MongoDB. Đã tải PHP MongoDB extension từ downloads.php.net vào `D:/learning/MNM/.an-runtime`, dùng php.ini riêng (không thay PHP hệ thống). PowerShell:

```powershell
cd D:\learning\MNM\shop-hoatuoi
$env:PHPRC = 'D:\learning\MNM\.an-runtime\php.ini'
php --ri mongodb
composer install
composer check-platform-reqs
```

Máy khác cần PHP >=8.3 và extension MongoDB tương thích lock (2.5.x), MongoDB server. `.env.example` giữ default SQL cho module Auth hiện có; cấu hình MongoDB bằng hai biến riêng. Với demo an toàn, dùng database mới riêng, ví dụ:

```dotenv
MONGODB_URI=mongodb://127.0.0.1:27028
MONGODB_DATABASE=shop_hoatuoi_an_demo
SESSION_DRIVER=file
CACHE_STORE=file
```

`.env` local đã được tạo/key:generate và ignore Git. Không chạy lại key:generate trên ứng dụng đang phục vụ session thật. Trên checkout mới hãy copy .env.example nếu chưa có .env, rồi tạo application key một lần.

```powershell
php artisan migrate --database=mongodb --path=database/migrations/mongodb
php artisan db:seed --class=ProductDemoSeeder
php artisan migrate:status --database=mongodb --path=database/migrations/mongodb
php artisan storage:link
npm.cmd ci --ignore-scripts
npm.cmd run build
php artisan serve --host=127.0.0.1 --port=8008
```

Chỉ chạy migration với **đúng --database và --path** trên. Không chạy toàn bộ SQL migrations với DB_CONNECTION=mongodb. Không dùng `composer run setup` cho MongoDB vì script gốc chạy migrate chung. Không chạy mongodb_init.js, migrate:fresh hoặc db:wipe trên dữ liệu hiện có.

Tại bàn giao, server demo đã chạy `http://127.0.0.1:8008/products`; MongoDB riêng bind localhost:27028, data/logs trong `D:/learning/MNM/.an-runtime`. Server PHP và MongoDB là process nền của lượt làm việc, không cài Windows service. Nếu đã dừng, khởi động mongod trên cùng data directory/port rồi chạy serve; tránh chạy hai server trùng cổng. Database demo chỉ chứa catalog fixture/migration, không tạo tài khoản đăng nhập demo trong database người dùng.

Demo: mở catalog, nhập `demo` thấy 6 gợi ý; chọn giá giảm dần; tới trang 2; mở detail. Admin dùng tài khoản Admin của Auth SQL hiện có và truy cập `/admin/products`; cần SQL schema/roles của Luân được setup. Test tự dựng SQL in-memory + MongoDB riêng để kiểm chứng login/logout/role thật, không yêu cầu tài khoản cục bộ của người dùng.

## Chạy test

MongoDB test phải chạy tại `mongodb://127.0.0.1:27028`. Mỗi test tạo tên database `shop_hoatuoi_an_test_<random 16 hex>`, tự dọn đúng database đó sau test; guard kiểm tra URI/tên trước dọn. Không dùng RefreshDatabase/migrate:fresh cho MongoDB.

```powershell
$env:PHPRC = 'D:\learning\MNM\.an-runtime\php.ini'
php artisan test --configuration=phpunit.products.xml
node --test tests/JavaScript/search-realtime.test.cjs

# Toàn bộ tests, bao gồm integration MongoDB thay vì skip mặc định:
$env:AN_MONGODB_TESTS = '1'
$env:MONGODB_URI = 'mongodb://127.0.0.1:27028'
php artisan test
Remove-Item Env:AN_MONGODB_TESTS
```

Default `php artisan test` không opt in sẽ skip các tests MongoDB để tránh tự kết nối/ghi vào database developer; suite integration riêng coi thiếu extension/server là lỗi, không tự tuyên bố pass.

## Dependency / giới hạn bàn giao

| Người | Tích hợp cần phối hợp |
|---|---|
| Luân | Auth/User/Role hiện SQL; chuyển toàn bộ Auth sang MongoDB là phần của Luân. Giữ contract `hasRole('Admin')`. Category CRUD phải tạo code/slug hợp lệ; rename cần đồng bộ snapshots. Category relation dựa category_id chỉ bao phủ document có reference; legacy filter dùng snapshot. Admin layout chưa có trên dev, views hiện dùng x-app-layout. |
| Tiến | Cart/order hiện SQL model; không dùng quan hệ SQL JOIN với Mongo products. Thống nhất dùng code/id; order snapshot không tự cập nhật khi sửa/xóa sản phẩm. Chưa kiểm chứng checkout/group E2E. |
| Huy | Giữ search partial đã nhúng trong layout app hoặc di chuyển vào header, tích hợp cart/wishlist thật. Navbar/theme giữ nguyên. Mobile 375px chưa kiểm thử trực quan; responsive toàn site là phần Huy. |
| Dũng | Bổ sung ảnh legacy và dataset nhóm, ERD/UML, E2E. Không coi hash demo trong mongodb_init.js là password hợp lệ khi chưa Hash::check. |

Mã mới không tự đồng bộ mọi snapshot khi category đổi tên, không xử lý tham chiếu cart/order khi xóa product và không nâng cấp toàn bộ SQL module; các interface này cần nhóm thống nhất khi merge. Image cleanup không atomic với DB; khi cả DB/filesystem lỗi có thể còn orphan file, có log để xử lý. Search substring có thể scan dữ liệu lớn; chưa benchmark latency/production.

## Đối chiếu rubric

| Tiêu chí | Liên quan An? | Đã triển khai | Phụ thuộc nhóm | Evidence |
|---|---|---|---|---|
| CRUD >=2 collections có quan hệ | Một phần | Product CRUD, Category reference/snapshot | Category CRUD: Luân | AdminProductTest + schema tests |
| Login/logout/Admin/User | Tích hợp | Dùng Auth/Role hiện có; server authorization | MongoDB Auth: Luân | ProductAuthIntegrationTest, AdminProductTest |
| Search/pagination | Có | 12/trang, category filters, giá/tên sort, AJAX 6 results | Header chung: Huy | FrontendProductTest, ProductSearchAjaxTest, screenshot |
| Validation | Có | FormRequest + DB unique/validator, image checks | Không | AdminProductTest, ProductFailureTest |
| Eloquent ORM | Có | MongoDB Product/Category; query builder | Cart/orders MongoDB: Tiến | CRUD BSON/readback tests |
| Migration/Seeder | Có trong catalog | Additive Mongo migration, idempotent ProductDemoSeeder | Seed tổng thể: Dũng | ProductSchemaTest, migrate:status |
| Controller/Middleware/Blade | Có | Controllers/views + tái sử dụng auth/role middleware | Layout admin: Luân | Routes, auth/CSRF tests |
| Bootstrap/Tailwind/responsive | Một phần | Tailwind layout sẵn có, grid/breakpoints | UI tổng thể/mobile QA: Huy | Desktop browser smoke; mobile chưa kiểm chứng |
| ERD/UML/báo cáo | Chỉ cung cấp contract | Mapping collection/SQL và dependency docs | Báo cáo tổng thể: Dũng | AN-01/AN-02 |
| Upload/AJAX/API ngoài | Upload/search | Upload, replace/delete, debounce + race handling | Wishlist/maps: Huy | AdminProductTest, ProductFailureTest, JS tests |

## Chuẩn bị PR sau bàn giao

Review diff và các shared-file notes trên; trao đổi Category/Product contract với nhóm. Khi người dùng yêu cầu mới commit/push nhánh và tạo PR vào dev. Không include .env, vendor, node_modules, runtime MongoDB, local credentials hay build outputs.

## Files modified / created

- `app/Exceptions/ProductDatabaseErrors.php`
- `app/Http/Controllers/Admin/ProductController.php`
- `app/Http/Controllers/ProductController.php`
- `app/Http/Requests/ProductBrowseRequest.php`
- `app/Http/Requests/ProductRequest.php`
- `database/migrations/mongodb/2026_10_26_000001_create_product_catalog.php`
- `database/seeders/ProductDemoSeeder.php`
- `docs/an/AN-01-scope-and-mapping.md`
- `docs/an/AN-02-implementation.md`
- `docs/an/AN-03-test-evidence.md`
- `docs/an/evidence/all-tests-final.txt`
- `docs/an/evidence/catalog-search.png`
- `docs/an/evidence/js-tests-final.txt`
- `docs/an/evidence/routes-final.txt`
- `phpunit.products.xml`
- `public/js/search-realtime.js`
- `resources/views/admin/products/create.blade.php`
- `resources/views/admin/products/edit.blade.php`
- `resources/views/admin/products/index.blade.php`
- `resources/views/admin/products/partials/form.blade.php`
- `resources/views/errors/product-database.blade.php`
- `resources/views/products/index.blade.php`
- `resources/views/products/partials/card.blade.php`
- `resources/views/products/partials/search.blade.php`
- `resources/views/products/show.blade.php`
- `routes/products.php`
- `tests/Feature/Products/AdminProductTest.php`
- `tests/Feature/Products/FrontendProductTest.php`
- `tests/Feature/Products/ProductAuthIntegrationTest.php`
- `tests/Feature/Products/ProductFailureTest.php`
- `tests/Feature/Products/ProductSchemaTest.php`
- `tests/Feature/Products/ProductSearchAjaxTest.php`
- `tests/Feature/Products/ProductTestCase.php`
- `tests/JavaScript/search-realtime.test.cjs`
- `.env.example`
- `app/Models/Category.php`
- `app/Models/Product.php`
- `bootstrap/app.php`
- `composer.json`
- `composer.lock`
- `config/database.php`
- `database/seeders/DatabaseSeeder.php`
- `resources/views/layouts/app.blade.php`
- `routes/web.php`
