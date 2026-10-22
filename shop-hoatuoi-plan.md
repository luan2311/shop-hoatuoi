# Shop Hoa Tươi – Laravel & MongoDB Project Plan

## Goal
Xây dựng website bán hoa tươi bằng **Laravel 12** kết nối **MongoDB** (chạy trên Laragon/Docker/Local MongoDB/Atlas), dựa theo giao diện [gochoaxinh.com](https://gochoaxinh.com) và port lại từ codebase C# ASP MVC 5 tại [luan2311/Shop-Flower](https://github.com/luan2311/Shop-Flower), đáp ứng đầy đủ tiêu chí chấm điểm (10đ).

---

## Database (MongoDB Document Model – Chuyển đổi từ QL_SHOPFLOWER_final.sql)

Toàn bộ dữ liệu từ `QL_SHOPFLOWER_final.sql` đã được chuyển đổi thành file script MongoDB chuẩn: `database/mongodb_init.js` (gồm 80 sản phẩm, 5 danh mục, 3 bài viết tin tức, vai trò, tài khoản quản trị và mẫu đơn hàng, giỏ hàng, liên hệ).

| Bảng gốc (SQL Server) | Collection MongoDB | Mô hình lưu trữ (Document Model) | Ghi chú chuyển đổi |
|---|---|---|---|
| `LOAIHANG` | `categories` | Standalone Collection | `code` (LH001..), `name`, `slug`, `description` |
| `SANPHAM` | `products` | Standalone + Embedded Category | `code` (PVN297..), `name`, `slug`, `price`, `image`, `description` (HTML), `status`, `brand`, `stock`, `category: { code, name, slug }`. Có Text Index hỗ trợ Live Search debounce. |
| `TAIKHOAN` + `VAITRO` + `TAIKHOAN_VAITRO` | `users` & `roles` | Standalone + Array of Roles | `username`, `name`, `email`, `password` (bcrypt), `roles: ['Admin', 'User']`, `is_active` |
| `HOADON` + `CTHD` | `orders` | **Embedded Document** (Mô hình NoSQL chuẩn) | `order_code`, `customer: { name, phone, email, address, note }`, `items: [ { product_code, product_name, price, quantity, subtotal, image } ]`, `total_amount`, `payment_method`, `status` |
| `CART` + `CART_ITEM` | `carts` | **Embedded Document** | `user_id`, `items: [ { product_code, name, price, quantity, image, added_at } ]`, `updated_at` |
| `WISHLIST` | `wishlists` | Standalone Collection | `user_id`, `product_code`, `created_at` (hỗ trợ Index kép `{ user_id: 1, product_code: 1 }`) |
| `LIENHE` | `contacts` | Standalone Collection | `code` (LH001..), `full_name`, `email`, `phone`, `message`, `status` |
| `TINTUC` | `posts` | Standalone Collection | `code` (TT001..), `title`, `slug`, `thumbnail`, `summary`, `content`, `published_at` |

---

## Tasks

- [ ] **T1: Cấu hình môi trường & MongoDB** – Cài đặt package `mongodb/laravel-mongodb` (hoặc cấu hình MongoDB client), thiết lập `.env` (`DB_CONNECTION=mongodb`, `MONGODB_URI=mongodb://127.0.0.1:27017`, `MONGODB_DATABASE=shop_hoatuoi`) → Verify: Chạy test kết nối MongoDB thành công qua command `php artisan db:monitor` hoặc `php artisan tinker`.

- [ ] **T2: Khởi tạo dữ liệu MongoDB từ `mongodb_init.js`** – Thực thi file script `database/mongodb_init.js` qua `mongosh` để tạo đủ 8 collections, thiết lập JSON Schema Validation, tạo Text Index cho sản phẩm và nạp toàn bộ 80 sản phẩm + danh mục + tin tức + tài khoản mẫu → Verify: `mongosh "mongodb://localhost:27017/shop_hoatuoi" database/mongodb_init.js` in ra thống kê đầy đủ 80 sản phẩm, 5 danh mục, 3 bài viết.

- [ ] **T3: Authentication & Phân quyền trên MongoDB (2 Role)** – Sử dụng MongoDB Model cho `User` kế thừa `MongoDB\Laravel\Eloquent\Model` (hoặc Custom Mongo Provider), middleware `role:admin` / `role:user`; Admin route group `/admin/*`, User route `/account/*` → Verify: Đăng nhập admin `admin@shophoatuoi.com` (mật khẩu `Admin@123`) chuyển hướng `/admin/dashboard`, đăng nhập user chuyển hướng `/account/profile`.

- [ ] **T4: Module Sản phẩm (CRUD + Search + Paginate)** – `ProductController` (8 methods), Blade views danh sách/chi tiết; Admin CRUD với upload ảnh `storage/products`; User: tìm kiếm theo tên/mô tả sử dụng MongoDB Full-text Index (`$text`) hoặc Regex search, lọc theo danh mục, phân trang 12 sp/trang → Verify: Admin thêm/sửa/xóa sản phẩm cập nhật ngay trên MongoDB; User search "hồng" ra kết quả chính xác theo debounce.

- [ ] **T5: Module Danh mục + Tin tức (CRUD)** – `CategoryController` CRUD danh mục hoa; `PostController` CRUD tin tức cho admin; Frontend hiển thị tin tức có ảnh bìa, ngày đăng chuẩn ISO Date → Verify: Admin thêm loại hoa mới; bài tin tức TT001, TT002, TT003 hiển thị đầy đủ ngoài trang chủ và trang blog.

- [ ] **T6: Giỏ hàng + Đặt hàng (NoSQL Embedded Model)** – `CartController` (add/update/remove/clear) thao tác trên mảng `items` của document giỏ hàng bằng toán tử `$push`, `$pull`, `$set`; `OrderController` tạo đơn hàng nhúng toàn bộ thông tin sản phẩm và thông tin người nhận vào 1 document duy nhất; cập nhật trạng thái `Pending → Processing → Shipped → Completed / Cancelled` → Verify: Thêm sp vào giỏ → checkout → document đơn hàng mới xuất hiện trong collection `orders` với danh sách items đầy đủ.

- [ ] **T7: Wishlist + Liên hệ (AJAX + Upload)** – Wishlist dùng AJAX (Fetch API) thêm/bớt hoa yêu thích không reload trang; Form liên hệ validate server-side (FormRequest) và lưu vào collection `contacts`; upload ảnh sản phẩm qua `Storage::disk('public')` → Verify: Click icon tim ❤ không reload; submit form liên hệ lưu vào MongoDB; ảnh upload hiển thị chính xác.

- [ ] **T8: Giao diện Bootstrap + Responsive** – Layout master Blade (`layouts/app.blade.php`, `layouts/admin.blade.php`) dùng Bootstrap 5; phong cách màu hồng-xanh gochoaxinh.com; mobile responsive kiểm tra 375px → Verify: Giao diện chuẩn mobile/desktop, hiển thị danh mục, banner, sản phẩm nổi bật và tin tức hài hòa.

- [ ] **T9: Validation + Tối ưu hóa NoSQL** – Validation FormRequest; AJAX live search realtime (debounce 300ms) truy vấn MongoDB Text Index; tích hợp Google Maps iframe trang Liên hệ → Verify: Submit form rỗng báo lỗi đúng field; gõ tìm kiếm phản hồi nhanh dưới 100ms nhờ MongoDB Index.

- [ ] **T10: Kiểm tra cuối + Tài liệu NoSQL Schema** – Chạy `php artisan test`; xuất sơ đồ cấu trúc dữ liệu NoSQL (Document Model & Collections Diagram); hoàn thiện hướng dẫn import MongoDB `database/mongodb_init.js` → Verify: Toàn bộ route hoạt động, dữ liệu đồng bộ và tài liệu đồ án đạt chuẩn 10đ.

---

## Done When

- [ ] Khởi tạo thành công CSDL MongoDB với 8 collections và nạp đủ 80 sản phẩm từ `mongodb_init.js`
- [ ] CRUD hoàn chỉnh sản phẩm và danh mục trên MongoDB
- [ ] Login/Logout hoạt động với 2 role Admin + User dựa trên Document Model của MongoDB
- [ ] Tìm kiếm full-text và phân trang sản phẩm mượt mà
- [ ] Đơn hàng và giỏ hàng áp dụng đúng tư duy NoSQL Embedded Document (không phụ thuộc JOIN quan hệ truyền thống)
- [ ] Validation đầy đủ tất cả Form (FormRequest)
- [ ] Blade Template + Controller + Middleware đúng chuẩn kiến trúc MVC
- [ ] Giao diện Bootstrap 5 responsive chuẩn gochoaxinh.com
- [ ] Upload file ảnh và lưu trữ `storage/app/public` hoạt động
- [ ] AJAX wishlist/search hoạt động không reload trang
- [ ] Tích hợp API bản đồ Google Maps

---

## Ghi chú quan trọng

> [!NOTE]
> **Tiêu chí điểm từ bảng rubric (Áp dụng cho MongoDB):**
> - Tiêu chí 1 (4đ): CRUD + Auth 2 role + Search/Paginate + Validation
> - Tiêu chí 2 (2.5đ): MongoDB Eloquent Model / Driver + Schema Validation/Seeder JS + Controller/Middleware/Blade
> - Tiêu chí 3 (1.5đ): Bootstrap 5 + Responsive Mobile First
> - Tiêu chí 4 (1đ): Báo cáo NoSQL Document Schema + Use Case UML + Thuyết trình
> - Tiêu chí 5 (1đ): Upload + AJAX + API ngoài (Google Maps)

> [!TIP]
> **Cách chạy file nạp CSDL MongoDB:**
> ```bash
> # Sử dụng MongoDB Shell (mongosh):
> mongosh "mongodb://localhost:27017/shop_hoatuoi" database/mongodb_init.js
> 
> # Hoặc mở mongosh rồi gõ:
> > load("database/mongodb_init.js")
> ```

> [!IMPORTANT]
> **Kết nối Laravel với MongoDB:**
> Thêm cấu hình trong `config/database.php`:
> ```php
> 'mongodb' => [
>     'driver' => 'mongodb',
>     'dsn' => env('MONGODB_URI', 'mongodb://127.0.0.1:27017'),
>     'database' => env('MONGODB_DATABASE', 'shop_hoatuoi'),
> ],
> ```
> Cài đặt thư viện: `composer require mongodb/laravel-mongodb`
> Đảm bảo máy tính đã cài đặt MongoDB Community Server hoặc dùng MongoDB Atlas connection string.
