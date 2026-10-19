# Giai đoạn A — Khảo sát căn cứ báo cáo Word

Tên đề tài đề xuất: Xây dựng website quản lý nhà hàng và đặt món trực tuyến bằng Laravel và MySQL.

Bản sản phẩm dùng làm căn cứ: NhaHangLaravel hiện tại. Hai tệp PROMPT WORD gửi liên tiếp có nội dung giống nhau; vị trí đầu ra theo yêu cầu mới nhất của người dùng là cùng thư mục README.md. Chỉ tạo tài liệu, không thay source/cấu hình hoặc tác động database.

## Mức xác minh và kết quả đã có

1. Có mã và có bằng chứng thực thi kiểm thử đã lưu.
2. Có mã/tài liệu nhưng chưa đủ căn cứ kết luận trọn chức năng.
3. Chưa triển khai/chưa xác minh thực tế; chỉ là giới hạn hoặc đề xuất.

Đã đọc routes, middleware/Gate, controller, service đặt món/đặt bàn/voucher, model/quyền sở hữu, Form Request, migration/seeder, các view liên quan và kiểm thử. Các nhận định không chỉ lấy từ README hay tên nút.

XML mới nhất trong docs ghi 118 tests, 598 assertions, 0 failures, 0 errors trên từng hệ SQLite/MySQL. JSON trình duyệt ghi 71 + 16 + 15 = 102 kiểm tra đạt. Đây là kết quả đã thực thi và bàn giao ở đợt triển khai UI trước; KHÔNG phải kết quả chạy lại trong đợt viết Word. Các file sửa/thêm có trong manifest UI vẫn khớp checksum đã lưu. Số tests không được cộng thành 236 test khác nhau hoặc coi 102 bước Chrome là 102 người dùng khảo sát.

Bằng chứng: test-results-ui-final-sqlite.xml; test-results-ui-final-mysql.xml; ui-review-round-3.json; ui-final-interactions.json; ui-final-extra-checks.json.

## Bảng đối chiếu bằng chứng

| Mã | Chức năng/yêu cầu | File/thành phần và kiểm tra | Mức | Nội dung được kết luận |
|---|---|---|---|---|
| BC01 | Đăng nhập, đăng xuất, đăng ký | app/Http/Controllers/AuthController.php; app/Models/User.php; tests/Feature/AuthenticationTest.php | 1 | Auth Laravel; hash mật khẩu; regenerate/invalidate session; đăng ký cố định User. Không đưa mật khẩu demo vào báo cáo. |
| BC02 | Quyền báo cáo doanh thu | routes/web.php; app/Providers/AppServiceProvider.php; app/Http/Controllers/DashboardController.php; tests/Feature/RequestedChangesTest.php | 1 | Chỉ owner/cashier được xem; admin và các chức vụ còn lại bị chặn tại server. |
| BC03 | Bảy chức vụ nhân viên | app/Models/Role.php; app/Models/User.php; app/Http/Middleware/RequireRole.php; app/Http/Requests/AccountRequest.php | 1 | Mỗi tài khoản có một role_id. Có 7 tên chức vụ nhân viên; chưa có ma trận quyền chuyên biệt cho từng bộ phận ngoài các Gate đã triển khai. |
| BC04 | Quản lý khách hàng | app/Http/Controllers/AccountController.php; tests/Feature/RequestedChangesTest.php; tests/Feature/AuthorizationTest.php | 1 | Admin/chủ thêm, xem và xóa khi chưa có tham chiếu; GET edit và PUT update khách đều bị chặn. |
| BC05 | CRUD loại món và món ăn có quan hệ | app/Http/Controllers/CategoryController.php; app/Http/Controllers/DishController.php; database/migrations/2026_10_17_143034_create_restaurant_tables.php; tests/Feature/CatalogTest.php; docs/ui-final-extra-checks.json | 1 | Đủ CRUD, chọn tên loại/NCC, FK/unique; chặn xóa loại có món hoặc món có lịch sử. Xóa nhiều là transaction toàn bộ. |
| BC06 | Tìm kiếm, lọc và phân trang | app/Http/Controllers/MenuController.php; resources/views/menu/results.blade.php; public/assets/app.js; tests/Feature/MenuInteractionTest.php; docs/ui-final-interactions.json | 1 | Thực đơn dùng fetch trả fragment Blade; tìm tên/mô tả, chọn loại và chuyển trang không tải lại document. Danh sách khác tùy module vẫn dùng GET/redirect. |
| BC07 | Giỏ hàng nhập số lượng | app/Http/Controllers/CartController.php; resources/views/cart/index.blade.php; public/assets/app.js; docs/ui-final-interactions.json | 1 | Giỏ tối đa 50 món khác nhau, mỗi món 1–99 phần; có nhập trực tiếp, ±, xác nhận xóa khi về 0, reload tính tổng. |
| BC08 | Đặt món nhất quán | app/Services/OrderService.php; app/Http/Requests/CheckoutRequest.php; tests/Feature/OrderingTest.php | 1 | Giá/chủ đơn từ server, snapshot tên/giá/địa chỉ; transaction lưu đơn/chi tiết/voucher; token chống gửi lặp. Không suy diễn thành kiểm thử tải. |
| BC09 | QR trước thông tin đơn | app/Http/Controllers/OrderController.php; app/Support/PaymentQr.php; resources/views/orders/payment.blade.php; tests/Feature/MenuInteractionTest.php; docs/ui-final-interactions.json | 1 | Khách online chưa báo chuyển khoản xem QR trước; bấm báo mới xem chi tiết. Không tự nhận tiền, không tích hợp API ngân hàng. Ẩn thông tin ngân hàng thật trong báo cáo. |
| BC10 | Trạng thái COD/online | app/Models/Order.php; app/Services/OrderService.php; tests/Feature/RequestedChangesTest.php | 1 | COD 1→2→3→4; online đã xác minh 1→2→3→6. Hủy từ 1/2/3 với nhân viên; khách chỉ hủy đơn mình ở 1. 4/5/6 kết thúc. |
| BC11 | Phân tách báo tiền và xác minh | app/Services/OrderService.php::reportPayment/verifyPayment; app/Models/Order.php; tests/Feature/RequestedChangesTest.php | 1 | Khách báo không tự bật is_paid; nhân viên xác minh không tự đổi bước thực hiện. Hủy sau nhận tiền gắn refund_required, không hoàn tiền tự động. |
| BC12 | Số tiền không làm tròn | app/Support/Money.php; tests/Feature/RequestedChangesTest.php; docs/ui-final-extra-checks.json | 1 | Tính bằng đơn vị nguyên ở mức 1/100 đồng và decimal(18,2); hiển thị bỏ phần thập phân bằng 0, giữ phần lẻ có nghĩa. Không dùng float để cộng tiền đơn. |
| BC13 | Đặt bàn và email | app/Services/TableBookingService.php; app/Http/Controllers/ReservationController.php; tests/Feature/RequestedChangesTest.php | 1 | Có bảng bàn 1–200, giữ theo cấu hình 120 phút, chặn chồng thời gian; kiểm email khi đặt món. Chưa có sơ đồ và sức chứa từng bàn. |
| BC14 | Điểm và voucher | app/Services/LoyaltyService.php; app/Services/OrderService.php; tests/Feature/LoyaltyTest.php; tests/Feature/OrderingTest.php | 1 | Điểm = floor(tổng đơn hoàn tất đã trả tiền/10.000 đồng)×10 trừ points_spent; khóa user/offer; snapshot quyền lợi; mã dùng một lần; hủy mở lại mã còn hạn. |
| BC15 | Hồ sơ, địa chỉ, đánh giá và sở hữu | app/Http/Controllers/ProfileController.php; app/Http/Controllers/AddressController.php; app/Http/Controllers/ReviewController.php; app/Policies; tests/Feature/CustomerFeaturesTest.php; tests/Feature/AuthorizationTest.php | 1 | Khách sửa dữ liệu của mình; đổi mật khẩu cần mật khẩu hiện tại; địa chỉ mặc định được giữ bằng transaction, không phải unique index có điều kiện. |
| BC16 | Gmail cố định trong giao diện hồ sơ | resources/views/profile/edit.blade.php; app/Http/Requests/GmailRequest.php; app/Http/Requests/ProfileRequest.php; tests/Feature/RequestedChangesTest.php::test_profile_gmail_suffix_is_fixed_for_every_role | 2 | Server đã kiểm thử nhận email_local và ghép Gmail, nhưng view hồ sơ hiện vẫn dùng input email đầy đủ. Không kết luận UI cố định hậu tố trên mọi màn hình. Không sửa source trong nhiệm vụ báo cáo. |
| BC17 | Thống kê và biểu đồ | app/Http/Controllers/DashboardController.php; resources/views/admin/dashboard.blade.php; tests/Feature/RequestedChangesTest.php | 1 | Chỉ đơn is_paid và trạng thái 4/6; kỳ theo created_at, tối đa 366 ngày; doanh thu sau giảm khác doanh số món trước giảm. Nhóm loại món theo danh mục hiện tại. Không phải báo cáo kế toán đã đối soát ngân hàng. |
| BC18 | Top món bán chạy | app/Http/Controllers/MenuController.php; tests/Feature/RequestedChangesTest.php::test_menu_top_ten_counts_completed_sales_including_online | 1 | Thực đơn chọn tối đa 10 món có doanh số từ đơn hoàn tất đã trả tiền. Trang chủ sắp xếp theo sold nhưng không có điều kiện whereHas giống trang thực đơn; không tuyên bố hai tập luôn đồng nhất. |
| BC19 | Migration/Seeder và dữ liệu mẫu | database/migrations; database/seeders/DatabaseSeeder.php; database/seeders/ReferenceDataSeeder.php; tests/Feature/RequestedChangesTest.php | 1 | 23 bảng tổng cộng, gồm 16 bảng nghiệp vụ/xác thực và 7 bảng hạ tầng; dữ liệu seed là demo. Chưa import database SQL Server thật. |
| BC20 | Validation, CSRF và whitelist | app/Http/Requests; bootstrap/app.php; app/Models/User.php; tests/Feature/CsrfTest.php; tests/Feature/UiPresentationTest.php | 1 | Form Request tiếng Việt, FK/unique/old input, CSRF; lỗi liên kết ARIA. Không nhận quyền, chủ đơn hoặc tổng tiền tùy ý từ client. |
| BC21 | Giao diện responsive và phản hồi | resources/views/layouts/app.blade.php; public/assets/app-ui.js; public/assets/ui-tokens.css; docs/ui-review-round-3.json; docs/ui-final-extra-checks.json | 1 | Có ảnh và kiểm tra Chrome ở 360/768/1024/1440; focus/modal/drawer, selected/sticky, loading/retry, reduced motion. Không suy rộng sang mọi trình duyệt hoặc WCAG toàn diện. |
| BC22 | Tự động ngân hàng, hoàn tiền, SMTP thật | app/Services/OrderService.php; config/mail.php; docs/04-chuyen-doi-va-gioi-han.md | 3 | Chưa có nhận giao dịch ngân hàng/hoàn tiền tự động; chưa xác minh gửi SMTP thật. Chỉ trình bày giới hạn/hướng phát triển. |
| BC23 | Kho nguyên liệu, ca làm, thuế, mobile và tải lớn | routes/web.php; app/Models/Role.php; docs/04-chuyen-doi-va-gioi-han.md; docs/09-ban-giao-nang-cap-ui.md | 3 | Tên chức vụ kho/bếp không đồng nghĩa có module nguyên liệu, ca/lương, hóa đơn thuế. Chưa triển khai lại API mobile cũ; chưa có đo tải, FPS, CLS hoặc Lighthouse. |
| BC24 | Bối cảnh chuyển C# sang Laravel | docs/01-phan-tich-source.md; docs/04-chuyen-doi-va-gioi-han.md; source Laravel hiện tại | 2 | Có hồ sơ chuyển đổi trước đó; đợt viết báo cáo này lấy sản phẩm Laravel cuối làm căn cứ. Không trộn chức năng C# và Laravel hoặc khẳng định đã chạy lại C#; chỉ thêm mục chuyển đổi khi yêu cầu môn học cần. |

## Các điểm bắt buộc diễn đạt chính xác

- Không mô tả một tài khoản có nhiều role đồng thời: users chỉ có một role_id. Bảy chức vụ phần lớn dùng quyền vận hành chung; quyền báo cáo chỉ cashier/owner và quản lý nhân viên/voucher chỉ owner.
- Email_local đã có kiểm thử server, nhưng trang hồ sơ đang dùng trường email đầy đủ; ghi khác biệt này thành hạn chế UI.
- QR không phải cổng thanh toán tự đối soát. Không đưa số tài khoản thật, ảnh QR mang thông tin thật hoặc mã xác thực vào tài liệu. Các ảnh QR có sẵn sẽ không dùng nguyên trạng.
- Thống kê lọc theo ngày tạo đơn; danh mục lịch sử dùng danh mục hiện tại. Không gọi là báo cáo kế toán đã kiểm toán.
- Dữ liệu demo và kiểm thử không chứng minh triển khai thương mại, khảo sát khách hàng hoặc hiệu năng tải. Không đưa mật khẩu demo vào báo cáo.
- Trường/khoa/môn/lớp/GV/thành viên/MSSV, phân công thực tế và phê duyệt đề tài đều cần nhóm xác nhận; để [CẦN BỔ SUNG] khi chưa có.

## Môi trường soạn Word và render

| Công cụ/điều kiện | Kết quả kiểm tra | Giới hạn hiện tại |
|---|---|---|
| Python | 3.13.5; lxml, Pillow, win32com có sẵn | python-docx, PyMuPDF, reportlab chưa có; không cài dependency vào dự án |
| Pandoc | 2.12 đã chạy --version | Có thể tạo .docx chỉnh sửa được; chưa tạo báo cáo ở A |
| Microsoft Word | WINWORD.EXE có trong Office16; Word.Application đăng ký COM | Chưa khởi tạo Word/render/cập nhật field, chưa xác nhận giấy phép hay số trang |
| Times New Roman | Có đủ times.ttf, timesbd.ttf, timesi.ttf, timesbi.ttf trong Windows Fonts | Chưa kiểm tra font sau render báo cáo |
| Sơ đồ | Graphviz dot.exe và Java có sẵn | Cần dựng/kiểm lại ERD/UML theo model/migration khi được duyệt đề cương |
| Web | Đã tìm và mở tài liệu chính thức Laravel 13.x, MySQL 8.4, Bootstrap 5.2, PHP | Chỉ nguồn mở đọc được mới đưa vào trích dẫn; không dùng trang PHP lỗi truy cập |
| Connector Word | Không có phiên document control đang kết nối | Có thể dùng Word COM cục bộ, không phụ thuộc connector |
| Phân quyền filesystem | Chỉ đọc mặc định; ghi báo cáo cần công cụ xin quyền ghi | Chỉ ghi tài liệu; không chạm DB và source ứng dụng |

Không tuyên bố đã render PDF hoặc đạt 40–50 trang ở giai đoạn A. Ở C/D sẽ dùng Word để cập nhật TOC, PAGE, SEQ, REF, xuất PDF và kiểm tra trực quan, tối đa ba vòng; nếu Word không chạy được thì báo đúng phần chưa xác minh.

## Nguồn chính thức đã mở đọc để chuẩn bị trích dẫn

- Laravel, Authentication, phiên bản 13.x: https://laravel.com/framework/docs/13.x/authentication
- Laravel, Eloquent: Getting Started, phiên bản 13.x: https://laravel.com/framework/docs/13.x/eloquent
- Laravel, Authorization, phiên bản 13.x: https://laravel.com/framework/docs/13.x/authorization
- Laravel, Database: Migrations, phiên bản 13.x: https://laravel.com/framework/docs/13.x/migrations
- Oracle, MySQL 8.4 Reference Manual — FOREIGN KEY Constraints: https://dev.mysql.com/doc/refman/8.4/en/create-table-foreign-keys.html
- Bootstrap team, Get started with Bootstrap, phiên bản 5.2: https://getbootstrap.com/docs/5.2/getting-started/introduction/
- PHP Documentation Group, Password Hashing: https://www.php.net/passwords

Ngày truy cập của các nguồn trên: 18/10/2026. Chưa đánh số IEEE cuối cùng vì số phải theo thứ tự xuất hiện trong bản thảo được duyệt. Đây là nguồn lý thuyết; hoạt động dự án phải dẫn chứng source/test nội bộ riêng.

## Khả năng viết 40–50 trang nội dung chính

Có đủ cơ sở để đề xuất mục tiêu 40–50 trang thực chất nhờ 10 nhóm use case, 23 bảng (16 nghiệp vụ/xác thực + 7 hạ tầng), giao dịch nhiều bảng, máy trạng thái đơn, quyền sở hữu, đặt bàn, snapshot/voucher và bằng chứng kiểm thử. Phần lý thuyết sẽ gọn; trọng tâm là phân tích, thiết kế, triển khai và kiểm thử. Sơ đồ và ảnh minh họa có nguồn thật; không phóng hình, lặp câu hoặc thêm trang trắng để đủ trang. Số trang chỉ xác nhận sau render, tính từ Mở đầu đến hết Kết luận.

## Thông tin cần xác nhận một lần ở cuối A

1. Có mẫu/quy định riêng của trường hoặc giảng viên không? Nếu chưa có, dùng A4, TNR 13, giãn 1,5; lề trái 3,5, phải 2, trên/dưới 2,5 cm theo prompt.
2. Nếu có, cung cấp tên trường/khoa/môn, giảng viên, lớp, danh sách thành viên và MSSV; thiếu để [CẦN BỔ SUNG], không bịa đóng góp.
3. Mặc định tập trung sản phẩm Laravel cuối; bối cảnh C# chỉ trình bày ngắn. Nếu môn yêu cầu chương chuyển đổi riêng, nhóm cần cho biết.
4. Xác nhận chuyển sang B — đề cương đến Heading 3 và kế hoạch dung lượng. Tệp PROMPT WORD yêu cầu dừng cuối A và cuối B; chưa tạo file Word chính trước các mốc duyệt này.

## Vị trí bàn giao sau khi duyệt A/B

- BaoCaoDoAn_QuanLyNhaHang_Laravel_MySQL.docx đặt ngay tại thư mục gốc dự án, cùng cấp README.md.
- BaoCaoDoAn_QuanLyNhaHang_Laravel_MySQL.pdf cùng vị trí, xuất từ bản Word cuối nếu render thực sự chạy được.
- Bảng bằng chứng, bản kiểm định và hướng dẫn cập nhật trường lưu trong docs, tách khỏi nội dung báo cáo.
- Kiểm tra quy định của trường về khai báo sử dụng AI; nhóm phải tự đọc lại, đối chiếu sản phẩm trước khi nộp.
