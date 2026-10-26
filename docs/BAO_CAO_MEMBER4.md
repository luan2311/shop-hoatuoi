# Báo cáo Member 4 – Frontend UI, Wishlist, Liên hệ & Tin tức

Nhánh: `feature/user-ui-wishlist-contact`

| Commit | Nội dung |
|---|---|
| `5ddcdc0` | Task 4.1 – Master layout + trang chủ |
| `7f2ee38` | Menu theo giao diện Shop-Flower cũ |
| `78a2c12` | Task 4.2 – Wishlist AJAX |
| `6be52e5` | Task 4.3 – Liên hệ + Google Maps + Tin tức |

## 1. Kết quả kiểm tra

| Hạng mục | Kết quả |
|---|---|
| Test (`php artisan test`) | ✅ 40/40 pass |
| Route Member 4 | ✅ `/`, `GET/POST /contact`, `/posts`, `/posts/{slug}`, `/wishlist`, `POST /wishlist/toggle` |
| Responsive 375px (trang chủ, tin tức, chi tiết bài, liên hệ, yêu thích) | ✅ Không tràn ngang |
| Menu mobile (nút ☰) | ✅ Mở đủ 8 mục |
| Lỗi console trình duyệt | ✅ Không có |
| Ảnh, CSS, JS | ✅ Tải đủ, không có ảnh hỏng |
| Link nội bộ | ✅ Link của Member 4 đều chạy. Link `/products` (Member 2) và `/cart` (Member 3) chờ ghép nhánh |
| Thao tác thực tế | ✅ Bấm tim lưu vào MongoDB, trang yêu thích, gửi liên hệ (báo lỗi khi để trống, lưu khi hợp lệ), xem tin tức |

## 2. Đối chiếu `docs/plans/2026-10-19-phan-chia-cong-viec-5-nguoi.md`

**Checklist Member 4**

| Checklist | Đạt |
|---|---|
| Master Layout `layouts/app.blade.php` theo màu gochoaxinh.com | ✅ |
| Responsive iPhone SE 375px, không vỡ, không tràn ngang | ✅ |
| Wishlist AJAX đổi tim đỏ không reload, có số đếm trên navbar | ✅ |
| Liên hệ: validate form, lưu DB, nhúng iframe Google Maps | ✅ |
| Tin tức: danh sách + chi tiết | ✅ |
| `HomePageTest`, `WishlistAjaxTest`, `ContactAndPostTest` pass 100% | ✅ |

**Từng task**

| Task | File theo plan | Chức năng | Commit message theo plan |
|---|---|---|---|
| 4.1 Layout + trang chủ | ✅ | ✅ Top banner, header, navbar, footer, carousel, khối cam kết, sản phẩm bán chạy, tin tức mới | ✅ |
| 4.2 Wishlist AJAX | ✅ | ✅ `toggle` trả `{status, count}`, Fetch API kèm CSRF, trang `/wishlist` | ✅ |
| 4.3 Liên hệ + Tin tức | ✅ | ✅ `ContactRequest`, lưu DB, thông báo gửi thành công, form trái – bản đồ phải, tin tức có ảnh bìa và ngày đăng | ✅ |

**Phân chia thư mục:** view đặt đúng chỗ (`layouts/app.blade.php`, `contact.blade.php`, `posts/`, `wishlist/`), controller trong `app/Http/Controllers/`. ✅

## 3. Đối chiếu `shop-hoatuoi-plan.md`

| Mục | Phần của Member 4 | Đạt |
|---|---|---|
| Database | `wishlists { user_id, product_code, created_at }`, `contacts { code, full_name, email, phone, message, status }`, `posts { code, title, slug, thumbnail, summary, content, published_at }`, `products.category` nhúng | ✅ |
| T5 – Tin tức | TT001–TT003 hiển thị ở trang chủ và trang tin tức, có ảnh bìa, ngày đăng | ✅ |
| T7 – Wishlist + Liên hệ | Wishlist dùng Fetch API không reload; liên hệ validate bằng FormRequest, lưu collection `contacts` | ✅ |
| T8 – Giao diện | Bootstrap 5, layout master, responsive 375px | ✅ |
| T9 – Validation + API ngoài | Gửi form rỗng báo lỗi đúng ô; nhúng Google Maps | ✅ |
| Done When | Bootstrap responsive, AJAX wishlist, Google Maps, validation form liên hệ | ✅ |
