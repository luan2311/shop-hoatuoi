# Admin — Nâng cấp trang Quản lý sản phẩm

Ngày 31/10/2026 (UTC+7). **Hoàn thành phạm vi UI và browse Admin Product**, với các giới hạn kiểm thử nêu dưới. Không commit/push.

## Khảo sát và phạm vi Git

Working directory `D:/learning/MNM/shop-hoatuoi-dev`, branch `feat/an-product-sales-lifecycle`, HEAD/base `1dc22d8b99b2b04aa2e23bc410996850d0e1aee0`. Lúc bắt đầu đã có thay đổi AN-12 chưa commit; giữ nguyên và phát triển tiếp view/index của Product. Không coi toàn bộ git diff tích lũy là thay đổi của nhiệm vụ UI.

Laravel 13.37, PHP 8.3.30, MongoDB Eloquent, Blade, Bootstrap 4.5.2 và jQuery/Popper có sẵn trong `layouts/admin.blade.php`. Route hiện có: GET `/admin/products`, controller `Admin\ProductController::index`, models `Product`/`Category`, view `admin.products.index`. Sidebar/topbar/layout Admin không chỉnh sửa.

Product dùng code/name/price/stock/status/image/created_at, embedded category và category_id legacy. `is_selling` và `sale_label` đã tồn tại từ AN-12 trước nhiệm vụ này. Đang bán = missing/null/true is_selling; Ngừng bán = những giá trị còn lại theo contract model; Hết hàng = **stock bằng 0**, độc lập với kinh doanh. Không dùng stock=0 để suy ra Ngừng bán. Giá trị status Hết hàng với kho dương vẫn được hiển thị theo label hiện hữu, nhưng không được tự cộng vào counter kho=0.

## Đã triển khai

- 4 thẻ thống kê đọc database toàn bộ, không phụ thuộc bộ lọc hoặc trang hiện tại. Nhóm có thể chồng lấn; phần chú thích ghi rõ. Tại lượt chụp thật: 81 tổng, 81 đang bán, 0 ngừng bán, 0 hết hàng; không lấy số 120/95/18/7 từ mockup.
- Search tên/mã, substring literal không phân biệt hoa/thường, escape regex. Giữ alias q/searchString/query; khả năng tìm trong mô tả/danh mục trước đây vẫn giữ qua scope matchingKeyword, bổ sung mã trong cùng nhóm OR.
- Dropdown danh mục từ MongoDB, sale_state, 7 cách sort whitelist. Timestamps hiện có dùng cho Mới nhất; `_id` là thứ tự phụ ổn định. Sort numeric trên BSON price/stock, không sort chuỗi định dạng tiền.
- Category filter ưu tiên snapshot code ổn định; fallback category_id string/ObjectId chỉ khi không có category.code, tránh một category_id cũ mâu thuẫn snapshot làm sai kết quả. Eager-load categoryRecord cho fallback hiển thị, không query live category cho từng dòng.
- Pagination Laravel **10/page** trên server, giữ query string; dòng Hiển thị first–last/tổng, Trước/Sau/số trang, empty state rõ và reset về URL sạch.
- Bảng giữ 6 cột, ảnh 64px, tên đậm/xuống dòng, code nhỏ, badge theo sale_label. Màu Sửa/search xanh dương, stop/resume vàng, delete đỏ, create xanh lá. Stats trắng/viền nhẹ/icon nhạt, không gradient.
- Lý do không xóa lấy nguyên kết quả ProductLifecycle, hiển thị qua button info + Bootstrap popover plaintext. Focus bằng Tab, hover/click/touch để mở, Escape/outside click để đóng. Không render đoạn dài trong dòng bảng. aria-label chứa lý do đầy đủ.
- Xem nhanh bằng modal: ảnh/tên/mã/giá/category/stock/trạng thái kinh doanh và tồn kho; dữ liệu từ 10 models đã query, không route/API mới. Blade escape payload trong attribute, JavaScript dùng textContent, không innerHTML dữ liệu sản phẩm. Ảnh lỗi có placeholder.
- CSS/JS chỉ include tại trang Product; preview CSS giới hạn theo modal của trang. Mobile filter xuống hàng, stats 2×2, bảng cuộn ngang trong table-responsive; empty state không bắt người dùng cuộn ngang mới đọc được.

## Giữ nguyên nghiệp vụ và dữ liệu

Không thay store/update/destroy/stopSelling/resumeSelling hoặc ProductLifecycle/SalesService trong nhiệm vụ UI. Các thay đổi còn xuất hiện ở các file này trong Git là của AN-12 trước đó. Không sửa Product/Category model, migration, schema, routes hoặc API contract mutation. Chỉ thêm các query params GET tùy chọn cho browsing Admin. Request riêng AdminProductBrowseRequest mở rộng validator hiện hữu, nên public catalog không bị mở thêm whitelist sort Admin.

Guard sơ bộ được chuyển từ Blade về controller để view chỉ render lý do. Backend DELETE vẫn tự kiểm điều kiện trong transaction theo AN-12, có confirmation và CSRF, không cascade Cart/Wishlist/Order. Không tạo dữ liệu mẫu vào database demo; browser không gọi các mutation Product để thử nút. Mutations được regression test trên database test ngẫu nhiên riêng.

Các kiểm tra ProductLifecycle theo từng dòng vẫn quét reference như trước; chưa batch lại vì không thay chính sách xóa trong nhiệm vụ này. Với dữ liệu lớn, đây vẫn là chi phí cần profiling. Eager loading chỉ giải quyết category fallback, không tuyên bố đã loại bỏ mọi query lặp của guard.

## Kiểm thử thực tế

| Nhóm | Kết quả |
|---|---|
| AdminProductBrowseTest + ProductLifecycleTest + WishlistAjaxTest | **24 PASS / 344 assertions**, [PHP](evidence/admin-product-ui/php-tests.json) |
| JavaScript hiện hữu | **15 PASS / 0 FAIL**, [log](evidence/admin-product-ui/javascript-tests.txt) |
| Chrome thật/Playwright | [browser-results](evidence/admin-product-ui/browser-results.json) |
| Responsive | 1440/768/375 không tràn document; bảng cuộn cục bộ, modal vừa màn hình |
| Filter/sort | Case-insensitive code/name, category + state + search + price sort, page2 giữ toàn bộ params, reset sạch, dropdown submit |
| Thống kê | Đối chiếu Mongo driver độc lập; Feature tests kiểm cả nhóm overlap (14 tổng/12 selling/2 stopped/2 stock0) và stats không đổi khi lọc |
| Security/validation | Invalid sort/state/category_id/q-array/page trả422; literal regex không wildcard; routes auth/Admin và CSRF mutations giữ nguyên |
| Lifecycle regression | Sửa/upload/replace/validation, stop/resume giữ kho, xóa an toàn hoặc bị chặn, legacy references, concurrency, Guest/User/CSRF đều nằm trong nhóm PHP PASS |
| Isolation trang khác | GET Categories/Dashboard 200; không include CSS/JS Product; layout file giữ nguyên |
| Network browser | Chỉ GET/HEAD và POST login; runner chặn mọi request ghi nghiệp vụ nếu phát sinh, không có request bị chặn |
| Console | Không có JS pageerror ở lượt thành công, [console](evidence/admin-product-ui/console.json) |
| Formatting/whitespace | Pint cho PHP liên quan và git diff --check PASS |

Không chạy lại full suite toàn nhóm trong nhiệm vụ UI này. Báo cáo AN-12 ghi riêng baseline 7 FAIL/27 SKIP; không dùng 24 tests liên quan để tuyên bố toàn dự án xanh. Unit/Feature fixtures chỉ tạo trong database tên random `shop_hoatuoi_test_admin_ui_*` hoặc helper AN12/test hiện hữu, có kiểm DB mới, giữ lại để review, không reset/drop database demo.

Browser dùng source thực đang chạy tại `http://127.0.0.1:8009/admin/products`, database QA `shop_hoatuoi_test_slides_20261030`, 100% zoom/deviceScaleFactor1, context mới. Không thêm sản phẩm/danh mục/đơn giả để có trạng thái đẹp trong ảnh. Nhóm stopped/stock0 bằng 0 trong demo được kiểm bằng empty state; positive states/overlap được Feature test trên DB test riêng.

Trong lượt đọc đầu, hash Product có thay đổi khi server demo vẫn đang mở cho thao tác khác; không có cơ sở quy nguyên nhân chỉ từ snapshot đó. Ở lần đối chiếu ngay sau lượt read-only đầu, các fingerprint khớp; snapshot chốt muộn hơn lại ghi nhận Product/Cart đổi hash dữ liệu, trong khi counts và schema hash vẫn giữ nguyên. Vì server demo có thể được thao tác đồng thời, không kết luận dữ liệu bất biến xuyên suốt nhiệm vụ, không quy nguồn ghi khi chưa có audit log. [data-before](evidence/admin-product-ui/data-before.json), [data-after](evidence/admin-product-ui/data-after.json) và [comparison](evidence/admin-product-ui/data-comparison.json) ghi đúng snapshot chốt. Browser runner chỉ gửi GET/HEAD và POST login, chặn mọi business mutation; source GET mới không có thao tác ghi. [requests](evidence/admin-product-ui/requests.json) không chứa body, credentials hoặc password hash.

Lượt Browser đầu assert URL ngay sau select trước khi navigation hoàn tất; đã đổi runner chờ load/URL và chạy lại thành công. Đây là sửa đồng bộ runner, không sửa backend để né lỗi. Screenshot chờ animation/font/image ổn định; không chỉnh pixel hoặc tạo mockup thay website.

## Screenshot

- [Desktop](evidence/admin-product-ui/listing-1440.png), [Tablet](evidence/admin-product-ui/listing-768.png), [Mobile](evidence/admin-product-ui/listing-375.png).
- [Popover bàn phím](evidence/admin-product-ui/reason-keyboard.png), [Preview mobile](evidence/admin-product-ui/preview-375.png).
- [Filter kết hợp](evidence/admin-product-ui/combined-filters.png), [Empty mobile](evidence/admin-product-ui/empty-mobile.png).
- [Manifest URL/viewport/thời gian](evidence/admin-product-ui/screenshots.json).

## File của nhiệm vụ này

1. `app/Http/Controllers/Admin/ProductController.php`: chỉ mở rộng index.
2. `app/Http/Requests/AdminProductBrowseRequest.php`: mới, validation browse Admin.
3. `resources/views/admin/products/index.blade.php`: UI trang.
4. `resources/views/admin/products/partials/pagination.blade.php`: mới, pagination tiếng Việt.
5. `public/css/admin-products.css`: mới, CSS scoped.
6. `public/js/admin-products.js`: mới, modal/popover và dropdown GET.
7. `tests/Feature/AdminProductBrowseTest.php`: mới, 6 tests bao phủ các trường hợp browse.
8. Báo cáo này và `docs/an/evidence/admin-product-ui/*`.

[Git status cuối](evidence/admin-product-ui/git-status.txt) bao gồm cả thay đổi AN-12 trước đó. Không commit/push, không cài thư viện, không chỉnh sidebar/topbar/trang Admin khác, không thêm/sửa migration hoặc field database trong nhiệm vụ UI.
