# AN-03 — Bằng chứng kiểm thử

Ngày: 26/10/2026 (Asia/Bangkok). Kết luận: **PASS_AN_01 trong phạm vi module sản phẩm Task 2.1–2.3**. Các dependency chưa hoàn thiện của nhóm và giới hạn manual/production không được tính là đã kiểm thử.

## Môi trường thực tế

- Windows PowerShell; PHP 8.3.30 ZTS x64, Composer 2.9.4.
- Laravel 13.37.0; official MongoDB integration 5.11.0/library 2.5.0; extension 2.5.0.
- MongoDB binary có sẵn: `C:/Program Files/MongoDB/Server/8.3/bin/mongod.exe`.
- MongoDB riêng được khởi động bind `127.0.0.1:27028`, dbpath `D:/learning/MNM/.an-runtime/mongo-data`. Không dùng database shop_hoatuoi đang có. Các test dùng random database có prefix và guard; demo dùng shop_hoatuoi_an_demo.
- PHP extension/ini riêng tại `D:/learning/MNM/.an-runtime`; PHPRC được đặt theo từng shell. Không sửa php.ini của Laragon.

## Lệnh và kết quả cuối

| Lệnh đã chạy | Kết quả thực tế |
|---|---|
| `git status --short --branch`, `git fetch origin`, kiểm tra origin/dev | Repo ban đầu clone sạch; nhánh feat/an-laravel-mongodb từ 3ffe4cb... |
| `composer install --no-interaction --prefer-dist` | PASS, cài lock gốc |
| `composer require mongodb/laravel-mongodb:^5.11 --no-interaction --minimal-changes` với extension 2.5.0 | PASS; chỉ 2 package thêm, 0 package cũ update/remove |
| `composer validate --no-check-publish` | PASS, composer.json valid |
| `composer check-platform-reqs` | PASS, gồm PHP 8.3.30/ext-mongodb 2.5.0 |
| `php artisan route:list --path=products -v` | PASS, 9 routes; admin auth + role:Admin |
| `php artisan migrate --database=mongodb --path=database/migrations/mongodb --no-interaction` | PASS trên demo riêng, batch 1 |
| `php artisan db:seed --class=ProductDemoSeeder --no-interaction` | PASS, 15 demo products |
| `php artisan migrate:status --database=mongodb --path=database/migrations/mongodb` | PASS, 2026_10_26_000001_create_product_catalog `[1] Ran` |
| `php artisan storage:link` | PASS, public/storage trỏ storage/app/public |
| `npm.cmd ci --ignore-scripts`; `npm.cmd run build` | PASS; Vite 8.3.4 build JS/CSS. npm audit trong install báo 3 moderate trong dependency tree hiện có; không auto-upgrade ngoài phạm vi |
| Pint trên PHP thay đổi (trừ format seeder chung để giữ diff tối thiểu) | PASS sau format |
| `php artisan test --compact` với AN_MONGODB_TESTS=1 và URI=localhost:27028 | **PASS: 43 tests, 43 passed, 283 assertions, 0 skipped; lượt cuối 9.986 giây** |
| `node --test tests/JavaScript/search-realtime.test.cjs` | **PASS: 4 tests, 0 failed, 0 skipped** |
| `node --check public/js/search-realtime.js` | PASS |
| `git diff --check` | PASS |
| `git -C ../Shop-Flower-reference status --short` | Sạch, source tham khảo không bị sửa |
| `git check-ignore .env public/storage public/build/manifest.json` | Cả 3 ignored; không stage secrets/build |

Log cuối đã giữ trong `docs/an/evidence/all-tests-final.txt`, `js-tests-final.txt`, `routes-final.txt`; screenshot `catalog-search.png`. Log development chi tiết nằm ngoài repo tại `.an-runtime`/storage/logs, không đưa exception chứa config vào tài liệu bàn giao.

## Test cases đã chạy trên MongoDB thật

| Suite | Nội dung kiểm chứng |
|---|---|
| AdminProductTest (5) | Create/read/update/delete, BSON casts và relation; upload/thay/giữ/xóa file; guest/User bị chặn mọi action; required/negative/noninteger/duplicate/unknown-category/invalid-status/SVG/oversize/array input; ID missing/malformed 404; escape HTML; không xóa ảnh legacy; mass-assignment fields lạ bị bỏ |
| FrontendProductTest (3) | 12/trang, page 2 còn 3, query string giữ nguyên; category id/slug và sort numeric; seed không category_id vẫn detail/related 4; 404/empty states; query sai 422 |
| ProductSearchAjaxTest (3) | Name/description/category matching, cap 6/JSON contract/empty query; regex/wildcards literal; array/long input rejected; grouped filter + q=0 hoạt động |
| ProductSchemaTest (2) | Migration chạy lại an toàn; seeder chạy 2 lần không nhân đôi/ghi đè; kiểm BSON ObjectId/date/stock/price/category; unique index từ chối duplicate trực tiếp |
| ProductFailureTest (4) | MongoDB validator từ chối ghi sau upload → 503, DB/ảnh cũ giữ nguyên, ảnh mới được dọn; duplicate race sau validation → 422 + cleanup; connection unavailable và route-binding error → generic 503; bật CSRF thực → thiếu token 419, đúng token xóa được |
| ProductAuthIntegrationTest (1) | SQL in-memory migrate + DatabaseSeeder nhóm, Category/Product lưu MongoDB; Hash::check seed Laravel; login Admin bằng Breeze, đọc/sửa MongoDB; logout; login User và 403 admin. Không mock Auth trong case này |

Tổng 18 tests sản phẩm + 25 tests Laravel/Auth/Profile/Example có sẵn = 43. Fake actor chỉ dùng để cô lập các case authorization module; case AuthIntegration dùng User/roles/password/session thật trên SQL in-memory. Không đổi User/Role sang MongoDB ngoài phân công.

JS tests dùng Node built-in test + VM/DOM harness: (1) debounce đúng 300 ms, abort và bỏ stale response, (2) textContent + URL escaping, (3) clear/Escape/outside cancels pending, (4) empty/error state. Không thay thế cho đo hiệu năng trên trình duyệt thật.

## Browser smoke

In-app browser mở `http://127.0.0.1:8008/products` với dữ liệu demo MongoDB thật:

1. Catalog render, pagination hiển thị 1–12 / 15.
2. Gõ `demo`: dropdown có đúng 6 kết quả, URL vẫn `/products`, không submit/reload.
3. Click kết quả đầu: detail ANDEMO01, giá 210.000 đ, tồn 10 và 4 related (ANDEMO02–05).
4. Đã chụp screenshot dropdown. Grid hiện placeholder vì demo không kèm ảnh.

Chưa thực hiện viewport mobile 375px, upload thủ công qua browser, hoặc E2E checkout/wishlist. Upload/authorization đã kiểm bằng HTTP feature tests và MongoDB thật. Tab demo được để lại cho người dùng.

## Các lỗi phát hiện và đã xử lý

- Extension 2.1.2 ban đầu không thỏa dependency library khả dụng; Composer require thất bại và tự revert manifest/lock. Đổi sang extension 2.5.0 riêng rồi cài thành công, không dùng ignore-platform-reqs.
- Lượt 12 tests đầu: 9 pass/3 fail. Scope `search` đụng method MongoDB Builder và assertion chỉ nhận float quá chặt so với BSON numeric cho phép int. Đổi scope `matchingKeyword`, kiểm numeric BSON hợp lệ; chạy lại 12/12 pass, 168 assertions.
- Regression đầu thiếu APP_KEY (repo vừa clone chưa .env): 24 errors; tạo .env/key local rồi 25/25 tests gốc pass, 12 Mongo tests skip có chủ đích khi chưa opt in.
- Bổ sung failures/CSRF: 16 tests /190 assertions pass. Một lượt artisan báo exit 1 dù JSON passed; chạy trực tiếp PHPUnit xác nhận exit 0. Lượt tổng cuối artisan exit 0.
- Test login User sau khi bị redirect admin ban đầu kỳ vọng dashboard; Breeze đúng là intended redirect `/admin/products`. Sửa expectation, kiểm tiếp role trả 403; không sửa Auth để ép test.
- Phát hiện seeder nhóm thiếu MongoDB category code/snapshot: sửa đúng slice catalog và xác minh bằng test AuthIntegration. Không sửa seed Auth/Post.

## Phần chưa kiểm chứng / phụ thuộc

- Đăng nhập dựa trên users/roles MongoDB: module của Luân hiện chưa chuyển, không tuyên bố đã pass. Auth SQL hiện có đã kiểm chứng tích hợp.
- Toàn bộ checkout/cart/wishlist/posts/contact/ERD/UML: ngoài scope An, chưa E2E.
- 80 ảnh từ seed chưa có trong public Laravel; cần Dũng cung cấp. Audit JSON seed: 80 products, 5 category codes, không trùng code/slug, 0 invalid price/stock, 80 HTML descriptions; script không được thực thi.
- Hash trong mongodb_init.js chưa được xác minh/dùng để login; Hash::check đã chạy cho tài khoản sinh bởi Laravel DatabaseSeeder trong SQL test, không phải hash script.
- Mobile visual QA, benchmark large datasets, concurrency replace/delete images toàn diện, production deployment chưa chạy. Search substring không có bằng chứng latency <100ms; không ghi nhận tiêu chí đó là PASS.
- Git cuối vẫn là working changes trên feature branch; chưa commit/push/merge/PR theo yêu cầu.
