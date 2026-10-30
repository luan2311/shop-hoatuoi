# Admin pagination

Áp dụng cho 6 danh sách: sản phẩm, hóa đơn, tài khoản, tin tức, liên hệ, danh mục.

- Thanh phân trang dùng chung: khoảng bản ghi/tổng số, Trước/Sau, số trang và chọn 5/10/20/50/100 mục (mặc định 10). Vẫn hiển thị khi chỉ có một trang hoặc danh sách rỗng.
- Chuyển trang giữ các query tìm kiếm/lọc/sắp xếp. Đổi số mục bỏ `page` để về trang 1. Form lọc giữ `per_page`; Đặt lại trở về mặc định.
- Server chỉ chấp nhận các kích thước cho phép và số trang nguyên dương. Sắp xếp số lượng danh mục dùng cùng kích thước với nhánh paginate của MongoDB.
- Mobile thu gọn thành Trước / trang hiện tại / Sau; tổng số và selector xuống dòng.

Implementation: `app/Support/AdminPagination.php`, component `resources/views/components/admin/pagination.blade.php`, view `resources/views/admin/partials/pagination.blade.php`, `public/css/admin-pagination.css`, 6 index controller và 6 index Blade tương ứng. Cập nhật hai assertion UI của `AdminProductBrowseTest` theo markup mới.

## Verification

- PHPUnit tập trung: `AdminPaginationTest|CategoryAdminUsageTest|AdminProductBrowseTest`: **19 PASS, 332 assertions** ở lượt trước PR, bao gồm lựa chọn 5 mục/trang. Database mới có tên ngẫu nhiên `shop_hoatuoi_test_*`, không chạy migration hoặc cleanup trên database demo.
- 6 danh sách đều HTTP 200, selector hoạt động, desktop 1440px và mobile 375px PASS qua Chrome/Playwright.
- Kiểm tra thực tế trang 2 sản phẩm, giữ từ khóa/sort, đổi 10 sang 20 về trang 1 và form tìm kiếm giữ 20 PASS.
- Không có request ghi nghiệp vụ trong lượt browser; chỉ đăng nhập và GET. Không thay schema, dependency, source các nghiệp vụ CRUD, hoặc dữ liệu demo. Không commit/push.
- `git diff --check`: PASS.

Bằng chứng: [tests](evidence/admin-pagination/tests.json), [browser](evidence/admin-pagination/browser.json), [desktop](evidence/admin-pagination/products-desktop.png), [mobile](evidence/admin-pagination/categories-mobile.png). Đây là kiểm thử tập trung, không phải full regression.
