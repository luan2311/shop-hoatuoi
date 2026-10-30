# Chương 4 — Chức năng khách hàng tự hủy đơn

## Giới thiệu và điều kiện

Khách hàng có thể hủy đơn của chính mình từ lịch sử hoặc chi tiết đơn, qua modal xác nhận. Chỉ đơn **Pending, COD chưa thanh toán** được phép hủy. Processing, Shipped, Completed, Cancelled không được hủy. Nếu có dấu hiệu đã thanh toán (paid_at/is_paid/paid hoặc payment_status khác unpaid), backend từ chối.

Audit base cho thấy chưa có payment ledger/webhook xác minh chuyển khoản. Vì vậy toàn bộ Banking/VietQR Pending đều hướng khách liên hệ Admin; không coi việc hiển thị QR là chứng minh chưa thanh toán và không tự động hoàn tiền. Không đổi COD, VietQR hoặc chính sách voucher.

## Luồng xử lý và kiểm tra quyền

1. User đăng nhập, mở lịch sử/chi tiết đơn đủ điều kiện, bấm **Hủy đơn hàng**.
2. Modal hỏi “Bạn có chắc chắn muốn hủy đơn hàng này không?”, có Quay lại, Xác nhận hủy và lý do tùy chọn tối đa 200 ký tự.
3. Form POST có CSRF gửi `/account/orders/{order}/cancel`; submit bị khóa và hiển thị Đang hủy để hạn chế double-click.
4. Backend tìm đơn bằng authenticated user_id (hỗ trợ BSON ObjectId và legacy string); đơn người khác trả 404, guest bị chặn. Không tin status hoặc actor từ client.
5. Trong transaction, đọc lại đơn/điều kiện, dùng chung state machine và hoàn kho của SalesService; lưu Cancelled, cancelled_at, cancelled_by.type=Customer, user_id BSON và cancel_reason. Không xóa đơn, không đổi item/giá/voucher/tổng tiền.
6. Redirect về detail, thông báo thành công, badge Đã hủy và nút hủy biến mất. Nếu Admin vừa đổi trạng thái, trả validation error, trang hiển thị lỗi và không hoàn kho.

## MongoDB atomicity và thay đổi an toàn tối thiểu

Đã tái sử dụng logic hoàn kho bằng `applyTransition`, không tạo bộ logic stock riêng cho Customer. Thiếu sản phẩm hoặc lỗi schema khi lưu order làm rollback toàn bộ stock/status/metadata. Hai request hủy và Admin Processing chạy đồng thời phải có một kết quả hợp lệ duy nhất nhờ transaction write conflict/retry và đọc lại trạng thái.

Hai điểm base bắt buộc sửa để bảo vệ chức năng:

- Bỏ fallback của runTransaction chạy callback ngoài transaction khi MongoDB không hỗ trợ transaction. Helper dùng chung nay truyền exception ra ngoài; không success giả hoặc replay callback. Yêu cầu replica set cho các luồng Sales dùng helper. Không thử phá dữ liệu trên standalone; tests mock exception ở ranh giới transaction và kiểm tra không ghi/replay.
- Admin `markCompleted` trước đây save status trực tiếp, có thể ghi đè Cancelled sau khi hoàn kho. Nay gọi SalesService transition, chỉ Shipped -> Completed theo state machine sẵn có. Pending -> Completed và Cancelled -> Completed bị chặn. CSRF/RBAC giữ nguyên. Đây là thay đổi contract shortcut cần Luân review.

## Kiểm thử tự động

15 tests mới PASS, 101 assertions, 0 failures/errors/skips. Nhóm CustomerCancel/Sales/Cart/Checkout/AdminOrders: **44 PASS, 463 assertions**.

| Test case | Kết quả |
|---|---|
| Owner Pending COD -> Cancelled, stock hoàn đúng | PASS |
| Giá/items/subtotal/discount/voucher/total snapshot không đổi | PASS |
| User A không hủy được đơn B; legacy string owner hoạt động | PASS |
| Guest bị chặn; GET không đổi trạng thái; CSRF invalid 419 | PASS |
| Reason trên 200 ký tự bị từ chối; client status bị bỏ qua | PASS |
| Processing/Shipped/Completed/Cancelled không hủy | PASS, data provider 4 trạng thái |
| Hủy hai lần không hoàn kho hai lần | PASS |
| Hai Customer requests đồng thời | PASS, independent PHP processes |
| Admin Processing và Customer cancel đồng thời | PASS, một winner và stock đúng |
| Admin complete không ghi đè Cancelled; Shipped complete vẫn hợp lệ | PASS |
| Thiếu item thứ hai rollback partial restock | PASS |
| Order update bị schema từ chối rollback stock/metadata | PASS |
| Transaction unavailable không ghi hoặc success giả | PASS |
| Callback không replay ngoài transaction | PASS |
| Banking không rõ thanh toán và COD đã paid bị chặn | PASS |
| List/detail chỉ có nút khi đủ điều kiện | PASS |
| Existing Sales concurrency/no-oversell/checkout/Admin tests | PASS |

Full PHPUnit cuối: **205 tests, 171 PASS, 7 FAIL, 27 SKIPPED; 2185 assertions, 0 errors**. Không nhận full suite PASS. Bảy failure trùng chính xác evidence đã tái hiện trên archived base bbc0212 ở lượt voucher: dataset manifest (1), ProductModel image URLs (2), Admin Dashboard heading (1), Authentication redirects (3). Đã đối chiếu danh sách failure với evidence đó; không chạy lại archived base trong lượt này. Product 27 SKIPPED là opt-in/hardcoded test connection 27028; không sửa tests ngoài scope.

PHP lint các file đổi/mới, Pint trên PHP files đổi, Blade compile, route list/cache/clear, frontend build PASS. Không format toàn dự án hoặc sửa các lỗi base khác.

## Browser E2E và readback thực tế

QA riêng tại http://127.0.0.1:8085, Admin dùng http://localhost:8085 với cookie host tách biệt. User login -> thêm H01 -> checkout COD -> success -> lịch sử -> detail -> mở modal/Quay lại tại 375px -> modal và xác nhận tại 390px -> Cancelled, nút biến mất -> Admin login độc lập thấy Đã hủy. Console User/Admin warn/error thu được rỗng. Không chuyển tiền thật.

| Thời điểm | Order status | Stock H01 | Tổng đơn |
|---|---|---:|---:|
| Trước đặt | chưa có đơn | 12 | — |
| Sau checkout / trước hủy | Pending | 11 | 650000 |
| Sau hủy | Cancelled | 12 | 650000 |

MongoDB readback có cancelled_at và cancelled_by.type=Customer, lý do QA; item H01 quantity=1, price=650000 và subtotal=650000 giữ nguyên.

Ảnh local ignored, có thể chèn trực tiếp vào báo cáo Word (không chỉnh sửa Word trong lượt này):

- Trước hủy: `.runtime/screenshots/history-before.jpg`.
- Sau hủy: `.runtime/screenshots/history-after.jpg`, `detail-after-375.jpg`, `detail-after-390.jpg`.
- Modal: `.runtime/screenshots/modal-375.jpg`, `modal-390.jpg`.
- Admin hiển thị trạng thái đã cập nhật bởi khách: `.runtime/screenshots/admin-cancelled.jpg` (Admin không thực hiện một lần hủy thứ hai).

![Modal xác nhận mobile](../../.runtime/screenshots/modal-375.jpg)
![Lịch sử sau hủy](../../.runtime/screenshots/history-after.jpg)
![Admin thấy Cancelled](../../.runtime/screenshots/admin-cancelled.jpg)

Các ảnh nằm local trong worktree, không được publish lên GitHub; các link ảnh không khả dụng trên remote. Evidence JSON/JUnit/readback được giữ ignored: `.runtime/mongo-before.json`, `mongo-after.json`, `browser-evidence.json`, `browser-console.json`, `sales-final-junit.xml`, `full-final-junit.xml`.

## Git, môi trường và phạm vi review

Base: `bbc02120d075f86548fdc1deee8807231b939e7e`, fetch origin/dev trước khi tạo nhánh. Worktree mới `shop-hoatuoi-customer-cancel`; branch `feature/customer-cancel-order`. Không merge/cherry-pick voucher. Source voucher/QR/checkout giữ nguyên base, các lỗi voucher/QR cũ chưa được giải quyết trên nhánh này.

Replica set mới `cancelRS:27043`, dbpath mới `.runtime/mongo-data`, database browser `shop_hoatuoi_test_cancel_browser_20261030` được kiểm tra chưa tồn tại trước khi tạo, marker ownership `_cancel_owner`. Tests dùng instance/prefix riêng, KEEP_TEST_DB=true để không drop. Không đụng database restored/team; không reset/xóa worktree cũ. Thư mục untracked `docs/report/` trong restored đã có trước lượt này được giữ nguyên.

Dependencies theo lockfile; vendor được copy đọc từ môi trường có cùng lock và kiểm tra composer install --no-autoloader, npm ci --ignore-scripts. Không đổi dependency hoặc cài Boost ngoài phạm vi. Mật khẩu chỉ trong ignored `.runtime/QA-LOGIN.json`, không stage secrets/runtime/database/screenshots.

Luân cần review: điều kiện COD chưa thanh toán; việc từ chối Banking chưa xác minh; helper fail-closed dùng chung và shortcut Admin phải qua state machine. Nhánh độc lập có file chung với PR voucher #5, cần review/giải quyết tích hợp khi Luân chọn merge sau này. Lượt này chỉ commit/push feature branch, không tạo PR, không merge hoặc push dev.
