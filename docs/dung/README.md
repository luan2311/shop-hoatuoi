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

Manifest hiện có **80 dòng/74 filename, đủ 74/74 ảnh minh họa, 0 filename thiếu và 0 ảnh xác minh đúng SKU**. Lượt này chỉ điền 31 filename trước đó `MISSING`, dùng 12 ảnh nguồn có giấy phép; giữ nguyên byte ảnh và toàn bộ metadata của 43 filename cũ (44 dòng sản phẩm). Các nhóm dùng lại nguồn cho sản phẩm demo cùng loại có `source_reuse_reason` trong manifest/provenance. Xem [evidence và hash trước/sau](evidence/product-images-main-type-2026-10-27.json), [bản xem nhanh 12 nguồn dùng cho 31 filename](evidence/product-images-main-type-contact-2026-10-27.jpg) và [provenance ledger](../../database/seeders/data/image-provenance.json). [Evidence lượt 13 ảnh](evidence/product-images-followup-2026-10-27.json) giữ nguyên như snapshot lịch sử.

Runtime không còn placeholder ở 74 filename sản phẩm; template chung `database/seeders/assets/placeholder.jpg` vẫn được giữ. Mọi dòng có `file_status=EXISTS`, `product_photo_status=EXISTS`, `fit_status=ILLUSTRATIVE`; không đánh đồng có file/minh họa với xác minh SKU. Tiêu chí mới chỉ đối chiếu loài hoa chính hoặc loại sản phẩm: kệ hoa dùng ảnh kệ hoa, hoa sáp dùng nguồn xác nhận soap flowers. Màu, số bông, giấy gói, kiểu bó/giỏ/hộp và SKU không phải điều kiện. Filename chung PVN276/PVN244 dùng ảnh có cả tulip và hồng theo chấp thuận của người dùng.

Nguồn ảnh gồm Wikimedia Commons và Pexels. Ảnh Commons giữ giấy phép CC ghi theo từng file; các filename dùng nguồn Pexels mang [Pexels License](https://www.pexels.com/license/), cho phép dùng trên website thương mại và chỉnh sửa. Giấy phép này được ghi `PEXELS-LICENSE`, không gán thành CC0/CC BY. Attribution Pexels không bắt buộc nhưng vẫn được lưu đầy đủ. Công cụ ảnh chỉ chấp nhận Pexels ở vai trò minh họa, với URL trang ảnh và URL giấy phép chính thức; ghi chú nêu rõ giới hạn đối chiếu và lý do tái sử dụng nguồn.

## Phần cần tiếp tục phối hợp

| Phạm vi | Trạng thái | Người phối hợp |
|---|---|---|
| Dữ liệu và importer trên database test riêng | Có code và kiểm thử; xem kết quả thực tế | Dũng |
| Ảnh sản phẩm demo | Đủ 74/74 filename minh họa; thêm 31, bảo toàn 43 cũ và metadata; nguồn/giấy phép/ghi chú tái sử dụng đầy đủ | Dũng ghi provenance; An xác nhận contract hiển thị và attribution |
| Category/Role và quan hệ SQL → MongoDB | Chưa tự chuyển; nhánh Luân chưa tương thích toàn bộ | Luân |
| Catalog, Product CRUD/upload, search/filter | Chưa có trên nền kiểm chứng | An |
| Layout cuối, wishlist, liên hệ/Maps, blog | Chưa có trên nền kiểm chứng | Huy |
| Hợp đồng cart/checkout/order | Tái sử dụng, không viết lại | Tiến |
| Database chung, hướng PR và nhánh nền chính thức | Chưa quyết định; không tự nhập code | Nhóm trưởng |

### Checklist hiện tại của Thành viên 5

| Hạng mục | Trạng thái | Kết quả và dependency |
|---|---|---|
| Ảnh minh họa sản phẩm demo | DONE | 74/74 filename có ảnh, 0 thiếu; 31 filename mới là `ILLUSTRATIVE` theo loại hoa/sản phẩm; 43 ảnh cũ và metadata không đổi. 93 hash bảo vệ khớp; 38/38 test, 706 assertions PASS. Không tải lại ảnh Wikimedia sau HTTP 429; dùng các file Commons đã tải hợp lệ. |
| 3 thumbnail bài viết | PARTIAL | Đã tái sử dụng và cài 3 ảnh CC có ghi attribution; module Posts chỉ có trong ref nhánh Huy, chưa hiển thị được trên checkout hiện tại. |
| Luồng tích hợp Catalog → Product → Cart → Checkout → History → Admin | BLOCKED | Catalog/Product route chưa có; ref product-search không có trong clone nên chưa thể kiểm thử xuyên suốt. PHPUnit backend riêng đã chạy PASS trên `dungQA`; điều đó không xác nhận Catalog/Admin UI đã tích hợp. |
| Hai lỗi mobile trang success | PARTIAL | Đã xác định email và bảng sản phẩm; patch đề xuất riêng cho view của Tiến. Chưa áp dụng/chưa chụp lại trên browser. |
| Full PHPUnit | DONE | 110/110 tests, 2.132 assertions, 0 failures/errors/skips lúc 11:36 +07 ngày 27/10/2026; chạy qua `scripts/test-dung-data.ps1` trên `dungQA` với MongoDB extension được nạp theo process. Evidence: [phpunit-runtime-2026-10-27.json](evidence/phpunit-runtime-2026-10-27.json). |

Phần ảnh minh họa hoàn tất theo tiêu chí demo đã chốt. Catalog/UI và nghiệm thu toàn website vẫn còn phụ thuộc tích hợp; 0 ảnh xác minh SKU được báo riêng và không phải điều kiện của lượt ảnh demo này.
