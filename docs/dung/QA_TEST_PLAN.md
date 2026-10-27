# Kế hoạch QA dữ liệu và tích hợp

## Phạm vi đã có test thực thi

Nguồn: [SeederIntegrityTest](../../tests/Feature/SeederIntegrityTest.php), [FlowerDatasetTest](../../tests/Unit/FlowerDatasetTest.php), [SeededSalesIntegrationTest](../../tests/Feature/SeededSalesIntegrationTest.php). Trạng thái của lượt chạy cụ thể ở [QA_KET_QUA](QA_KET_QUA.md), không suy PASS chỉ từ việc có file test.

| Mã | Test case | Kết quả mong đợi |
|---|---|---|
| DATA-01 | Checksum dataset khớp initializer | Không dùng bản dữ liệu lệch source |
| DATA-02 | Đủ 5 danh mục/80 sản phẩm/3 bài | Đủ counts, code và slug unique |
| DATA-03 | Thiếu mã/tên/giá/danh mục hoặc dữ liệu sai kiểu | Từ chối trước import |
| DATA-04 | Giá âm/lẻ/chuỗi format/quá giới hạn | Từ chối; tiền nguyên VND |
| DATA-05 | Kho âm/lẻ, trạng thái khác contract | Từ chối |
| DATA-06 | Danh mục nhúng không khớp | Từ chối |
| DATA-07 | Ảnh traversal/URL ngoài hoặc ngày bài viết sai | Từ chối |
| DATA-08 | Mongo test riêng, validators nguồn và migrations additive | Dữ liệu BSON đúng, không SQL fallback |
| DATA-09 | Dry-run | Không thêm document hoặc index |
| DATA-10 | Seed lần đầu và lần hai | 5/80/3 rồi 0 insert; IDs/timestamps giữ nguyên |
| DATA-11 | Sản phẩm của An đã sửa nội dung/giá/kho | Không ghi đè hoặc đánh dấu lại ownership |
| DATA-12 | Danh mục Luân đã đổi tên | Giữ danh mục; sản phẩm mới lấy snapshot hiện tại |
| DATA-13 | Slug nguồn đã thuộc mã sản phẩm khác | Dừng trước khi thêm catalog |
| DATA-14 | Đã có user/order/cart và stock giảm sau checkout | Fingerprints trước/sau reimport giống nhau |
| DATA-15 | Production, database chung, target sai, SQL hoặc server remote | Từ chối trước khi kết nối/ghi vào đích đó |
| DATA-16 | Tài khoản QA mới với mật khẩu ngẫu nhiên | Hash đúng, auth Mongo được; output không có password |
| DATA-17 | User QA đã đổi mật khẩu/quyền/trạng thái | Seed lại không đổi user |
| DATA-18 | Thiếu password QA hoặc thiếu opt-in adapter | Không ghi dữ liệu |
| DATA-19 | MongoDB lỗi hoặc validator từ chối giữa đợt import | FAIL có progress; retry giữ bản ghi đã thêm, không xuất URI/credentials |
| DATA-20 | Command Artisan/adapter seeder | Dry-run mặc định; apply và target rõ ràng; chạy lại an toàn |
| IMAGE-01 | Ảnh đầu vào, preview/apply và giới hạn chuẩn hóa | Không ghi khi preview; JPEG 800×800, tối đa 350 KB |
| IMAGE-02 | Asset có provenance, runtime thiếu hoặc còn placeholder của project | Nhận asset/placeholder đã biết; cập nhật hash runtime; file lạ không bị ghi đè |
| IMAGE-03 | Filename được nhiều mã sản phẩm dùng chung | Chỉ role `product` khi metadata xác minh đủ mọi mã; còn lại là illustration |
| SEC-01 | POST cart/checkout không có CSRF token | HTTP 419 khi chạy middleware ngoài môi trường testing |
| SEC-02 | Tên người nhận có markup script được lưu rồi render | Blade xuất HTML đã escape; không phát ra thẻ script thực thi |
| INDEX-01 | Readback index và explain tìm sản phẩm theo code | `code_1` được chọn; 1 key/1 document; không suy thành benchmark SLA |
| SALES-01 | Dữ liệu thật của bộ seed → login → cart → checkout | Dùng route hiện có và giá server; token UUID; stock đúng |
| SALES-02 | Retry ngay và sau refill giỏ | Một đơn cũ, giữ giỏ mới, không trừ kho thêm |
| SALES-03 | User khác truy cập đơn, User truy cập Admin | 404/403 |
| SALES-04 | Admin đi đúng chuỗi trạng thái, thử nhảy bước | Chuỗi hợp lệ thành công; nhảy bước bị chặn |
| SALES-05 | Hủy và hủy lại; đơn Completed không được hủy | Hoàn kho đúng một lần; trạng thái cuối bất biến |
| SALES-06 | Seed lại sau toàn bộ nghiệp vụ trên | User/product/category/post/order/cart giữ nguyên |

Các Feature HTTP test gọi Laravel trong test process và dùng MongoDB thật. IMAGE-01–03 được kiểm tra trong `ProductImageLibraryTest`; SEC-02 chạy qua checkout và HTML Blade trong `SeededSalesIntegrationTest`; SEC-01 cùng validation, retry và lỗi checkout có trong `OrderCheckoutTest`. Chúng không phải browser thao tác qua TCP, không đo responsive và không khẳng định Catalog đã hoạt động. Xem [QA_KET_QUA](QA_KET_QUA.md) để biết số thực tế của lượt mới nhất.

## Nghiệm thu toàn website — đang chờ dependency

Chưa tạo test toàn trình giả định route Catalog để rồi ghi PASS. `FullCustomerJourneyTest.php` chỉ nên triển khai khi nhóm chốt các route/model tích hợp. Không dùng ví dụ cũ với pivot roles, thiếu checkout_token hoặc Pending → Completed trực tiếp.

## Trạng thái module theo nhánh đã kiểm tra

Lượt test gần nhất có evidence chạy trên HEAD `d9239c741239ea73310ebebced8fd05e5a9b621d`. Review contract ngày 27/10/2026 được thực hiện trên HEAD hiện tại `996cad97dcd87e22bccbf6da8dc4c2999c97b27e` và ref `origin/feat/an-laravel-mongodb` tại `9a081ba37e54dddabfa8c7a25f81f6f06ba94452`, dựa trên merge-base `3ffe4cb0a09be7b7372dee98bf5b0580c83d350f`. Chỉ dùng Git read-only để xem hai cây; không checkout, fetch hoặc merge trong lượt review này. Từ HEAD test đến HEAD hiện tại chỉ đổi QA docs/evidence, không đổi app/routes/tests.

| Thành viên | Có trên nhánh hiện tại | Chỉ có trên ref khác | Trạng thái |
|---|---|---|---|
| Luân — Auth, Category, Admin | Breeze Auth routes/controllers/views, Profile, RoleMiddleware và Admin Order route | CategoryController/CRUD, DashboardController, routes/admin.php và views ở `origin/feature/auth-category-admin` (`c370d6f`) | Auth/profile Feature tests và Admin Order role tests PASS; Category/Admin Dashboard chưa tích hợp |
| An — Catalog, Product CRUD, Search, Filter, Upload | Product model dùng chung với Sales; không có ProductController, catalog/admin product routes hoặc product views | Product controllers, routes/products.php, views, tests và browser evidence ở `origin/feat/an-laravel-mongodb` (`9a081ba`) | Có implementation/test evidence trên ref riêng; chưa hợp nhất contract với Cart/Checkout tại checkout hiện tại |
| Tiến — Cart, Checkout, Orders | Routes, controllers, service, models, Mongo migrations, Blade views và Feature tests đều có | Ref gốc `origin/feature/cart-checkout-orders` (`89242cd`) đã có trong lịch sử checkout | 26 test lớp Sales PASS trong lượt hiện tại |
| Huy — Layout, Wishlist, Contact, Blog | Breeze app/guest layout cơ bản; không có HomeController, Wishlist/Contact/Post controllers, routes hoặc views tương ứng | Controllers/routes/views/test files ở `origin/feature/user-ui-wishlist-contact` (`8d8d700`) | Module và test source còn ở ref riêng; chưa nghiệm thu trên checkout này |

Các test/evidence trên ref riêng chỉ chứng minh trạng thái của ref đó, không được tính là PASS tích hợp trên HEAD hiện tại. `routes/web.php` là file chia sẻ bị các module cùng thay đổi; khi tích hợp cần ghép `auth.php`, `sales.php`, `products.php`, admin/user routes có kiểm tra trùng route name và middleware.

| Mã | Luồng cần kiểm tra | Điều kiện | Trạng thái hiện tại |
|---|---|---|---|
| WEB-01 | Đăng ký, login/logout, hồ sơ, hai role | Auth Mongo tích hợp vào nền chung | Feature tests hiện có PASS (11 Auth/Profile tests; role được kiểm tra trong AdminOrderManagementTest); browser E2E NOT RUN |
| WEB-02 | Xem danh mục, catalog, lọc, search, paginate12 | ProductController/routes/views của An | BLOCKED |
| WEB-03 | Chi tiết sản phẩm → component thêm giỏ | Catalog dùng ObjectId, code/status/image đúng contract | BLOCKED |
| WEB-04 | Cập nhật quantity, vượt kho, xóa/clear giỏ | Sales + Catalog tích hợp | Cart HTTP tests PASS; nối từ trang Catalog BLOCKED |
| WEB-05 | Checkout form, validation, CSRF, retry | Lấy token qua GET checkout; không tin giá client | Checkout, rollback, CSRF và idempotency Feature tests PASS; browser E2E NOT RUN |
| WEB-06 | Lịch sử và owner access | Auth/User Mongo + routes orders | Feature tests lịch sử, phân trang và owner access PASS; browser E2E NOT RUN |
| WEB-07 | Admin xử lý/hủy, hoàn kho, filter/search | Layout Admin + schema mới đã hợp nhất | Admin orders, role, chuyển trạng thái và hoàn kho Feature tests PASS; dashboard/category UI BLOCKED |
| WEB-08 | Wishlist, contact/Maps, blog | Code Huy, schema các module được chốt | BLOCKED |
| WEB-09 | Ảnh sau upload hiện đúng ở catalog/cart | Ảnh có quyền sử dụng, Product CRUD/upload, storage link | BLOCKED |
| WEB-10 | Desktop/mobile các route hiện có; full site | Browser Chrome, server loopback và 4 viewport; Catalog/UI/ảnh thật vẫn thiếu | NOT RUN lượt này; evidence cũ cho thấy 2 overflow ở success; patch đề xuất chưa áp dụng |

## Lượt kiểm thử tích hợp gần nhất có evidence

Ngày 27/10/2026, chạy filter Auth/Sales qua `scripts/test-dung-data.ps1` trên `dungQA` loopback, HEAD `d9239c741239ea73310ebebced8fd05e5a9b621d`. Kết quả mới: **37 tests, 315 assertions, 0 failures, 0 errors, 0 skipped**, PASS. JUnit chi tiết và summary nằm tại [member5-integration-2026-10-27.xml](evidence/member5-integration-2026-10-27.xml) và [member5-integration-summary-2026-10-27.json](evidence/member5-integration-summary-2026-10-27.json). Test run sinh 37 database QA riêng; chúng được giữ nguyên, không cleanup. Kết quả này chỉ xác nhận các Feature test HTTP trong tiến trình PHPUnit; không xác nhận browser hoặc luồng xuyên Catalog.

| Lớp test | Số test | Kết quả |
|---|---:|---|
| `RegistrationTest`, `AuthenticationTest`, `ProfileTest` | 11 | PASS |
| `CartOperationsTest`, `OrderCheckoutTest`, `SalesFinalAuditTest`, `SalesConcurrencyTest`, `SalesSetupTest`, `AdminOrderManagementTest` | 26 | PASS |

## Fixture và case chuẩn bị cho module chưa tích hợp

Fixture hiện có `SalesFixtures::customer()` tạo User test riêng, gán role bằng mảng `roles`; `SalesFixtures::product()` tạo product test với `code` duy nhất, `price` nguyên VND, `stock`, `status = Còn hàng` và category snapshot. Khi Catalog của An được tích hợp, bổ sung đúng contract đã chốt: `slug`, `category_id` dạng ObjectId, image path hợp lệ và dữ liệu Category; trong test upload dùng file giả lập, không lấy ảnh thật không có quyền. POST giỏ phải dùng `$product->id` (ObjectId), còn cart item được định danh theo `code`.

Các case dưới đây đã chuẩn bị về expected/fixture nhưng **chưa chạy** vì route/controller chưa có trên nhánh hiện tại:

| Case | Fixture/luồng | Expected | Trạng thái |
|---|---|---|---|
| CAT-01 | Category + 13 products, phân bố 2 category | Catalog có 12/trang, filter giữ query và paginate đúng | BLOCKED: Catalog branch-only |
| CAT-02 | Product slug và category snapshot hợp lệ | Trang detail tải đúng Product, route không dùng SQL relation | BLOCKED |
| CAT-03 | Product ObjectId + User `User` | Thêm giỏ dùng ObjectId, cart lưu `code`, stock chưa giảm trước checkout | BLOCKED |
| CAT-04 | Product có ảnh file giả JPEG/PNG/WebP và ảnh sai định dạng | Upload hợp lệ tạo path mới; invalid file bị từ chối; ảnh cũ chỉ xóa khi không còn document tham chiếu | BLOCKED |
| TEAM-01 | Admin/User + route Catalog/Product/Admin | User bị chặn CRUD; Admin được phép; validation và lỗi MongoDB không lộ nội bộ | BLOCKED: cần hợp nhất Auth/An |
| TEAM-02 | Huy Home/Wishlist/Contact/Post routes với seed `posts` | Public pages trả đúng dữ liệu; wishlist theo user; contact validate; post detail theo slug | BLOCKED: Huy branch-only |

## Quy trình nghiệm thu khi đủ dependency

1. Ghi SHA nhánh tích hợp và versions thực tế; không chỉ ghi tên branch.
2. Chọn database test riêng có replica set; chụp counts/fingerprints ban đầu.
3. Seed an toàn; đăng ký khách mới và dùng QA Admin. Không đưa password vào biên bản.
4. Từ catalog: tìm, lọc, phân trang, xem chi tiết, thêm giỏ bằng ObjectId đúng.
5. Thử quantity bất hợp lệ/vượt kho, cập nhật, xóa/clear; giá hiển thị từ server.
6. Lấy checkout_token từ form; thử lỗi form rồi đặt đơn hợp lệ, đọc lại MongoDB.
7. Retry token cũ, refill giỏ rồi retry; xác minh không phát sinh đơn/kho ngoài ý muốn.
8. Kiểm tra đơn cá nhân/khác tài khoản và quyền Admin.
9. Đi Pending → Processing → Shipped → Completed; tạo đơn khác để Cancelled; thử thao tác lặp và trạng thái cuối.
10. Seed lại và so fingerprints user/product/order/cart, không reset stock.
11. Browser desktop 1280×900 và mobile 375×812: form/errors, overflow, navbar, ảnh, thao tác thật. Đo viewport/document width và chụp bằng chứng.
12. Kiểm tra Laragon Apache/Nginx riêng nếu đó là môi trường demo. Kết quả Artisan/test process không thay cho kết quả web server.

Mỗi case lưu PASS/FAIL/SKIP/BLOCKED, điều kiện, expected/actual, SHA và evidence. Không chuyển BLOCKED thành PASS bằng cách tạo catalog giả hoặc nới quyền/validation.

## Review contract Catalog/Product ↔ Cart/Checkout — 27/10/2026

Đây là đối chiếu source tĩnh giữa HEAD `996cad97` và ref An `9a081ba`; không chạy PHPUnit, không chạy browser và không kết nối MongoDB trong lượt này. Các test/evidence ở ref An chỉ phản ánh nhánh An, chưa chứng minh tích hợp.

| Hạng mục | Bằng chứng từ source | Kết luận / việc cần làm |
|---|---|---|
| Loader route | Nhánh hiện tại `routes/web.php` nạp `sales.php`; ref An nạp `products.php` thay vào cùng vị trí | Khi ghép phải giữ cả `auth.php`, `sales.php` và `products.php`; kiểm tra tên route và middleware sau ghép. Nếu thay file bằng bản An, toàn bộ route Sales sẽ mất. |
| Product Detail → Cart | `routes/products.php` dùng URL `/products/{slug}`; Cart dùng `POST /cart/add`. `CartRequest` yêu cầu `product_id` là chuỗi 24 ký tự hex; component Sales gửi `$product->id` | Định dạng URL/ID tương thích: slug dùng để xem, Mongo ObjectId dùng để thêm. Tuy nhiên `products/show.blade.php` của An hiện không render `<x-add-to-cart>` hoặc form thay thế: luồng bị BLOCKED cho tới khi An thêm CTA và xử lý guest cần đăng nhập. |
| Mã sản phẩm trong Cart | `SalesService::change(add)` tìm Product bằng `_id`, sau đó lưu `product_code`; cập nhật/xóa Cart dùng `code` | Tương thích nếu form gửi ObjectId và Product có `code` ổn định. Tiến cần xác nhận bằng test xuyên form → Cart, không đổi khóa Cart sang slug. |
| Tồn kho và trạng thái | Sales yêu cầu `status = Còn hàng`, quantity 1–999 và không vượt `stock`; component giới hạn quantity theo stock. Checkout mới trừ kho | Contract tương thích ở giá trị trạng thái và kho; cần test stock 0, hết hàng, quantity vượt kho và xác nhận add/update không trừ kho trước checkout. |
| Giá | An `ProductRequest` cho phép số lẻ và tới `999999999999`, admin controller ép `float`; Sales từ chối số lẻ và giá trên `1000000000` | **Mismatch đã xác nhận từ validation/service source.** An nên giới hạn tiền nguyên VND 0–1.000.000.000 ở request và form, đồng thời giữ kiểu số phù hợp. Không mở giới hạn Sales nếu chưa có quyết định nghiệp vụ và test biên của Tiến. |
| Ảnh | An lưu upload dưới `products/<filename>` và `image_url` xử lý được dạng này. Dataset hiện tại dùng filename trần; ảnh runtime ở `storage/app/public/products/<filename>`. Accessor An chỉ kiểm tra `products/...` hoặc `public/images/products`/`public/Images`, không tra Storage cho filename trần | **Mismatch đường dẫn đã xác nhận từ source.** An cần hỗ trợ filename trần qua public Storage hoặc chuẩn hóa dữ liệu ảnh sang `products/<filename>`. Hiện dataset có 80 product rows/74 filename: 6 ảnh minh họa dùng cho 7 rows, 68 target thiếu ảnh thật được lấp bằng placeholder runtime, 0 SKU xác minh. Nhánh An không mang bộ asset sản phẩm; `ProductDemoSeeder` cũng không đặt trường ảnh. Không coi placeholder/ảnh minh họa là ảnh đúng SKU. |
| Category/schema | Product An có `category_id` và embedded snapshot `{code,name,slug}`; Catalog lọc/detail dựa vào `category.code`. Category hiện tại là SQL model; Category An là Mongo model | **Dependency cần Luân chốt**: collection, ID, `code` ổn định, slug và snapshot. Không ghép riêng Category model trước khi xác nhận hợp đồng và cách seed dữ liệu. |
| Product model chung | Cả hai cây sửa `app/Models/Product.php`. Bản hiện tại có các quan hệ Sales và `scopeSearch`; bản An thêm cast/image accessor, `categoryRecord` và `scopeMatchingKeyword` | File có nguy cơ conflict trực tiếp. Khi ghép phải giữ quan hệ `cartItems`, `orderDetails`, `wishlists`, slug và stock; kiểm tra call-site trước khi thay `scopeSearch` hoặc đổi quan hệ Category. |
| Auth/Admin | Nhánh hiện tại dùng Mongo `User` với roles dạng mảng; ref An giữ SQL `User`. Test `ProductAuthIntegrationTest` của An giả định SQLite auth và SQL seeder | **Test/auth contract chưa tương thích nếu dùng nguyên test An.** Luân và An cần điều chỉnh guard/provider/seeder/test để kiểm tra Product Admin bằng User/role đang dùng trên nhánh tích hợp. Không suy từ `role:Admin` cùng tên rằng hai cơ chế User giống nhau. |

### File cần ghép có kiểm soát

- `routes/web.php`: hai nhánh thêm require khác nhau; giữ đồng thời route Sales và Catalog.
- `app/Models/Product.php`: thay đổi trực tiếp hai phía; hợp nhất các accessor/scope/relations.
- `app/Models/Category.php`: SQL ở checkout hiện tại so với Mongo ở ref An; cần quyết định schema với Luân.
- `bootstrap/app.php`: cả hai phía thêm xử lý exception khác nhau; phải giữ role alias và compose handler cho lỗi Catalog lẫn Sales.
- `composer.json`/`composer.lock`: checkout yêu cầu `mongodb/laravel-mongodb ^5.7`, ref An yêu cầu `^5.11`; thống nhất constraint rồi resolve lock từ một cây tích hợp.
- `config/database.php`: cả hai thêm Mongo connection; kiểm tra giữ cấu hình thống nhất, không ghi đè cấu hình môi trường.
- `.env.example`: URI mặc định khác nhau (Sales dùng replica set `tienRS` trên 27018, An dùng 27017); chỉ hợp nhất ví dụ theo môi trường, không chép vào `.env`.
- `app/Models/User.php`: chỉ checkout hiện tại đổi sang Mongo kể từ base; đây là dependency ngữ nghĩa cho test/admin An dù không phải sửa trực tiếp cùng phía.
- Migration Catalog và Sales cùng tạo index/schema trên collection `products` nhưng tên file khác nhau; không có filename collision đã xác nhận, vẫn phải chạy migration trên DB tích hợp cô lập để kiểm tra validators/index idempotence.

### Checklist sau khi dependency được ghép

Các case dưới đây là **NOT RUN** trong lượt review này. Thực hiện trên Mongo QA cô lập sau khi thống nhất Category, User, giá, route và ảnh; ghi SHA của candidate và evidence mới.

| Case | Luồng / dữ liệu | Kỳ vọng | Trạng thái hiện tại |
|---|---|---|---|
| CAT-SALES-01 | Catalog → category filter/search/sort/page → detail theo slug | Đúng Product/category; filter và query string giữ nguyên; detail hiển thị trạng thái/giá | NOT RUN; Catalog chỉ có trên ref An |
| CAT-SALES-02 | User đăng nhập, detail bấm thêm với Product Mongo ObjectId | POST `/cart/add` 302 về Cart; Cart lưu `product_code`; kho chưa giảm | BLOCKED; detail An chưa có CTA |
| CAT-SALES-03 | Guest, ObjectId sai, status hết hàng, stock 0, quantity 0/1000/vượt kho | Guest được yêu cầu login; input sai bị 4xx/validation; dữ liệu Cart/kho không đổi | NOT RUN; cần CTA và test kết hợp |
| CAT-SALES-04 | PATCH `/cart/items/{code}` tăng/giảm quantity, DELETE item/clear | Cart dùng code; không vượt stock; chỉ checkout mới trừ kho | NOT RUN; Cart độc lập đã có test trước đó, chưa test từ Catalog An |
| CAT-SALES-05 | GET checkout lấy token → POST đơn hợp lệ/thiếu trường/hết kho | Giá lấy từ DB; order snapshot code/name/slug/image/price; trừ kho đúng một lần, Cart rỗng khi thành công | NOT RUN xuyên Catalog; giá An cần khớp Sales trước khi chạy |
| CAT-SALES-06 | Retry checkout token cũ sau khi thay Cart; hai khách mua stock cuối | Một order theo token; không trừ kho kép; một checkout cạnh tranh thất bại an toàn | Chưa chạy xuyên Catalog; logic Sales có test độc lập trong evidence trước |
| CAT-SALES-07 | Admin/Role User CRUD Product, upload ảnh hợp lệ/sai, Product public detail | User bị 403; Admin được phép; upload theo Storage path; ảnh hiển thị ở Catalog và Cart | BLOCKED tới khi thống nhất User/Category và image accessor |

### Phân công xử lý

- **An:** thêm CTA Cart vào Product Detail; giới hạn price theo contract Sales; hỗ trợ filename ảnh runtime hiện tại và placeholder; phối hợp chuyển Product model mà không mất các quan hệ Sales; cập nhật auth integration test theo User Mongo.
- **Tiến:** xác nhận contract `ObjectId → product_code`, stock, giá nguyên VND, ảnh snapshot; bổ sung/duyệt test xuyên Product Detail → Cart → Checkout trên candidate sau ghép.
- **Luân:** chốt Category Mongo/ID/code/slug/snapshot và cơ chế User/Admin chung trước khi merge module.
- **Dũng:** giữ checklist/evidence tách bạch; sau khi candidate an toàn sẵn sàng, chạy nhóm case CAT-SALES trên QA riêng và ghi PASS/FAIL/BLOCKED mới. Review này không chạy test.
