# FLOWER-FIX-003 — Voucher end-to-end

Ngày 30/10/2026. Phạm vi: voucher, tổng thanh toán; chỉ commit/push branch, không PR hoặc merge.

## Git và môi trường QA

- Base `origin/dev`: `bbc02120d075f86548fdc1deee8807231b939e7e`, đã fetch và ls-remote xác minh.
- Worktree riêng: `D:\Learning\OpenSourceProgramming\DA\shop-hoatuoi-fix-voucher`.
- Branch: `fix/checkout-voucher-total`.
- Bản restored trên 8080 và database demo không bị sửa. Không sửa Dashboard, live search, authentication hoặc Admin workflow.
- QA Laravel: http://127.0.0.1:8083; Admin phiên riêng: http://localhost:8083.
- MongoDB instance mới: `voucherRS:27042`, loopback, dbpath `.runtime/mongo-data` mới và rỗng trước khi khởi động. listDatabases ban đầu chỉ có admin/config/local.
- Database browser: `shop_hoatuoi_test_voucher_browser_20261030`, xác minh chưa tồn tại trước khi tạo; marker `_voucher_owner`, run FIX-003.
- PHPUnit dùng cấu hình ignored trỏ duy nhất vào instance mới và prefix mới. Fixtures tạo database riêng từng test. `KEEP_TEST_DB=true` ngăn teardown drop. Không dùng initializer drop, reset, wipe, migrate:fresh hoặc database của nhóm.
- Cài dependency theo lockfile; không thay dependency. Không thêm Boost vì yêu cầu chỉ sửa voucher.

## Root cause và regression trước sửa

Checkout cũ chỉ trừ tiền bằng JavaScript. Input không gửi mã voucher, OrderRequest không nhận mã, SalesService lưu tổng snapshot sản phẩm chưa giảm. Refresh mất giảm giá. VietQR độc lập hardcode amount=370000.

Trước sửa, test `test_cod_voucher_is_persisted_in_the_authoritative_order_total` FAIL đúng lỗi: actual 2100000, expected 2050000. Chromium trước sửa hiển thị 650000 -> 600000 và báo áp mã thành công hoàn toàn phía client (`before-voucher.jpg`).

## Chính sách đã xác minh và cách sửa

- Source checkout có `HOATUOI2026`: giảm cố định 50000 VND. Giảm tối đa subtotal, giữ tổng không âm như zero floor cũ và yêu cầu của task.
- Giữ `FREESHIP`: phí giao hàng hiện vốn bằng 0 nên giảm tiền thêm bằng 0; chuyển từ HOATUOI2026 sang FREESHIP xóa giảm tiền cũ.
- Không nhập mã: không giảm. Mã lạ: validation error. Giữ trim/uppercase như UI cũ. Không tự đặt minimum spend, expiry, usage limit hoặc điều kiện tài khoản mới.
- VoucherService dùng chung cho server quote và checkout. VND là integer; subtotal lấy từ giá MongoDB và lượng trong cart. Request subtotal/discount/total không được tin cậy.
- POST `/checkout/voucher` có auth/CSRF, trả code/subtotal/discount/total authoritative; session giữ mã hợp lệ qua refresh. Mã sai trả 422 và xóa giảm cũ.
- JavaScript chỉ hiển thị số server trả, không hardcode phép trừ. Chỉ gửi code. Chặn request áp mã trùng khi đang chờ và tạm khóa submit. Lỗi mạng không hiển thị success giả.
- Checkout xác minh lại mã và tính lại giá hiện tại trong transaction behavior đang có. Lưu `subtotal_amount`, `discount_amount`, `voucher_code`, `total_amount` cuối cùng; giá gốc từng item giữ nguyên snapshot.
- Retry checkout_key trả lại snapshot đơn cũ, không giảm lặp, không mua giỏ mới hoặc trừ kho lần hai.
- Success, history modal, User detail và Admin detail hiển thị snapshot; đơn cũ thiếu voucher fields vẫn render được.

## COD và VietQR

Giữ cả hai phương thức. COD thu total cuối cùng. VietQR hiển thị sau khi xác nhận, tại success và User detail, lấy amount từ order đã lưu thay vì cart preview có thể thay đổi. Checkout giữ Banking và thông tin ngân hàng, giải thích nơi xuất hiện QR.

Giữ nguyên bank ID, số tài khoản và accountName parameter của source. addInfo dùng order_code đã lưu; amount dùng total_amount. Hiển thị QR không đổi Pending thành Paid/Completed. Không tạo payment gateway hoặc cơ chế xác nhận thanh toán tự động. Chưa kiểm chứng quyền sở hữu tài khoản nhận/settlement; không chuyển tiền thật.

## MongoDB readback của browser

| Phương thức | Số lượng | Subtotal | Discount | total_amount | Status |
|---|---:|---:|---:|---:|---|
| COD | H01 x1 | 650000 | 50000 | 600000 | Pending |
| Banking | H01 x2 | 1300000 | 50000 | 1250000 | Pending |

Cả hai lưu voucher_code=HOATUOI2026, unit price=650000. Stock H01 từ 12 xuống 9 theo ba sản phẩm đã đặt; cart rỗng. Chỉ áp mã không đổi stock. URL và ảnh QR thực tế hiển thị amount=1250000, nội dung đúng order_code. Ví dụ 2100000 -> 2050000 có HTTP test và assertion đọc document MongoDB.

## Kết quả kiểm thử

| Kiểm tra | Kết quả |
|---|---|
| Regression trước sửa | Expected FAIL: 2100000 != 2050000 |
| Voucher feature + policy | PASS: 11 tests, 127 assertions |
| Voucher/Checkout/Sales/AdminOrder group | PASS: 35 tests, 417 assertions |
| Full PHPUnit | 201 tests: 167 PASS, 7 FAIL, 27 SKIPPED; 2211 assertions, 0 errors |
| Đối chiếu failing classes trên archived base | Cùng 7 failures: 60 tests, 53 PASS, 7 FAIL, 519 assertions |
| JavaScript suite | PASS: 15 tests, 0 failures/skips |
| PHP lint files đổi/mới | PASS |
| Pint files PHP/test/routes đổi/mới | PASS |
| Blade compile | PASS |
| Route list/cache/clear | PASS, endpoint voucher có web/auth |
| Frontend build | PASS, Vite 8.3.4 |
| Composer install | PASS, lockfiles không đổi |

Bảy failure được tái hiện độc lập trên source base lưu bằng git archive, dùng cùng locked dependencies và instance QA mới: FlowerDatasetTest manifest runtime asset (1), ProductModelTest URL ảnh (2), AdminDashboardTest heading (1), AuthenticationTest User redirects (3). Không thay assertion để làm xanh.

27 Product tests vẫn SKIPPED do opt-in và connection test hardcode port 27028; không đổi test ngoài scope hoặc trỏ sang database có sẵn. Không nhận full suite PASS.

Coverage voucher: mã đúng/sai/trống, áp hai lần, FREESHIP/removal, lượng/giá cart thay đổi, không âm, spoofed totals, COD persistence, hai tổng Banking/QR khác nhau, success/history/Admin detail, retry, rollback khi schema từ chối order, auth/CSRF. Existing Sales concurrency/no-oversell/cancel/restock tests PASS. BUG-001 fallback không đổi hành vi; Pint chỉ format exception import.

## Chromium và evidence

User login, thêm H01, checkout, AJAX mã sai/đúng, refresh; đặt COD và kiểm tra success/history/User detail/Admin detail độc lập đều đúng 600000. Giỏ mới H01 x2 tính lại 1250000; Banking confirmation và ảnh QR khớp, Pending giữ nguyên. Console warn/error thu được cho User/Admin rỗng. Không quét QR qua app thanh toán, không test transfer/callback.

Request quote/checkout được quan sát trong QA server log; response status/JSON/CSRF có feature tests. Công cụ browser không cung cấp raw DevTools Network export nên không tuyên bố có HAR.

Evidence local ignored trong `.runtime/`: voucher-before.txt, voucher-after.txt, sales-tests.txt, sales-junit.xml, full-phpunit.txt, full-junit.xml, baseline-phpunit.txt, baseline-junit.xml, js-tests.txt, pint-test.txt, php-lint.txt, blade.txt, routes-checkout.json, routes-cache.txt, build.txt, mongodb-readback.json, browser-console.json.

Ảnh trong `.runtime/screenshots/`: before-voucher.jpg, after-voucher.jpg, invalid-voucher.jpg, cod-success.jpg, order-history.jpg, user-cod-detail.jpg, admin-cod-detail.jpg, banking-success-qr.jpg. Mật khẩu QA chỉ ở `.runtime/QA-LOGIN.json`. Không stage runtime, credentials, sessions, database hoặc screenshots.

## Files và giới hạn

Business: SalesService, VoucherService mới, OrderRequest, Order Model, OrderController, sales routes. UI: checkout Blade, checkout-voucher.js mới, order detail partial, banking-qr partial mới, User history/detail và Admin detail. Tests: VoucherCheckoutTest, VoucherServiceTest, checkout-voucher.test.cjs.

Các lỗi base ngoài voucher, gồm unsafe transaction fallback và Admin status bypass, vẫn còn. Đây không phải nghiệm thu toàn website. Chỉ push nhánh `fix/checkout-voucher-total`; không tạo PR, không merge, không push dev.
