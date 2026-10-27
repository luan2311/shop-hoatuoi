# AN-02 — Pre-merge review

Ngày kiểm tra: 26/10/2026. Kết luận: **NOT_READY_FOR_PR**.

Branch `feat/an-laravel-mongodb`; base `origin/dev` và HEAD: `3ffe4cb0a09be7b7372dee98bf5b0580c83d350f`. Chưa commit, push, merge hoặc tạo PR. Review cả tracked diff và file mới chưa track; `git diff origin/dev` đơn thuần không bao gồm các file mới.

## 1. Phạm vi và bằng chứng

Đã đọc AN-01-scope-and-mapping, AN-02-implementation, AN-03-test-evidence và `docs/plans/2026-10-19-phan-chia-cong-viec-5-nguoi.md`. Tài liệu/script đính kèm là nguồn đối chiếu; không chạy script `mongodb_init.js` có lệnh xóa collection. Phạm vi sửa chỉ Product và phần Category/Product liên quan trực tiếp trong shared seeder. Không triển khai Auth, Cart, Order, Wishlist hoặc layout mới.

MongoDB riêng: `127.0.0.1:27028`, database nghiệm thu `shop_hoatuoi_an_review_20261026`; SQL auth demo ở `D:/learning/MNM/.an-runtime/an02-review/auth.sqlite`. Fixture có 22 sản phẩm, 5 danh mục; tài khoản demo Admin/User đã kiểm tra hash trước bước browser. PHPUnit tạo database ngẫu nhiên `shop_hoatuoi_an_test_<hex>` cho từng test, guard URI/tên trước cleanup; SQL tests dùng SQLite memory. Không thao tác database quan trọng.

`.env` đã khôi phục bản trước nghiệm thu. Đã bỏ HTML viewport fixture tạm trong public storage. Database demo nghiệm thu và fixture JPEG/PNG nằm riêng trong `.an-runtime/an02-review` để có thể tiếp tục QA. Không có screenshot AN-02 được lưu; screenshot AN-01 không được dùng làm bằng chứng cho lần này.

## 2. Findings và lỗi đã sửa

| Mức | Phát hiện | Sửa / bằng chứng |
|---|---|---|
| P1 | `category()` trùng tên embedded attribute: MongoDB relationship che snapshot khi có category_id; đối tượng trả về là Category thay vì snapshot | Bỏ relation trùng tên, giữ `categoryRecord()` cho live relation. Test đổi category kiểm tra cả snapshot và id |
| P1 | Hai request thay ảnh đồng thời: request đã bind dữ liệu cũ có thể ghi đè ảnh mới và để lại orphan; delete cũ có rủi ro tương tự | Update/delete dùng atomic filter `_id`, ảnh và updated_at ban đầu; không match trả 409. Upload của request thất bại được cleanup. Hai test interleaving thực trên MongoDB |
| P1 | Migration lỗi khi index tương đương tồn tại với tên khác | Nhận diện keys/unique/options thay vì tên; không coi sparse/partial/collation khác là tương đương. Test giữ nguyên document, validator/options và custom named index |
| P2 | POST name/code/description dạng array bị validate nhưng GET form sau redirect trả 500 | Scalar-safe old input trong form; vẫn Blade escape. Test POST lỗi rồi GET 200 |
| P2 | Đổi slug danh mục khiến lọc bằng slug hiện tại bỏ sót product còn snapshot cũ | Resolve Category hiện tại rồi lọc theo code ổn định; test rename slug |
| P2 | Shared seeder tìm category bằng slug mặc định dù firstOrCreate giữ slug đã sửa; có thể null khi seed trên catalog sẵn có | Slice Product lookup bằng code LH001/LH002. Test giữ nguyên category đổi slug, product đã sửa và giá hiện tại |

Lần chạy 5 test điều tra đầu tiên có 4 assertion failures và 1 error, đã tái hiện độc lập các vấn đề đầu. Sau sửa, 8 test nghiệm thu bổ sung đều qua trong full suite.

Atomic update/delete dùng MongoDB driver để kiểm tra matched/deleted count; create vẫn dùng Eloquent. Update/delete không phát Eloquent model events. Hiện Product không có update/delete observer; nếu nhóm thêm audit/cache observer phải tích hợp rõ. Cơ chế này bảo vệ thay đổi xảy ra sau route binding, không phải khóa phiên chỉnh sửa từ lúc mở form. Database và filesystem không có transaction chung: lỗi mạng làm kết quả ghi không xác định hoặc storage delete lỗi vẫn có thể để lại file cần đối soát; cleanup chỉ xóa khi xác nhận không còn tham chiếu và ghi log khi lỗi. Không tự xóa ảnh legacy/ảnh ngoài products/ hoặc ảnh đang được product khác dùng.

## 3. Review bảo mật, truy vấn và migration

- Admin routes có web + auth + role:Admin; FormRequest kiểm tra quyền, validation chỉ lấy safe fields. ObjectId sai/không tồn tại trả 404 trong tests. Unique/exists trỏ model MongoDB, không dùng SQL mặc định.
- Có CSRF regression 419; XSS qua tên/mô tả được escape, AJAX dùng textContent; ảnh chỉ raster JPEG/PNG/WebP tối đa 2 MB, đường dẫn upload do Storage sinh. Không nhận arbitrary image path qua mass assignment.
- Search regex literal đã escape, grouped OR; q tối đa 100 ký tự, AJAX tối đa 6, debounce 300 ms và loại response cũ. Catalog 12/trang, giữ query string và tie-break `_id`; admin 15/trang. Regex substring không tận dụng text index và có nguy cơ scan khi dữ liệu lớn.
- Kế hoạch có nhắc full-text nhưng mapping AN-01 giữ substring từ MVC. Cần chốt contract tìm kiếm trước PR; chưa gọi substring là full-text, chưa thêm Atlas Search/module mới.
- Migration không sửa document/validator của collection có sẵn, không drop index. Duplicate code làm unique-index migration thất bại, test xác nhận document nguyên vẹn. Index option conflict vẫn có thể chặn migration: cần owner xử lý trước deploy, không tự drop/rebuild. DDL không atomic; index tạo trước điểm lỗi có thể đã tồn tại. Rollback chủ động từ chối xóa shared collection.
- ProductDemoSeeder chạy lặp không ghi đè. **Toàn bộ DatabaseSeeder chưa idempotent**: lần hai lỗi unique `roles.name` ở phần Auth trước khi tới catalog. Regression test xác nhận lỗi này và catalog không thay đổi. Không sửa phần Auth/Post ngoài phạm vi; chưa cho phép kết luận seed toàn nhóm an toàn trên mọi database có sẵn.

## 4. Browser E2E

**BLOCKED cho nghiệm thu browser tổng thể.** Tool trả nguyên văn `Local browser use is disabled in Work settings` khi bắt đầu thao tác lọc. Không dùng HTTP/PHPUnit thay cho kết quả browser. Tài khoản demo có sẵn nhưng chưa đăng nhập qua browser trong AN-02.

| Bước | Kết quả AN-02 |
|---|---|
| 1. Guest /products | Đã quan sát trang trước khi tool bị tắt; chưa có screenshot lưu, cần chạy lại trên code cuối |
| 2. Lọc category, sort giá | BLOCKED |
| 3. Trang 1 → 2 | BLOCKED |
| 4. AJAX tối đa 6 gợi ý | BLOCKED |
| 5. Chi tiết sản phẩm | BLOCKED |
| 6. Login Admin | BLOCKED |
| 7. Tạo với JPEG/PNG | BLOCKED |
| 8. Kiểm tra DB/ảnh sau thao tác browser | BLOCKED |
| 9. Sửa tên/giá/stock/thay ảnh | BLOCKED |
| 10. Kiểm tra ảnh cũ | BLOCKED |
| 11. Xóa sản phẩm vừa tạo | BLOCKED |
| 12. Kiểm tra DB/storage sau xóa browser | BLOCKED |
| 13. Login User, cấm Admin CRUD | BLOCKED |
| 14. Form invalid | BLOCKED |

Backend tests có kiểm tra nhiều contract tương ứng, nhưng dùng test client và storage test; không chứng minh trải nghiệm browser hoặc public image serving thực tế.

## 5. Responsive

| Viewport | Grid, dropdown, tên/giá, pagination, CRUD form, ảnh, horizontal overflow |
|---|---|
| Desktop 1440 px | BLOCKED |
| Tablet 768 px | BLOCKED |
| Mobile 375 px | BLOCKED |

Chưa đo layout thực tế ở ba kích thước trên. Không tuyên bố mobile PASS và không redesign layout của Huy.

## 6. Integration readiness và dependency

**Blocking trước PR/merge:** bật lại local browser để chạy đủ E2E/responsive trên code cuối; chốt substring/full-text; Luân/Dũng thống nhất cách seed trên dữ liệu có sẵn vì DatabaseSeeder tổng thể hiện không chạy lặp được. Nếu target bắt buộc Auth MongoDB thì đây cũng là dependency blocking: tests hiện chứng minh Breeze SQL auth + MongoDB Product, không chứng minh MongoDB users.roles.

**Cần phối hợp:**

- Luân: giữ category.code ổn định; category_id là chuỗi ObjectId, category snapshot chứa code/name/slug. Snapshot cũ vẫn có thể hiển thị tên cũ sau category rename; lọc hiện tại đã resolve code. Category delete/rename policy và backfill snapshot-only records do owner thống nhất. `Category::products()` theo category_id không bao phủ seed chỉ có snapshot. Dùng `categoryRecord`, không gọi relation `category()` cũ.
- Tiến: Product id là ObjectId string, không integer SQL FK. Cart/Order script dùng embedded items/product_code; các relation legacy `cartItems/orderDetails` chưa là contract MongoDB đã kiểm chứng. Checkout phải cập nhật tồn kho atomically; không khẳng định tương thích chỉ vì Product tests qua.
- Huy: wishlist script theo product_code, model relation SQL legacy chưa kiểm chứng; cần ghép nút/cart/wishlist và layout do Huy quản lý. Search partial hiện được include trong shared app layout, không include lặp.
- Dũng: cung cấp ảnh legacy tương ứng, giữ mã sản phẩm/danh mục, tách seed demo khỏi dữ liệu thật. Cần đối soát orphan khi storage/database outage và E2E toàn nhóm.

**Ngoài phạm vi An:** chuyển Auth/Role/User sang MongoDB, triển khai Cart/Order/Wishlist, đổi layout, bổ sung full bộ ảnh, chỉnh seed Auth/Post. Không sửa các module này trong AN-02.

## 7. Retest cuối

| Kiểm tra | Kết quả | Bằng chứng |
|---|---|---|
| PHP full suite, MongoDB thật riêng | PASS: 51 tests, 322 assertions | [php-tests.txt](evidence/pre-merge/php-tests.txt) |
| JavaScript node tests | PASS: 4/4 | [javascript-tests.txt](evidence/pre-merge/javascript-tests.txt) |
| Composer validate --strict | PASS, exit 0 | [composer-validate.txt](evidence/pre-merge/composer-validate.txt) |
| Composer check-platform-reqs | PASS, exit 0 | [platform.txt](evidence/pre-merge/platform.txt) |
| Route listing/middleware | PASS: 9 product routes, 6 admin có auth+role | [routes.txt](evidence/pre-merge/routes.txt) |
| Mongo migration + ProductDemoSeeder chạy hai lần | PASS, mỗi lệnh exit 0 trên review DB riêng | [migration-seed.txt](evidence/pre-merge/migration-seed.txt) |
| Existing document/index/validator, duplicate-index failure, seed preservation | PASS trong PreMergeReviewTest và ProductSchemaTest | PHP suite phía trên |
| npm run build | PASS, exit 0 | [build.txt](evidence/pre-merge/build.txt) |
| git diff --check | PASS | [git-checks.txt](evidence/pre-merge/git-checks.txt) |
| Browser / responsive | BLOCKED | Mục 4–5 |

Runtime kiểm tra: PHP 8.3.30 với `.an-runtime/php.ini`, MongoDB extension 2.5; lock Laravel 13.37.0, mongodb/laravel-mongodb 5.11, mongodb/mongodb 2.5. Không nâng dependencies trong AN-02. Laravel 13 yêu cầu PHP >=8.3 ([release notes](https://laravel.com/framework/docs/releases)); cần team dùng PHP/extension tương thích và composer install từ lock ([MongoDB compatibility](https://www.mongodb.com/docs/drivers/compatibility/?driver-language=php&php-driver-framework=laravel)). Platform pass chỉ áp dụng máy kiểm tra; không chứng minh môi trường deploy. Upgrade guide lưu ý thay session serialization có thể invalid sessions ([Laravel upgrade](https://laravel.com/framework/docs/13.x/upgrade)); không thay cấu hình này.

## 8. File thay đổi và conflict shared

AN-02 sửa: `app/Models/Product.php`, `app/Http/Controllers/Admin/ProductController.php`, `app/Http/Controllers/ProductController.php`, `resources/views/admin/products/partials/form.blade.php`, `database/migrations/mongodb/2026_10_26_000001_create_product_catalog.php`, slice lookup category của `database/seeders/DatabaseSeeder.php`. Thêm `tests/Feature/Products/PreMergeReviewTest.php`, báo cáo này và `docs/an/evidence/pre-merge/*`. Phần còn lại là thay đổi AN-01 đã có trước nghiệm thu; xem [git-checks.txt](evidence/pre-merge/git-checks.txt) để biết toàn bộ status.

Shared tracked files so với base: `.env.example`, `app/Models/Category.php`, `app/Models/Product.php`, `bootstrap/app.php`, `composer.json`, `composer.lock`, `config/database.php`, `database/seeders/DatabaseSeeder.php`, `resources/views/layouts/app.blade.php`, `routes/web.php`.

Giảm conflict khi nhóm chuẩn bị PR: review riêng từng hunk shared với owner; giữ routes/products.php tách riêng, web chỉ include; app layout chỉ guest guard và search partial, Huy ghép vị trí cuối; Category model/config/Auth contracts do Luân chốt; Dũng ghép slice catalog seeder; Composer giữ lock đồng bộ, không giải conflict bằng lấy nguyên file từ một nhánh. Bootstrap renderer chỉ dành lỗi database product routes. Không tự rebase/commit khi working tree còn các thay đổi này.

## 9. Kết luận

**NOT_READY_FOR_PR**: đã sửa 6 nhóm lỗi thực tế và retest backend/build đạt, nhưng browser/responsive bắt buộc còn BLOCKED và có contract/seed dependency cần nhóm chốt. Không coi kết quả AN-01 hoặc PHP tests là bằng chứng hoàn tất các phần đó. Sau khi bật local browser, chạy lại toàn bộ 14 bước và ba viewport trên database demo riêng, lưu screenshot thật, rồi cập nhật kết luận.
