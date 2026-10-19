# Shop Hoa Tươi – Laravel Project Plan

## Goal
Xây dựng website bán hoa tươi bằng **Laravel 12** chạy trên **Laragon**, dựa theo giao diện [gochoaxinh.com](https://gochoaxinh.com) và port lại từ codebase C# ASP MVC 5 tại [luan2311/Shop-Flower](https://github.com/luan2311/Shop-Flower), đáp ứng đầy đủ tiêu chí chấm điểm (10đ).

---

## Database (MySQL – từ QL_SHOPFLOWER_final.sql)

| Bảng gốc (SQL Server) | Bảng Laravel (MySQL) | Ghi chú |
|---|---|---|
| `LOAIHANG` | `categories` | MaLoai → id |
| `SANPHAM` | `products` | MaSP → id |
| `TAIKHOAN` + `VAITRO` | `users` + `roles` | Laravel Auth chuẩn |
| `HOADON` | `orders` | Hóa đơn |
| `CTHD` | `order_details` | Chi tiết hóa đơn |
| `CART` + `CART_ITEM` | `carts` + `cart_items` | Giỏ hàng DB |
| `WISHLIST` | `wishlists` | Yêu thích |
| `LIENHE` | `contacts` | Form liên hệ |
| `TINTUC` | `posts` | Tin tức/Blog |

---

## Tasks

- [ ] **T1: Khởi tạo project** – Chạy `composer create-project laravel/laravel shop-hoatuoi`, cấu hình `.env` MySQL (Laragon port 3306, DB: `shop_hoatuoi`) → Verify: `php artisan serve` trả về trang welcome

- [ ] **T2: Migration + Seeder** – Tạo 9 migration files cho `categories`, `products`, `users`, `roles`, `user_roles`, `orders`, `order_details`, `carts`, `cart_items`, `wishlists`, `contacts`, `posts`; chạy `php artisan migrate --seed` với dữ liệu từ SQL file → Verify: DB có đủ bảng và data sản phẩm mẫu

- [ ] **T3: Authentication (2 role)** – Dùng Laravel Breeze, thêm middleware `role:admin` / `role:user`; Admin route group `/admin/*`, User route `/account/*` → Verify: Login admin vào `/admin/dashboard`, login user vào `/account/profile`

- [ ] **T4: Module Sản phẩm (CRUD + Search + Paginate)** – Controller `ProductController` (8 methods), Blade views cho danh sách/chi tiết; Admin CRUD với upload ảnh `storage/products`; User: tìm kiếm theo tên/loại, phân trang 12 sp/trang → Verify: Admin thêm/sửa/xóa sp; User search "hồng" ra kết quả đúng

- [ ] **T5: Module Danh mục + Tin tức (CRUD)** – `CategoryController` CRUD cho admin; `PostController` CRUD cho admin; Frontend hiển thị tin tức có ảnh bìa, ngày đăng → Verify: Admin tạo loại hàng mới; bài tin tức hiển thị đúng ngoài trang

- [ ] **T6: Giỏ hàng + Đặt hàng** – `CartController` (add/update/remove/clear) lưu DB theo session user; `OrderController` tạo hóa đơn từ cart (fields: TenNguoiNhan, DiaChiNhan, SDTNhan, GhiChu, PhuongThucThanhToan); Admin cập nhật trạng thái `Pending → Processing → Shipped → Completed / Cancelled` → Verify: Thêm sp vào giỏ → checkout → đơn hàng xuất hiện ở admin

- [ ] **T7: Wishlist + Liên hệ (AJAX + Upload)** – Wishlist dùng AJAX (fetch/axios) add/remove không reload trang; Form liên hệ validate server-side (FormRequest); upload ảnh sản phẩm qua `Storage::disk('public')` → Verify: Click tim ❤ không reload; submit form liên hệ lưu DB; ảnh upload hiển thị đúng

- [ ] **T8: Giao diện Bootstrap + Responsive** – Layout master Blade (`layouts/app.blade.php`, `layouts/admin.blade.php`) dùng Bootstrap 5; màu chủ đạo hồng-xanh theo phong cách gochoaxinh.com; mobile responsive kiểm tra 375px → Verify: Open DevTools, chọn iPhone SE, giao diện không bị vỡ layout

- [ ] **T9: Validation + Tối ưu** – Validation đầy đủ tất cả Form (FormRequest classes); AJAX search sản phẩm realtime (debounce 300ms); Tích hợp Google Maps iframe trang Liên hệ (API bên ngoài) → Verify: Submit form trống báo lỗi đúng field; search realtime gõ chữ không reload trang

- [ ] **T10: Kiểm tra cuối + Tài liệu** – Chạy `php artisan test`; screenshot tất cả trang; viết báo cáo ERD + UML Use Case (PlantUML) → Verify: Tất cả route hoạt động, ảnh chụp đủ trang User + Admin

---

## Done When

- [ ] CRUD hoàn chỉnh ít nhất 2 bảng có quan hệ (products ↔ categories, orders ↔ order_details)
- [ ] Login/Logout hoạt động với 2 role Admin + User, phân quyền đúng
- [ ] Tìm kiếm và phân trang sản phẩm hoạt động
- [ ] Validation đầy đủ tất cả form
- [ ] Eloquent ORM được sử dụng (không raw SQL)
- [ ] Có đủ Migration + Seeder
- [ ] Blade Template + Controller + Middleware đúng chuẩn Laravel
- [ ] Giao diện Bootstrap responsive, màu hài hòa
- [ ] Upload file ảnh hoạt động
- [ ] AJAX wishlist/search hoạt động không reload
- [ ] Tích hợp ít nhất 1 API bên ngoài (Google Maps)

---

## Ghi chú quan trọng

> [!NOTE]
> **Tiêu chí điểm từ bảng rubric:**
> - Tiêu chí 1 (4đ): CRUD + Auth 2 role + Search/Paginate + Validation
> - Tiêu chí 2 (2.5đ): Eloquent ORM + Migration/Seeder + Controller/Middleware/Blade
> - Tiêu chí 3 (1.5đ): Bootstrap/Tailwind + Responsive
> - Tiêu chí 4 (1đ): Báo cáo ERD/UML + thuyết trình
> - Tiêu chí 5 (1đ): Upload + AJAX + API ngoài

> [!TIP]
> **Cấu trúc thư mục đề nghị:**
> ```
> app/
>   Http/Controllers/
>     Admin/       ← ProductController, CategoryController, OrderController, PostController
>     Auth/        ← Laravel Breeze
>     CartController.php
>     WishlistController.php
>     ContactController.php
>   Models/        ← Product, Category, Order, OrderDetail, Post, Contact, Cart, Wishlist
>   Http/Requests/ ← ProductRequest, OrderRequest, ContactRequest
>   Http/Middleware/ ← RoleMiddleware.php
> resources/views/
>   layouts/       ← app.blade.php, admin.blade.php
>   user/          ← home, products, product-detail, cart, checkout, orders, wishlist, contact
>   admin/         ← dashboard, products/*, categories/*, orders/*, posts/*, users/*
> ```

> [!IMPORTANT]
> Project chạy trên **Laragon** → MySQL host: `127.0.0.1`, port `3306`, user `root`, pass rỗng.
> Virtual host Laragon: `http://shop-hoatuoi.test`
> Chạy `php artisan storage:link` để ảnh upload hiển thị đúng.
