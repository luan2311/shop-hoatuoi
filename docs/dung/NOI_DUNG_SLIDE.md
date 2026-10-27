# Nội dung thuyết trình đồ án Shop Hoa Tươi

Dàn ý này phản ánh code trên nhánh QA và các kết quả có evidence. Đây là nội dung Markdown, chưa phải file PowerPoint. Thời lượng gợi ý: 8–10 phút, 12 slide.

## Slide 1 — Shop Hoa Tươi

**Thông điệp:** Website bán hoa tươi đang được chuyển một phần nghiệp vụ sang MongoDB.

- Đề tài: quản lý sản phẩm và quy trình mua hoa trực tuyến.
- Phạm vi trình bày: nền Auth/Sales, schema MongoDB, seeder và QA.
- Phiên bản báo cáo: 27/10/2026; nhánh QA feature/testing-docs-seeder.

**Ghi chú:** Nêu rõ đây là nhánh làm việc tích hợp từng phần, chưa phải website hoàn chỉnh.

## Slide 2 — Bài toán và mục tiêu

- Cần quản lý danh mục, sản phẩm, khách hàng, giỏ và đơn hàng.
- Dữ liệu giỏ/đơn có cấu trúc nhúng phù hợp document database.
- Mục tiêu QA: dữ liệu mẫu có checksum, kiểm tra kiểu/quan hệ, import an toàn và kiểm thử trên MongoDB riêng.
- Mục tiêu giao diện toàn website chưa nghiệm thu.

**Gợi ý hình:** Sơ đồ user flow trong [SO_DO.md](SO_DO.md).

## Slide 3 — Công nghệ thực tế

- PHP 8.3 và Laravel 13.37.
- MongoDB 8.3 với Laravel MongoDB 5.7.
- Blade, Breeze, Tailwind CSS, Alpine.js và Vite.
- PHPUnit 12 và MongoDB replica set riêng cho test.

**Ghi chú:** Kế hoạch ban đầu có đề cập Laravel 12/MySQL; code được kiểm tra hiện chạy Laravel 13/MongoDB.

## Slide 4 — Kiến trúc Laravel – MongoDB

- Request đi qua route, middleware và Form Request.
- Controller gọi SalesService; service thao tác MongoDB models và transaction.
- Blade render Auth, hồ sơ, giỏ, checkout và đơn hàng.
- Importer dữ liệu có guard riêng, tách khỏi request của website.

**Gợi ý hình:** Dùng flowchart kiến trúc đã có ở [SO_DO.md](SO_DO.md), không vẽ lại.

## Slide 5 — MongoDB và document nhúng

- Nguồn mô tả 9 collection; importer giai đoạn này dùng categories/products/posts.
- Dataset hiện có 5 categories, 80 products, 3 posts.
- Cart nhúng items; Order nhúng customer và order item snapshots.
- User lưu roles dưới dạng mảng; không dùng role pivot SQL.
- MongoDB không cưỡng chế foreign key; importer/service phải tự kiểm tra.

**Gợi ý hình:** ERD trong [SO_DO.md](SO_DO.md) và định nghĩa/index trong [MONGODB_SCHEMA.md](MONGODB_SCHEMA.md).

## Slide 6 — Auth và luồng mua hàng

- Auth/Profile có tests trên nhánh.
- Giỏ đọc giá hiện tại từ Product; checkout kiểm tra stock và trạng thái.
- Checkout dùng UUID token để chống đặt lặp và transaction để cập nhật stock/order/cart.
- Admin chuyển trạng thái theo luồng hợp lệ; hủy đơn hoàn kho trong transaction.

**Giới hạn:** Chưa có Catalog giao diện để dẫn khách từ trang sản phẩm đến giỏ trong website đã tích hợp.

## Slide 7 — Dataset và seeder an toàn

- Parser đọc literal từ initializer; không chạy JavaScript nguồn.
- Dry-run là mặc định; apply yêu cầu database test/dev xác nhận và MongoDB loopback.
- Dữ liệu đã tồn tại được giữ nguyên; importer chỉ insert thiếu bằng $setOnInsert.
- Không chạy mongodb_init.js; file đó gọi drop() collection.

**Gợi ý hình:** Flow import và QA trong [SO_DO.md](SO_DO.md), lệnh mẫu trong [SEED_VA_KIEM_THU.md](SEED_VA_KIEM_THU.md).

## Slide 8 — Tình trạng hình ảnh

- 74 tên file ảnh sản phẩm duy nhất trong manifest.
- 6 ảnh có license là minh họa, chưa xác minh đúng SKU.
- Còn 68 tên file ảnh sản phẩm thiếu asset; 0 ảnh đã xác minh đúng SKU.
- Đã chuẩn bị 3 thumbnail bài viết có giấy phép từ ảnh Commons; route Posts chưa tích hợp để kiểm tra hiển thị.
- Template placeholder chung được track trong Git; runtime còn 68 placeholder sản phẩm, không tính là ảnh hoàn chỉnh.

**Gợi ý hình:** Trích bảng trạng thái từ [product-images.json](product-images.json); không trình bày placeholder như ảnh hoa thật.

## Slide 9 — Kiểm thử tự động

- Full suite mới nhất PASS: 110 tests, 2.132 assertions, 0 failure/error/skip trên replica set QA riêng `dungQA` tại `127.0.0.1:27028`.
- PHP CLI mặc định chưa nạp extension; launcher QA nạp extension 2.5.5 chỉ trong process test và khôi phục biến môi trường.
- Test chỉ ghi vào các database QA có tiền tố riêng; các DB giữ lại, không có DB nào bị xóa.
- Xem [QA_KET_QUA.md](QA_KET_QUA.md) và [runtime evidence](evidence/phpunit-runtime-2026-10-27.json).

**Ghi chú:** Đây là kết quả PHPUnit; không phải chứng nhận production hoặc browser toàn website.

## Slide 10 — Browser QA và các lỗi còn lại

- Evidence browser hiện có 24 phép đo desktop/mobile.
- Đăng ký, thêm giỏ có CSRF và checkout thành công trong lượt browser đó.
- Desktop không tràn ngang; trang success tràn tới 396 px ở viewport 390 px và 375 px.
- Browser evidence chưa chạy lại trong lần cập nhật tài liệu.

**Gợi ý hình:** Dùng screenshot success mobile trong thư mục evidence/browser-2026-10-27/ và đánh dấu đây là lỗi còn mở.

## Slide 11 — Phạm vi chưa tích hợp

- Trang chủ vẫn là Laravel welcome.
- Thiếu Catalog, Product CRUD/upload, tìm kiếm/phân trang.
- Category/Role/Wishlist/Contact/Post còn lệch giữa SQL models và hợp đồng MongoDB.
- Wishlist AJAX, Maps, blog và dashboard chung chưa nghiệm thu.
- Phụ thuộc phối hợp: chốt Auth/Category với thành viên 1; Catalog/upload với thành viên 2; Wishlist/Contact/Blog với thành viên 4; hợp nhất Sales và sửa view cùng thành viên 3, chủ view và nhóm trưởng.

**Ghi chú:** Nêu rõ sự khác nhau giữa code có trong nhánh thành viên và code đã hợp nhất.

## Slide 12 — Kết luận và hướng phát triển

- Nền Auth/Sales và QA MongoDB có kiểm thử thực tế.
- Tài liệu có schema, architecture, Use Case, Activity, Sequence, seeder và evidence.
- Tiếp theo: thống nhất contract/nhánh tích hợp, hoàn thiện Catalog/upload, thay placeholder bằng ảnh được cấp phép, sửa lỗi mobile và chạy full journey.

**Kết luận:** Kết quả hiện tại là một nền tích hợp có phạm vi giới hạn; chưa khẳng định toàn bộ rubric 10/10.
