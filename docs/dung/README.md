# Dữ liệu MongoDB và QA giai đoạn 1 — Đồng Viết Dũng

Thành viên 5, tài khoản GitHub `Dungmeww`. Phạm vi: dữ liệu mẫu, manifest ảnh, kiểm tra tính toàn vẹn, QA tích hợp và tài liệu đồ án. Nguồn phân công: [Task 5.1–5.3](../plans/2026-10-19-phan-chia-cong-viec-5-nguoi.md#phần-5-member-5--qa-testing-seeder-data--báo-cáo-erduml) và [bàn giao nhóm](../2026-10-26-tien-laravel-mongodb-handover.md).

## Nền kiểm chứng và giới hạn tích hợp

Phần Dũng được tách trên `feature/testing-docs-seeder`, dựa trên commit `89242cd` của nhánh cart/checkout. Đây là nhánh làm việc riêng, không merge hoặc rebase nhánh thành viên khác. Nền tích hợp chính thức `dev`/`develop` vẫn do nhóm quyết định.

Remote đã đọc lại có `main` (`d69cbfc`), `dev` (`3ffe4cb`), `feature/auth-category-admin` (`c370d6f`) và `feature/cart-checkout-orders` (`89242cd`). PR #1 vẫn mở, từ nhánh Tiến vào `main`. Chưa có Catalog của An hoặc UI của Huy trên remote. Chưa thấy nhánh QA trước lần triển khai này.

Source thực tế: PHP `^8.3`, Laravel lock `13.37.0`, `mongodb/laravel-mongodb 5.7.0`, MongoDB library `2.5.0`. Giao diện Breeze/Tailwind/Alpine trên checkout; nhánh Luân có Bootstrap Admin riêng. Không đổi dependency, models, controllers bán hàng, routes hoặc layout của thành viên khác.

## Hồ sơ bàn giao

| Tài liệu | Nội dung |
|---|---|
| [MONGODB_SCHEMA.md](MONGODB_SCHEMA.md) | Collection, trường, ObjectId, embedded documents, indexes và các khác biệt giữa nhánh |
| [SEED_VA_KIEM_THU.md](SEED_VA_KIEM_THU.md) | Chạy trên Windows/Laragon, xem trước, seed an toàn, kiểm thử và xử lý lỗi |
| [QA_TEST_PLAN.md](QA_TEST_PLAN.md) | Test đã triển khai, điều kiện nghiệm thu toàn website, phần chờ tích hợp |
| [SO_DO.md](SO_DO.md), [USE_CASE.puml](USE_CASE.puml) | Kiến trúc Laravel–MongoDB, collection, import, activity/sequence checkout và Use Case; phân biệt code đang có với kế hoạch |
| [QA_KET_QUA.md](QA_KET_QUA.md) | Kết quả chạy thực tế và giới hạn; đọc cùng evidence |
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

Manifest đếm file có thể phân phối trong Git: **6 ảnh minh họa có nguồn và giấy phép, 0 ảnh được xác minh đúng SKU, 68 ảnh sản phẩm còn thiếu** trên 74 tên file duy nhất. Sáu ảnh minh họa có dải nhãn “ILLUSTRATION – PRODUCT MATCH NOT VERIFIED”; chúng không được báo là ảnh sản phẩm hoàn chỉnh. Ba thumbnail bài viết cũng chưa có asset được cấp phép. Danh sách từng filename, mã sản phẩm và attribution nằm trong [product-images.json](product-images.json) và [`image-provenance.json`](../../database/seeders/data/image-provenance.json).

Nguồn ảnh là Wikimedia Commons: [Ninix8 – hoa hồng đỏ](https://commons.wikimedia.org/wiki/File:Bouquet_of_Red_Roses.jpg), [Astari28 – bó hồng hồng](https://commons.wikimedia.org/wiki/File:Buket_bunga_mawar_pink_1.jpg), [PattayaPatrol – hồng và hướng dương](https://commons.wikimedia.org/wiki/File:DFC_0794_Bright_bouquet_of_pink_roses_and_a_cheerful_sunflower_arranged_with_greenery_on_a_golden_surface.jpg), [Salicyna – tulip](https://commons.wikimedia.org/wiki/File:Tulipa_2019-02-09_5461.jpg), [WikipediaNeko – mẫu đơn](https://commons.wikimedia.org/wiki/File:Picture_of_a_Peony.jpg), và [Missvain/Sarah Stierch – gerbera](https://commons.wikimedia.org/wiki/File:Birthday_flower_bouquet_-_2021-08-26_-_Sarah_Stierch.jpg). Các asset dùng CC BY 4.0 hoặc CC BY-SA 4.0; attribution và ghi chú chỉnh kích thước/đóng dấu được lưu cùng manifest.

## Phần cần tiếp tục phối hợp

| Phạm vi | Trạng thái | Người phối hợp |
|---|---|---|
| Dữ liệu và importer trên database test riêng | Có code và kiểm thử; xem kết quả thực tế | Dũng |
| Ảnh đúng mẫu sản phẩm | 6 file minh họa có nguồn mở, giấy phép và nhãn trên ảnh; chưa có ảnh nào được xác minh là đúng SKU; 68 tên file còn thiếu ảnh sản phẩm | Dũng chuẩn bị nguồn hợp lệ, An xác nhận contract ảnh |
| Category/Role và quan hệ SQL → MongoDB | Chưa tự chuyển; nhánh Luân chưa tương thích toàn bộ | Luân |
| Catalog, Product CRUD/upload, search/filter | Chưa có trên nền kiểm chứng | An |
| Layout cuối, wishlist, liên hệ/Maps, blog | Chưa có trên nền kiểm chứng | Huy |
| Hợp đồng cart/checkout/order | Tái sử dụng, không viết lại | Tiến |
| Database chung, hướng PR và nhánh nền chính thức | Chưa quyết định; không tự nhập code | Nhóm trưởng |

Không đánh dấu hoàn thành checklist toàn nhóm: ảnh thật, Catalog/UI và nghiệm thu website hoàn chỉnh còn thiếu dependency.
