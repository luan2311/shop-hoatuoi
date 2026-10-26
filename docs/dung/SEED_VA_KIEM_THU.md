# Hướng dẫn dữ liệu và kiểm thử trên Windows/Laragon

## Yêu cầu môi trường

PHP 8.3+ đúng kiến trúc TS/NTS/x64, extension MongoDB tương thích, Composer và MongoDB/mongosh. Kiểm chứng giai đoạn này dùng PHP 8.3.30 ZTS x64 của Laragon, extension 2.5.5 trong runtime riêng, Composer 2.9.4, MongoDB Server 8.3.8. Dependency Laravel/MongoDB giữ nguyên theo composer.lock. Không cài thêm package hoặc sửa PHP hệ thống.

Chạy tại root repository. Nếu chưa có vendor, bật extension phù hợp rồi dùng `composer install --no-interaction --prefer-dist --no-scripts`; tiếp đó `composer check-platform-reqs`. Lệnh này lấy đúng lock, không chạy Composer setup/migration và không thay dependency của nhóm.

Trên máy đã chuẩn bị runtime riêng trong checkout này:

```powershell
$env:PHP_INI_SCAN_DIR = (Resolve-Path .runtime/php).Path
php --ri mongodb
composer check-platform-reqs
```

DLL/runtime không nằm trong Git. Máy khác tự bật extension đúng PHP đang chọn trong Laragon; không copy DLL khác phiên bản. Nếu dùng web server Laragon, kiểm tra extension của PHP web server riêng với PHP CLI.

## Đọc và so sánh dữ liệu, chưa kết nối database

```powershell
python scripts/prepare-dung-data.py
# Nếu muốn đối chiếu bản trên máy, truyền đúng đường dẫn riêng của mình:
python scripts/prepare-dung-data.py --compare "$env:USERPROFILE/Downloads/mongodb_init.js"
```

Parser chỉ chấp nhận literal dữ liệu/new Date và tham chiếu mẫu đã biết, không eval/exec hoặc gọi MongoDB. Mặc định đối chiếu dataset/manifest đã có; exit 1 nếu artifacts lệch hoặc bản so sánh khác nội dung sau chuẩn hóa xuống dòng. Khi source hoặc ảnh thay đổi có chủ đích, review trước rồi chạy `--write` để tái tạo ba artifacts. Không chạy `mongosh ... mongodb_init.js`.

## Khởi động instance QA riêng

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File scripts/start-dung-qa.ps1
```

`ExecutionPolicy Bypass` chỉ áp dụng process lệnh trên để Windows PowerShell chạy script đã review, không đổi policy hệ thống. Script tìm mongod đã cài, bind loopback cổng 27028, replica set `dungQA`, dữ liệu trong `.runtime/dung-qa/data`. Nếu cần chỉ định binary: thêm `-MongoBin 'đường-dẫn-mongod.exe'`. Nếu cổng thuộc instance/path khác thì dừng, không reconfigure. Script không cài package, seed hoặc migrate. Không chạm các cổng 27017/27018.

Tiến dùng transaction cho giỏ hàng/checkout; QA tích hợp cần replica set hoặc sharded cluster. Không chuyển sang standalone để bỏ qua transaction. Instance riêng được giữ chạy để có thể kiểm tra lại; PID nằm trong `.runtime/dung-qa/mongod.pid`. Không dọn dữ liệu hoặc dừng một instance khác bằng các script này.

## Xem trước và nạp dữ liệu vào database development riêng

Các lệnh bên dưới chỉ dành cho database cá nhân mới được xác nhận. Không trỏ sang database dùng chung.

```powershell
$env:APP_ENV = 'local'
$env:DB_CONNECTION = 'mongodb'
$env:MONGODB_URI = 'mongodb://127.0.0.1:27028/?replicaSet=dungQA&serverSelectionTimeoutMS=1500'
$env:MONGODB_DATABASE = 'shop_hoatuoi_dung_dev'

# Dry-run: chưa tạo index, collection hoặc document
php artisan flowers:import-samples --target=shop_hoatuoi_dung_dev

# Chỉ thêm dữ liệu thiếu vào đích riêng đã xác nhận
php artisan flowers:import-samples --target=shop_hoatuoi_dung_dev --apply
```

Trước khi chạy, không dùng cache cấu hình trỏ đích cũ. Nếu app đã cache config trên máy của mình, xem lại `php artisan config:show database` ở chế độ riêng tư hoặc kiểm tra cấu hình local, không đưa URI/password vào báo cáo. Command so sánh database cấu hình với `--target` và tên database driver thực tế. Nếu không khớp thì dừng, không tự thay đích.

Guard giai đoạn 1 bắt buộc APP_ENV `local/testing`, connection `mongodb`, URI MongoDB loopback và tên `shop_hoatuoi_dung_dev[_...]` hoặc `shop_hoatuoi_test_*`. `shop_hoatuoi`, production, server remote, SQL fallback và target rỗng/khác cấu hình đều bị từ chối trước khi ghi. Hỗ trợ Atlas/server chung cần nhóm thiết kế riêng; không mở guard để vượt qua lỗi.

JSON kết quả có inserted/skipped/updated/would_insert theo collection. Lần đầu trên database trống: 5 categories, 80 products, 3 posts. Lần hai: inserted=0, skipped tương ứng 5/80/3, updated luôn 0. `PREVIEW` không phải đã seed.

Importer kiểm tra xung đột code/slug/định danh trước khi thêm dữ liệu. Apply có thể thêm indexes bảo vệ định danh trên database riêng; không sửa validators hiện có. Các upsert là nguyên tử từng document, **không phải một transaction cho cả đợt import**. Khi DB lỗi giữa chừng, output FAIL có số đã thêm; có thể chạy lại sau khi xử lý nguyên nhân vì bản ghi cũ được giữ nguyên. Không dùng reset/drop để xử lý.

## Tài khoản QA tùy chọn

Không nhập mật khẩu/hash hoặc thông tin liên hệ từ initializer cũ. Chỉ khi cần thử đăng nhập:

```powershell
$env:FLOWER_DEMO_USER_PASSWORD = [Net.NetworkCredential]::new('', (Read-Host 'Mat khau QA User >=12 ky tu' -AsSecureString)).Password
$env:FLOWER_DEMO_ADMIN_PASSWORD = [Net.NetworkCredential]::new('', (Read-Host 'Mat khau QA Admin >=12 ky tu' -AsSecureString)).Password
php artisan flowers:import-samples --target=shop_hoatuoi_dung_dev --apply --demo-users
Remove-Item Env:FLOWER_DEMO_USER_PASSWORD,Env:FLOWER_DEMO_ADMIN_PASSWORD
```

Tạo mới `user.dung@example.test`/`admin.dung@example.test`, roles tương ứng, chỉ khi chưa tồn tại. Mật khẩu tối thiểu 12 ký tự, hash bằng Laravel Hash. Không ghi đè mật khẩu, tên, trạng thái hoặc role nếu tài khoản đã có. Không in mật khẩu trong kết quả. Test dùng mật khẩu ngẫu nhiên trong memory, không lưu vào evidence.

Nếu cần API seeder Laravel thay command, dùng adapter rõ class:

```powershell
$env:FLOWER_SAMPLE_DATABASE = 'shop_hoatuoi_dung_dev'
$env:FLOWER_SAMPLE_APPLY = 'true'
php artisan db:seed --class=FlowerSampleSeeder
Remove-Item Env:FLOWER_SAMPLE_DATABASE,Env:FLOWER_SAMPLE_APPLY
```

Adapter dùng cùng guard/importer. Mặc định không tạo user; muốn tùy chọn đó phải bật thêm `FLOWER_SAMPLE_DEMO_USERS=true` và hai password môi trường. Không gọi `db:seed` mặc định hoặc `migrate --seed` vì DatabaseSeeder/migrations SQL cũ chưa chuyển xong.

## Kiểm thử độc lập

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File scripts/test-dung-data.ps1
# Chỉ đọc/validate dataset, không cần MongoDB:
php vendor/bin/phpunit --filter FlowerDatasetTest
```

Launcher đặt URI riêng, sinh APP_KEY tạm trong memory nếu chưa có, rồi khôi phục biến môi trường của process. Test trait bắt buộc APP_ENV testing và database prefix test, mỗi test tạo database `shop_hoatuoi_test_dung_<pid>_<random>` mới. Trait chỉ tạo validators/migrations additive trên DB đó, không migrate database development. DB test được giữ lại để đọc bằng chứng; có thể tích lũy dung lượng, chưa tự dọn.

Thiếu extension/server/replica set: tests dữ liệu Mongo ghi SKIP/BLOCKED. Launcher trả exit 2 nếu có test skipped, không coi exit 0 thông thường của PHPUnit là PASS toàn bộ.

Kết quả mặc định ở `.runtime/dung-qa/phpunit.xml`; readback nghiệp vụ ở `.runtime/dung-qa/business-readback.json`. Dùng `-EvidencePath` để chọn đường dẫn trong repository. Có thể chạy các test hiện có của nhánh bằng `-Filter '.*'`; điều này vẫn chưa kiểm thử UI/Catalog chưa được tích hợp.

## Hình ảnh và xử lý lỗi

Manifest [product-images.json](product-images.json) giữ filename theo source và phân biệt ảnh được commit trong `database/seeders/assets/products` với file runtime trong `storage/app/public/products`. Hiện có **6 ảnh minh họa có attribution/CC BY 4.0 hoặc CC BY-SA 4.0; 0 ảnh được xác minh đúng SKU; còn thiếu 68 ảnh đúng mẫu** trên 74 tên file duy nhất. Mỗi minh họa được chuẩn hóa thành JPEG 800×800, tối đa 350 KB và có dải chữ “ILLUSTRATION – PRODUCT MATCH NOT VERIFIED”. Sáu tên file được hai sản phẩm dùng chung; tên chung không chứng minh hai ảnh là cùng sản phẩm. Ba thumbnail bài viết vẫn thiếu.

Trên checkout mới, cài asset đã được kiểm tra bằng:

```powershell
php scripts/prepare-product-images.php --install-assets
php scripts/prepare-product-images.php --write-manifest
python scripts/prepare-dung-data.py
```

Lệnh cài chỉ nhận asset có hash/giấy phép khớp provenance; có thể thay placeholder do script dự án tạo, nhưng từ chối file runtime khác hoặc asset repository bị sửa. Muốn thay ảnh, dùng `--target=products/<filename> --input=<file> --rights=<metadata.json>` để preview; thêm `--apply` sau khi đã review nguồn, giấy phép, tác giả và mã sản phẩm. `role=product` đòi hỏi xác minh tất cả code dùng chung filename; ảnh chung chung phải để `role=illustration`.

| Thông báo/tình huống | Xử lý |
|---|---|
| Initializer thay đổi | Đọc diff, so checksum, chạy lại parser sau review; không bỏ kiểm tra checksum |
| Target không hợp lệ | Kiểm tra APP_ENV, default connection, URI và database cá nhân; không dùng database chung |
| Slug/username đã thuộc bản ghi khác | Dừng, đối chiếu người phụ trách; không xóa hoặc đổi code tự động |
| Unique index không tạo được | Kiểm tra dữ liệu trùng trong DB riêng; importer không tự xóa/trộn dữ liệu |
| Validator từ chối dữ liệu | Đối chiếu schema nhóm; không tự nới validator của thành viên khác |
| MongoDB không sẵn sàng | Khởi động instance QA đúng topology; kết quả chưa phải PASS |
| Ảnh không hiển thị | Kiểm tra file thật, filename và storage link; không đổi trạng thái manifest thành hoàn thành khi chưa có file |
| Asset hiện có không khớp registry | Dừng và review hash/attribution; không ghi đè file không đăng ký |

Không commit `.env`, runtime/DLL, vendor, DB data/log/PID, password, cookie, caches hoặc build artifacts. Chỉ các JSON/XML evidence đã xem xét, không chứa credentials, mới thuộc hồ sơ review.
