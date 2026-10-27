# Cấu trúc dữ liệu MongoDB và đối chiếu các nhánh

Nguồn đối chiếu: [initializer](../../database/mongodb_init.js), [hợp đồng Tiến](../TIEN-02-integration-contract.md), models tại commit `89242cd`, migrations trong `database/migrations/mongodb`, và nhánh Luân `c370d6f`. [source-audit.json](source-audit.json) lưu kết quả máy đọc; không suy số lượng database đang chạy từ số dòng seed.

## Bản initializer đã kiểm tra

Tìm thấy `mongodb_init.js` ở Downloads và `database/mongodb_init.js` trong repository. Bản Downloads dài 201.137 byte, bản Git dài 200.718 byte. Khác biệt chỉ là CRLF/LF: SHA-256 sau chuẩn hóa cùng bằng `fbdc44b7d5fc8f3976494d998a777b51c0022ec65e804599c791fbb589c590d1`. Bản trên `dev`, nhánh Luân và nhánh Tiến có cùng checksum; `main` chưa chứa file. Không sao chép đè bản nào và không chạy initializer.

Script chọn cố định `getSiblingDB('shop_hoatuoi')` và gọi `drop()` 9 collection. Đổi database trong URI không vô hiệu hóa đích cố định này. Không có lời gọi `dropDatabase()` hoặc `deleteMany()` trong bản đã đọc. Không dùng file này để chuẩn bị test hoặc chạy lặp.

| Collection | Bản ghi được khai báo trong script | Trường chính | Tình trạng trong runtime Tiến |
|---|---:|---|---|
| `categories` | 5 | `code`, `name`, `slug`, `description`, timestamps | Model Category vẫn SQL; importer Dũng dùng MongoDB driver riêng |
| `products` | 80 | `code`, `name`, `slug`, `price`, `original_price`, `image`, `description`, `status`, `brand`, `stock`, `category{}` | Product đã dùng MongoDB; các quan hệ/scopes SQL cũ chưa được chuyển hết |
| `users` | 2 | `username`, `name`, `email`, `password`, `phone`, `address`, `roles[]`, `is_active`, timestamps | Mongo Auth User; roles là mảng chuỗi |
| `roles` | 2 | `name`, `description`, `permissions[]`, `created_at` | Model Role vẫn SQL; auth Mongo không dùng pivot/collection này |
| `orders` | 1 | `order_code`, `user_id`, `customer{}`, `items[]`, `total_amount`, `payment_method`, `status`, ngày/timestamps | Runtime thêm `checkout_key`; items nhúng, không dùng OrderDetail SQL |
| `carts` | 0 | Script chỉ tạo collection; runtime có `user_id`, `items[{product_code,quantity}]` | Một giỏ/tài khoản; giá hiện tại lấy từ Product |
| `wishlists` | 0 | Script tạo collection và index `user_id + product_code` | Model SQL dùng `product_id`, chưa có controller/UI |
| `contacts` | 2 | `code`, `full_name`, `email`, `phone`, `message`, `status`, `created_at` | Model SQL dùng `content`, chưa có controller/UI |
| `posts` | 3 | `code`, `title`, `slug`, `thumbnail`, `summary`, `content`, `published_at`, timestamps | Model Post vẫn SQL, chưa có controller/UI |

Tổng cộng **9 collection, 95 bản ghi mẫu khai báo**. Carts/wishlists có 0 vì không có lệnh insert tương ứng. Số này không phải số bản ghi trên database ứng dụng hiện có.

Parser kiểm tra không phát hiện trùng code/slug/email/username/order_code trong từng collection hoặc thiếu trường bắt buộc cấp document theo validators nguồn. Kiểm tra PHP bổ sung giá/kho/trạng thái và liên kết danh mục cho toàn bộ dataset catalog. Không suy rằng script cũ đăng nhập được: hai hash người dùng giống nhau; cả hai phép `password_verify` với mật khẩu trong comment trả false. [Bằng chứng không chứa mật khẩu/hash](evidence/legacy-password-check.json).

## Kiểu dữ liệu và hợp đồng nghiệp vụ

- `_id`: initializer không gán trực tiếp; MongoDB tự tạo ObjectId. Importer Dũng tạo BSON ObjectId ổn định từ namespace seeder, collection và code/email. Không dùng SQL integer id.
- `products.code`: mã nghiệp vụ unique, ổn định, không có `/`; cart/order tham chiếu code. Không đổi code sản phẩm đã được sử dụng khi chưa có kế hoạch chuyển dữ liệu.
- `products.price`: source cho BSON int/double/long/decimal. Bộ 80 mẫu hiện có giá nguyên VND từ 200.000 đến 3.700.000. Bán hàng chấp nhận số nguyên 0–1.000.000.000; importer giữ nguyên integer.
- `stock`: integer không âm, bộ nguồn có khoảng 10–16. Không đồng nghĩa lúc chạy lại phải còn số này: stock giảm bởi đơn hàng phải được giữ nguyên.
- `status`: nguồn hiện có 80 giá trị `Còn hàng`; tập importer chấp nhận là `Còn hàng`/`Hết hàng`, khớp điều kiện mua của Tiến.
- `category`: snapshot nhúng `{code,name,slug}`. Năm danh mục nguồn khớp sản phẩm. Khi danh mục đã tồn tại với tên/slug mới, chỉ sản phẩm mới được seed mới lấy snapshot đang lưu; danh mục và sản phẩm tồn tại được giữ nguyên.
- `image`: filename hoặc contract `products/filename` của Tiến; bộ nguồn là filename. File thực dự kiến ở `storage/app/public/products`, URL qua `public/storage`. Không tự chuyển sang URL CDN hoặc thư mục lồng.
- `users.roles`: mảng chuỗi `Admin`/`User`. QA seed không cấp quyền cho tài khoản đang tồn tại và không phục hồi quyền khi seed lại.
- `orders.user_id`: BSON ObjectId cho đơn mới; service hỗ trợ string legacy khi truy vấn owner. `customer` chứa thông tin nhận hàng; `items` giữ snapshot giá/tên/ảnh/subtotal, không phụ thuộc tên/giá sản phẩm sau mua.
- `checkout_key`: SHA-256 theo tài khoản và checkout token, unique sparse; retry cùng token trả đơn cũ. UUID của form khác CSRF token.
- `published_at`: source có `new Date(ISO-string)`; dataset giữ marker `$date`, importer chuyển thành BSON UTCDateTime. `created_at/updated_at` chỉ tạo khi insert, không đổi khi seed lại.
- Không có foreign key MongoDB cưỡng chế giữa collection. Bộ dữ liệu và service phải tự kiểm tra liên kết, tồn kho, quyền truy cập và tổng tiền.

## Index

Initializer có **23 định nghĩa index**, chưa tính `_id` mặc định:

| Collection | Index nguồn |
|---|---|
| categories | unique `code`, unique `slug` |
| products | text `(name,description)` với weights 10/1 và `default_language=none`; unique `code`, unique `slug`; `category.code`; `(category.slug,price)`; `price`; `status` |
| users | unique `username`, unique `email` |
| roles | unique `name` |
| orders | unique `order_code`; `user_id`; `status`; `order_date desc` |
| posts | unique `code`, unique `slug`; `published_at desc` |
| contacts | unique `code`; `created_at desc` |
| wishlists | unique `(user_id,product_code)` |
| carts | unique `user_id` |

Migrations Tiến chỉ chuẩn bị các collection/index cần cho sales/auth: carts.user_id unique; orders.order_code unique, user_id và hai compound indexes phân trang; users.email/username và products.code/slug unique; orders.checkout_key unique sparse. Chúng không tạo toàn bộ schema/index cho các module khác.

Importer Dũng đảm bảo unique code/slug cho categories/products/posts, unique email/username nếu thêm QA users, các lookup danh mục và ngày bài viết. Nếu chưa có text index sản phẩm thì tạo theo nguồn; nếu có thì giữ nguyên, không tự thay weights. Không tạo/drop index của orders/carts/wishlists/contacts/roles, không sửa validator đang tồn tại. Các JSON Schema gốc trong tests được áp dụng lên database test mới để xác minh dữ liệu nhập được.

## Những khác biệt cần nhóm xác nhận

| Vấn đề | Nhánh/file liên quan | Ảnh hưởng | Phương án đề xuất | Người xác nhận |
|---|---|---|---|---|
| Tài liệu Laravel 12/MySQL; lock Laravel 13.37/MongoDB bridge | Kế hoạch vs composer/config nhánh Tiến | Hướng dẫn cài đặt và báo cáo sai nền | Chốt cấu hình chung và cập nhật tài liệu sau tích hợp | Luân |
| SQL roles/pivot vs `users.roles[]` | User/Role, tests và DatabaseSeeder nhánh Luân/đầu dự án | Không dùng `roles()->attach()` cho Mongo User | Lead chuyển auth tests/seed và duyệt bridge | Luân |
| Category/Product relation SQL vs category nhúng | Category, Product.category(), scopeSearch | `with(category)`/`whereHas` không đại diện category nhúng | Chốt API Catalog Mongo; Dũng không sửa các model này | Luân, An |
| Dashboard đọc `receiver_name/total` | Dashboard nhánh Luân vs Order Tiến | Hiển thị sai hoặc thiếu người nhận/tổng tiền | Chuyển về `customer.name/total_amount` trong module chủ quản | Luân, Tiến |
| Wishlist `product_id` vs `product_code` | Wishlist model vs initializer | Dữ liệu không khớp liên kết | Chốt schema trước seeder wishlist | Huy, An |
| Contact `content` vs `message` | Contact model, Task 4.3 và initializer | Form không lưu đúng trường nếu dùng nguyên ví dụ | Chốt request → document mapping | Huy |
| Post SQL vs date BSON/slug ổn định | Post model và posts mẫu | Chưa thể nghiệm thu blog | Huy chuyển module, giữ hợp đồng slug/date | Huy |
| Default DatabaseSeeder vẫn SQL/pivot | database/seeders/DatabaseSeeder.php | Không dùng cho Mongo runtime | Giữ nguyên; gọi FlowerSampleSeeder rõ class trong giai đoạn 1 | Luân, Dũng |
| `routes/web.php` được cả hai nhánh sửa | Nhánh Luân và Tiến | Cần giữ include admin/user/auth/sales khi tích hợp | Lead hợp nhất sau review; chưa thử merge | Luân |

## Quy tắc dữ liệu mẫu mới

Chỉ seed catalog/categories/posts; tùy chọn tạo **tài khoản QA mới** với email `.example.test`, mật khẩu môi trường được hash thật. Không nhập users/contacts/orders lịch sử từ initializer. Không seed giỏ/wishlist. Trường `sample_data.owner=dung-flowers-v1` ghi nguồn gốc, không cấp quyền sửa bản ghi. Phiên bản hiện tại có **0 cơ chế update**, kể cả bản ghi do seeder tạo trước đó. Cần sửa dữ liệu mẫu phải có thiết kế riêng được review, không dùng seed lại để reset stock/nội dung.
