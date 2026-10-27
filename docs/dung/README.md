# Dữ liệu MongoDB và QA giai đoạn 1 — Đồng Viết Dũng

Thành viên 5, tài khoản GitHub `Dungmeww`. Phạm vi: dữ liệu mẫu, manifest ảnh, kiểm tra tính toàn vẹn, QA tích hợp và tài liệu đồ án. Nguồn phân công: [Task 5.1–5.3](../plans/2026-10-19-phan-chia-cong-viec-5-nguoi.md#phần-5-member-5--qa-testing-seeder-data--báo-cáo-erduml) và [bàn giao nhóm](../2026-10-26-tien-laravel-mongodb-handover.md).

## Nền kiểm chứng và giới hạn tích hợp

Phần Dũng được tách trên `feature/testing-docs-seeder`, dựa trên commit `89242cd` của nhánh cart/checkout. Đây là nhánh làm việc riêng, không merge hoặc rebase nhánh thành viên khác. Nền tích hợp chính thức `dev`/`develop` vẫn do nhóm quyết định.

Snapshot remote-tracking trong clone gồm `main` (`d69cbfc`), `dev` (`3ffe4cb`), `feature/auth-category-admin` (`c370d6f`), `feature/cart-checkout-orders` (`89242cd`), `feature/user-ui-wishlist-contact` (`8d8d700`) và nhánh Dũng tại `f71b010`. Local HEAD hiện là `2a7b76afef4d863837f56c2d09437016f73fed6a`, ahead 1 commit; GitHub chưa truy cập được nên ref remote vẫn là cache và không xác nhận trạng thái live. Ref `feature/product-search` không có trong clone. Catalog/Product CRUD, upload ảnh và UI của thành viên khác chưa tích hợp vào checkout Dũng.

Source thực tế: PHP `^8.3`, Laravel lock `13.37.0`, `mongodb/laravel-mongodb 5.7.0`, MongoDB library `2.5.0`. Giao diện Breeze/Tailwind/Alpine trên checkout; nhánh Luân có Bootstrap Admin riêng. Không đổi dependency, models, controllers bán hàng, routes hoặc layout của thành viên khác.

## Hồ sơ bàn giao

| Tài liệu | Nội dung |
|---|---|
| [MONGODB_SCHEMA.md](MONGODB_SCHEMA.md) | Collection, trường, ObjectId, embedded documents, indexes và các khác biệt giữa nhánh |
| [SEED_VA_KIEM_THU.md](SEED_VA_KIEM_THU.md) | Chạy trên Windows/Laragon, xem trước, seed an toàn, kiểm thử và xử lý lỗi |
| [QA_TEST_PLAN.md](QA_TEST_PLAN.md) | Test đã triển khai, điều kiện nghiệm thu toàn website, phần chờ tích hợp |
| [SO_DO.md](SO_DO.md), [USE_CASE.puml](USE_CASE.puml) | Kiến trúc Laravel–MongoDB, collection, import, activity/sequence checkout và Use Case; phân biệt code đang có với kế hoạch |
| [QA_KET_QUA.md](QA_KET_QUA.md) | Kết quả chạy thực tế và giới hạn; đọc cùng evidence |
| [Báo cáo đồ án](../BAO_CAO_DO_AN_10_DIEM.md) | Tóm tắt đề tài, trạng thái chức năng, thiết kế, kiểm thử, hạn chế và hướng phát triển dựa trên code hiện có |
| [Nội dung slide](NOI_DUNG_SLIDE.md) | Dàn ý 12 slide có thông điệp và ghi chú trình bày; chưa phải file PowerPoint |
| [source-audit.json](source-audit.json) | Toàn bộ collection, số bản ghi trong script, 23 định nghĩa index và checksum |
| [product-images.json](product-images.json) | 80 sản phẩm, 74 tên file ảnh; phân biệt ảnh sản phẩm đã xác minh, ảnh minh họa có attribution và file còn thiếu |
| [evidence/](evidence/) | Bằng chứng kiểm thử và bảo toàn dữ liệu đã lược bỏ thông tin nhạy cảm |

## Đầu ra triển khai

1. [Dataset chuẩn hóa](../../database/seeders/data/flowers.json) được trích từ initializer bằng [parser literal](../../scripts/prepare-dung-data.py), không thực thi JavaScript. File gốc giữ nguyên.
2. [FlowerDataset](../../app/Support/Seeding/FlowerDataset.php) kiểm tra checksum, cấu trúc, định danh, giá, kho, trạng thái, ảnh, danh mục và ngày đăng.
3. [SafeFlowerImporter](../../app/Support/Seeding/SafeFlowerImporter.php) chỉ thêm bản ghi thiếu bằng `$setOnInsert`. Bản ghi tồn tại, kể cả dữ liệu mẫu cũ, không được cập nhật.
4. [Lệnh Artisan](../../app/Console/Commands/ImportFlowerSamples.php) mặc định dry-run; [FlowerSampleSeeder](../../database/seeders/FlowerSampleSeeder.php) là adapter để gọi rõ class, không thay `DatabaseSeeder` SQL cũ.
5. [SeederIntegrityTest](../../tests/Feature/SeederIntegrityTest.php), [FlowerDatasetTest](../../tests/Unit/FlowerDatasetTest.php), [SeededSalesIntegrationTest](../../tests/Feature/SeededSalesIntegrationTest.php) kiểm tra dữ liệu và khả năng sử dụng bộ mẫu với code Tiến.

### Tình trạng ảnh

Manifest sản phẩm có 80 dòng/74 tên file duy nhất: **0 ảnh xác minh đúng SKU; 43 file minh họa có giấy phép gắn với 44 dòng sản phẩm; 31 filename chưa có ảnh sản phẩm** (36 dòng SKU). Trong 68 filename thiếu ban đầu, đã bổ sung 37 ảnh: 24 ảnh Commons ở lượt trước được giữ nguyên và 13 ảnh Pexels ở lượt tiếp theo. Tất cả chỉ là `ILLUSTRATIVE`. Ba thumbnail bài viết có provenance riêng. Manifest ghi riêng `file_status` (file vật lý, kể cả placeholder), `product_photo_status`, `fit_status`, URL nguồn, tác giả, giấy phép và ghi chú cho từng SKU. Xem [evidence lượt bổ sung 13 ảnh](evidence/product-images-followup-2026-10-27.json) và [provenance ledger](../../database/seeders/data/image-provenance.json).

Git cũng lưu template placeholder chung tại `database/seeders/assets/placeholder.jpg`. Runtime hiện còn 31 placeholder cho 31 filename chưa có ảnh sản phẩm; ba ảnh bài viết không còn placeholder. Trường `file_status=EXISTS` chỉ xác nhận file vật lý có mặt, còn `product_photo_status=MISSING` phân biệt placeholder với ảnh hoa. Các filename dùng chung nhưng khác loại sản phẩm/màu sắc tiếp tục thiếu nếu chưa có ảnh phù hợp mọi SKU.

Nguồn ảnh gồm Wikimedia Commons và Pexels. Ảnh Commons giữ giấy phép CC đã ghi trước đó; 13 ảnh Pexels dùng [Pexels License](https://www.pexels.com/license/), cho phép dùng trên website thương mại và chỉnh sửa. Giấy phép này được ghi `PEXELS-LICENSE`, không gán thành CC0/CC BY. Attribution Pexels không bắt buộc nhưng vẫn được lưu đầy đủ. Công cụ ảnh chỉ chấp nhận Pexels ở vai trò minh họa, với URL trang ảnh và URL giấy phép chính thức; ghi chú nêu rõ những thành phần, kiểu dáng hoặc bối cảnh chưa đối chiếu được.

## Phần cần tiếp tục phối hợp

| Phạm vi | Trạng thái | Người phối hợp |
|---|---|---|
| Dữ liệu và importer trên database test riêng | Có code và kiểm thử; xem kết quả thực tế | Dũng |
| Ảnh sản phẩm | 43 file minh họa có nguồn/giấy phép, 0 ảnh xác minh đúng SKU; lượt này thêm 13 filename, còn 31 filename thiếu ảnh | Dũng ghi provenance; An xác nhận contract hiển thị ảnh |
| Category/Role và quan hệ SQL → MongoDB | Chưa tự chuyển; nhánh Luân chưa tương thích toàn bộ | Luân |
| Catalog, Product CRUD/upload, search/filter | Chưa có trên nền kiểm chứng | An |
| Layout cuối, wishlist, liên hệ/Maps, blog | Chưa có trên nền kiểm chứng | Huy |
| Hợp đồng cart/checkout/order | Tái sử dụng, không viết lại | Tiến |
| Database chung, hướng PR và nhánh nền chính thức | Chưa quyết định; không tự nhập code | Nhóm trưởng |

### Checklist hiện tại của Thành viên 5

| Hạng mục | Trạng thái | Kết quả và dependency |
|---|---|---|
| Ảnh sản phẩm | PARTIAL | Giữ nguyên 24 ảnh Commons đã thêm, bổ sung 13 ảnh Pexels có provenance; 0 ảnh xác minh đúng SKU, còn 31 filename thiếu. Không thử lại tải Wikimedia sau HTTP 429; StockSnap/Public Domain Pictures chặn truy cập nên đã dừng. Các ảnh không phù hợp kiểu dáng, chất liệu hoặc SKU dùng chung giữ `MISSING`. |
| 3 thumbnail bài viết | PARTIAL | Đã tái sử dụng và cài 3 ảnh CC có ghi attribution; module Posts chỉ có trong ref nhánh Huy, chưa hiển thị được trên checkout hiện tại. |
| Luồng tích hợp Catalog → Product → Cart → Checkout → History → Admin | BLOCKED | Catalog/Product route chưa có; ref product-search không có trong clone nên chưa thể kiểm thử xuyên suốt. PHPUnit backend riêng đã chạy PASS trên `dungQA`; điều đó không xác nhận Catalog/Admin UI đã tích hợp. |
| Hai lỗi mobile trang success | PARTIAL | Đã xác định email và bảng sản phẩm; patch đề xuất riêng cho view của Tiến. Chưa áp dụng/chưa chụp lại trên browser. |
| Full PHPUnit | DONE | 110/110 tests, 2.132 assertions, 0 failures/errors/skips lúc 11:36 +07 ngày 27/10/2026; chạy qua `scripts/test-dung-data.ps1` trên `dungQA` với MongoDB extension được nạp theo process. Evidence: [phpunit-runtime-2026-10-27.json](evidence/phpunit-runtime-2026-10-27.json). |

Không đánh dấu hoàn thành checklist toàn nhóm: ảnh đúng SKU, Catalog/UI và nghiệm thu website hoàn chỉnh còn dependency.
