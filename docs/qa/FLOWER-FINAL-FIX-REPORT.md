# FLOWER-FINAL-FIX — Báo cáo nghiệm thu

Ngày 30/10/2026, UTC+7. **BUG-001…BUG-009 đã xử lý. READY_TO_MERGE=PASS cho các gate kỹ thuật đã kiểm; việc merge còn phải đối chiếu quyền/checks trên PR tại thời điểm tích hợp.** READY_TO_DEMO=YES cho COD và các luồng chính, với giới hạn ảnh tin cũ bên dưới. Không tuyên bố có payment gateway Banking hoặc voucher backend.

## Base và phạm vi Git

- Repository: https://github.com/luan2311/shop-hoatuoi ; đích `dev`.
- Base đã fetch lại trước sửa và sau regression: `675ff89d02966de95987a424821cfddf0472ff85`. Remote dev chưa có commit mới trong các lần đối chiếu trước commit.
- Working copy riêng: `D:\Learning\OpenSourceProgramming\DA\shop-hoatuoi-fix-all`, branch `fix/full-qa-issues`, xuất phát từ origin/dev. Khi tiếp tục lượt này, working copy đã có diff và evidence chưa commit; đã rà soát và kiểm thử lại, giữ nguyên các thay đổi đó.
- Không sửa/reset clone QA, Tiến04/Tiến05 hoặc local dev đang gắn với QA worktree. Không copy đè file hotfix cũ, force push, stash hoặc drop database cũ.
- Tài khoản GitHub hiện tại có quyền push, không có quyền admin. API xác nhận dev không protected, rules/rulesets rỗng; repository có 0 Actions workflows. Do đó **không có remote CI check để gọi là CI PASS**; local regression là bằng chứng kiểm thử. Phải kiểm lại các điều kiện này khi PR sẵn sàng merge.
- Bản ghi SHA push/PR/merge cuối được đối chiếu trên GitHub và báo trong kết quả tích hợp cuối; báo cáo này mô tả source và gate nghiệm thu trước tích hợp.

## Audit trước sửa

Đã đối chiếu BUGS.md, REPORT.md, TEST_MATRIX.md, HTTP/Mongo/browser evidence của `2026-10-30-dev-full-test`, báo cáo và readback retest `2026-10-30-675ff89-retest-20261030-111819`, source/tests và TIEN-05 report/patch. HEAD mới nhất cùng SHA baseline; cả 9 lỗi **VERIFIED_OPEN**, không có ALREADY_FIXED. Bảng chi tiết trước sửa: [FLOWER-FINAL-FIX-BEFORE.md](FLOWER-FINAL-FIX-BEFORE.md).

| ID | Root cause | Fix / files chính | Regression và verdict |
|---|---|---|---|
|BUG-001 P0|runTransaction bắt exception standalone và replay callback ngoài transaction|SalesService bỏ hoàn toàn catch/fallback, giữ transaction attempts=5; exception đi lên handler hiện có.|2 boundary tests: lỗi trước callback và lỗi sau callback bắt đầu, không replay; real replica rollback tại stock/order/cart, checkout retry, concurrent checkout/no oversell/cancel PASS. Standalone riêng từ lượt trước giữ stock/cart/orders nguyên vẹn qua 2 attempts. **FIXED**.|
|BUG-002 P0|POST complete gán status trực tiếp, bỏ SalesService state machine|Admin OrderController::markCompleted gọi transition; Blade orders/index chỉ hiện shortcut khi state cho phép Completed.|POST/PATCH Pending/Processing/Cancelled/Completed bị chặn; Shipped được phép; stock không trừ bổ sung. Race complete/cancel một winner, restock một lần PASS. Browser Pending bị chặn, Processing→Shipped→POST complete PASS. **FIXED**.|
|BUG-003 P1|Voucher JS giảm 50k nhưng chưa có policy backend|Checkout Blade gỡ controls/calculation/success giả; OrderRequest từ chối voucher/voucher_code. Không tự tạo chính sách mới.|Mã HOATUOI2026/INVALID bị422 không ghi dữ liệu. Request tự sửa total/discount bị bỏ qua; server snapshots đúng. Browser hai tổng800k/125k nhất quán. **DISABLED_SAFELY**.|
|BUG-004 P1|QR amount370000/recipient/nội dung và fallback host8000 cố định, chưa có payment contract xác minh|Checkout Blade gỡ Banking radio/QR/account; COD mặc định; OrderRequest chỉ nhận COD và thông báo Banking chưa hỗ trợ.|Banking422 không ghi/trừ kho; hai tổng khác nhau không render QR hay success thanh toán giả. Không đánh Paid/Completed do ảnh QR. **DISABLED_SAFELY**.|
|BUG-005 P1|Chỉ chặn tiền tố http/https; protocol-relative lọt qua|AuthenticatedSessionController kiểm local path sau giải mã nhiều lớp; chặn //,backslash,control,encoded variants,array; intended cùng origin chuyển về local path.|ReturnUrlSecurityTest matrix + Auth tests PASS; HTTP không follow external: Location fallback nội bộ cho4variants PASS. Local query/intended hợp lệ; Admin dashboard/User home giữ đúng role. **FIXED**.|
|BUG-006 P2|Card Tổng số đơn render completedOrders|Dashboard Blade dùng totalOrders đã có trong controller.|Mixed5states total5/completed1 và empty dataset PASS; browser trước completion5orders/0revenue, sau completion5orders/800k; metric không đổi sai. **FIXED**.|
|BUG-007 P2|Search IIFE trong head chạy trước form|layouts/app load search-realtime.js bằng defer, giữ một lần load.|4Node tests debounce/abort/late response/escape/XSS/empty/failure PASS. Chromium gõ hồng trả6links, Escape đóng, rỗng clear, no-result message, Enter tìm toàn trang PASS. Không thêm keyboard contract mới. **FIXED**.|
|BUG-008 P2|2image expectations/1dashboard heading cũ; User login vòng qua dashboard;27Product tests opt-in/SQL cũ|ProductModelTest dùng bundled /images có thật; AdminDashboardTest theo UI hiện tại; User login trực tiếp home. ProductTestCase tích hợp Mongo thật, actor persist; assertions giá nguyên VND là int, giá thập phân vẫn float.|Full217PASS/0FAIL/0SKIP; dedicated27PASS/260assertions. Không đổi model giá để né assertions. Seeder test bỏ assumption lỗi UNIQUE của SQL vì toàn ứng dụng dùng Mongo. Manifest runtime_exists là snapshot, không phải trạng thái upload ignored bất biến. **FIXED**.|
|BUG-009 P3|34files Pint lỗi whitespace/imports/braces|Pint theo chuẩn dự án, review diff; các file thuần formatting tách commit style, formatting trong file logic đi cùng bản sửa.|Pint toàn repo PASS, PHP lint147files PASS, diff-check PASS. Inventory34files gốc: flower-final-fix-evidence/pint-before.json. **FIXED**.|

Lỗi bổ sung được phát hiện khi mở Product suite: form Admin Product render old input dạng array gây500 sau validation. Ba form create/edit/partial dùng scalar-safe old input; regression `test_invalid_array_input_can_be_rendered_again_without_a_500` PASS. Browser create/upload/edit fixture riêng PASS. Không đưa thay đổi nghiệp vụ giá/kho ngoài yêu cầu.

## Database test và an toàn dữ liệu

Replica `tienRS` tại27018; main fixture DB `shop_hoatuoi_test_finalfix_20261030_1305` đã tạo riêng ở lượt sửa trước, kiểm tra rỗng trước import80products/5categories/3posts. Lượt tiếp tục chỉ tạo user/product/order/contact fixtures riêng, giữ dữ liệu cũ. Không dùng27017 cho checkout, không mongodb_init.js, migrate:fresh, truncate hoặc drop database cũ.

Hai helpers UsesMongoDatabase/UsesDungMongoDatabase tạo tên ngẫu nhiên mới, kiểm tra không tồn tại, ghi ownership token; cleanup giữ exact database object/name/token và chỉ cho phép drop đúng DB do chính test tạo. Guard khởi tạo theo từng TestCase instance. Toàn bộ các lượt regression này dùng **KEEP_TEST_DB=true**, không chạy cleanup drop. Product tests dùng helper chung, không còn opt-in27skips. Script cleanup cũ vốn xóa theo prefix đã chuyển sang inventory-only; `--drop` từ chối ngay trước connection, exit1 đã kiểm.

Mongo readback HTTP release: stock11→9 khi muaqty2→11 sau cancel; order/item subtotal250000, cart[], stateCancelled; repeated cancel và complete sau cancel422, dữ liệu không đổi. Browser đơn1 snapshot400000×2=800000, Completed sau workflow hợp lệ; đơn2 snapshot125000×1=125000, Pending. Tiền được lấy từ server, không lấy total/discount từ request.

Standalone27029 của lượt trước dùng dbpath mới trong.runtime; service thật + exception renderer503 qua2attempts giữ10/10stock,2cartlines,0orders. Process do lượt đó tạo đã dừng đúngPID; không tái chạy trên standalone đang được sử dụng. Không gọi bằng chứng này là browser/HTTP network.

## Kết quả cuối

| Gate | Thực chạy / kết quả |
|---|---|
|Full PHPUnit sau thay đổi cuối|**217 PASS /0 FAIL /0 ERROR /0 SKIPPED,2915assertions**, JUnit full-release.xml|
|Dedicated Product|**27 PASS /0 FAIL /0 SKIPPED,260assertions**, invocation riêng; không cộng đôi vào full suite|
|Sales/security|Transaction unavailable/no replay, rollback sau final cart-save, idempotency, concurrent checkout/oversell, cancel/restock, complete race và returnUrl PASS trong full suite|
|HTTP network regression mới|**44 PASS /0 FAIL**, cookies/session/CSRF419/RBAC403/owner404, COD/tamper/retry/voucher/Banking/workflow/restock/redirect/search; fixtures riêng|
|HTTP evidence lượt sửa trước|75/75PASS trên cùng application diff; giữ riêng http.json, không gộp với44thành một invocation|
|JavaScript|**12 PASS /0 FAIL**:4search +8address autocomplete|
|Composer|validate --strict và check-platform-reqs PASS; composer.json/lock không đổi|
|PHP lint|147files,0errors|
|Pint|PASS toàn dự án|
|Frontend build|PASS,Vite8; gọi local Vite CLI vì npm launcher hệ thống từng lỗi; không đổi lockfile|
|Laravel routes/Blade|110routes,0duplicate method/domain/URI; route cache/clear và view cache/clear PASS|
|Mongo|Writable replica/read-write, rollback/concurrency/nooversell/state machine/money snapshot PASS; giữ DB để review|
|Chromium thực tế|Core interactions PASS;18pages×4viewports=72settled views,0document overflow;2external inline article images404 là giới hạn nội dung bên dưới|
|Console/network|0warning/error trong captured browser logs;44HTTP checks và image DOM audit thực tế. Không có full HAR; không suy ra mọi request ngoài đều200|

Lượt bổ sung test callback ban đầu gặp lỗi signature Mockery với named argument attempts; đã sửa đúng signature, focused2testsPASS và full217tests chạy lạiPASS. Không thay application để né lỗi test. Một số screenshot đầu chụp trong animation0.7s/Chart resize là mẫu tạm; đã chụp lại sau load/animation, chỉ browser-responsive.json dùng sample cuối mỗi page/size.

## Browser coverage và giới hạn

Đã thao tác register/logout/login User/Admin, catalog filter/page2 giữquery, detail, wishlist AJAX/count và chuyển sangcart, qty update, live search tiếng Việt/noresult/empty/Escape/Enter, mobile menu, COD2đơn, success/history/detail, Admin dashboard/order rejected skip/hợp lệ/shortcutShipped, product create/upload/edit, contact submit thành công, news list/detail. Bốn kích thước1440×900,1280×800,390×844,375×812 áp dụng cho18pages được liệt kê trong browser.json. Screenshot viewport lưu private `.runtime/screenshots/resumed/`.

**Còn lại ngoài9bugs:**2ảnh inline trong bài tin mẫu TT002 tham chiếu CDN cũ trả404 ở cả HTTP/HTTPS. Cover, nội dung đọc và route hoạt động. Đây là lỗi nội dung/asset ngoài (P3), không phát sinh do bản sửa, không sửa/xóa dữ liệu tin đã tồn tại. Browser audit ghi nhận8broken-image observations (2URLs×4sizes), không ghi0brokenimages cho toàn website. Các trang catalog/product/cart/checkout/Admin đã đo không có broken images.

Search failure state được kiểm bằng Node tests, không fault-inject API trên browser. Không kiểm mọi route×viewport×state, load/performance, mọi browser, gửi email ngoài, thanh toán/chuyển tiền thật hoặc xác minh Banking. Voucher/Banking cố ý chưa hỗ trợ tới khi có backend policy và cấu hình người nhận hợp lệ. Các giới hạn này không che lỗi P0/P1 đã kiểm; không tuyên bố thanh toán live sẵn sàng.

## Evidence và demo

Evidence đã lọc trong `flower-final-fix-evidence/`: phpunit.json, products.json, http-release.json, mongo-release.json, browser-orders.json, browser.json, browser-responsive.json, standalone.json, lint.json, routes.json, pint.json/pint-before.json. Raw JUnit/logs/HTML/cookies/credentials/database runtime giữ ignored.runtime; không stage secrets hoặc.env. Các screenshot tiêu biểu: checkout-1440.jpg, cod-success-375.jpg, order-detail-1440.jpg, pending-complete-blocked-1440.jpg, shipped-complete-modal-1440.jpg, admin-upload-created-1440.jpg, admin-dashboard-390.jpg.

Website thực tế: **http://127.0.0.1:8050/**. Khởi động lại tại worktreefix bằng `.\.runtime\start-final-fix.ps1`; launcher dùng replica hiện có và.envlocal, không seed/reset/drop. Credentials fixture chỉ trong ignored.runtime/QA-LOGIN.json; không đưa vào report/Git. Khi clone mới, dùng cấu hình Mongo replica riêng và cài dependencies theo lockfile; không chạy initializer destructive.

**READY_TO_DEMO=YES cho COD/core flows với2ảnh tin cũ bị404 đã khai báo. READY_TO_SUBMIT=YES về kỹ thuật khi PR tích hợp được xác minh. READY_TO_MERGE=PASS các gate local; chỉ merge sau remote checks/mergeability/protection/head-SHA được xác nhận.** Nếu remote yêu cầu approval hoặc checks mới chưa đạt, giữ nhánh/PR và báo MERGE_BLOCKED; không bypass.

## Commits nghiệm thu

- `e13aaa8` style: apply Pint formatting
- `5dd2970` test: align contracts and strengthen regression coverage
- `8bb72bb` fix(ui): correct dashboard and realtime search
- `4787417` fix(auth): prevent unsafe return redirects
- `93fa69b` fix(checkout): resolve voucher and payment inconsistencies
- `1f7c547` fix(sales): enforce transaction safety and order workflow

Các commit trên chứa source đã kiểm thử. Commit docs tiếp theo chỉ chứa báo cáo và JSON evidence đã lọc. Secrets/path scan trước commit:75files,0findings. Không có workflow CI trên remote; không gọi absence-of-checks là CI PASS.
