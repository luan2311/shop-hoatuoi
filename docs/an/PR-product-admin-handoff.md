# Product lifecycle and Admin handoff

Base: `dev` / `1dc22d8`. Head: `feat/an-product-sales-lifecycle`.

Phạm vi: vòng đời ngừng bán/bán lại/xóa có điều kiện của Product; trang quản lý sản phẩm; Confirm Modal dùng chung; thống kê/tham chiếu danh mục; phân trang chung 6 danh sách Admin với 5/10/20/50/100 mục.

## Validation

- Trước commit: 19 PHP tests / 332 assertions PASS (`AdminPaginationTest`, `CategoryAdminUsageTest`, `AdminProductBrowseTest`), 4 JavaScript tests của Confirm Modal PASS.
- Browser evidence các nhiệm vụ nằm trong `docs/an/evidence/{an12,admin-product-ui,confirm-modal,category-ui,admin-pagination}`. Browser phân trang thực tế: 6 trang desktop 1440/mobile 375 PASS; chuyển trang, giữ lọc và đổi kích thước PASS.
- AN12 trước đó: focused 18 PASS / 261 assertions; full regression 214 tests: 180 PASS, 7 FAIL, 27 SKIP. 7 FAIL đã tái hiện tại base; không tuyên bố full suite hiện tại xanh. Chưa chạy lại full suite ở lượt tạo PR.
- Rà soát diff/file mới: không đưa `.env`, credentials, runtime MongoDB, vendor, node_modules, log runtime hoặc browser profile vào commit. Evidence lưu kết quả QA/fixture, không lưu token hoặc mật khẩu.

## Review requirements

- **MongoDB replica set bắt buộc**: Product reference lock và mutation dùng transaction; đã bỏ fallback chạy không transaction trên standalone.
- Review phối hợp các reference writers trong SalesService, WishlistController, ProductLifecycle và dữ liệu legacy. Product có `is_selling` tương thích bản ghi cũ và `lifecycle_revision` phục vụ transaction contention; không backfill dữ liệu demo hay chạy migration ở nhiệm vụ này.
- Category usage quét projection một lượt và từ chối xóa khi tham chiếu không xác định; chưa có MongoDB foreign key và không tuyên bố khóa mọi interleaving của category writers. Sắp xếp count thực hiện trên danh sách danh mục phía server.
- Các file shared cần chú ý: Product model, SalesService, WishlistController, HomeController, public ProductController, routes/admin.php, các product card/add-to-cart/wishlist view và 6 index controller/view Admin.
- Chỉ opt-in Confirm Modal cho Product và Category; không thay các modal nghiệp vụ khác. Không thay sidebar/topbar.

Các câu “chưa commit/push” trong báo cáo/bản chụp Git status trước đây là trạng thái lịch sử của từng lượt QA, không phải trạng thái sau handoff PR này.
