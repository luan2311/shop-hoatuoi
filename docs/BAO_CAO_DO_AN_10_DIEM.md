# Báo cáo đồ án: Website Shop Hoa Tươi

| Thông tin | Giá trị |
|---|---|
| Ngày cập nhật | 27/10/2026 |
| Phạm vi khảo sát | Nhánh feature/testing-docs-seeder, commit f71b010 |
| Thành viên 5 | Đồng Viết Dũng |
| Trạng thái | Báo cáo phản ánh code và bằng chứng hiện có; không khẳng định website đã tích hợp đủ rubric hoặc sẵn sàng production. |

## Tóm tắt

Shop Hoa Tươi là ứng dụng Laravel đang chuyển một số module bán hàng sang MongoDB. Nhánh được khảo sát có đăng ký/đăng nhập, hồ sơ, giỏ hàng, checkout, lịch sử đơn hàng và quản lý đơn hàng Admin. Bộ dữ liệu MongoDB mẫu và importer an toàn được xây dựng để hỗ trợ các module này.

Kết quả PHPUnit mới nhất được xác minh là **110 tests, 2.132 assertions, 0 failure, 0 error, 0 skipped**. Kết quả này xác nhận test suite hiện có, không xác nhận Catalog, Product CRUD/upload, wishlist, contact, blog hoặc toàn bộ giao diện đã tích hợp. Trang chủ vẫn trả view Laravel welcome. Ảnh thật đúng SKU và nghiệm thu browser toàn website còn thiếu.

## 1. Đề tài và mục tiêu

Đề tài xây dựng website giới thiệu và bán hoa tươi, có khách hàng và quản trị viên. Mục tiêu kỹ thuật ban đầu gồm quản lý sản phẩm/danh mục, tài khoản và phân quyền, giỏ hàng/đơn hàng, giao diện responsive, upload ảnh, wishlist, liên hệ/Maps và tin tức.

Nhánh hiện tại là nền làm việc để kiểm tra tích hợp Auth/Sales với MongoDB và cung cấp QA dữ liệu. Đây chưa phải bản hợp nhất cuối cùng của cả năm thành viên. Phân công gốc nằm trong [kế hoạch 5 thành viên](plans/2026-10-19-phan-chia-cong-viec-5-nguoi.md); báo cáo này dùng trạng thái code hiện tại thay cho các ví dụ SQL ban đầu của kế hoạch.

## 2. Công nghệ

| Thành phần | Nền được kiểm tra |
|---|---|
| Backend | PHP 8.3.30, Laravel 13.37.0 |
| Document database | MongoDB Server 8.3.8, mongodb/laravel-mongodb 5.7.0 |
| Authentication | Laravel Auth trên MongoDB User; role lưu trong mảng users.roles |
| Giao diện | Blade, Breeze, Tailwind CSS, Alpine.js, Vite |
| Kiểm thử | PHPUnit 12, MongoDB PHP extension 2.5.5, replica set riêng cho test |
| Môi trường xác minh | Windows/Laragon, Composer 2.9.4, PHP CLI và MongoDB loopback |

Kế hoạch ban đầu có đoạn mô tả Laravel 12/MySQL và quan hệ Eloquent kiểu pivot. composer.json, lock file và code nhánh hiện tại sử dụng Laravel 13/MongoDB. Các ví dụ SQL cũ không đại diện cho cấu hình chạy này.

## 3. Chức năng và trạng thái triển khai

| Phạm vi | Trạng thái quan sát được |
|---|---|
| Đăng ký, đăng nhập, xác minh email, đổi mật khẩu và hồ sơ | Có route, controller và tests trên nhánh |
| Giỏ hàng, checkout, lịch sử đơn hàng | Có route/controller/service MongoDB và Feature tests |
| Quản lý đơn hàng Admin | Có route, role middleware, chuyển trạng thái và kiểm tra hoàn kho |
| Trang chủ storefront và Catalog | Chưa hoàn thành; / trả resources/views/welcome.blade.php; không có Product/Catalog controller trên nhánh |
| Category CRUD và dashboard Admin | Không có trong nhánh đang khảo sát; tồn tại ở ref thành viên khác nhưng chưa hợp nhất vào nhánh này |
| Product CRUD, tìm kiếm và upload ảnh | Chưa tích hợp |
| Wishlist, contact/Maps, blog | Chưa có luồng tích hợp được nghiệm thu; các model liên quan vẫn theo SQL cũ |
| Dữ liệu mẫu và QA MongoDB | Có dataset 5/80/3, importer giới hạn database, tests và tài liệu |

Trạng thái chi tiết theo module và các luồng chờ được ghi trong [QA Test Plan](dung/QA_TEST_PLAN.md) và [Use Case](dung/USE_CASE.puml).

## 4. Kiến trúc Laravel – MongoDB

Các route Auth/Sales nhận HTTP request, middleware kiểm tra đăng nhập/quyền, Form Request kiểm tra dữ liệu, controller gọi SalesService, rồi MongoDB models đọc/ghi document. Blade render các trang Auth, giỏ, checkout và đơn hàng. Checkout và cập nhật trạng thái dùng transaction; do đó test tích hợp yêu cầu replica set.

Các file triển khai chính:

- Route: [web.php](../routes/web.php), [sales.php](../routes/sales.php), [auth.php](../routes/auth.php)
- Logic bán hàng: [SalesService.php](../app/Services/SalesService.php)
- MongoDB models: [User.php](../app/Models/User.php), [Product.php](../app/Models/Product.php), [Cart.php](../app/Models/Cart.php), [Order.php](../app/Models/Order.php)
- QA seeder: [SafeFlowerImporter.php](../app/Support/Seeding/SafeFlowerImporter.php)

Sơ đồ kiến trúc, parser/import flow, Activity đặt hàng và Sequence checkout đã được giữ trong [SO_DO.md](dung/SO_DO.md); báo cáo không vẽ lại các sơ đồ đó.

## 5. Thiết kế dữ liệu MongoDB

Initializer nguồn mô tả 9 collection và 95 document mẫu. Dataset importer giai đoạn này chỉ nạp categories, products, posts: lần đầu dự kiến 5/80/3. Tài liệu đầy đủ về trường, kiểu dữ liệu, index và khác biệt SQL/MongoDB nằm trong [MONGODB_SCHEMA.md](dung/MONGODB_SCHEMA.md).

| Collection | Cách dùng trong hợp đồng hiện tại |
|---|---|
| users | Tài khoản Auth; roles là mảng chuỗi, không dùng pivot SQL |
| categories | Có trong nguồn dữ liệu; model Category hiện vẫn là SQL |
| products | MongoDB document, code/slug unique, giá và kho là số nguyên |
| carts | Một cart theo user_id; items được nhúng, lưu product_code và quantity |
| orders | Customer và snapshot items được nhúng; checkout key hỗ trợ retry idempotent |
| roles, wishlists, contacts, posts | Có mặt trong hợp đồng dữ liệu nguồn; không đồng nghĩa module tương ứng đã chuyển hoàn chỉnh sang MongoDB |

Product có snapshot category {code,name,slug}; Order giữ snapshot tên, giá, ảnh và số lượng tại thời điểm mua. MongoDB không áp dụng foreign key giữa collections, nên service và importer phải kiểm tra các liên kết. Các quyết định embed/reference và indexes được mô tả trong [MONGODB_SCHEMA.md](dung/MONGODB_SCHEMA.md) và [ERD trong SO_DO.md](dung/SO_DO.md).

## 6. Dataset, seeder và an toàn dữ liệu

prepare-dung-data.py đọc literal từ database/mongodb_init.js bằng parser hạn chế; không thực thi JavaScript. Dataset lưu checksum nguồn và loại bỏ runtime IDs. FlowerDataset kiểm tra cấu trúc, code/slug, giá, stock, trạng thái, snapshot danh mục, ngày bài viết và tên ảnh.

flowers:import-samples mặc định dry-run. Apply cần target xác nhận, môi trường local/testing, MongoDB loopback và database có tiền tố development/test được cho phép. Importer kiểm tra xung đột trước khi ghi, dùng ObjectId xác định và $setOnInsert; document tồn tại được giữ nguyên, không cập nhật hoặc xóa. Index catalog được thêm theo hướng additive. Cả đợt import không nằm trong một transaction, nên lỗi giữa lượt có thể để lại một phần insert hợp lệ; báo cáo progress và chạy lại sau khi xử lý lỗi.

DatabaseSeeder.php là seeder SQL/pivot cũ, không phải seeder MongoDB an toàn của nhánh. Không chạy initializer nguồn: file chọn database cố định shop_hoatuoi và có thao tác drop() collection. Quy trình an toàn được mô tả trong [SEED_VA_KIEM_THU.md](dung/SEED_VA_KIEM_THU.md).

## 7. Ảnh sản phẩm

Manifest có 80 sản phẩm và 74 tên file ảnh duy nhất:

- **6** file ảnh hoa có license và attribution, đã đóng dấu là minh họa.
- **0** ảnh được xác minh là đúng SKU.
- **68** tên ảnh sản phẩm thiếu asset đúng mẫu.
- **3** thumbnail bài viết chưa có asset có giấy phép.
- Git có thêm một template placeholder chung tại database/seeders/assets/placeholder.jpg. Template không phải ảnh sản phẩm; runtime có 68 placeholder sản phẩm và 3 placeholder bài viết được tạo từ template.

Các số liệu tách biệt trong [product-images.json](dung/product-images.json), [image-audit.json](dung/image-audit.json) và [provenance ledger](../database/seeders/data/image-provenance.json). Tài liệu không xem placeholder hoặc ảnh minh họa là ảnh sản phẩm hoàn chỉnh.

## 8. Kiểm thử và QA

Lượt PHPUnit mới nhất được ghi nhận ngày 27/10/2026 lúc 10:24 +07:00 trên replica set QA loopback:

| Phạm vi | Tests | Assertions | Failures | Errors | Skips |
|---|---:|---:|---:|---:|---:|
| Toàn bộ suite | 110 | 2.132 | 0 | 0 | 0 |
| Dữ liệu/seeder/image/sales tích hợp của Dũng | 59 | 1.777 | 0 | 0 | 0 |
| Hồi quy Auth/Profile/Sales còn lại | 51 | 355 | 0 | 0 | 0 |

JUnit đầy đủ và summary JSON được lưu ở [evidence](dung/evidence/). Bộ test chạy trên database có tiền tố shop_hoatuoi_test_*; kết quả không đại diện dữ liệu production hay database dùng chung.

Browser evidence hiện có bao gồm 24 phép đo ở desktop/mobile. Các thao tác đăng ký/giỏ/checkout trong evidence thành công; có hai lỗi tràn ngang ở trang success tại viewport 390 px và 375 px. Browser chưa được chạy lại trong lượt cập nhật tài liệu này. Kết quả browser và hạn chế được ghi riêng trong [QA_KET_QUA.md](dung/QA_KET_QUA.md).

## 9. Hạn chế và phụ thuộc

| Việc còn thiếu | Phụ thuộc |
|---|---|
| Chốt Category/Auth contract và model MongoDB chung | Thành viên 1/nhóm trưởng |
| Catalog, Product CRUD, search/filter và upload ảnh | Thành viên 2 |
| Wishlist, contact/Maps, blog và UI hoàn chỉnh | Thành viên 4 |
| Hợp nhất route/schema sales và xử lý lỗi mobile đã ghi nhận | Thành viên 3, thành viên sở hữu view và nhóm trưởng |
| Database QA chung, nhánh tích hợp và quy trình release | Nhóm trưởng |

Ngoài các phụ thuộc trên, 68 ảnh sản phẩm đúng SKU và 3 thumbnail bài viết còn thiếu; phương thức thanh toán mới ghi nhận lựa chọn COD/Banking, chưa có tích hợp cổng thanh toán. DatabaseSeeder và một số ví dụ trong kế hoạch còn theo SQL/pivot, cần giữ tách biệt khỏi MongoDB importer.

## 10. Đối chiếu tiêu chí đồ án

| Tiêu chí | Nhận định có bằng chứng |
|---|---|
| Nghiệp vụ/Auth | PARTIAL: Auth/Profile và Sales có code/tests; Catalog, Product CRUD và Category CRUD chưa tích hợp chung |
| Kiến trúc/CSDL | PARTIAL: MongoDB replica-set, embedded cart/order và importer có tests; nhiều module vẫn mang model SQL |
| UI/Responsive | PARTIAL: có Auth/Sales views; trang chủ chưa là storefront, browser evidence có hai lỗi mobile |
| Báo cáo/thiết kế | DONE: báo cáo, MongoDB schema, ERD, Use Case, Activity, Sequence và nội dung slide được liên kết trong bộ tài liệu |
| Kỹ thuật nâng cao/tích hợp | PARTIAL: upload sản phẩm, wishlist AJAX và luồng contact/Maps/blog chưa nghiệm thu trên nhánh chung; các phần này chờ module phụ trách được hợp nhất |

Bảng này ghi trạng thái kỹ thuật, không quy đổi thành điểm hoặc xác nhận đạt 10/10.

## 11. Hướng phát triển

1. Nhóm thống nhất nhánh tích hợp và contract MongoDB/Auth/Category trước khi ghép module.
2. Hoàn thành Catalog, Product CRUD, upload và liên kết danh mục; viết test trên database QA riêng.
3. Thay 68 ảnh placeholder sản phẩm và 3 thumbnail bằng asset có quyền sử dụng, provenance và SKU được xác nhận.
4. Tích hợp wishlist, contact/Maps, blog và dashboard theo schema đã thống nhất.
5. Sửa hai lỗi mobile đã ghi nhận, chạy lại browser trên các viewport và viết full journey test dựa trên route/model thật.
6. Rà soát lại DatabaseSeeder, migration và tài liệu hướng dẫn trước khi chọn quy trình triển khai chung.

## Tài liệu liên quan

- [MongoDB schema và indexes](dung/MONGODB_SCHEMA.md)
- [Kiến trúc, ERD, Activity và Sequence](dung/SO_DO.md)
- [Use Case UML](dung/USE_CASE.puml)
- [Kế hoạch QA](dung/QA_TEST_PLAN.md)
- [Kết quả QA](dung/QA_KET_QUA.md)
- [Quy trình Seeder/Windows](dung/SEED_VA_KIEM_THU.md)
- [Nội dung thuyết trình](dung/NOI_DUNG_SLIDE.md)
