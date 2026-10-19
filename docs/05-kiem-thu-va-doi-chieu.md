# Kiểm thử và bảng đối chiếu yêu cầu môn học

Phiên bản cập nhật ngày **18/10/2026**, Windows, PHP 8.3.30, Laravel 13.37.0, MySQL 8.4.3, Apache 2.4.66. Dự án: D:\nam4Ki1\LapTrinhMaNguonMo_PHP\NhaHangLaravel.

## Cập nhật menu, giỏ hàng và màn hình QR mới nhất

- Full suite: **116 test, 581 assertions đạt trên cả SQLite và MySQL**, exit 0. [JUnit SQLite](test-results-menu-ui-sqlite.xml), [JUnit MySQL](test-results-menu-ui-mysql.xml).
- MenuInteractionTest kiểm HTML Blade trả về khi fetch, lọc loại/từ khóa/phân trang, tìm chữ 0, nhập số lượng 49/tổng tiền và validation, QR trước chi tiết, báo chuyển khoản vẫn chưa tự trả tiền, COD/nhân viên xem chi tiết đúng quyền.
- Chrome: **16/16 kiểm tra đạt**, không có lỗi JavaScript. Kiểm thực tế việc chọn loại/gõ/phân trang không reload (biến đánh dấu document vẫn giữ), gõ nhanh không nhận kết quả cũ, giữ focus, giỏ cạnh menu desktop/mobile, nhập 49 và nút ±, hủy/đồng ý xóa, QR trước chi tiết, báo chuyển khoản mở chi tiết, quản lý món cũng tìm không reload; [JSON](browser-menu-ui-results.json).
- Apache đang chạy cổng 18080 đã trả đúng HTML kết quả riêng cho tìm kiếm bằng fetch, HTTP 200: [JSON](http-live-menu-results.json).
- Các báo cáo/ảnh Chrome và test có tên khác bên dưới là chứng cứ các lần chạy trước. Dữ liệu thử Chrome dùng nhahang_browser_feature_test, không tạo đơn trong database demo đang dùng. Thay đổi giao diện này không cần migration.

## Điều chỉnh quyền xem báo cáo

Chỉ **Thu ngân (cashier)** và **Chủ nhà hàng (owner)** được xem doanh thu/thống kê. Gate view-reports áp dụng tại route /admin, DashboardController và menu. Các chức vụ khác bị 403 khi nhập URL; Thu ngân vẫn không có quyền quản lý nhân viên/voucher.

Bộ kiểm thử dành cho thay đổi này: 16 test, 89 assertions đạt trên cả SQLite và MySQL; kết quả MySQL lưu ở [JUnit quyền báo cáo MySQL](test-results-report-permissions-mysql.xml). [JUnit quyền báo cáo SQLite](test-results-report-permissions-sqlite.xml). Bao phủ cả 9 vai trò, khách chưa đăng nhập, hiển thị menu, dữ liệu/biểu đồ và không cấp thêm quyền Chủ cho Thu ngân. Các kết quả toàn suite 100 test và Chrome dưới đây là lần chạy trước điều chỉnh quyền này.

## Chứng cứ thực chạy

| Kiểm tra | Kết quả / chứng cứ |
|---|---|
| Toàn bộ SQLite :memory: | **100 test, 475 assertions, exit 0**; [JUnit](test-results-sqlite-20261018.xml) |
| Toàn bộ MySQL | **100 test, 475 assertions, exit 0**; [JUnit](test-results-mysql-20261018.xml) |
| Migration nâng cấp trên nhahang_demo | Đã sao lưu SQL và source vào .runtime/backup-20261018-before-fixes rồi chạy migration ngày 18; không xóa/seed đè |
| Migration/seeder cài mới | Đã thực chạy trên nhahang_browser_feature_test; Feature Test kiểm đủ 7 chức vụ, 200 bàn, email booking khớp user, trạng thái online và chạy seeder lần hai không ghi đè |
| Kiểm thử trình duyệt | Chrome 155 headless, 13/13 kiểm tra; [JSON](browser-results-20261018.json). Database riêng nhahang_browser_feature_test, URL http://127.0.0.1:18081 |
| QR thực từ canvas | OpenCV giải mã ảnh chụp từ Chrome; payload khớp, số tài khoản 0333820793 và số tiền 25000 của đơn thử đúng; [JSON](qr-check-20261018.json) |
| PHP lint | 114 file, không lỗi cú pháp; [JSON](php-lint-20261018.json) |
| Blade, route, Pint | view:cache, route:cache và Pint chạy đạt; đã clear cache sau kiểm tra |
| Apache hiện tại | httpd -t Syntax OK; GET http://127.0.0.1:18080/ trả 200; liên kết storage đã sửa đúng root mới; [HTTP JSON](http-smoke-20261018.json) |

MySQL test dùng nhahang_feature_test, SQLite dùng bộ nhớ. TestCase chặn tên database MySQL không kết thúc _feature_test. Các XML/HTTP JSON mang ngày 17/10 (tên không có 20261018) là chứng cứ **lịch sử phiên bản trước**, không thay thế báo cáo hiện tại.

## Phạm vi kiểm thử

- AuthenticationTest: đăng nhập/đăng xuất, hash, đăng ký không nâng quyền, email trùng, reset token và rate limit.
- AuthorizationTest: ma trận quyền, sở hữu, URL/HTTP verb, module chỉ chủ.
- CatalogTest: CRUD hai bảng categories/dishes, FK/unique, validation/old input, tìm/lọc/phân trang, rỗng, upload và xóa có liên kết.
- OrderingTest/LoyaltyTest: giá từ DB, snapshot, voucher, transaction/rollback, token chống gửi lặp, hủy và tính điểm.
- CustomerFeaturesTest/PagesTest/CsrfTest: hồ sơ/địa chỉ/booking/review, render dữ liệu liên quan, CSRF 419 khi thiếu token.
- RequestedChangesTest: tiền không làm tròn; 7 chức vụ; chặn owner injection; Gmail ba nhóm; chặn GET/PUT sửa khách; Admin thêm/xóa khách; rỗng tìm kiếm; cart ±/zero; COD đúng bước; online báo chuyển khoản/xác minh riêng/hoàn tất; hủy sau trả tiền cần hoàn; QR CRC; email/số bàn/trùng giờ; top 10 và hai biểu đồ theo quyền view-reports (Thu ngân/Chủ); dữ liệu mẫu cài mới và không seed đè.

Chrome đã kiểm tra đăng nhập khách, tăng giỏ và tổng tiền, giảm về 0 có dialog, hủy dialog giữ 1, đồng ý xóa món, đặt online hiện QR/chờ/chưa trả tiền, khách báo chuyển khoản chưa tự trả tiền, nhân viên xác minh mà đơn vẫn chờ, nav nhân viên không có thực đơn, Admin /admin bị 403, chủ thấy hai biểu đồ, viewport 390px không tràn ngang. Đã xem ảnh desktop/mobile, chỉnh biểu đồ vừa khung và kiểm tra lại. Ảnh/bộ điều khiển Chrome lưu trong .runtime để không trộn công cụ debug vào source nghiệp vụ.

## Chưa kiểm thử / giới hạn

- Không chuyển tiền thật, chưa quét bằng ứng dụng MB trên điện thoại; việc giải mã QR không xác nhận chủ tài khoản hay giao dịch đã vào ngân hàng.
- Không gửi SMTP thật; chưa thử Nginx, tải lớn hoặc nhiều tiến trình đồng thời. Transaction/lock có test chức năng, không gọi đó là kiểm thử tải.
- Chưa import dữ liệu SQL Server thật/chạy mobile cũ. Nhóm vẫn cần kiểm tra trên máy và điện thoại thuyết trình, điền thành viên và xác nhận đề tài với giảng viên.

## Chạy lại an toàn

```powershell
php artisan config:clear
php vendor/phpunit/phpunit/phpunit --log-junit docs/test-results-sqlite-20261018.xml
php vendor/phpunit/phpunit/phpunit --configuration phpunit.mysql.xml --log-junit docs/test-results-mysql-20261018.xml
```

MySQL cần .env.testing riêng theo README; không trỏ vào nhahang_demo hoặc dữ liệu thật. Test tạo/làm mới bảng trong database test riêng.

## Yêu cầu môn học → chức năng/file đáp ứng → trạng thái

| Yêu cầu | Chức năng / file đáp ứng | Trạng thái kiểm tra |
|---|---|---|
| Đọc dự án C# thực tế trước khi chuyển | docs/01-phan-tich-source.md; đối chiếu controller/entity/SQL/View | Đã đọc/phân tích; không chạy C# |
| Dự án mới ngang cấp TrangChu, giữ gốc | Bản ban đầu cạnh TrangChu; người dùng chuyển sang D:\nam4Ki1\LapTrinhMaNguonMo_PHP\NhaHangLaravel | Giữ nguyên C#; bản hiện tại đã sửa cấu hình theo đường dẫn mới |
| PHP/Laravel/MySQL | composer.json/lock, config/database.php, .env.example | Đã chạy PHP 8.3.30, Laravel 13.37.0, MySQL 8.4.3 |
| Eloquent ORM + MVC | app/Models, Controllers, routes/web.php, Blade | Đã chạy qua Feature Test và Apache |
| Migration/Seeder/quan hệ | database/migrations, seeders, model relations | Migrate/seed MySQL mới đạt |
| Blade + Bootstrap | resources/views/layouts/components/shared, public/assets | Blade render/cache, Chrome desktop/mobile và HTTP asset đạt |
| Ít nhất Admin/User | roles, User, RequireRole, Gate; thêm owner giữ nghiệp vụ chủ | Test ma trận quyền đạt |
| Auth hash/login/logout | AuthController, Login/RegisterRequest, User password hashed cast | Feature Test và HTTP login đạt |
| Quyền server, không chỉ ẩn nút | nhóm route auth/role/can; middleware/policies | User gọi URL/HTTP verb admin bị chặn; Apache 403 đạt |
| Quyền sở hữu | OrderPolicy, AddressPolicy, ReviewPolicy, ReservationController | Test truy cập chéo đạt |
| Đăng ký mặc định User | AuthController::register, User không fillable role_id | Test gửi role giả đạt |
| Tài khoản demo | DatabaseSeeder, README | Đã seed và login Admin/User qua Apache |
| CRUD hai bảng nghiệp vụ quan hệ thật | CategoryController/DishController + Requests + views; categories→dishes | CRUD và unique/FK/delete đạt |
| FK/unique/kiểu dữ liệu chính xác | migration restaurant, users/roles; docs/03 | MySQL migrate và ràng buộc trong luồng đạt |
| Form chọn tên dữ liệu liên quan | admin/dishes/form.blade.php, category/supplier relations | Render có quan hệ đạt |
| Xóa bản ghi đang được tham chiếu | restrict FK, controller kiểm tra và bắt lỗi FK | Loại có món, món có đơn, voucher có lịch sử: test đạt |
| Transaction nhiều bảng | OrderService, LoyaltyService, AddressController, bulk delete | Test commit/rollback/replay đạt; tải đồng thời chưa chạy |
| Tìm kiếm/phân trang/giữ điều kiện | paginate()->withQueryString(), shared/search, Bootstrap paginator | Test 14 bản ghi qua 2 trang và giữ q/category đạt |
| Không có dữ liệu/kết quả | forelse/empty trong Blade | Test empty state đạt |
| Validation server / tiếng Việt / old input | app/Http/Requests, lang/vi, component field | Giá/FK/unique/điện thoại/ngày/số lượng và old input theo các test đạt |
| Bỏ qua dữ liệu nhạy cảm client | validated/safe/fillable, Auth user, giá DB, role owner-only | Test role/user_id/total/paid giả đạt |
| CSRF | middleware web Laravel, @csrf trong form | POST thiếu token 419 / hợp lệ lưu đạt |
| Nav theo vai trò, thông báo, xác nhận xóa | layout app, component delete, app.js | Chrome xác nhận xóa/giỏ hàng/QR đạt |
| Asset cục bộ | public/assets, không Vite/npm | HTTP asset/ảnh 200 đạt |
| Dữ liệu mẫu đủ phân trang | 48 món, 16 danh mục/NCC/voucher, 40 tài khoản, 36 đơn | Seeder và kiểm đếm MySQL đạt |
| Phân biệt demo / dữ liệu thật | README, docs/04 | Có ghi rõ chưa import; không đụng DB cũ |
| Báo cáo nhóm / quyền / luồng / use case | docs/02-bao-cao-nhom.md | Đã viết theo triển khai; thông tin nhóm cần điền |
| Thiết kế DB / ERD | docs/03-thiet-ke-database.md, erd.mmd | Đối chiếu migration/model; chưa render bằng công cụ sơ đồ |
| UML Use Case đúng loại | docs/use-case.puml, đặc tả docs/02 | Có actor/ranh giới/include/extend; mã chỉnh sửa sẵn |
| Phân công thực tế, phê duyệt giảng viên | bảng điền trong docs/02 | Cần nhóm xác nhận, không bịa tên/đóng góp |
| Kịch bản demo/kết quả test | docs/02, tài liệu này, JUnit/HTTP JSON | Kịch bản có sẵn, kết quả tự động thực chạy |
| README Laragon cho người mới | README, docs/laragon-apache.conf | Apache/PHP thực chạy; hướng dẫn cài mới/URL/extension/DB/key/asset/404 có đủ |
| Hạn chế và bước tiếp theo | docs/04 và README | Đã nêu rõ SMTP/ngân hàng/mobile/import/kiểm tra trực quan |


## Đối chiếu các lỗi cần sửa ngày 18/10

| Yêu cầu | File/chức năng đáp ứng | Trạng thái |
|---|---|---|
| Bỏ phần ,00 thừa, không làm tròn | Support/Money.php, toàn bộ Blade tiền | Test giá nguyên/lẻ/số lớn đạt |
| Nhiều chức vụ, không chọn chủ | Models/Role.php, AccountRequest, AccountController, seeder | Test quyền/chống role giả/seeder đạt |
| Gmail cố định cho ba nhóm | GmailRequest, components/gmail-email, form register/account/profile | Feature Test đạt |
| Bỏ nút đặt lại | shared/search, query controller, app.js | Test từ khóa trống xóa lọc đạt |
| Nav quản trị, thống kê chỉ Thu ngân/Chủ | layout, routes, Gate view-reports, DashboardController | Bộ Feature Test quyền báo cáo mới nhất; Chrome ghi dưới đây thuộc bản trước |
| Quản trị không sửa hồ sơ khách | AccountRequest/Controller, accounts views | GET/PUT 403; thêm/xóa được, test đạt |
| Quản lý trạng thái đúng bước | Order::nextStatuses, OrderService, admin/orders, order-actions | COD/online/hủy/terminal test đạt |
| QR MB và xác minh thủ công | PaymentQr, config/restaurant, QRCode.js, order detail/report/verify | QR decode + Chrome + Feature Test đạt |
| Top 10 theo số phần bán | MenuController::index, menu/index | Test xếp hạng/lọc đơn đạt |
| Cart ±, zero cần xác nhận | CartController, cart/index, app.js | Feature Test và Chrome đạt |
| Bàn đúng email giữ bàn | DiningTable, migration, TableBookingService, ReservationRequest | Test đúng/sai email/trùng giờ đạt |
