# Kết quả QA dữ liệu, ảnh và giao diện — 27/10/2026

Thành viên: Đồng Viết Dũng (`Dungmeww`). Nhánh riêng: `feature/testing-docs-seeder`, nền `89242cd` của nhánh cart/checkout. Nghiệm thu này không gộp code nhóm và không khẳng định website đã tích hợp đầy đủ.

## Tóm tắt

- Lượt kiểm thử tích hợp chọn lọc mới nhất trên HEAD `d9239c741239ea73310ebebced8fd05e5a9b621d` chạy lúc 12:37 +07 ngày 27/10/2026: **PASS 37/37 tests, 315 assertions, 0 failures/errors/skips**, thời gian 74,17 giây. Bao phủ Auth/Profile và các luồng Cart, Checkout, lịch sử đơn, Admin order, transaction/concurrency. Đây là lượt test mới, không phải full suite hay browser E2E. [JUnit](evidence/member5-integration-2026-10-27.xml) và [summary](evidence/member5-integration-summary-2026-10-27.json). Chạy trên `dungQA` loopback; inventory tăng từ 675 lên 712 do 37 DB test riêng được giữ lại; lượt này không dọn DB.
- Full suite gần nhất đã **PASS 110/110 tests, 2.132 assertions, 0 failures/errors/skips** lúc 11:36 +07 ngày 27/10/2026 trên replica set QA riêng `dungQA` (`127.0.0.1:27028`). Chạy bằng `scripts/test-dung-data.ps1`, dùng extension MongoDB 2.5.5 trong `.runtime/php` qua `PHP_INI_SCAN_DIR` chỉ thuộc process của test; launcher khôi phục biến môi trường sau khi chạy. Lượt gọi PHPUnit mặc định lúc 11:20 +07 vẫn ghi nhận 37 PASS, 700 assertions, 50 ERROR và 23 SKIP do extension chưa được nạp; lỗi này đã được khắc phục cho QA mà không đổi cấu hình PHP toàn hệ thống. JUnit mới được xác minh là 110 tests/2.132 assertions/0 failures-errors-skips, SHA-256 `84e4eaa9cf7e7f56ebf95b15e1c4cff539f334970cf25f1a12ba0de50fdc3c96`. Số database trên replica set tăng từ 603 lên 675 trong lượt QA; test giữ lại DB riêng và không xóa DB nào. [Evidence runtime](evidence/phpunit-runtime-2026-10-27.json).
- Browser QA đã ghi nhận 24 phép đo trên `/`, `/login`, `/register`, `/cart`, `/checkout`, `/orders/success`. Ở trang thành công, hai lỗi đã được xác định: email dài không ngắt dòng làm document rộng 396 px tại viewport 390/375 px; bảng sản phẩm đặt `min-width: 640px` và nội dung bị cắt/đẩy trong vùng mobile. Bằng chứng là snapshot cũ; lượt này không có Playwright/Puppeteer để chụp lại. Đã tạo patch đề xuất riêng cho view do Thành viên 3 quản lý, chưa áp dụng và chưa tuyên bố đã sửa. [Browser evidence](evidence/browser-2026-10-27/) và [patch đề xuất](evidence/proposed-orders-success-mobile.patch).
- Route `/` hiện vẫn là trang chào mặc định Laravel, chưa phải storefront. Checkout bán hàng và quản lý đơn hàng có route/controller/model/view trên nhánh này, nhưng Catalog/Product Detail chưa tích hợp; Admin dashboard/category và Posts/Wishlist/Contact nằm ở ref nhánh thành viên khác, chưa nhập vào checkout.
- Cập nhật ảnh tiếp theo ngày 27/10: giữ nguyên 24 ảnh Commons đã bổ sung, nhập thêm **13 ảnh Pexels có giấy phép** vào đúng filename dataset. Hiện có **43 filename minh họa cho 44 dòng SKU; 31 filename còn thiếu ảnh (36 dòng SKU); 0 ảnh xác minh đúng SKU**. Đã kiểm tra 55 hash bảo vệ, gồm năm file QA cleanup, 48 bản asset/runtime của 24 ảnh trước và dataset/initializer. Không thử lại tải Wikimedia sau HTTP 429; StockSnap/Public Domain Pictures chặn truy cập nên dừng. [Evidence và danh sách 31 filename còn thiếu](evidence/product-images-followup-2026-10-27.json).

PHP CLI là Laragon PHP 8.3.30. Lần gọi trực tiếp dùng `php.ini` mặc định, không nạp MongoDB extension nên phát sinh lỗi `MongoDB\Driver\Manager` trước khi mở kết nối. Extension không thiếu: `.runtime/php/php_mongodb.dll` và `.runtime/php/mongodb.ini` có sẵn trong repository; file INI này nạp MongoDB extension 2.5.5. `scripts/test-dung-data.ps1` tạm đặt `PHP_INI_SCAN_DIR` cho process test, buộc URI QA loopback `127.0.0.1:27028`/replica `dungQA`, dùng database test riêng và khôi phục biến môi trường khi kết thúc. Vì vậy suite đã chạy lại thành công mà không sửa `.env`, `php.ini` hoặc cấu hình PHP toàn hệ thống. Không chạy initializer; không xóa database nào; số DB quan sát được là 603 trước và 675 sau lượt test, gồm các DB test được giữ lại.

## Import MongoDB từ CLI

Artisan chạy trên database mới, riêng: `shop_hoatuoi_test_dung_cli_20261027_130120`.

| Lượt | categories | products | posts |
|---|---:|---:|---:|
| Dry-run | would_insert 5 | would_insert 80 | would_insert 3 |
| Apply lần 1 | inserted 5 | inserted 80 | inserted 3 |
| Apply lần 2 | skipped 5 | skipped 80 | skipped 3 |
| Cả ba lượt | updated 0 | updated 0 | updated 0 |

MongoDB readback ngay sau import: 5/80/3. [Bằng chứng CLI và readback](evidence/import-cli-2026-10-27.json). Browser QA sau đó dùng cùng database riêng, tạo thêm user/order fixture; các fixture này được giữ lại, không xóa.

## Ảnh và provenance

| Trạng thái trong Git | Số filename duy nhất | Ghi chú |
|---|---:|---|
| Ảnh sản phẩm được xác minh đúng SKU | 0 | Không có ảnh nguồn nào đối chiếu được với sản phẩm bán cụ thể |
| Ảnh minh họa sản phẩm có attribution/giấy phép | 43 file | 44 dòng sản phẩm; lượt này bổ sung 13 ảnh Pexels, giữ nguyên 24 ảnh Commons trước đó; JPEG 800×800 có nhãn minh họa |
| Tên file ảnh sản phẩm còn thiếu | 31 tên | 36 dòng SKU; placeholder runtime ghi `file_status=EXISTS` nhưng `product_photo_status=MISSING` |
| Thumbnail bài viết có asset được cấp phép | 3/3 | JPEG minh họa 800×800; provenance theo từng bài, chưa kiểm thử route blog trên nhánh hiện tại |

Thư viện asset có **47 JPEG**: 43 ảnh minh họa sản phẩm (30 Commons + 13 Pexels), 3 thumbnail và một template placeholder chung tại `database/seeders/assets/placeholder.jpg`. Audit runtime ghi 46 đường dẫn illustration (43 product + 3 post) và 31 placeholder sản phẩm. Template không được tính là ảnh hoa. Không có ảnh nào được xác minh là ảnh chụp đúng sản phẩm/SKU. Lượt này chưa commit hoặc push.

Manifest có 80 dòng/74 filename: 44 dòng tham chiếu 43 ảnh minh họa, còn 36 dòng tham chiếu 31 filename thiếu ảnh. Trong 68 filename thiếu ban đầu đã bổ sung 37 ảnh (24 Commons + 13 Pexels); 31 placeholder còn lại tiếp tục dùng template chung. Ba thumbnail có provenance riêng. 13 ảnh lượt này đều JPEG 800×800, từ 49.482 đến 117.055 bytes, đọc được và có hash khớp asset/runtime/public path. Dataset, SKU, giá và dữ liệu nghiệp vụ không đổi.

Nguồn Commons giữ giấy phép CC0/CC BY/CC BY-SA đã ghi theo từng file. 13 ảnh mới dùng [Pexels License](https://www.pexels.com/license/), cho phép dùng trên website thương mại và chỉnh sửa; attribution không bắt buộc nhưng vẫn ghi tác giả, trang ảnh, URL giấy phép, hash ảnh nguồn và các thay đổi đã thực hiện trong [provenance](../../database/seeders/data/image-provenance.json). Không gán Pexels thành giấy phép CC. Không tái dùng cùng source/hash cho nhiều SKU mới. Đã loại ảnh hộp có thương hiệu, ảnh tú cầu dạng giỏ dùng cho SKU dạng hộp; 31 filename còn thiếu có lý do cụ thể trong manifest/evidence.

## Kiểm thử và kiểm tra kỹ thuật

- Dataset: checksum, counts, kiểu dữ liệu, code/slug trùng, category snapshot, ngày bài, tên ảnh traversal và field injection.
- Importer: dry-run, first/second import, giữ nguyên bản ghi cũ, conflict trước khi ghi, unique/text/category indexes, QA user tùy chọn và adapter seeder.
- Lỗi/phục hồi: MongoDB không sẵn sàng trả lỗi đã lược URI; validator chặn giữa lượt có progress, retry giữ dữ liệu đã insert.
- Sales/Auth HTTP: đăng nhập, phân quyền user/Admin, owner order, giá server, validation, checkout token chống trùng, stock, hủy/hoàn kho và reimport giữ fingerprints. Test hiện có cũng kiểm tra CSRF 419; test mới xác nhận tên người nhận chứa script được Blade escape.
- Asset QA mới: preview không ghi, chuẩn hóa kích thước, quyền/provenance, shared filename, cài asset vào runtime, nhận placeholder project và từ chối file chưa đăng ký.
- Các lượt trước cài 3 thumbnail, 6 ảnh có sẵn và 24 ảnh Commons; [evidence cài thumbnail ban đầu](evidence/image-install-2026-10-27.json) là snapshot lịch sử. Lượt tiếp theo đã preview rồi cài **13/13 ảnh Pexels** vào placeholder được registry xác nhận; không ghi đè ảnh sản phẩm có sẵn. Audit mới: 43 product illustration, 31 product placeholder, 3 thumbnail; 77/77 file runtime đọc được, JPEG 800×800. [Manifest](product-images.json), [audit](image-audit.json) và [evidence lượt này](evidence/product-images-followup-2026-10-27.json).
- Công cụ QA ảnh và parser được mở rộng cho `PEXELS-LICENSE` ở vai trò `illustration`, yêu cầu URL trang ảnh Pexels và URL giấy phép chính thức; không dùng giấy phép này để tự xác nhận SKU. Hai file unit test ảnh/dataset: **38/38 PASS, 706 assertions**; PHP lint, Pint và Python syntax PASS. Không chạy full PHPUnit, không mở kết nối MongoDB hoặc tạo database QA. Kiểm tra hash xác nhận 33 đường dẫn ảnh minh họa cũ và 64 entry provenance không thuộc 13 ảnh mới được giữ nguyên; năm file QA cleanup không đổi.
- `public/storage` đang là Windows Junction tới `storage/app/public`; link sẵn có được giữ nguyên, không chạy lệnh ghi đè. Product model chưa có accessor `image_url`; code view hiện dùng filename dưới `/storage/products/`. An cần dùng `Storage::disk('public')->url('products/'.$product->image)` hoặc `asset('storage/products/'.basename($product->image))` trong contract/view Catalog để filename hiển thị đúng.
- Index readback trên DB riêng: code/slug, text, category, publish-time indexes có mặt; tìm `PVN297` chọn `code_1`, examined 1 key/1 document. Đây là kiểm tra kế hoạch truy vấn trên bộ 80 sản phẩm, không phải benchmark SLA. [JSON index/explain](evidence/mongodb-index-readback-2026-10-27.json).
- `php -l`: 11 file PHP PASS; Pint PASS; `composer validate --strict` PASS; `composer check-platform-reqs` PASS; parser/checksum/manifest PASS.

## Kết quả browser và việc cần sửa

Browser snapshot Chrome 156 cho thấy đăng ký, giỏ, checkout và trang thành công đã hoạt động trong QA trước đó. Ở mobile, email dài không ngắt dòng mở document tới 396 px; bảng snapshot có `min-width: 640px` khiến sản phẩm bị cắt trong vùng mobile. Đây là view đơn hàng thuộc phạm vi Thành viên 3. Không sửa trực tiếp; patch responsive riêng ở evidence là đề xuất để Thành viên 3 xem xét. Không có browser automation cài trong checkout hiện tại, nên không có screenshot sau sửa.

Trang chủ trả HTTP 200 nhưng vẫn là trang Laravel scaffold. Chưa có Catalog/Product CRUD, upload, dashboard hợp nhất, wishlist/blog/contact hoặc trình duyệt nghiệm thu toàn website. Vì vậy **desktop smoke không đồng nghĩa website đã đạt nghiệm thu**; mobile đang có defect cụ thể nêu trên.

## Hồ sơ và kết quả lịch sử

[MONGODB_SCHEMA.md](MONGODB_SCHEMA.md) mô tả collections/embedded data và indexes; [SO_DO.md](SO_DO.md) có sơ đồ kiến trúc Laravel–MongoDB, import, activity đặt hàng và sequence checkout; [USE_CASE.puml](USE_CASE.puml) phân biệt chức năng đang có với phần theo kế hoạch. [QA_TEST_PLAN.md](QA_TEST_PLAN.md) liệt kê test dữ liệu/sales/security và điều kiện nghiệm thu cuối.

JUnit `phpunit-full-2026-10-27.xml` và `phpunit-dung-2026-10-27.xml` là evidence PASS được lưu trong repository từ trước. Lượt khôi phục ngày 27/10/2026 có JUnit runtime tại `.runtime/dung-qa/phpunit-recovery-full.xml`; file bị ignore theo cấu hình repo, còn kết quả, đường dẫn và SHA-256 được ghi trong `phpunit-runtime-2026-10-27.json`. Không ghi password, token, Mongo credential hoặc dữ liệu cá nhân thật vào evidence.
