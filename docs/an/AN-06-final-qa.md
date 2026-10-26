# AN-06 — Final browser QA và responsive

Ngày: 27/10/2026 (Asia/Bangkok). **NOT_READY_FOR_PR**.

## Phạm vi và môi trường

Working directory `D:/learning/MNM/shop-hoatuoi`, branch `feat/an-laravel-mongodb`, HEAD `3ffe4cb0a09be7b7372dee98bf5b0580c83d350f`. Không commit/push/merge/PR. Đã đọc lại AN-04 và setup AN-02; chỉ sửa lỗi Product phát hiện bằng browser.

PHP 8.3.30, extension MongoDB 2.5.0 qua `.an-runtime/php.ini`; Laravel 13.37.0. Dùng MongoDB đang chạy tại `127.0.0.1:27028`, database `shop_hoatuoi_an_review_20261026`, Auth SQLite `.an-runtime/an02-review/auth.sqlite`. Không chạy setup.php cũ, DatabaseSeeder tổng thể hay script drop collections.

Sandbox ban đầu không thấy process/cổng và không kết nối được MongoDB; lần thử khởi động bị lock. Kiểm tra ngoài sandbox xác nhận mongod PID 23436 và PHP PID 2172 đã chạy. Không xóa lock, không khởi tạo lại database. Chỉ khởi động lại PHP demo thành PID 9364 tại `127.0.0.1:8008`, dùng biến môi trường riêng cho review MongoDB, SQLite Auth, file session/cache, APP_URL và APP_DEBUG=false. HTTP /products 200 trước khi mở browser. `.env` giữ nguyên hash trước/sau; không chép nội dung hoặc credentials vào evidence.

Trước/sau: 22 products, 5 categories, 2 users, roles Admin/User. Không có product code prefix AN03. Hash catalog không thuộc AN03 giữ nguyên `6c974b8f2191b6eded634d28950bb3bbdfc6748901b140ba07eb6978d7e9d64b`. Xem [before](evidence/final-browser/environment-before.json), [after](evidence/final-browser/environment-after.json). Không tuyên bố đã đối soát toàn bộ orphan files vì vòng CRUD browser chưa hoàn tất.

## Browser E2E thực tế

Dùng in-app browser thật, không thay bằng HTTP test. E01–E07 đã thao tác được trước khi quyền Browser bị tắt ở lượt tiếp tục. Lỗi hiện tại: `Local browser use is disabled in Work settings`.

| Case | Kết quả | Bằng chứng / giới hạn |
|---|---|---|
| E01 Catalog | PASS | 12 cards, 22 tổng; [ảnh](evidence/final-browser/e01-catalog-desktop.jpg) |
| E02 Category | PASS | Chọn Kệ Chúc Mừng, 2 sản phẩm H53/H63; [ảnh](evidence/final-browser/e02-category.jpg) |
| E03 Sort | PASS | Thao tác tăng/giảm giá trên Bó Hoa Tươi; giảm từ 1.700.000 xuống 290.000 ở trang 1; [ảnh](evidence/final-browser/e03-price-desc.jpg) |
| E04 Pagination | PASS | Trang 1 tối đa 12; trang 2 còn 8/20 trong danh mục, giữ category và sort; [ảnh](evidence/final-browser/e04-pagination.jpg) |
| E05 AJAX demo | PASS | 6 gợi ý, không submit form, URL trang 2 giữ nguyên; [ảnh](evidence/final-browser/e05-ajax-search.jpg). Timing 300ms được kiểm bằng JS tests, không benchmark browser |
| E06 Detail | PASS | Click gợi ý ANDEMO01, giá 210.000, tồn 10, 4 related; [ảnh](evidence/final-browser/e06-detail.jpg) |
| E07 Admin login | PASS | Login fixture SQL thật, vào trang admin 15/22 records; [ảnh](evidence/final-browser/e07-admin-login.jpg) |
| E08 Create + JPEG | FAIL lần đầu; retest BLOCKED | Form fixture AN03QA20261027 gửi price/stock chuỗi, Mongo validator trả 503. Đã sửa và regression PASS; chưa được phép gọi browser PASS. [Ảnh lỗi](evidence/final-browser/e08-created.jpg) — tên file lịch sử không có nghĩa tạo thành công |
| E09 Update | BLOCKED | Chưa có fixture browser được tạo thành công |
| E10 Replace image | BLOCKED | Chưa chạy |
| E11 Delete fixture | BLOCKED | Chưa chạy; không có fixture AN03 tồn tại để xóa |
| E12 MongoDB consistency | PARTIAL / BLOCKED tổng thể | Đã xác minh create thất bại không tạo document và catalog giữ nguyên; chưa có vòng CRUD thành công để đối chiếu |
| E13 User denied Admin CRUD | BLOCKED | Backend có coverage nhưng chưa kiểm bằng browser lần này |
| E14 Invalid form | BLOCKED | Chưa kiểm bằng browser lần này |

## Lỗi mới và bản sửa

**P1 — Create từ form HTML thất bại với MongoDB validator.** Giá `123456` và stock `7` được multipart POST gửi dưới dạng string. Eloquent casts đọc giá trị không đảm bảo raw attributes được lưu thành BSON numeric; kiểm tra raw attributes cho thấy string. MongoDB trả code 121 Document failed validation, UI trả generic 503 và không lộ exception.

Sửa tối thiểu `app/Http/Controllers/Admin/ProductController.php`: chuyển price sang float, stock sang int trước cả hai nhánh create/update, thay vì chỉ chuyển ở update. Không sửa validator hoặc thuật toán search. Thêm `test_browser_numeric_strings_are_stored_as_bson_numbers` trong AdminProductTest: POST string decimal price và integer stock, kiểm BSON raw types/values và upload tồn tại.

Regression riêng **1 test / 6 assertions PASS**; full suite **52 / 328 PASS**. Cần retest E08 browser rồi tiếp tục E09–E14 trước kết luận hoàn tất An.

## Responsive

Viewport override thật, không dùng iframe fixture. Các phép đo form Create: 1440 → document scrollWidth 1425; 768 → 753; 375 → 365; không có phần tử form đo được vượt viewport. Đã lưu screenshot ở cả ba kích thước. Form mobile có thể đọc các trường và nút Lưu/Quay lại; cần tiếp tục quan sát margin/form có dữ liệu, lỗi validation và preview khi browser mở lại.

| Trang | 1440 | 768 | 375 |
|---|---|---|---|
| Create rỗng, overflow | PASS đo DOM | PASS đo DOM | PASS đo DOM |
| Listing | Đã quan sát, chưa đủ ma trận | BLOCKED | BLOCKED |
| Search dropdown / pagination | Đã quan sát desktop | BLOCKED | BLOCKED |
| Detail / tên dài | Chưa đủ stress case | BLOCKED | BLOCKED |
| Edit / preview ảnh | BLOCKED | BLOCKED | BLOCKED |

Ảnh: [Create 1440](evidence/final-browser/responsive-create-1440.jpg), [768](evidence/final-browser/responsive-create-768.jpg), [375](evidence/final-browser/responsive-create-375.jpg). **Responsive tổng thể BLOCKED**, không đánh đồng kiểm tra form rỗng với nghiệm thu toàn bộ UI. Không redesign layout của Huy. Browser bị disabled nên chưa reset được viewport override trước khi dừng thao tác.

## Regression mới

| Kiểm tra | Kết quả | Log |
|---|---|---|
| Full PHP suite | PASS 52 tests, 328 assertions, exit 0 | [php-tests](evidence/final-browser/php-tests.txt) |
| Numeric string regression | PASS 1 test, 6 assertions, exit 0 | [regression](evidence/final-browser/numeric-string-regression.txt) |
| JavaScript | PASS 4 tests | [JS](evidence/final-browser/javascript-tests.txt) |
| Composer validate strict | PASS | [validate](evidence/final-browser/composer-validate.txt) |
| Platform | PASS PHP 8.3.30 / ext-mongodb 2.5.0 | [platform](evidence/final-browser/platform.txt) |
| Routes | 9 routes, 6 admin auth + role:Admin | [routes](evidence/final-browser/routes.txt) |
| Vite build | PASS exit 0 | [build](evidence/final-browser/build.txt) |
| PHP syntax hai file sửa; git diff --check | PASS | Kiểm tra tại phiên này |

Tests dùng `AN_MONGODB_TESTS=1`, URI localhost:27028, database ngẫu nhiên `shop_hoatuoi_an_test_<16 hex>`, SQL `:memory:`. Đã đọc guard trước khi chạy. Test tự chạy migration và cleanup trên database test; không trỏ cleanup vào review database. Lệnh test lần trước bị chặn vì auto-review hết usage; lượt tiếp tục đã chạy thành công, không dùng log AN-02 thay kết quả mới.

## Integration và readiness

**Blocking thuộc An:** E08 sau sửa chưa retest browser; E09–E14 và responsive matrix còn thiếu. Đây là lý do NOT_READY_FOR_PR hiện tại.

**Cần phối hợp trước merge:** Luân chốt category.code ổn định, categoryRecord/live relation và snapshot policy; review shared config/Category/routes. An và lead chốt tiêu chí search: hiện regex substring case-insensitive, literal escaped, không phải full-text và không tận dụng text index; có cần full-text theo rubric không? Không tự đổi thuật toán. Update/delete dùng driver atomic, không phát model events; owner audit/cache cần biết.

**Dependency không tự động chặn PR riêng:** Auth MongoDB do Luân, cart/order ObjectId/business code và tồn kho atomic do Tiến, wishlist/layout do Huy, ảnh legacy/seed tổng thể/E2E nhóm do Dũng. Product đã chạy với SQL Auth + MongoDB catalog; không tuyên bố MongoDB Auth hoặc cart/wishlist đã tương thích. Seeder tổng thể chưa idempotent ở Auth/Post là vấn đề Luân/Dũng, không sửa ngoài phạm vi và không dùng nó làm lý do duy nhất chặn PR An.

## Git và bước tiếp theo

Giữ nguyên branch, 10 tracked modified như lúc bắt đầu; controller/test sửa lần này nằm trong các file untracked đã có. Thêm report và evidence final-browser. [Git status](evidence/final-browser/git-status.txt); diff tracked không bao gồm source untracked, cần review đầy đủ khi chuẩn bị PR.

Browser cần được bật lại để retest E08 và tiếp tục các case; PHP QA endpoint và MongoDB review được giữ lại. Playwright/@playwright/test chưa có trong node_modules, chưa thấy thư mục browser cache ở LOCALAPPDATA/ms-playwright; chưa cài dependency hoặc chạy Playwright độc lập. Nếu dùng fallback trong phiên sau, cài runner cô lập ngoài source và trỏ cùng môi trường review, chỉ CRUD code AN03. Hoặc chạy thủ công Chrome/Edge DevTools cùng ma trận, lưu screenshot và DB/storage readback. Không coi backend PASS là browser PASS.
