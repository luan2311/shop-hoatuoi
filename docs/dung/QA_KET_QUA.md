# Kết quả QA dữ liệu, ảnh và giao diện — 27/10/2026

Thành viên: Đồng Viết Dũng (`Dungmeww`). Nhánh riêng: `feature/testing-docs-seeder`, nền `89242cd` của nhánh cart/checkout. Nghiệm thu này không gộp code nhóm và không khẳng định website đã tích hợp đầy đủ.

## Tóm tắt

- **PHPUnit: 110/110 test PASS, 2.132 assertions, 0 FAIL, 0 ERROR, 0 SKIP.** Bộ Dũng: **59 test / 1.777 assertions**; hồi quy có sẵn: 51 test / 355 assertions. [JUnit đầy đủ](evidence/phpunit-full-2026-10-27.xml), [JUnit Dũng](evidence/phpunit-dung-2026-10-27.xml), [tổng hợp JSON](evidence/phpunit-summary-2026-10-27.json).
- **Chrome thật ở headless mode:** 24 phép đo cho `/`, `/login`, `/register`, `/cart`, `/checkout`, `/orders/success` tại 1366×768, 1280×720, 390×844 và 375×812. Đăng ký QA, thêm sản phẩm qua POST có CSRF, và checkout thật đều thành công. Desktop không tràn ngang. Mobile có **2 lần FAIL layout** ở trang thành công: tại màn 390 px và 375 px, layout mở rộng đến 396 px; email và ô sản phẩm bị cắt ở cạnh phải. [JSON browser QA và 13 screenshot](evidence/browser-2026-10-27/).
- Route `/` hiện vẫn là trang chào mặc định Laravel, chưa phải storefront. Giỏ hàng hiển thị ảnh placeholder có nhãn; không có ảnh sản phẩm đúng SKU để nghiệm thu hình ảnh thật. Catalog, upload ảnh và Admin UI chưa tích hợp trên checkout này.
- Manifest Git ghi nhận **6 ảnh minh họa có giấy phép, 0 ảnh xác minh đúng SKU, 68 file ảnh sản phẩm còn thiếu** trên 74 filename duy nhất. Ba thumbnail bài viết cũng chưa có asset.

Môi trường: PHP 8.3.30, Composer 2.9.4, MongoDB PHP extension 2.5.5, Laravel 13.37.0, Laravel MongoDB 5.7.0, MongoDB Server 8.3.8. Replica set riêng `dungQA` tại `127.0.0.1:27028`. Dữ liệu test được giữ trong database `shop_hoatuoi_test_*`; không chạy initializer hoặc ghi database dùng chung.

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
| Ảnh được xác minh đúng sản phẩm | 0 | Chưa có ảnh nguồn khớp SKU và mọi mã dùng chung filename |
| Minh họa có attribution/giấy phép | 6 | Được đóng dấu “ILLUSTRATION – PRODUCT MATCH NOT VERIFIED”, JPEG 800×800, tối đa 350 KB |
| Còn thiếu ảnh sản phẩm đúng mẫu | 68 | Không tính placeholder runtime |
| Thumbnail bài viết | 3 | Chưa có asset được cấp phép |

Sáu nguồn Wikimedia Commons được ghi trong ledger và manifest: [Ninix8](https://commons.wikimedia.org/wiki/File:Bouquet_of_Red_Roses.jpg), [Astari28](https://commons.wikimedia.org/wiki/File:Buket_bunga_mawar_pink_1.jpg), [PattayaPatrol](https://commons.wikimedia.org/wiki/File:DFC_0794_Bright_bouquet_of_pink_roses_and_a_cheerful_sunflower_arranged_with_greenery_on_a_golden_surface.jpg), [Salicyna](https://commons.wikimedia.org/wiki/File:Tulipa_2019-02-09_5461.jpg), [WikipediaNeko](https://commons.wikimedia.org/wiki/File:Picture_of_a_Peony.jpg), và [Missvain / Sarah Stierch](https://commons.wikimedia.org/wiki/File:Birthday_flower_bouquet_-_2021-08-26_-_Sarah_Stierch.jpg). Chúng dùng CC BY 4.0 hoặc CC BY-SA 4.0; attribution, URL giấy phép và thay đổi ảnh được lưu trong [image-provenance.json](../../database/seeders/data/image-provenance.json). Placeholder do script tạo chỉ ở runtime, không tính là asset hoàn tất và không được commit.

## Kiểm thử và kiểm tra kỹ thuật

- Dataset: checksum, counts, kiểu dữ liệu, code/slug trùng, category snapshot, ngày bài, tên ảnh traversal và field injection.
- Importer: dry-run, first/second import, giữ nguyên bản ghi cũ, conflict trước khi ghi, unique/text/category indexes, QA user tùy chọn và adapter seeder.
- Lỗi/phục hồi: MongoDB không sẵn sàng trả lỗi đã lược URI; validator chặn giữa lượt có progress, retry giữ dữ liệu đã insert.
- Sales/Auth HTTP: đăng nhập, phân quyền user/Admin, owner order, giá server, validation, checkout token chống trùng, stock, hủy/hoàn kho và reimport giữ fingerprints. Test hiện có cũng kiểm tra CSRF 419; test mới xác nhận tên người nhận chứa script được Blade escape.
- Asset QA mới: preview không ghi, chuẩn hóa kích thước, quyền/provenance, shared filename, cài asset vào runtime, nhận placeholder project và từ chối file chưa đăng ký.
- Local image install readback: 6 asset đã cấp phép được nhận diện/skip; 71 placeholder runtime không có source asset trong Git, không bị tính là ảnh hoàn thành. [JSON kết quả](evidence/image-install-2026-10-27.json).
- Index readback trên DB riêng: code/slug, text, category, publish-time indexes có mặt; tìm `PVN297` chọn `code_1`, examined 1 key/1 document. Đây là kiểm tra kế hoạch truy vấn trên bộ 80 sản phẩm, không phải benchmark SLA. [JSON index/explain](evidence/mongodb-index-readback-2026-10-27.json).
- `php -l`: 11 file PHP PASS; Pint PASS; `composer validate --strict` PASS; `composer check-platform-reqs` PASS; parser/checksum/manifest PASS.

## Kết quả browser và việc cần sửa

Chrome 156 đã tải CSS/JS và thao tác trang bằng browser ở đúng bốn cấu hình viewport. Trang đăng ký, giỏ, form checkout và trang thành công chạy được; checkout tạo một order trong database test riêng. Tổng thể trang desktop có document width khớp màn hình. Ở mobile, trang thành công mở layout tới 396 px dù màn hình là 390 px hoặc 375 px; nội dung email và bảng snapshot đơn bị cắt. Giỏ hàng cũng cần cuộn ngang trong bảng có `min-width: 640px`. Đây là lỗi hiển thị trên view sales hiện hữu; report giữ nguyên phạm vi QA Dũng, không sửa code nghiệp vụ/view của thành viên khác.

Trang chủ trả HTTP 200 nhưng vẫn là trang Laravel scaffold. Chưa có Catalog/Product CRUD, upload, dashboard hợp nhất, wishlist/blog/contact hoặc trình duyệt nghiệm thu toàn website. Vì vậy **desktop smoke không đồng nghĩa website đã đạt nghiệm thu**; mobile đang có defect cụ thể nêu trên.

## Hồ sơ và kết quả lịch sử

[MONGODB_SCHEMA.md](MONGODB_SCHEMA.md) mô tả collections/embedded data và indexes; [SO_DO.md](SO_DO.md) có sơ đồ kiến trúc Laravel–MongoDB, import, activity đặt hàng và sequence checkout; [USE_CASE.puml](USE_CASE.puml) phân biệt chức năng đang có với phần theo kế hoạch. [QA_TEST_PLAN.md](QA_TEST_PLAN.md) liệt kê test dữ liệu/sales/security và điều kiện nghiệm thu cuối.

Bằng chứng không định ngày như `phpunit.xml`, `phpunit-summary.json`, `import-cli.json` và `database-readback.json` là lịch sử lượt 26/10. `phpunit-2026-10-27.xml` là lượt Dũng trung gian trước khi thêm coverage image/XSS; kết quả cuối là `phpunit-full-2026-10-27.xml` và `phpunit-dung-2026-10-27.xml`. Không ghi password, token, Mongo credential hoặc dữ liệu cá nhân thật vào evidence.
