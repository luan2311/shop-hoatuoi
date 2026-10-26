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

| Mã | Luồng cần kiểm tra | Điều kiện | Trạng thái hiện tại |
|---|---|---|---|
| WEB-01 | Đăng ký, login/logout, hồ sơ, hai role | Auth Mongo tích hợp vào nền chung | Có code/test trên nhánh Tiến; nghiệm thu nền chung BLOCKED |
| WEB-02 | Xem danh mục, catalog, lọc, search, paginate12 | ProductController/routes/views của An | BLOCKED |
| WEB-03 | Chi tiết sản phẩm → component thêm giỏ | Catalog dùng ObjectId, code/status/image đúng contract | BLOCKED |
| WEB-04 | Cập nhật quantity, vượt kho, xóa/clear giỏ | Sales + Catalog tích hợp | Sales độc lập đã có test; luồng toàn website BLOCKED |
| WEB-05 | Checkout form, validation, CSRF, retry | Lấy token qua GET checkout; không tin giá client | Có Feature tests; browser tích hợp BLOCKED |
| WEB-06 | Lịch sử và owner access | Auth/User Mongo + routes orders | Có Feature tests; nền chung BLOCKED |
| WEB-07 | Admin xử lý/hủy, hoàn kho, filter/search | Layout Admin + schema mới đã hợp nhất | Sales độc lập có test; dashboard/category chung BLOCKED |
| WEB-08 | Wishlist, contact/Maps, blog | Code Huy, schema các module được chốt | BLOCKED |
| WEB-09 | Ảnh sau upload hiện đúng ở catalog/cart | Ảnh có quyền sử dụng, Product CRUD/upload, storage link | BLOCKED |
| WEB-10 | Desktop/mobile các route hiện có; full site | Browser Chrome, server loopback và 4 viewport; Catalog/UI/ảnh thật vẫn thiếu | Đã đo 24 route/viewport; desktop PASS, mobile có 2 overflow ở trang success; full site chưa tích hợp |

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
