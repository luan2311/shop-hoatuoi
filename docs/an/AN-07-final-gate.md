# AN-07 — Independent browser E2E & final PR gate

Ngày 27/10/2026 (Asia/Bangkok). **READY_FOR_PR — phạm vi module Product của An.** Đây là sẵn sàng gửi review, không phải phê duyệt merge hoặc nghiệm thu toàn hệ thống.

## Môi trường và bảo vệ dữ liệu

Working directory `D:/learning/MNM/shop-hoatuoi`; branch `feat/an-laravel-mongodb`; HEAD `3ffe4cb0a09be7b7372dee98bf5b0580c83d350f`. Đã đọc AN-04 và AN-06. Không đổi branch, commit, push, merge hoặc tạo PR.

Playwright/playwright-core đã có trong runtime `C:/Users/baoan/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules`. Không cài package, không đổi package.json/lock Laravel. Runner độc lập `.an-runtime/an07-review/run.cjs`, readback `.an-runtime/an07-review/readback.php`; dùng Chrome cài sẵn, headless thật với browser context mới, không phụ thuộc Work Browser, không dùng profile cá nhân. Phiên bản browser nằm trong [browser-version.txt](evidence/an07-browser/browser-version.txt).

Laravel `127.0.0.1:8008` PID 9364, MongoDB `127.0.0.1:27028` PID 23436 đã chạy; HTTP catalog 200. Giữ môi trường AN-06: MongoDB `shop_hoatuoi_an_review_20261026`, SQL Auth `.an-runtime/an02-review/auth.sqlite`, ini PHP riêng. Không chạy migration/seed trên review DB. `.env` hash giữ nguyên `00AF3A66D94408FFA7B961CD312C6BA28DFCAC3DFC0D73FC4280FF54DEF8D987`.

Chỉ CRUD fixture `AN07QA20261027`, slug `an07-qa-20261027`. Các sản phẩm review gốc chỉ đọc. Mỗi lượt runner kiểm 22 records ban đầu và không có AN07 trước khi tạo. Sau xóa, hash 22 records giữ nguyên `6c974b8f2191b6eded634d28950bb3bbdfc6748901b140ba07eb6978d7e9d64b`, inventory file products khớp trước chạy. Browser context đóng sau test; server QA giữ nguyên.

## Browser E01–E14

Kết quả mới từ Playwright trong [results.json](evidence/an07-browser/results.json) và [runner.log](evidence/an07-browser/runner.log), không tái sử dụng PASS AN-06.

| ID | Kết quả | Kiểm chứng |
|---|---|---|
| E01 | PASS | Catalog 12/22 cards |
| E02 | PASS | Category Kệ Chúc Mừng trả 2 sản phẩm |
| E03 | PASS | Kiểm numeric order giá tăng và giảm, không so chuỗi giá |
| E04 | PASS | Bó Hoa Tươi trang 1 có 12, trang 2 có 8/20; giữ filter/sort |
| E05 | PASS | Gõ demo, 6 gợi ý AJAX, URL không đổi |
| E06 | PASS | Click suggestion, detail giá 210.000 và 4 related |
| E07 | PASS | Login Admin SQL thật, truy cập admin; [ảnh](evidence/an07-browser/E07-admin.png) |
| E08 | PASS | Form HTML tạo JPEG, BSON price float 123456.75, stock int 7; [ảnh](evidence/an07-browser/E08-created.png), [public image](evidence/an07-browser/E08-public-image.png) |
| E09 | PASS | Đổi tên dài, giá 654321.25 và stock 9; giữ ảnh cũ khi không upload |
| E10 | PASS | Thay JPEG bằng PNG; file mới tồn tại, file cũ đã xóa, public detail và edit preview tải đúng |
| E11 | PASS | Xóa fixture qua nút Xóa; document và PNG không còn; [ảnh](evidence/an07-browser/E11-deleted.png) |
| E12 | PASS | Baseline 22 documents/hash không đổi; storage inventory trở về ban đầu |
| E13 | PASS trong phạm vi browser navigation | User login thật; GET admin index/create/edit của sản phẩm còn tồn tại đều 403; [ảnh](evidence/an07-browser/E13-forbidden.png). POST/PUT/DELETE bị cấm được kiểm bằng full PHP suite, không tuyên bố đã submit các method đó từ browser |
| E14 | PASS | Submit mã có khoảng trắng, server trả lỗi validation hiện trên form, giữ dữ liệu nhập; DB vẫn giữ mã hợp lệ |

Lượt runner đầu kiểm edit URL sau khi fixture đã bị xóa nên nhận 404 thay vì expected 403. Đây là lỗi thiết kế test, không sửa app. Đổi kiểm tra GET edit sang sản phẩm review còn tồn tại (chỉ đọc); chạy lại toàn bộ runner exit 0, CRUD fixture được dọn cả hai lượt. Log cuối là lượt đã sửa test; không che giấu lần đầu trong kết luận.

## MongoDB và storage readback

Readback bằng PHP MongoDB driver riêng, không giả lập dữ liệu browser. [before](evidence/an07-browser/before.json), [created](evidence/an07-browser/created.json), [updated](evidence/an07-browser/updated.json), [replaced](evidence/an07-browser/replaced.json), [invalid](evidence/an07-browser/invalid.json), [after-delete](evidence/an07-browser/after-delete.json).

- Tạo: đúng một fixture, giá BSON float, stock int, JPEG tồn tại và browser naturalWidth > 0.
- Sửa: đúng price/stock mới, image path giữ nguyên.
- Thay ảnh: path khác, PNG tồn tại, JPEG không còn; ảnh 640×480 giữ tỷ lệ khi render detail/preview.
- Validation: mã lỗi không ghi vào DB.
- Xóa: không còn AN07, không còn file PNG, toàn bộ tên file trong products directory giống baseline. Không thao tác collection khác hoặc tài khoản review ngoài login/session thông thường.

## Responsive

21 lượt đo: 7 màn hình × 3 viewport, mọi `scrollWidth` bằng `innerWidth`, không horizontal overflow. Ảnh đã upload tải được; kiểm screenshot mẫu desktop/tablet/mobile, form/validation/preview và long-name detail. Đây là QA Chromium, không phải chứng nhận mọi thiết bị hoặc Safari/Firefox.

| Trang | 1440 | 768 | 375 | Evidence prefix |
|---|---|---|---|---|
| Listing | PASS | PASS | PASS | listing |
| Search dropdown 6 gợi ý | PASS | PASS | PASS | dropdown |
| Pagination | PASS | PASS | PASS | pagination |
| Create | PASS | PASS | PASS | create |
| Edit + PNG preview | PASS | PASS | PASS | edit-preview |
| Detail tên dài không khoảng trắng | PASS | PASS | PASS | detail-long-name |
| Validation form | PASS | PASS | PASS | validation |

Ảnh theo mẫu `evidence/an07-browser/<prefix>-<width>.png`. Ví dụ [desktop](evidence/an07-browser/listing-1440.png), [tablet edit](evidence/an07-browser/edit-preview-768.png), [mobile detail](evidence/an07-browser/detail-long-name-375.png), [mobile validation](evidence/an07-browser/validation-375.png). Nút chính cao khoảng 40–42 px, không bị cắt; có thể tăng lên 44 px khi Huy chuẩn hóa touch targets. Tên dài xuống dòng ở detail, nội dung input cuộn trong trường thay vì kéo rộng trang.

**Search trong Admin Create/Edit:** layout `resources/views/layouts/app.blade.php` include search partial vô điều kiện, nên xuất hiện trên mọi trang dùng x-app-layout. Không gây lỗi chức năng hoặc overflow; có thể làm người nhập rời form. Đề xuất Huy/An chốt giới hạn block bằng `@unless(request()->routeIs('admin.*'))` hoặc đưa search vào header catalog. Chưa áp dụng vì đây là lựa chọn layout chung, không phải lỗi blocking. Validation hiện còn tiếng Anh do locale mặc định; ghi nhận cải thiện UI, chưa thay locale toàn site.

## Final regression mới

| Kiểm tra | Kết quả | Log |
|---|---|---|
| Full PHP | PASS 52 tests / 328 assertions, exit 0 | [PHP](evidence/an07-browser/php-tests.txt) |
| JavaScript | PASS 4/4 | [JS](evidence/an07-browser/javascript-tests.txt) |
| Composer strict | PASS | [composer](evidence/an07-browser/composer.txt) |
| Platform | PASS PHP 8.3.30, ext-mongodb 2.5.0 | [platform](evidence/an07-browser/platform.txt) |
| Routes | 9 routes; 6 admin auth + role:Admin | [routes](evidence/an07-browser/routes.txt) |
| Vite build | PASS | [build](evidence/an07-browser/build.txt) |
| git diff --check | PASS | Thực thi sau regression |

Full PHP dùng SQL memory, `AN_MONGODB_TESTS=1`, URI localhost:27028 và guard database random `shop_hoatuoi_an_test_<hex>` đã kiểm ở AN-06. Migration/cleanup trong tests chỉ trên test DB. Không cleanup review DB. Không đổi source ứng dụng trong AN-07; bản sửa numeric AN-06 nay đã được nghiệm thu qua browser thật.

## PR gate và integration

**READY_FOR_PR riêng An:** E08 đã retest, vòng CRUD/upload thật và responsive đạt phạm vi trên; không còn lỗi blocking Product đã biết từ lần QA này. Chưa tạo PR theo yêu cầu.

Trước merge cần reviewers thống nhất shared changes và contracts:

- Luân: category.code ổn định, ObjectId string/categoryRecord/snapshot, rename/delete policy; Auth hiện SQL. Không tự suy ra MongoDB Auth đã đạt.
- Tiến: không SQL JOIN vào Mongo products; thống nhất code/id và order snapshot, trừ stock atomic; xử lý references khi xóa product.
- Huy: search placement trong layout, tránh include lặp; wishlist dùng contract code/id; typography/touch targets và locale.
- Dũng: ảnh legacy còn placeholder, seed tổng thể chưa idempotent ở Auth/Post; ghép E2E toàn nhóm và dataset. Không chạy lại DatabaseSeeder chung như bước setup bắt buộc.
- Search là regex substring literal, không phải full-text; text index không tối ưu regex này. Lead cần chốt rubric full-text trước merge nếu đó là yêu cầu bắt buộc. Không tự đổi thuật toán hoặc cam kết latency.
- Atomic update/delete qua driver không phát Eloquent events; nếu thêm observers audit/cache phải tích hợp rõ. Outage DB/filesystem vẫn có thể cần đối soát orphan. Những điểm này là giới hạn hợp đồng tích hợp, không được che bằng kết quả tests.

## Files và Git

AN-07 chỉ thêm `docs/an/AN-07-final-gate.md` và `docs/an/evidence/an07-browser/*`; runner/readback ở `.an-runtime/an07-review` ngoài repo. Không cài dependency, không sửa .env, không sửa layout/source trong lượt này. Trạng thái tích lũy vẫn có 10 tracked modified và nhiều file untracked từ AN-01–AN-06; xem [git-status](evidence/an07-browser/git-status.txt). Review phải gồm cả untracked controllers/tests/views/migration, không chỉ git diff. Không commit/push/merge/PR.
