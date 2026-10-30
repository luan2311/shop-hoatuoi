# ConfirmModal dùng chung cho Admin

Ngày 31/10/2026 (UTC+7). **PASS — phạm vi Thêm/Sửa/Xóa/Ngừng bán/Bán lại của Product.** Chưa commit/push/PR.

## Phạm vi và bảo vệ WIP

Repository `D:/learning/MNM/shop-hoatuoi-dev`, branch `feat/an-product-sales-lifecycle`, HEAD `1dc22d8b99b2b04aa2e23bc410996850d0e1aee0`. Trước sửa đã kiểm Git status/diff, sao lưu ba view Product và tracked WIP patch trong `.an-runtime/confirm-modal/before` ngoài repo. Giữ các thay đổi AN-12 và Admin browse UI đang có.

Khảo sát thấy Product index dùng onsubmit/confirm cho stop/resume/delete; create/edit dùng form POST thông thường. Bootstrap 4.5.2, jQuery và Popper đã có trong layout. Không thêm UI library hoặc đổi layout. Các module Category/Contact/Post còn native confirm và Users/Orders có modal riêng: không chuyển chúng trong nhiệm vụ này. Alert thông báo chọn file không phải ảnh cũng giữ nguyên vì không thuộc xác nhận mutation.

## Component và tích hợp

`resources/views/components/admin/confirm-modal.blade.php` là anonymous Blade component, dùng `@once` + stacks để mỗi trang chỉ render một modal. Ba view Product cùng include `<x-admin.confirm-modal />`; các trang Admin khác không include component và không bị intercept.

Các form dùng metadata title/message/type/confirmText/cancelText/subject, không eval JavaScript trong attribute. Một form-scoped submit listener xử lý toàn bộ năm thao tác. Nội dung hiển thị qua textContent; Bootstrap giữ focus trong modal. Khi đóng, trả focus về control đã mở. Mặc định focus vào Hủy để tránh Enter vô tình chấp thuận thao tác nguy hiểm.

| Thao tác | Tiêu đề | Loại/màu | Nút xác nhận |
|---|---|---|---|
| Thêm | Xác nhận thêm sản phẩm | success/xanh lá | Thêm sản phẩm |
| Sửa | Xác nhận lưu thay đổi | primary/xanh dương | Lưu thay đổi |
| Xóa | Xác nhận xóa sản phẩm | danger/đỏ | Xóa sản phẩm |
| Ngừng bán | Xác nhận ngừng bán | warning/vàng | Ngừng bán |
| Bán lại | Xác nhận bán lại | success/xanh lá | Bán lại |

Delete/stop/resume hiển thị thêm tên và mã từ chính dòng sản phẩm. Form create chỉ mở sau HTML validity; khi xác nhận kiểm lại validity. Backend vẫn validate lại như trước.

Thiết kế modal trắng, radius14px, icon tông nhạt, shadow nhẹ, backdrop Bootstrap tối, không gradient/neon; max dialog460px (content khoảng436px), co giãn mobile. Nút X/Hủy luôn type=button. Không thay CSS/sidebar/topbar toàn site.

## Submit, validation và fail-closed

- Hủy/X/Esc/backdrop trước submit chỉ đóng modal, không request, không reset form.
- Khi đang xử lý: khóa nút confirm/cancel/X và chặn hide/Esc, giữ pending trên form để chặn submit lặp.
- Product dùng `data-confirm-transport="fetch"`, **vẫn POST tới action cũ** với FormData: CSRF, `_method=PUT/DELETE`, file và submitter được giữ. Controller đã có JSON success/message/redirect, không sửa backend để hỗ trợ modal.
- Chỉ điều hướng khi nhận success=true và redirect cùng origin. Không tạo success toast trước/sau lỗi hoặc suy thành công từ HTTP200 HTML.
- Lỗi422/503/network giữ modal với thông báo lỗi plaintext, mở lại controls để người dùng sửa/thử lại; không tự retry. Text/select/file vẫn nằm trong form DOM, không mất file sau validation. Không dùng HTML navigation ở các form Product để tránh mất file khi reload.
- Script vẫn hỗ trợ native form mode mặc định cho nơi tái sử dụng cần navigation chuẩn, thông qua requestSubmit để không bỏ qua validity/submit listeners. Product không dùng mode đó.
- Khi JS/Bootstrap/script component không sẵn sàng, **fieldset chứa cả inputs/CSRF/_method/submit vẫn disabled từ HTML**. Chỉ gỡ disabled sau khi cài đầy đủ interception. Notice hướng dẫn bật JS/tải lại được giữ nếu chưa sẵn sàng. Không fallback sang submit mutation không xác nhận.
- pageshow từ BFCache giải phóng pending và trạng thái disabled cũ. Không reset pending do pageshow ban đầu trong lúc request đang chạy.

Xác nhận là UX phía client, không thay thế Auth/Role/CSRF/validation/ProductLifecycle tại backend. Không thêm token/field xác nhận vào Product document. Nếu mạng mất sau khi server đã commit nhưng trước response thì không thể suy trạng thái commit từ UI; người dùng cần kiểm tra dữ liệu trước khi retry. Không tuyên bố UI tạo idempotency cho endpoint create hiện hữu.

## Tái sử dụng

Include component trên trang muốn opt-in. Với form JSON endpoint đã có, dùng mẫu:

```blade
<x-admin.confirm-modal />
<form method="POST" action="{{ $existingAction }}"
      data-confirm-form data-confirm-transport="fetch"
      data-confirm-type="danger"
      data-confirm-title="Xác nhận thao tác"
      data-confirm-message="Bạn có muốn tiếp tục?"
      data-confirm-text="Xác nhận" data-cancel-text="Hủy">
    <fieldset data-confirm-gate disabled>
        @csrf
        {{-- Giữ @method(...) hiện hữu nếu có --}}
        <button type="submit">Thực hiện</button>
    </fieldset>
</form>
```

Hoặc callback JavaScript tin cậy, không eval từ dữ liệu server:

```js
const confirmed = await window.AdminConfirm.open({
    title: 'Xác nhận thao tác',
    message: 'Bạn có muốn tiếp tục?',
    type: 'primary',
    confirmText: 'Xác nhận',
    cancelText: 'Hủy',
    onConfirm: async () => { /* callback hiện hữu; throw/reject khi thất bại */ }
});
```

API còn có options.loading và AdminConfirm.setLoading(boolean). Promise trả false khi hủy, true sau callback thành công/đóng; với form navigation, trang mới tiếp quản thay vì chờ Promise trong document cũ. Chỉ cho một context hoạt động tại một thời điểm. Không tự động áp dụng cho form chưa có data-confirm-form.

## Kết quả kiểm thử

| Kiểm tra | Kết quả |
|---|---|
| AdminProductBrowseTest + ProductLifecycleTest + WishlistAjaxTest | **25 PASS / 375 assertions**, [PHP](evidence/confirm-modal/php-tests.json) |
| JavaScript | **19 PASS / 0 FAIL**: 15 cũ + 4 tests component (fail closed, cấu hình/text/focus, double-click/loading, callback reject/reopen), [log](evidence/confirm-modal/javascript-tests.txt) |
| Browser Chrome thật | **19 case PASS**, [results](evidence/confirm-modal/results.json) |
| Responsive | Mỗi popup trong 5 thao tác ×1440/768/375, không tràn document/modal |
| Hủy/X/Esc/reopen | Không có mutation request; input và file giữ nguyên; focus về trigger |
| Frontend invalid | Không mở confirm, không request |
| Backend422 | Mã sai qua HTML-valid form bị backend từ chối; modal hiện lỗi, text và file giữ nguyên |
| Create double-click | Một POST thực; nút loading, Hủy/X/Esc không đóng khi đang gửi |
| Sửa | Hủy không ghi; confirm đổi tên/price/stock đúng, ảnh cũ còn |
| Stop/resume | Đổi is_selling đúng, price/stock/code/image giữ nguyên |
| Xóa | Hủy không request; confirm xóa fixture và owned image qua endpoint cũ |
| Order guard | Direct authenticated DELETE tới fixture có Pending order vẫn422, document giữ nguyên |
| Server error | 503 mô phỏng trước backend: modal lỗi, không success giả, không xóa dữ liệu |
| Callback API | Promise reject hiển thị lỗi, cancel/reopen hoạt động |
| JS off/script blocked | Guarded controls disabled, Enter không gửi mutation |
| Native confirm và console | Không native dialog, không JS pageerror trong final run |
| Trang Admin khác | Categories/Dashboard không có component/script; popup riêng không sửa |
| Backend bảo vệ | [Hash](evidence/confirm-modal/protected-backend.json) ProductLifecycle, SalesService, Product, routes/admin, Admin ProductController giữ nguyên từ trước nhiệm vụ |

Network metadata ở [requests](evidence/confirm-modal/requests.json) chỉ lưu path/method/method-spoof/CSRF-presence, không lưu token/body/password. csrf=null nghĩa CDP không cung cấp multipart body cho request đó, không kết luận thiếu CSRF. Các request có body quan sát được vẫn có CSRF, PUT/DELETE spoof đúng; CSRF thiếu419 được kiểm trong Feature suite.

Lượt Browser cuối dùng `http://127.0.0.1:8012`, database QA đã kiểm `shop_hoatuoi_test_an12_browser_20261030`, replica slideQA27030. Chỉ tạo/sửa/xóa fixture prefix AN12QACFM; không thao tác mutation trên database demo cổng8009. Fixture có đơn từ AN12 chỉ bị thử DELETE và nhận422, không sửa snapshot. Các fixtures từ lượt runner lỗi trước được giữ trong QA để đối soát, không reset/drop database. Source và database sản xuất không được dùng để test phá hủy.

Các lần chuẩn bị runner: sửa selector accessible name của submit icons (đánh dấu icon trang trí aria-hidden); bỏ route.fetch replay multipart, dùng trì hoãn continue để giữ binary upload gốc; chờ navigation thật cho redirect về cùng URL; đọc disabled property của fieldset thay cho isDisabled API chỉ phù hợp control. Đã chạy lại toàn bộ runner sau các chỉnh này. failure.png là ảnh lượt lỗi trước, không phải evidence PASS của lượt cuối. 503 là fault injection có nhãn QA, không gọi là outage thật.

Chưa chạy full regression toàn nhóm ở nhiệm vụ này. 7 FAIL/27 SKIP baseline của AN12 vẫn là vấn đề riêng đã ghi trong AN12 report; 25 related tests PASS không có nghĩa toàn dự án đã xanh. BFCache có recovery handler nhưng chưa chứng nhận mọi browser/Safari/Firefox hoặc mọi trạng thái mất mạng sau commit.

## Ảnh và file

Ảnh desktop: [Thêm](evidence/confirm-modal/create-1440.png), [Sửa](evidence/confirm-modal/edit-1440.png), [Xóa](evidence/confirm-modal/delete-1440.png), [Ngừng bán](evidence/confirm-modal/stop-1440.png), [Bán lại](evidence/confirm-modal/resume-1440.png). Mobile/tablet cùng prefix, hậu tố375/768; [manifest](evidence/confirm-modal/screenshots.json) lưu viewport/URL/thời gian chụp. 17 ảnh trạng thái cuối gồm 15 popup responsive và422/503; ảnh lỗi preliminary được tách ý nghĩa như trên.

File của nhiệm vụ:

1. resources/views/components/admin/confirm-modal.blade.php — mới, một component.
2. public/css/admin-confirm-modal.css — mới, CSS scoped.
3. public/js/admin-confirm-modal.js — mới, API/callback/submit/loading/focus.
4. resources/views/admin/products/{index,create,edit}.blade.php — opt-in năm thao tác, guard fieldset, bỏ native confirm.
5. tests/JavaScript/admin-confirm-modal.test.cjs — mới.
6. tests/Feature/AdminProductBrowseTest.php — thêm kiểm tra component/method/CSRF markup và isolation.
7. Báo cáo này và docs/an/evidence/confirm-modal.

[Git status cuối](evidence/confirm-modal/git-status.txt) gồm cả WIP từ AN12/UI trước đó. Không stage/commit/push/PR, không thay schema/validator/migration/field Product, không sửa ProductLifecycle/Cart/Wishlist/Checkout/Order. Sidebar/topbar và layout Admin dùng chung giữ nguyên.
