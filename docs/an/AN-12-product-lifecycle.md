# AN-12 — Product lifecycle

Ngày 30–31/10/2026, UTC+7. **PARTIAL_AN_12**: nghiệp vụ AN-12 đã triển khai và có Feature/Browser QA; full regression còn các lỗi baseline và Product suite cũ bị skip. Chưa đủ điều kiện kết luận toàn nhánh xanh.

## Git và môi trường

- Thực hiện trong bản clone mới nhất theo yêu cầu tiếp nối: `D:/learning/MNM/shop-hoatuoi-dev`.
- Git sạch ở đầu lượt; đã fetch origin --prune. Tạo nhánh mới **feat/an-product-sales-lifecycle** trước khi sửa. Base **1dc22d8b99b2b04aa2e23bc410996850d0e1aee0** (origin/dev, merge PR #5). Không merge/rebase các nhánh thành viên.
- PR #2 đã merge: merge commit `c330ce37fbefa603ed358fc6ec37e1d0e0b01a12` là ancestor của origin/dev (exit 0). Product/Cart/Order/Wishlist đã có trong base; không thay đổi PR #2.
- QA riêng: replica **slideQA** tại 127.0.0.1:27030; web AN12 **http://127.0.0.1:8012**. Database browser `shop_hoatuoi_test_an12_browser_20261030`, khởi tạo sau kiểm tra không có QA users. Chỉ tạo category, 2 QA users và sản phẩm/đơn fixture AN12. Không dùng dữ liệu sản phẩm/đơn gốc của nhóm.
- Không thay .env, dependency/lockfile hoặc cấu hình PHP hệ thống. Dùng PHP ini ngoài repo và biến môi trường riêng theo tiến trình. Không migrate:fresh, db:wipe, initializer DROP, tổng DatabaseSeeder, commit/push/PR.

## Schema và quyết định tương thích

Product giữ `code`, `name`, `slug`, `price`, `stock`, `status`, `image`, `category_id` và snapshot `category {code,name,slug}`. `status` vẫn là Còn hàng/Hết hàng, stock độc lập với kinh doanh.

- Bổ sung optional **is_selling** boolean. Document cũ không có trường này được hiểu là đang kinh doanh; không suy ngừng bán từ stock=0. Dữ liệu boolean không hợp lệ không được bán.
- Bổ sung optional **lifecycle_revision** counter để các writer cùng tranh chấp trên Product document trong transaction. Đây là metadata kỹ thuật, không phải tồn kho.
- Không mass-assign hai trường mới từ form CRUD; lifecycle chỉ qua endpoint Admin riêng. Không backfill hoặc sửa validator của database đang dùng. Các JSON Schema hiện tại không đặt additionalProperties=false nên các trường optional này hợp lệ; không cần migration dữ liệu để kích hoạt. Các migration catalog/index cũ giữ nguyên. [Readback](evidence/an12/mongo-readback.json) ghi validator/index/count QA sau thao tác.
- Product tạo mới/cũ có stock=0 vẫn được Bán lại nhưng label Hết hàng và backend không cho mua. Nếu thao tác Ngừng bán cũ từng xóa stock về 0 thì không thể phục hồi con số cũ từ dữ liệu hiện tại; không tự đoán hoặc backfill stock.

Order thực tế lưu `items[] {product_code,product_name,product_slug,image,price,quantity,subtotal}`, customer nhúng, user_id BSON ObjectId (service hỗ trợ owner string cũ), trạng thái Pending/Processing/Shipped/Completed/Cancelled. AN12 không đổi snapshot hay state machine đơn hàng. Cart dùng items[].product_code/quantity. Wishlist dùng user_id + product_code.

Models CartItem/OrderDetail cũ còn tồn tại dưới dạng Eloquent mặc định; luồng Sales hiện tại không ghi các bảng rời này. Guard vẫn đọc MongoDB `cart_items` và `order_details` để bảo vệ các tài liệu legacy. Nếu default database khác mongodb, guard từ chối xóa vì chưa xác minh được nơi lưu reference legacy.

## Năm quy tắc đã triển khai

1. CRUD tên/mô tả/giá/kho/ảnh/danh mục và validation hiện có được giữ. Mã đã lưu trở thành bất biến để không làm đứt tham chiếu lịch sử; request đổi code trả 422. Cần nhóm thống nhất mọi thay đổi mã trong tương lai qua migration/contract riêng.
2. Ngừng bán chỉ ghi is_selling=false và metadata revision/time. Không đổi code/price/stock/image, không xóa document/file. Không dùng stock=0 làm trạng thái kinh doanh.
3. Có bất kỳ đơn hàng lịch sử liên quan đều chặn DELETE, không phụ thuộc status (kể cả Cancelled). Trả validation 422 hoặc redirect-back với thông báo: “Không thể xóa sản phẩm vì đã phát sinh đơn hàng. Bạn có thể chọn Ngừng bán để dừng kinh doanh sản phẩm này.”
4. Chỉ xóa khi không có Order/Cart/Wishlist/legacy reference và xác minh thành công. Cart/Wishlist còn reference thì từ chối, không cascade xóa dữ liệu thành viên khác. Sau commit xóa document, cleanup ảnh dùng helper sở hữu ảnh cũ: chỉ products/safe-name.jpg/jpeg/png/webp không còn Product khác tham chiếu; giữ shared/legacy files. Không Soft Delete/Lưu trữ.
5. Bán lại chỉ ghi is_selling=true và metadata; giữ nguyên định danh/giá/kho/ảnh. Kho=0 vẫn hiện Hết hàng và không mua được.

## Guard reference và concurrency

`ProductLifecycle::deletionReason` kiểm toàn bộ orders/order_details/carts/cart_items/wishlists. Nhận biết product_code, product_id (string/ObjectId), productId, MaSP và code trong item legacy; so sánh cả mã lẫn ObjectId, không phân biệt hoa/thường để bao phủ contract cart cũ. Không lọc đơn theo status. Shape thiếu/không hợp lệ hoặc lookup lỗi dẫn đến từ chối xóa. Không gọi SQL fallback để giả định không có đơn.

DELETE re-read Product và kiểm stale image/updated_at trong transaction, ghi revision lên Product, kiểm references lại rồi mới delete. Cart add/update và Wishlist toggle ghi cùng Product trong chính transaction tạo reference. Checkout đã decrement Product; bổ sung selling predicate vào decrement và kiểm available. Stop/resume ghi cùng document nên xung đột với các transaction đang mua/xóa. MongoDB write conflict được retry tới 5 lần.

Không còn fallback replay Sales callback ngoài transaction khi MongoDB standalone báo không hỗ trợ. Delete/Cart/Checkout/Wishlist yêu cầu replica set/sharded transactions; khi không xác minh an toàn, DELETE từ chối. Đây là thay đổi contract tối thiểu cần phối hợp Tiến/Huy, tránh một luồng không dùng transaction phá bảo vệ xóa.

**Giới hạn:** các writer của ứng dụng hiện tại tham gia protocol này. Raw Mongo scripts, import lịch sử hoặc service tương lai có thể bỏ qua lock; không được coi MongoDB có foreign key tự cưỡng chế. Nhóm phải giữ code bất biến và dùng cùng transaction/lock khi tạo reference mới. Các ca chạy đồng thời là kiểm chứng có giới hạn, không phải chứng minh mọi lịch interleaving. File cleanup sau commit không transactional cùng MongoDB; khi lỗi filesystem có thể cần đối soát file orphan. Unknown legacy documents có thể chặn xóa rộng hơn mong muốn; cần review riêng, không bypass guard.

Guard hiện quét reference để ưu tiên fail-closed và nhận biết legacy. Chi phí tỷ lệ dữ liệu; Admin list gọi cho từng dòng nên cần profiling/batch audit/index normalization nếu dataset lớn. Không cam kết latency hoặc đã tối ưu truy vấn cho production.

Hardening cuối: reference code/id không phân giải được tới Product hiện có cũng bị chặn (có thể là mã cũ trước khi đổi tên/mã). Các raw driver reads truyền MongoDB session tường minh khi đang trong transaction; lock từ chối chạy ngoài transaction. Focused tests đã chạy lại sau hardening. Full regression bên dưới được chạy trước bước hardening này, không tuyên bố đã chạy full lần nữa trên chính diff cuối.

## UI và hợp đồng thành viên

- Admin: Sửa, Ngừng bán/Bán lại theo is_selling; Xóa chỉ xuất hiện khi guard sơ bộ cho phép, backend luôn kiểm lại. Native confirm cho mọi lifecycle mutation, CSRF + auth + role:Admin. Hiển thị validation thân thiện.
- Giữ layout chung; bỏ modal cũ hứa đổi stock về 0 và CSS/JS chỉ phục vụ modal đó. Bảng responsive hiện có vẫn cuộn ngang; thêm rule nhỏ cho cột thao tác để lý do chặn xóa xuống dòng.
- **Tiến:** Cart add/update chặn stopped; checkout kiểm cả trạng thái và predicate atomic, giữ nguyên snapshot/idempotency/cancel/refund. Tài liệu đang còn trong lịch sử thì không xóa sản phẩm. Không cascade Cart.
- **Huy:** home/catalog/search/related chỉ liệt kê sản phẩm đang kinh doanh. Direct detail vẫn xem được sản phẩm stopped, label Ngừng bán và nút mua disabled. Wishlist được giữ để người dùng tự bỏ; nút mua stopped/sold-out disabled, việc thêm/bỏ reference chạy transaction. Không thay layout tổng thể.
- **Luân:** route resume mới dùng cùng middleware Admin; cần chốt chính sách mã bất biến và yêu cầu replica khi tích hợp.
- **Dũng:** Product test suite cũ và các assertion ảnh/UI/Auth baseline cần cập nhật riêng trước khi tuyên bố full regression PASS.

## Feature tests và bằng chứng

Test mới: `tests/Feature/ProductLifecycleTest.php`; worker chạy PHP độc lập: `tests/Fixtures/lifecycle-worker.php`. Database tên ngẫu nhiên mới `shop_hoatuoi_test_an12_<hex>`, kiểm không tồn tại trước khi tạo, giữ lại để review, không drop/truncate. Các fixture reference được xóa theo chính inserted _id chỉ trong test mới khi kiểm matrix. Full suite dùng config bên ngoài repo chỉ thay URI test sang replica QA và KEEP_TEST_DB=true; không cleanup database browser/review.

| Yêu cầu | Kiểm chứng |
|---|---|
| T01 | Admin edit name/price/stock; lifecycle state không bị ghi qua form |
| T02–03 | Stop/resume stock15, code/price/image không đổi |
| T04–06, T13 | Matrix 5 states × references code/case-insensitive/string id/ObjectId/MaSP; direct DELETE422 |
| T07 | Delete unreferenced; owned image cleanup; shared/legacy images giữ nguyên |
| T08–09 | Guest401, User403 cho cả delete/stop/resume; CSRF thiếu trả419 |
| T10 | Snapshot Order trước/sau stop/resume giữ nguyên |
| T11 | Stopped add/checkout rejected, orders không phát sinh, stock giữ nguyên |
| T12 | Resume stock0 vẫn active/Hết hàng |
| T14 | Unknown order shape + simulated lookup failure trả422; document còn |
| T15 | 3 cặp PHP processes đồng thời Delete với Cart add, Wishlist toggle, Checkout; không dangling refs trong kết quả quan sát |
| T16 | Feature tests riêng upload/replace/invalid price/delete; catalog15items →12/page, page2=3, filter, numeric sort và AJAX6results; JS tests |
| T17 | Legacy OrderDetail/CartItem/Wishlist và embedded Cart được chặn, dữ liệu phụ thuộc không bị cascade |

Kết quả chi tiết cuối cùng được lưu cùng evidence:

- [Focused AN12 + Wishlist](evidence/an12/focused-results.json).
- [Full regression](evidence/an12/regression-results.json), không thay bằng số AN-07/slide evidence cũ.
- [Baseline đối chứng](evidence/an12/baseline-results.json): 60 tests, 53 PASS, 7 FAIL tại commit gốc, tái hiện lỗi ảnh/manifest/dashboard/Auth.
- [JavaScript](evidence/an12/javascript-tests.txt): 15/15 PASS.
- [Platform](evidence/an12/platform.txt), [routes](evidence/an12/routes.json), [Git status](evidence/an12/git-status.txt).

Focused cuối **18 PASS / 261 assertions**, gồm 13 tests AN12 và 5 Wishlist. Full regression invocation cuối: **214 tests, 180 PASS, 7 FAIL, 27 SKIP, 2.447 assertions, 0 ERROR**, exit1. Không cộng hai invocation với nhau. 7 FAIL gồm manifest ảnh (1), image URL expectations (2), heading Dashboard (1), Auth redirect expectations (3), đã tái hiện đủ trên base. Chưa sửa các module này trong AN12.

Các lượt chuẩn bị test ban đầu đã sửa lỗi label null trên Product thiếu status và missing import trong test upload; focused chạy lại sau sửa. Không chỉnh production logic để né lỗi baseline, không gọi các lần chạy lỗi là PASS. Product suite cũ 27 tests opt-in vẫn skip trong full config; báo cáo không tính chúng là đã kiểm. Những test lifecycle/catalog/upload mới chạy MongoDB thật độc lập với actor SQL cũ.

## Browser QA

Chrome/Playwright có sẵn, context mới, không profile cá nhân. Runner ở `.an-runtime/an12`, chỉ fixture AN12 trên port8012/database riêng. [Browser results](evidence/an12/browser-results.json), [responsive](evidence/an12/responsive-results.json).

- Login Admin; tạo JPEG fixture qua HTML; stop giữ stock15/price350000/image; resume; delete thành công và Mongo readback product=null.
- Fixture thứ hai mua qua SalesService thật trong QA, tạo Pending order. UI không hiện nút Xóa, direct authenticated DELETE422. Không tạo order giả bằng ảnh/mock UI.
- Login User thật, GET admin403. Write methods trái quyền được kiểm bằng Feature tests.
- 1440/768/375: không document overflow; cuộn ngang trong `.table-responsive`, đã click Stop/Resume ở từng width. `actions-stopped-*` và `actions-resumed-*` chụp cột thao tác. Không tuyên bố mobile hiển thị đủ mọi cột cùng lúc.
- Lượt runner đầu chọn nhầm text nút tạo; lượt tiếp dùng code có underscore gây slug validation của base. Đổi fixture sang mã chữ/số hợp lệ rồi chạy lại thành công. `create-failure.png` là evidence lần validation lỗi, không dùng làm ảnh PASS.
- Product ordered được giữ để review: stock14 sau mua1 từ15, image còn, is_selling=true sau các lượt stop/resume; không sửa/xóa order fixture sau tạo. Snapshot invariance được kiểm riêng trong Feature test.

## File thay đổi và kết luận

Implementation: Admin ProductController; ProductController/HomeController; WishlistController; ProductRequest; Product model; SalesService; ProductLifecycle mới; routes/admin.php; admin/products/index và edit (code readonly); products/show; partials/product-card; components/add-to-cart; wishlist/index.

Tests: ProductLifecycleTest + lifecycle-worker mới. Documentation: báo cáo này và docs/an/evidence/an12. Không stage/commit/push/merge/PR. Nhánh dev vẫn giữ tại base, working branch feat/an-product-sales-lifecycle có thay đổi chưa commit.

**PARTIAL_AN_12** do full regression baseline chưa xanh và suite Product cũ chưa tương thích test config chung. Các quy tắc mới có Feature/Browser evidence, nhưng cần nhóm xử lý regression/test infrastructure và review protocol reference writers trước tích hợp. Không coi lỗi baseline là lỗi mới của An hoặc tự sửa module Auth/Dashboard/QA ngoài phạm vi.
