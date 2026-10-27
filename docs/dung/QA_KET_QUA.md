# Kết quả QA dữ liệu, ảnh và giao diện — 27/10/2026

Thành viên: Đồng Viết Dũng (`Dungmeww`). Nhánh riêng: `feature/testing-docs-seeder`, nền `89242cd` của nhánh cart/checkout. Nghiệm thu này không gộp code nhóm và không khẳng định website đã tích hợp đầy đủ.

## Tóm tắt

- Full suite gần nhất đã **PASS 110/110 tests, 2.132 assertions, 0 failures/errors/skips** lúc 11:36 +07 ngày 27/10/2026 trên replica set QA riêng `dungQA` (`127.0.0.1:27028`). Chạy bằng `scripts/test-dung-data.ps1`, dùng extension MongoDB 2.5.5 trong `.runtime/php` qua `PHP_INI_SCAN_DIR` chỉ thuộc process của test; launcher khôi phục biến môi trường sau khi chạy. Lượt gọi PHPUnit mặc định lúc 11:20 +07 vẫn ghi nhận 37 PASS, 700 assertions, 50 ERROR và 23 SKIP do extension chưa được nạp; lỗi này đã được khắc phục cho QA mà không đổi cấu hình PHP toàn hệ thống. JUnit mới được xác minh là 110 tests/2.132 assertions/0 failures-errors-skips, SHA-256 `84e4eaa9cf7e7f56ebf95b15e1c4cff539f334970cf25f1a12ba0de50fdc3c96`. Số database trên replica set tăng từ 603 lên 675 trong lượt QA; test giữ lại DB riêng và không xóa DB nào. [Evidence runtime](evidence/phpunit-runtime-2026-10-27.json).
- Browser QA đã ghi nhận 24 phép đo trên `/`, `/login`, `/register`, `/cart`, `/checkout`, `/orders/success`. Ở trang thành công, hai lỗi đã được xác định: email dài không ngắt dòng làm document rộng 396 px tại viewport 390/375 px; bảng sản phẩm đặt `min-width: 640px` và nội dung bị cắt/đẩy trong vùng mobile. Bằng chứng là snapshot cũ; lượt này không có Playwright/Puppeteer để chụp lại. Đã tạo patch đề xuất riêng cho view do Thành viên 3 quản lý, chưa áp dụng và chưa tuyên bố đã sửa. [Browser evidence](evidence/browser-2026-10-27/) và [patch đề xuất](evidence/proposed-orders-success-mobile.patch).
- Route `/` hiện vẫn là trang chào mặc định Laravel, chưa phải storefront. Checkout bán hàng và quản lý đơn hàng có route/controller/model/view trên nhánh này, nhưng Catalog/Product Detail chưa tích hợp; Admin dashboard/category và Posts/Wishlist/Contact nằm ở ref nhánh thành viên khác, chưa nhập vào checkout.
- Tại lần kiểm kê này: **0 ảnh được xác minh đúng SKU**; 6 file ảnh Wikimedia có giấy phép đang được dùng làm minh họa cho 7 dòng sản phẩm; còn **68 tên file sản phẩm duy nhất** thiếu asset (73 dòng SKU trỏ tới các tên file thiếu). Đã chuẩn bị và cài **3/3 thumbnail minh họa có giấy phép** cho bài viết; việc hiển thị qua route blog chưa xác minh vì route Posts không có trên nhánh hiện tại.

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
| Ảnh minh họa sản phẩm có attribution/giấy phép | 6 file | 7 dòng sản phẩm dùng 6 file; có nhãn “ILLUSTRATION – PRODUCT MATCH NOT VERIFIED”, JPEG 800×800 |
| Tên file ảnh sản phẩm còn thiếu | 68 tên | 73 dòng sản phẩm trỏ tới các tên file này; placeholder runtime không tính là ảnh |
| Thumbnail bài viết có asset được cấp phép | 3/3 | JPEG minh họa 800×800; provenance theo từng bài, chưa kiểm thử route blog trên nhánh hiện tại |

Trong Git có **10 file JPEG dùng cho thư viện ảnh**: 6 ảnh Commons minh họa sản phẩm, 3 bản sao dùng làm thumbnail bài viết và một template placeholder chung tại `database/seeders/assets/placeholder.jpg`. Chín file illustration tương ứng với sáu ảnh nguồn phân biệt; mọi ảnh sản phẩm vẫn chưa xác minh đúng SKU. Template 800×800 được lưu trong Git để tạo/cài placeholder runtime; nó không phải ảnh hoa, ảnh sản phẩm hay ảnh bài viết và không được tính vào illustration.

Manifest sản phẩm có 80 dòng và 74 tên file duy nhất: 7 dòng tham chiếu 6 ảnh minh họa, còn 73 dòng tham chiếu 68 tên ảnh chưa có asset sản phẩm đúng mẫu. Ba thumbnail bài viết hiện có asset CC được tái sử dụng từ ảnh Commons; cả ba được ghi role `illustration`, không được tính là ảnh SKU. Kết quả cài asset mới: cài 3 thumbnail, giữ nguyên 6 asset sản phẩm hiện có, còn 68 ảnh sản phẩm thiếu asset; không có file bị từ chối. Runtime có 68 placeholder sản phẩm từ template chung và 9 illustration hiện diện (6 product paths + 3 post paths). `database/seeders/assets/placeholder.jpg` vẫn là template riêng được Git lưu; không bị tính vào 68 ảnh thiếu hoặc 9 illustration.

Sáu nguồn Wikimedia Commons được ghi trong ledger và manifest: [Ninix8](https://commons.wikimedia.org/wiki/File:Bouquet_of_Red_Roses.jpg), [Astari28](https://commons.wikimedia.org/wiki/File:Buket_bunga_mawar_pink_1.jpg), [PattayaPatrol](https://commons.wikimedia.org/wiki/File:DFC_0794_Bright_bouquet_of_pink_roses_and_a_cheerful_sunflower_arranged_with_greenery_on_a_golden_surface.jpg), [Salicyna](https://commons.wikimedia.org/wiki/File:Tulipa_2019-02-09_5461.jpg), [WikipediaNeko](https://commons.wikimedia.org/wiki/File:Picture_of_a_Peony.jpg), và [Missvain / Sarah Stierch](https://commons.wikimedia.org/wiki/File:Birthday_flower_bouquet_-_2021-08-26_-_Sarah_Stierch.jpg). Chúng dùng CC BY 4.0 hoặc CC BY-SA 4.0; attribution, URL giấy phép và thay đổi ảnh được lưu trong [image-provenance.json](../../database/seeders/data/image-provenance.json).

## Kiểm thử và kiểm tra kỹ thuật

- Dataset: checksum, counts, kiểu dữ liệu, code/slug trùng, category snapshot, ngày bài, tên ảnh traversal và field injection.
- Importer: dry-run, first/second import, giữ nguyên bản ghi cũ, conflict trước khi ghi, unique/text/category indexes, QA user tùy chọn và adapter seeder.
- Lỗi/phục hồi: MongoDB không sẵn sàng trả lỗi đã lược URI; validator chặn giữa lượt có progress, retry giữ dữ liệu đã insert.
- Sales/Auth HTTP: đăng nhập, phân quyền user/Admin, owner order, giá server, validation, checkout token chống trùng, stock, hủy/hoàn kho và reimport giữ fingerprints. Test hiện có cũng kiểm tra CSRF 419; test mới xác nhận tên người nhận chứa script được Blade escape.
- Asset QA mới: preview không ghi, chuẩn hóa kích thước, quyền/provenance, shared filename, cài asset vào runtime, nhận placeholder project và từ chối file chưa đăng ký.
- Local image install readback: `installed=3`, `skipped=6`, `missing=68`, `rejected=0`; 3 thumbnail có JPEG signature, 800×800, dưới 128 KB và runtime state `present`. 6 product illustration giữ role minh họa; 68 placeholder product runtime lấy từ template chung. Không có MongoDB seeder/importer nào chạy trong bước cài ảnh. [JSON kết quả](evidence/image-install-2026-10-27.json) và [audit từng filename](image-audit.json).
- Index readback trên DB riêng: code/slug, text, category, publish-time indexes có mặt; tìm `PVN297` chọn `code_1`, examined 1 key/1 document. Đây là kiểm tra kế hoạch truy vấn trên bộ 80 sản phẩm, không phải benchmark SLA. [JSON index/explain](evidence/mongodb-index-readback-2026-10-27.json).
- `php -l`: 11 file PHP PASS; Pint PASS; `composer validate --strict` PASS; `composer check-platform-reqs` PASS; parser/checksum/manifest PASS.

## Kết quả browser và việc cần sửa

Browser snapshot Chrome 156 cho thấy đăng ký, giỏ, checkout và trang thành công đã hoạt động trong QA trước đó. Ở mobile, email dài không ngắt dòng mở document tới 396 px; bảng snapshot có `min-width: 640px` khiến sản phẩm bị cắt trong vùng mobile. Đây là view đơn hàng thuộc phạm vi Thành viên 3. Không sửa trực tiếp; patch responsive riêng ở evidence là đề xuất để Thành viên 3 xem xét. Không có browser automation cài trong checkout hiện tại, nên không có screenshot sau sửa.

Trang chủ trả HTTP 200 nhưng vẫn là trang Laravel scaffold. Chưa có Catalog/Product CRUD, upload, dashboard hợp nhất, wishlist/blog/contact hoặc trình duyệt nghiệm thu toàn website. Vì vậy **desktop smoke không đồng nghĩa website đã đạt nghiệm thu**; mobile đang có defect cụ thể nêu trên.

## Hồ sơ và kết quả lịch sử

[MONGODB_SCHEMA.md](MONGODB_SCHEMA.md) mô tả collections/embedded data và indexes; [SO_DO.md](SO_DO.md) có sơ đồ kiến trúc Laravel–MongoDB, import, activity đặt hàng và sequence checkout; [USE_CASE.puml](USE_CASE.puml) phân biệt chức năng đang có với phần theo kế hoạch. [QA_TEST_PLAN.md](QA_TEST_PLAN.md) liệt kê test dữ liệu/sales/security và điều kiện nghiệm thu cuối.

JUnit `phpunit-full-2026-10-27.xml` và `phpunit-dung-2026-10-27.xml` là evidence PASS được lưu trong repository từ trước. Lượt khôi phục ngày 27/10/2026 có JUnit runtime tại `.runtime/dung-qa/phpunit-recovery-full.xml`; file bị ignore theo cấu hình repo, còn kết quả, đường dẫn và SHA-256 được ghi trong `phpunit-runtime-2026-10-27.json`. Không ghi password, token, Mongo credential hoặc dữ liệu cá nhân thật vào evidence.
