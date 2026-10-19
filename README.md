# Nhà Hàng Ngon — Laravel / MySQL

Chuyển đổi từ dự án **DoAn_LTW_NhaHang** (ASP.NET MVC 5, .NET Framework 4.7.2, EF6, SQL Server). Bản chuyển đổi ban đầu được tạo ngang cấp `TrangChu`; hiện dự án đã được chuyển theo đường dẫn người dùng cung cấp: `D:\nam4Ki1\LapTrinhMaNguonMo_PHP\NhaHangLaravel`. Source C# và SQL gốc được giữ nguyên. Đây vẫn là đề tài quản lý nhà hàng.

## Công nghệ và phiên bản

| Thành phần | Phiên bản đã sử dụng |
|---|---|
| PHP | 8.3.30 TS x64 của Laragon; yêu cầu PHP 8.3 trở lên theo composer.json |
| Laravel | 13.37.0, cố định qua composer.lock |
| Composer | 2.9.4 |
| MySQL | 8.4.3, InnoDB, utf8mb4_unicode_ci |
| Giao diện | Blade + Bootstrap 5.2.3 cục bộ |
| PHPUnit | 12.5.40 |
| Node.js | Đã phát hiện 24.19.0; không cần cài/chạy cho ứng dụng này |

Laravel 13 yêu cầu PHP >=8.3: [tài liệu phiên bản](https://laravel.com/docs/13.x/releases). Không dùng React/Vue/SPA. CSS, JS, ảnh đã đặt trong `public/assets`; **không cần npm install, npm run dev hoặc npm run build**. `vendor` đang có trong bản làm việc, nhưng khi chuyển máy vẫn nên chạy `composer install` theo file khóa. Không dùng `composer update` chỉ để chạy đồ án.

## Chức năng

- Xem thực đơn; gõ tìm tên/mô tả, chọn loại và chuyển trang cập nhật riêng danh sách bằng fetch, không reload; chi tiết và món cùng loại; top 10 theo số phần thuộc đơn đã thanh toán.
- Đăng ký User, đăng nhập/đăng xuất, đổi mật khẩu và đặt lại mật khẩu bằng token Laravel.
- Admin CRUD loại món, món ăn, nhà cung cấp; quản lý album ảnh, xóa nhiều món; chặn xóa dữ liệu có liên kết.
- Giỏ hàng cạnh Thực đơn (điện thoại cạnh nút menu), nút ± và ô nhập số lượng trực tiếp 1–99; đặt món tại bàn/mang về/giao hàng, chọn tiền mặt/chuyển khoản; giá và chủ đơn lấy từ server; lưu hóa đơn và chi tiết bằng transaction.
- Admin duyệt trạng thái và xác nhận đã nhận tiền. Khách chỉ xem/hủy đơn của mình khi còn chờ xác nhận. Hủy giữ lịch sử.
- Hồ sơ, ảnh đại diện, địa chỉ giao hàng có một địa chỉ mặc định, điểm thưởng, đổi và dùng voucher một lần.
- Đặt bàn, theo dõi/hủy yêu cầu; liên hệ; đánh giá món và ảnh đánh giá.
- Chủ nhà hàng quản lý nhân viên với 7 chức vụ, voucher và thống kê có biểu đồ. Admin/chủ được thêm/xóa khách hàng, không sửa thông tin của khách. Khách tự sửa hồ sơ của mình.
- Chỉ Thu ngân và Chủ nhà hàng xem báo cáo doanh thu, thống kê và biểu đồ theo khoảng ngày; các chức vụ khác bị chặn tại server.

Bảng quyền chi tiết, thay đổi so với C#, phạm vi API mobile: [docs/01-phan-tich-source.md](docs/01-phan-tich-source.md), [docs/02-bao-cao-nhom.md](docs/02-bao-cao-nhom.md).

## Mở ngay bản demo trong workspace hiện tại

Bản này đã chạy qua Apache Laragon tại **http://127.0.0.1:18080/**, MySQL riêng cổng 33307. Có thể mở URL để dùng ngay khi hai tiến trình còn chạy.

Sau khi khởi động lại máy, mở PowerShell tại chính thư mục này và chạy:

```powershell
.\Start-Demo.ps1
```

Nếu Windows chặn script, dùng chế độ bỏ chặn chỉ cho lần chạy này:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\Start-Demo.ps1
```

Script chỉ khởi động runtime đã có trong workspace này; không tạo/xóa/seed lại database. Nó không thay cấu hình Laragon hiện có. Script tự cập nhật document root theo thư mục hiện tại. Nếu đổi máy hoặc muốn dùng MySQL 3306/nhahang.test, cài mới theo các bước dưới đây; không sao chép `.runtime` sang máy khác.

## 1. Chuẩn bị Laragon trên Windows

Mở **Laragon → Terminal**. Chọn PHP 8.3 hoặc 8.4 phù hợp; nếu dùng Apache module PHP, chọn bản **Thread Safe (TS)**. Bật Apache và MySQL bằng **Start All**.

```powershell
php -v
php --ini
php -m
composer --version
mysql --version
```

Cần các extension: `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`, `openssl`, `pcre`, `PDO`, `pdo_mysql`, `session`, `tokenizer`, `xml`; `gd` để kiểm thử ảnh; `pdo_sqlite` cho test SQLite; `zip` để Composer tải bản dist thuận tiện. Bật extension trong đúng `php.ini` rồi restart Apache/Terminal. Windows cần Microsoft Visual C++ Redistributable x64 phù hợp bản PHP/Apache. Để tải đồng thời ảnh đại diện và album, đặt upload_max_filesize=2M và post_max_size=20M trong php.ini; mỗi ảnh vẫn được Laravel giới hạn 2MB.

Trên máy bàn giao, PHP/Composer không nằm trong PATH của PowerShell thường. Có thể dùng Laragon Terminal hoặc thêm riêng cho phiên terminal:

```powershell
$env:Path = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;C:\laragon\bin\composer;C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin;" + $env:Path
```

Thay đường dẫn nếu Laragon cài nơi khác. Không cần cài lại PHP khi Laragon đã có phiên bản phù hợp.

## 2. Đặt dự án và cài dependency

Sao chép **nội dung thư mục NhaHangLaravel** vào `C:\laragon\www\nhahang`. Không đưa toàn bộ thư mục C# làm document root của Laravel.

```powershell
cd C:\laragon\www\nhahang
composer install
```

Nếu Composer báo thiếu ZIP: mở `php.ini`, bỏ dấu `;` ở `extension=zip`, mở lại terminal và chạy lại. Lựa chọn tạm thời, không sửa php.ini:

```powershell
php -d extension=zip C:\laragon\bin\composer\composer.phar install
```

Không cần dependency frontend. Laravel Boost là dependency phát triển theo AGENTS.md của bộ khung, không tham gia nghiệp vụ nhà hàng.

## 3. Tạo .env và APP_KEY

Với bản cài mới, tạo `.env` từ mẫu. Nếu đã có `.env` do bạn cấu hình, lưu bản sao trước khi thay.

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Chỉnh `.env` bằng trình soạn thảo:

```dotenv
APP_NAME="Nhà Hàng Ngon"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://nhahang.test
APP_LOCALE=vi
APP_FALLBACK_LOCALE=vi
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nhahang_laravel
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
```

Mật khẩu rỗng chỉ là mặc định thường gặp của MySQL Laragon mới cài. Nếu máy bạn có mật khẩu, nhập vào `.env` riêng. `.env.example` không chứa bí mật thật; không commit `.env` hoặc gửi APP_KEY cho người khác.

**Bản workspace hiện tại** được cấu hình riêng để kiểm tra: MySQL `127.0.0.1:33307`, database `nhahang_demo`, dữ liệu ở `.runtime/mysql-data`; không phải database Laragon đang có của bạn. Khi cài theo Laragon thông thường, dùng `.env.example` và cổng MySQL thực tế của Laragon như trên.

## 4. Tạo database mới, migration và dữ liệu demo

Trong HeidiSQL/phpMyAdmin hoặc MySQL console, tạo database mới:

```sql
CREATE DATABASE nhahang_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Không chọn database SQL Server cũ hoặc database MySQL đang có dữ liệu. Nếu tên trên đã tồn tại, chọn tên mới và sửa `DB_DATABASE`; không xóa database để làm lại.

```powershell
php artisan optimize:clear
php artisan migrate --seed
php artisan storage:link
```

Mọi bảng được tạo bằng Migration. Seeder tạo dữ liệu demo, không import dữ liệu cũ. Nếu đã có user/danh mục/món, seeder demo sẽ bỏ qua để tránh ghi đè. Không chạy `migrate:fresh`, `migrate:refresh` hoặc `db:wipe` trên database sử dụng.

`storage:link` nối `public/storage` đến `storage/app/public` cho ảnh upload. Trên Windows, nếu không tạo được symbolic link, bật **Developer Mode** hoặc chạy Laragon Terminal bằng quyền Administrator rồi chạy lại. Khi sao chép dự án sang đường dẫn mới, nếu link còn trỏ đường dẫn cũ, chạy `php artisan storage:unlink` rồi `php artisan storage:link` trong dự án mới; các lệnh này chỉ xử lý link công khai.

## 5. Document root Apache/Nginx

Ưu tiên **Apache** để demo:

1. Laragon → Preferences → General → bật Auto virtual hosts.
2. Tạo/chỉnh virtual host `nhahang.test` để **DocumentRoot trỏ tới `C:/laragon/www/nhahang/public`**.
3. Cấu hình mẫu nằm ở [docs/laragon-apache.conf](docs/laragon-apache.conf). Đảm bảo `mod_rewrite` bật, `<Directory>` có `AllowOverride All` và `Require all granted`.
4. Laragon → Reload/Restart Apache. Dùng tên `nhahang.test` trong file hosts; Laragon thường tự thêm khi chạy với quyền phù hợp.
5. Truy cập **http://nhahang.test/**; trang đăng nhập: **http://nhahang.test/dang-nhap**. Đổi APP_URL nếu dùng tên miền/cổng khác.

Không trỏ root vào thư mục gốc Laravel và không mở URL chứa `/public` như cách triển khai chính. `.htaccess` Laravel trong `public` đã có sẵn.

Nếu chọn Nginx: cấu hình `root C:/laragon/www/nhahang/public;`, `index index.php;` và `try_files $uri $uri/ /index.php?$query_string;`; giữ cấu hình PHP FastCGI tương ứng của Laragon. Nginx không đọc `.htaccess`; không sao chép các chỉ thị Apache sang Nginx.

Chạy thay thế khi chưa cấu hình virtual host:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Truy cập **http://127.0.0.1:8000/** và đặt `APP_URL=http://127.0.0.1:8000` nếu dùng chế độ này. MySQL vẫn cần chạy.

## 6. Tài khoản demo

Chỉ tồn tại sau khi Seeder chạy trên database mới. Đây là thông tin **demo công khai**, không dùng cho triển khai thực tế.

| Vai trò | Email | Mật khẩu demo |
|---|---|---|
| Chủ nhà hàng | owner@example.test | Demo@12345 |
| Quản lý vận hành (Admin) | admin@example.test | Demo@12345 |
| User / khách hàng | user@example.test | Demo@12345 |

Mật khẩu trong DB được hash. Đăng ký luôn tạo User, không nhận quyền từ client. User mẫu có hóa đơn đã thanh toán để thử đổi điểm và voucher chưa dùng **DEMO20K**. Tạo thêm User thứ hai bằng đăng ký để thử chặn truy cập chéo.

## 7. Mail và chuyển khoản

- `MAIL_MAILER=log`: liên kết đặt lại mật khẩu được ghi vào `storage/logs/laravel.log` để demo cục bộ. Mở liên kết khi **đã đăng xuất**. Không gửi email thật ở chế độ mặc định. Có thể cấu hình Mailpit/SMTP bằng biến môi trường riêng.
- Không gửi mật khẩu rõ qua email. Liên kết dùng token Laravel, có hạn và chỉ dùng một lần.
- Online: sau khi đặt món, khách vào màn hình QR trước, chưa hiện bảng món/thông tin đơn. Bấm Tôi đã chuyển khoản mới mở chi tiết đơn (vẫn chờ nhân viên xác minh tiền). QR tạo tại máy, **MB BANK — QUAN AN VUI VE — 0333820793** theo thông tin người dùng cung cấp. Có thể cấu hình BANK_NAME/BANK_BIN/BANK_ACCOUNT/BANK_HOLDER trong .env. Không cần Internet để dựng ảnh QR.
- Khách bấm **Tôi đã chuyển khoản** → nhân viên kiểm tra giao dịch trong tài khoản ngân hàng → bấm **Xác minh đã nhận tiền**. Chỉ lúc này đơn hiển thị đã thanh toán; trạng thái thực hiện vẫn **Chờ xác nhận**. Hệ thống không tự đọc ngân hàng và không coi quét QR là đã nhận tiền.
- Nhân viên gọi xác nhận rồi lần lượt bấm **Đang làm → Đang giao → Đã giao thành công** (online), hoặc **Đang làm → Đang giao → Đã thanh toán** (COD). Không nhảy bước/quay lại. Hủy là kết thúc thay thế; đơn đã hoàn tất/hủy không sửa trạng thái. Online chưa xác minh tiền chưa được chuyển Đang làm.
- Khách chỉ hủy đơn của mình lúc Chờ xác nhận. Nhân viên được hủy ở mọi bước chưa kết thúc; đơn đã nhận tiền bị hủy sẽ được gắn **cần hoàn tiền**, không tự chuyển tiền hoàn.

## 8. Kiểm thử an toàn

Mặc định SQLite trong bộ nhớ, không sửa database demo:

```powershell
php artisan test
```

Chạy MySQL trên **database thử nghiệm riêng**. Tên database phải kết thúc bằng `_feature_test`; lớp TestCase chặn cấu hình không phù hợp trước khi chạy cơ chế làm mới database.

```sql
CREATE DATABASE nhahang_feature_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```powershell
Copy-Item .env.testing.example .env.testing
php artisan key:generate --env=testing
```

Sửa thông tin MySQL trong `.env.testing` cho database thử nghiệm, tuyệt đối không trỏ tới database demo/thật. Sau đó:

```powershell
php artisan config:clear
php artisan test --configuration=phpunit.mysql.xml
```

Test sẽ tạo lại bảng trong **database thử nghiệm** bằng `LazilyRefreshDatabase` và rollback thay đổi sau mỗi test. Chỉ dùng tài khoản MySQL có quyền trên database test nếu muốn cách ly thêm. Kết quả thực chạy và phần chưa kiểm tra xem [docs/05-kiem-thu-va-doi-chieu.md](docs/05-kiem-thu-va-doi-chieu.md).

## 9. Lỗi thường gặp

| Triệu chứng | Cách xử lý |
|---|---|
| `php` / `composer` không được nhận diện | Dùng Laragon Terminal, chọn đúng PHP hoặc cập nhật PATH cho phiên terminal. |
| PHP quá cũ / dependency không tương thích | Chọn PHP >=8.3, dùng `composer install` theo lock, kiểm tra `composer check-platform-reqs`. |
| Thiếu `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, `zip` | Xem `php --ini`, bật extension trong đúng php.ini, restart Apache. PHP CLI và Apache có thể đọc cấu hình khác nhau. |
| `SQLSTATE[HY000] [2002]` | Bật MySQL, kiểm tra host/cổng. Bản kiểm thử workspace dùng 33307; Laragon thường dùng 3306. |
| `Access denied` / `Unknown database` | Kiểm tra DB_USERNAME/DB_PASSWORD và tạo database mới đúng tên; chạy `php artisan config:clear`. |
| `No application encryption key` | Chạy `php artisan key:generate`, `php artisan config:clear`; không bỏ trống APP_KEY trong .env. |
| Không ghi được log/session/view | Cho tài khoản đang chạy PHP quyền Modify với `storage` và `bootstrap/cache`; không cấp quyền toàn ổ đĩa. |
| 404 ở `/dang-nhap` nhưng `/` chạy | DocumentRoot phải trỏ `public`; bật rewrite/AllowOverride Apache hoặc try_files Nginx, restart. |
| Trang thiếu CSS/JS | Kiểm tra URL `/assets/bootstrap.min.css`, `/assets/app.css`, root public và APP_URL. Không cần Vite manifest hay npm build. |
| Ảnh upload bị 404 | Chạy `storage:link`; sau khi chuyển máy kiểm tra link trỏ đúng đường dẫn và quyền đọc ảnh. |
| 419 | Tải lại trang để nhận CSRF/session mới; bật cookie, dùng thống nhất host và scheme; không tắt CSRF. |
| 403 | Kiểm tra vai trò; User không có quyền truy cập admin và chỉ xem dữ liệu thuộc mình. |
| Không xóa được loại/món/tài khoản | Có dữ liệu tham chiếu; giữ lịch sử. Món không bán nữa có thể chọn Tạm ngừng. |
| Không nhận mail | Mặc định dùng mail log; xem log hoặc cấu hình SMTP/Mailpit của riêng bạn. |
| Test bị từ chối database | Sửa config test sang SQLite :memory: hoặc database MySQL mới có hậu tố _feature_test; không gỡ chốt bảo vệ. |

## Các điều chỉnh ngày 18/10/2026

- Tiền hiển thị 1.000 thay 1.000,00; phần lẻ khác 0 vẫn giữ nguyên, không làm tròn.
- Form đăng ký, tạo tài khoản và sửa hồ sơ/nhân viên nhập phần trước @gmail.com. Email demo cũ @example.test vẫn đăng nhập bằng email đầy đủ; không tự đổi email đã lưu. Khi sửa bằng form mới, hậu tố Gmail được áp dụng và kiểm tra unique.
- Nhân viên có các chức vụ Quản lý vận hành, Thu ngân, Phục vụ, Đầu bếp, Lễ tân, Nhân viên giao hàng, Quản lý kho. Các chức vụ dùng nhóm quyền vận hành; riêng Thu ngân và Chủ nhà hàng được xem báo cáo doanh thu/thống kê. Chỉ Chủ quản lý nhân viên/voucher.
- Không có nút đặt lại bộ lọc: trên danh sách món, gõ tìm kiếm cập nhật sau khoảng 180 ms, chọn loại/phân trang cập nhật không reload. Xóa hết từ khóa đưa danh sách về mặc định; đổi bộ lọc áp dụng độc lập. Khi JavaScript không hoạt động, form GET/phân trang thông thường vẫn dùng được.
- Đặt bàn chọn số 1–200; giữ bàn 120 phút, không nhận đặt trùng khung. Đặt món kiểm email đặt bàn trong khung từ 120 phút trước đến 30 phút sau thời điểm hiện tại và chặn bàn đang có đơn của khách khác. Dữ liệu đặt bàn cũ chưa có số được giữ nguyên, không đoán số bàn.
- Bản đang dùng: đã sao lưu .runtime/backup-20261018-before-fixes rồi chạy migration nâng cấp, không seed đè. Khi cập nhật bản có dữ liệu ở máy khác: sao lưu trước, chạy php artisan migrate; không chạy migrate:fresh.

Chi tiết sửa đổi, kịch bản và kết quả: [docs/06-cap-nhat-20261018.md](docs/06-cap-nhat-20261018.md).

## Tài liệu bàn giao

- [Phân tích source và đối chiếu C# → Laravel](docs/01-phan-tich-source.md)
- [Báo cáo nhóm, phân quyền, use case, phân công, demo](docs/02-bao-cao-nhom.md)
- [Từ điển database](docs/03-thiet-ke-database.md)
- [ERD Mermaid](docs/erd.mmd) và [UML Use Case PlantUML](docs/use-case.puml)
- [Hướng dẫn import dữ liệu cũ, thay đổi và giới hạn](docs/04-chuyen-doi-va-gioi-han.md)
- [Kết quả kiểm thử và bảng yêu cầu môn học → file → trạng thái](docs/05-kiem-thu-va-doi-chieu.md)

Tên/số thành viên và việc giảng viên duyệt đề tài **cần nhóm xác nhận**, chưa được tự điền hay tuyên bố đã duyệt.


## Giao diện đã nâng cấp

Giao diện sáng dùng Blade + Bootstrap cục bộ, responsive, sidebar theo vai trò, bảng và form thống nhất, xác nhận thao tác, skeleton và thử lại khi tìm món lỗi mạng. Không cần build asset mới. Chỉ Chủ nhà hàng và Thu ngân xem báo cáo; tài khoản ở database cũ cần được Chủ nhà hàng gán đúng chức vụ trong Nhân viên & chức vụ.

[Báo cáo bàn giao UI, ảnh trước/sau, kiểm thử, tùy biến và khôi phục](docs/09-ban-giao-nang-cap-ui.md).
