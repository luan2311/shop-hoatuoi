# AN-01 — Phạm vi và mapping

Ngày khảo sát: 26/10/2026. Base `origin/dev`: `3ffe4cb0a09be7b7372dee98bf5b0580c83d350f`.

## Phân công đã đọc

Nguồn: toàn bộ `docs/plans/2026-10-19-phan-chia-cong-viec-5-nguoi.md`, đặc biệt Task 2.1–2.3 và checklist Member 2. Theo thứ tự thành viên trong yêu cầu:

| Thành viên | Phạm vi |
|---|---|
| Luân (1) | Auth/role middleware, admin layout/dashboard, category CRUD |
| An (2) | Admin Product CRUD, upload/thay/xóa ảnh public, FormRequest, catalog 12/trang, lọc category id/slug, sắp xếp giá, chi tiết + 4 sản phẩm liên quan, AJAX search tối đa 6 kết quả/debounce 300 ms |
| Tiến (3) | Cart lưu DB, checkout, tồn kho khi đặt hàng, quản lý/history orders |
| Huy (4) | Layout user/trang chủ, wishlist AJAX, contact/maps, posts |
| Dũng (5) | Seed tổng thể/ảnh, test E2E toàn nhóm, ERD/UML/báo cáo/slide |

Tài liệu kế hoạch ghi Laravel 12/MySQL và nhánh develop; yêu cầu AN-01 ưu tiên Laravel thực tế/MongoDB và nhánh dev. Composer lock thực tế Laravel 13.37.0, PHP ^8.3, PHPUnit 12.5.40. Không nâng/hạ Laravel. Không chạy các lệnh commit minh họa của kế hoạch, không push/merge/PR.

## Mapping ASP.NET → Laravel

Source tham khảo riêng `../Shop-Flower-reference/ShopFlower/`, chỉ đọc.

| Chức năng An | Source ASP.NET MVC | Laravel target | Collection | Trạng thái khảo sát |
|---|---|---|---|---|
| CRUD/upload | Areas/Admin/Controllers/DashboardController.cs: QL_SanPham, CreateSanPham, EditSanPham, DeleteSanPham; Areas/Admin/Views/Dashboard/*SanPham.cshtml | Admin/ProductController, ProductRequest, admin/products/* | products | Chưa có controller/view Laravel |
| Catalog/filter/sort | Controllers/SanPhamController.cs: tat_ca_san_pham, bo_hoa_tuoi, ke_hoa_chuc_mung, gio_hoa_chuc_mung, hoa_cuoi, hoa_sap | ProductController@index, products/index | products, categories | Chưa có; thống nhất 12/trang theo Task 2.2 |
| Detail/related | SanPhamController: chi_tiet_san_pham, Views/SanPham/chi_tiet_san_pham.cshtml | ProductController@show, products/show | products | Chưa có; 404 thay redirect lỗi |
| Search | SanPhamController: Search (SQL SearchProducts), SearchAjax (Contains name/description/category) | ProductController@searchAjax, public/js/search-realtime.js | products | Chưa có; 6 kết quả theo Task 2.3, giữ tìm chuỗi con |
| Ngưng bán | DashboardController: StopSellingSanPham | Edit status/stock qua CRUD | products | Không thêm endpoint riêng ngoài phân công |

C# yêu cầu ảnh khi tạo, giá >0, stock >=0; kế hoạch Task 2.1 quy định giá min:0. Chọn giá >=0 theo nhiệm vụ đích, yêu cầu ảnh khi tạo, cho phép giữ ảnh khi sửa. Không mang sang cách đặt tên file theo upload hoặc trả exception cho khách. Giữ slug khi đổi tên để liên kết cũ hoạt động; mã sản phẩm do admin nhập theo kế hoạch.

## Mapping SQL → MongoDB

Đọc script người dùng `D:/learning/MNM/mongodb_init.js` (SHA256 BF54986396DB094DBDCE065A4B536DC461D38F1915FD419ECD0AEE5BC9AE6313) và script repo `database/mongodb_init.js` (khác hash). Cả hai là tài liệu dữ liệu, không thực thi: có drop collections và ghi cứng shop_hoatuoi.

| SQL | MongoDB | Quy ước |
|---|---|---|
| SANPHAM.MaSP | products.code | String, unique business key; khác `_id` ObjectId |
| TenSP/GiaBan/AnhSP/MoTaSP/TinhTrang/ThuongHieu/SoLuongTon | name/price/image/description/status/brand/stock | price numeric BSON, stock int, ảnh chuỗi đường dẫn |
| MaLoai → LOAIHANG | category {code,name,slug} | Snapshot nhúng theo script; lọc dùng category.code/slug |
| LOAIHANG.MaLoai/TenLoai | categories.code/name/slug | String business key, _id ObjectId, unique code/slug |
| Ngày tạo/sửa | created_at/updated_at | BSON dates do integration cast |

Giữ snapshot `category` bắt buộc theo validator script và thêm `category_id` dạng chuỗi ObjectId khi ghi mới để tích hợp quan hệ Eloquent. Seed cũ không có category_id vẫn lọc/detail được bằng snapshot. Không ép backfill hay đổi validator dữ liệu hiện có. Rename category cần Luân đồng bộ snapshot; category code là business key ổn định. Description seed có HTML, hiển thị escaped/plain text để tránh XSS. Seed ảnh chỉ là tên file, cần asset do Dũng cung cấp; hỗ trợ đường dẫn legacy `images/products` và `Images` nếu tồn tại, ảnh upload mới `products/*` trên public disk.

Script có unique products.code/slug; indexes category.code, category.slug+price, price, status và text name/description (language none). Tìm chuỗi con dùng regex literal escape thay vì text search đổi nghĩa; text index không tăng tốc regex này. Không tuyên bố tìm không dấu; cần Atlas Search riêng nếu dữ liệu lớn. users.roles là array chuỗi (`Admin`, `User`), còn collection roles có name/description/permissions; orders/carts có embedded items, wishlists tham chiếu product_code. Không thay schema/model các module đó.

Audit chỉ parse JSON product literals (không chạy script): 80 sản phẩm, 5 category codes LH001–LH005, không trùng code/slug, không thiếu code/name/slug/category.code, không có giá/stock âm hoặc stock không nguyên. Cả 80 description có HTML; 0 ảnh tương ứng trong public Laravel tại thời điểm khảo sát. Hash tài khoản trong script chưa được dùng hay coi là hợp lệ.

## Tái sử dụng, dependency và thay đổi dự kiến

Tái sử dụng Breeze auth, RoleMiddleware `role:Admin` đã đăng ký, x-app-layout/Tailwind/navigation hiện có, Storage public, routes web/auth, PHPUnit. Repo chưa có admin layout/category controllers hay routes con; dùng app layout cho view tối thiểu. Không xây layout chung mới. Auth/User/Role còn SQL: test product routes qua fake User implements hasRole, không chiếm module Auth MongoDB của Luân. Muốn đăng nhập MongoDB thực tế cần nhóm hoàn thành adapter User/Role.

File dự kiến: Product model; Category model tối thiểu để đọc MongoDB + code/description và giữ slug nhập; config/database.php (thêm connection, giữ default); composer json/lock (official integration cần thiết, không cài Boost theo ưu tiên user không thêm dependency không cần thiết); .env.example; routes/products.php + include web; controllers/requests sản phẩm; views products/admin/products + partial search; JS; Mongo migration riêng database/migrations/mongodb (không chạy SQL migrations trên Mongo); ProductDemoSeeder riêng; tests module + config test riêng; 3 tài liệu docs/an.

Điều chỉnh shared code sau khảo sát: `layouts/app` bọc navigation trong `@auth` vì navigation Breeze đọc user.name và gây lỗi cho guest; `bootstrap/app` đăng ký renderer lỗi DB chỉ cho route products; `DatabaseSeeder` chỉ sửa slice category/product (thêm code và snapshot, firstOrCreate) để seeder nhóm không hỏng sau chuyển model MongoDB. Giữ nguyên seed Auth/Post. Đã bổ sung test tích hợp seeder nhóm + login/logout/role SQL thực + đọc/sửa sản phẩm MongoDB; không chỉ dựa trên fake actor.

## Tiêu chí PASS

CRUD admin bảo vệ server/auth/role, validate unique code/slug và category hợp lệ, upload raster <=2 MB, thay/xóa ảnh an toàn sau ghi DB thành công, xử lý 404/duplicate/lỗi DB không lộ chi tiết. Catalog/filter/sort/query-string pagination 12, related <=4, search literal <=6 và JS debounce 300 ms chống kết quả cũ/XSS. Migration index không xóa dữ liệu, seeder rerun không ghi đè dữ liệu đã có. Test phải chạy trên MongoDB riêng có guard; SQL/Auth regression chạy suite sẵn có. Ghi rõ integration Auth còn phụ thuộc và visual/browser case chưa kiểm chứng; không PASS toàn task nếu còn tiêu chí bắt buộc chưa xác minh.
