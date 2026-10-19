# Bàn giao nâng cấp giao diện — giai đoạn C và D

Dự án: Nhà Hàng Ngon, tại D:\nam4Ki1\LapTrinhMaNguonMo_PHP\NhaHangLaravel. Thiết kế sáng, xanh lá đậm và điểm nhấn ấm theo mẫu giai đoạn B đã được chấp nhận. Hoàn thành ba vòng thẩm định; kết quả cuối 102/102 kiểm tra trình duyệt đạt.

## 1. Phạm vi và bảo toàn chức năng

Áp dụng layout và component chung cho toàn bộ 55 view gốc, bổ sung bốn partial/component Blade dùng lại. Có sidebar quản trị trên máy tính, drawer trên màn hình nhỏ, điều hướng khách hàng, breadcrumb, bảng, form, nút, thẻ, badge, modal xác nhận, thông báo, skeleton và trạng thái rỗng/lỗi.

Đối chiếu SHA-256 của 84 file trong app, routes và database với bản sao trước giai đoạn C: không file nào thay đổi. Không thay controller, service, model, policy, middleware, migration, seeder hoặc cấu trúc database. Source C# được giữ nguyên. Không đổi tên field, id/action đang dùng hoặc tên route; các id lỗi ARIA và hook UI mới chỉ bổ sung trình bày.

Các quy tắc được giữ: tiền không thêm phần ,00 và không làm tròn; chỉ Chủ nhà hàng/Thu ngân xem báo cáo; người quản trị không sửa tài khoản khách; tìm/chọn loại món không tải lại trang; giỏ hàng nhập số lượng trực tiếp và xác nhận khi về 0; QR trước thông tin đơn; khách báo chuyển khoản rồi nhân viên xác minh; đơn không quay ngược trạng thái; kiểm tra email đặt bàn và quyền sở hữu.

## 2. Thành phần và cách dùng

| Thành phần | Vị trí / quy ước |
|---|---|
| Token | public/assets/ui-tokens.css; màu, chữ, khoảng cách, bo góc, bóng, motion |
| Component nền | public/assets/ui-components.css; dùng chung cho .ui-preview và .ui-app |
| Bootstrap + nghiệp vụ | public/assets/app.css; dùng class hiện hữu, không style inline |
| Giao diện bằng JS | public/assets/app-ui.js; RestaurantUI.enhance/dispose, confirm, markSubmitting, setLoading/showRetry |
| Sự kiện nghiệp vụ hiện hữu | public/assets/app.js; giữ FormData, action, kiểm tra số lượng, tìm trực tiếp, QR |
| Form | x-field và x-gmail-email: lỗi server kèm aria-invalid/aria-describedby |
| Xóa | x-delete: thêm prop item khi có tên đối tượng; không đổi action/method/CSRF |
| Trạng thái rỗng | x-empty-state với prop message; không có nút đặt lại |
| Bảng | .table-responsive > .table; th scope=col; tbody.ui-table-body; .ui-numeric cho tiền/số lượng |
| Icon | shared/ui-icons.blade.php; SVG dùng chung, không thêm thư viện |

Sidebar có nút ẩn/hiện trên desktop, lưu trạng thái tại localStorage. Drawer trên mobile có backdrop, đóng bằng Esc và trả focus. Hàng bảng có hover, focus-within, selected, header sticky trong vùng cuộn; checkbox có vùng bấm 44px và trạng thái chọn một phần. Văn bản dài có ellipsis và tooltip dùng được bằng focus. Nút không bị làm mờ khi chưa hover để giữ độ tương phản.

Thông báo thành công tự đóng sau 7 giây và tạm dừng khi hover/focus; lỗi được giữ đến khi xử lý. Modal giữ focus bên trong, trả focus khi đóng, phân biệt nút nguy hiểm. Khi gửi form dùng aria-busy/aria-disabled và chặn gửi lặp; giữ nguyên giá trị submitter để server nhận đúng dữ liệu.

Tìm kiếm trực tiếp có skeleton, giữ kết quả cũ khi lỗi và có Thử lại. Sau thay danh sách bằng fetch, giải phóng tooltip cũ và khởi tạo UI cho dữ liệu mới. Ảnh dùng lazy loading, decoding async, kích thước/tỷ lệ ảnh theo class để giữ chỗ. Biểu đồ có số liệu ở bảng đi kèm và cuộn riêng khi thiếu chỗ.

## 3. Kiểm tra đã thực hiện

| Kiểm tra | Kết quả và bằng chứng |
|---|---|
| SQLite trong bộ nhớ | 118 tests, 598 assertions đạt; [JUnit](test-results-ui-final-sqlite.xml) |
| MySQL nhahang_feature_test | 118 tests, 598 assertions đạt; [JUnit](test-results-ui-final-mysql.xml) |
| Chrome — màn hình/viewport/quyền/lỗi mạng | 71/71 đạt; [chi tiết vòng 3](ui-review-round-3.json) |
| Chrome — thực đơn, giỏ hàng và QR | 16/16 đạt; [chi tiết](ui-final-interactions.json) |
| Chrome — CRUD, bàn phím, tương phản | 15/15 đạt; [chi tiết](ui-final-extra-checks.json) |
| PHP formatter | Pint đạt với đường dẫn tests/Feature/UiPresentationTest.php; --dirty không dùng được vì thư mục không có Git |
| JS syntax | node --check cho app.js và app-ui.js đạt |
| Apache demo | GET http://127.0.0.1:18080/dang-nhap trả 200 và nạp layout/token/helper mới |

Chạy Feature Test trên database riêng có tên kết thúc _feature_test hoặc SQLite :memory:, theo guard trong tests/TestCase.php. Không chạy migrate:fresh hoặc xóa database demo. Browser dùng nhahang_browser_feature_test, tách biệt database demo và database Feature Test. Tài khoản staff2@example.test trong database browser cũ còn vai trò admin; chỉ fixture này được gán cashier để thử đúng vai trò. Không cập nhật quyền tài khoản trong database đang dùng.

Đã thử tạo loại món, tạo món liên kết với giá lẻ, sửa giá chính xác, chặn xóa loại món còn liên kết, xóa món rồi xóa loại món. Đã thử đăng nhập/đăng xuất, Admin bị chặn báo cáo, Thu ngân và Chủ nhà hàng xem được, truy cập trái quyền, validation, tìm kiếm/phân trang, số lượng 49, hủy/đồng ý xóa khi số lượng 0, QR trước chi tiết, báo chuyển khoản vẫn chờ xác minh. Bộ Feature Test kiểm tra thêm trạng thái đơn, quyền sở hữu, Gmail cố định, đặt bàn, CSRF, voucher và dữ liệu liên quan.

Sau từng nhóm giai đoạn C đã chạy kiểm tra riêng: layout/quyền 23 tests; bảng 10; form 21; nút/thẻ 15; modal/đặt món 60; tải/rỗng/lỗi 15; chi tiết 12; responsive/render 10. Đây là các lần chạy giao nhau, không cộng thành số tests duy nhất.

## 4. Ba vòng thẩm định

| Vòng | Ghi nhận |
|---|---|
| 1 | 69/71 mục đạt. Đối chiếu lại fixture Thu ngân và thời điểm kiểm tra focus; sửa ảnh trùng thuộc tính lazy, trạng thái rỗng thực đơn, badge, colspan và bổ sung ghi nhớ sidebar. [Kết quả](ui-review-round-1.json) |
| 2 | 70/71 mục đạt. Kiểm tra focus còn đọc quá sớm khi backdrop đang đóng; hiệu chỉnh bộ kiểm tra đợi cả modal/backdrop đóng. [Kết quả](ui-review-round-2.json) |
| 3 | 71/71 mục và các kiểm tra tương tác/CRUD bổ sung đều đạt. Bộ kiểm tra bổ sung phải kích hoạt tab Chrome khi kiểm tra focus/timer; lỗi truyền tham số vào CDP đã được sửa trong bộ kiểm tra. Không mở vòng sửa sản phẩm thứ tư. |

## 5. Checklist nghiệm thu

| Yêu cầu | Trạng thái | Bằng chứng / giới hạn |
|---|---|---|
| Chức năng, route, quyền | Đạt trong phạm vi kiểm thử | 118 tests trên mỗi DB; checksum 84 file nghiệp vụ; Chrome các luồng chính |
| Token và nhất quán | Đạt | Token trung tâm; bỏ 5 inline style trong Blade; không thêm màu HEX vào view/app.css |
| Bảng hover/focus/selected/sticky/numeric | Đạt | Chrome, checkbox chọn tất cả/một phần, header trong vùng cuộn, tooltip |
| Sắp xếp bảng | Không áp dụng | Bản gốc không có sắp xếp; không thêm nút giả hoặc đổi truy vấn trong đợt UI |
| Animation và reduced motion | Đạt ở các thành phần đã thử | Token 120/200/320ms; hàng lệch 25ms, tối đa 12 hàng; reduced-motion; không animate chiều rộng/chiều cao sidebar |
| Form và phản hồi | Đạt | Liên kết lỗi server, focus, spinner, chặn gửi trùng, modal, thông báo tự đóng có pause |
| Trạng thái tải/rỗng/lỗi | Đạt | Thực đơn/admin tìm trực tiếp; lỗi mạng được giả lập; thử lại giữ cùng điều kiện |
| Tương phản và bàn phím | Đạt ở mẫu đã đo | Tab, Esc, modal/drawer focus, lỗi form, các cặp màu dưới đây; chưa phải đánh giá WCAG toàn diện |
| Responsive 360/768/1024/1440 | Đạt ở Chrome | Không cuộn ngang toàn trang trong các trang đo; bảng/biểu đồ có vùng cuộn riêng |
| 60 fps, CLS, Lighthouse và hiệu năng so sánh | Chưa kiểm chứng | Có timing tải trang trong JSON nhưng khác dữ liệu/môi trường; không dùng để kết luận hiệu năng không giảm |
| Chrome Windows headless | Đạt theo các test đã lưu | Đây là mô phỏng viewport, không phải thử trên điện thoại vật lý |
| Edge, Firefox, Safari | Chưa kiểm chứng | Không tuyên bố đã chạy |

Không dùng count-up cho tiền/KPI để giá trị báo cáo hiện ngay và ổn định. Không giảm opacity nút thao tác xuống 0.6 để giữ độ tương phản. Không thêm dark mode vì mẫu được duyệt là giao diện sáng. Các trang lỗi 403/404 đã duyệt bằng Chrome; không cố tạo sự cố thật để mở 500/503. Các form còn lại được kiểm tra render và bởi Feature Test; không tuyên bố đã bấm tay từng nút ở mọi tổ hợp dữ liệu.

Các cặp màu lấy từ computed style trên trang quản lý đơn, không chỉ từ mã HEX:

| Mẫu | Màu chữ / nền | Tương phản |
|---|---|---|
| body | rgb(28, 48, 38) / rgb(245, 247, 243) | 12.99:1 |
| .text-muted | rgb(88, 103, 94) / rgb(245, 247, 243) | 5.54:1 |
| .btn-primary | rgb(255, 255, 255) / rgb(31, 90, 67) | 8.07:1 |
| .btn-outline-danger | rgb(180, 49, 58) / rgb(255, 255, 255) | 6.1:1 |
| .badge-status | rgb(130, 83, 7) / rgb(255, 244, 220) | 6.03:1 |
| .form-control | rgb(28, 48, 38) / rgb(255, 255, 255) | 14:1 |

Custom CSS/JS nạp trong ứng dụng có tổng 64568 byte trước nén, gồm token, component, adapter và hai file JS. Bootstrap 5.2.3 và QR cục bộ được giữ nguyên; không thêm dependency/font/CDN. ui-preview.js chỉ phục vụ trang mẫu, không được nạp trong layout ứng dụng.

## 6. Ảnh trước và sau

| Màn hình | Trước | Sau |
|---|---|---|
| Đăng nhập 1440 | [Ảnh](ui-before-login-1440.png) | [Ảnh](ui-after-login-1440.png) |
| Tổng quan 1440 | [Ảnh](ui-before-dashboard-1440.png) | [Ảnh](ui-after-dashboard-1440.png) |
| Trạng thái đơn 1440 | [Ảnh](ui-before-orders-1440.png) | [Ảnh](ui-after-orders-1440.png) |
| Món ăn 1440 | [Ảnh](ui-before-dishes-1440.png) | [Ảnh](ui-after-dishes-1440.png) |
| Món ăn 1024 | [Ảnh](ui-before-dishes-1024.png) | [Ảnh](ui-after-dishes-1024.png) |
| Món ăn 768 | [Ảnh](ui-before-dishes-768.png) | [Ảnh](ui-after-dishes-768.png) |
| Món ăn 360 | [Ảnh](ui-before-dishes-360.png) | [Ảnh](ui-after-dishes-360.png) |
| Form món ăn | [Ảnh](ui-before-dish-form-1440.png) | [Ảnh](ui-after-dish-form-1440.png) |
| Lỗi form | [Ảnh](ui-before-dish-form-errors-1440.png) | [Ảnh](ui-after-dish-form-errors-1440.png) |
| Thực đơn 1440 | [Ảnh](ui-before-menu-1440.png) | [Ảnh](ui-after-menu-1440.png) |

Ảnh bổ sung: [thực đơn 360](ui-after-menu-360.png), [xác nhận xóa](ui-after-confirmation-1440.png), [lỗi mạng + thử lại](ui-after-search-error-1440.png), [QR trước chi tiết](ui-after-payment-first.png). Ảnh trước cao 900px, ảnh sau thường cao 1000px; cùng độ rộng ghi trong tên file. Dữ liệu trong ảnh là dữ liệu demo.

## 7. Danh sách file thay đổi

33 file đã sửa và 6 file triển khai/test được thêm trong giai đoạn C, chưa tính tài liệu/ảnh/README. Hash trước/sau và từng asset: [manifest](ui-files-changed.json). Các view không sửa trực tiếp vẫn nhận layout và CSS chung.

| File | Thay đổi |
|---|---|
| [resources/views/admin/accounts/index.blade.php](../resources/views/admin/accounts/index.blade.php) | Chuẩn hóa bảng, ảnh hoặc badge; giữ truy vấn, field và action |
| [resources/views/admin/categories/index.blade.php](../resources/views/admin/categories/index.blade.php) | Chuẩn hóa bảng, ảnh hoặc badge; giữ truy vấn, field và action |
| [resources/views/admin/dashboard.blade.php](../resources/views/admin/dashboard.blade.php) | Chuẩn hóa bảng, ảnh hoặc badge; giữ truy vấn, field và action |
| [resources/views/admin/dishes/form.blade.php](../resources/views/admin/dishes/form.blade.php) | Chuẩn hóa bảng, ảnh hoặc badge; giữ truy vấn, field và action |
| [resources/views/admin/dishes/index.blade.php](../resources/views/admin/dishes/index.blade.php) | Chuẩn hóa bảng, ảnh hoặc badge; giữ truy vấn, field và action |
| [resources/views/admin/dishes/show.blade.php](../resources/views/admin/dishes/show.blade.php) | Chuẩn hóa bảng, ảnh hoặc badge; giữ truy vấn, field và action |
| [resources/views/admin/orders/index.blade.php](../resources/views/admin/orders/index.blade.php) | Chuẩn hóa bảng, ảnh hoặc badge; giữ truy vấn, field và action |
| [resources/views/admin/suppliers/index.blade.php](../resources/views/admin/suppliers/index.blade.php) | Chuẩn hóa bảng, ảnh hoặc badge; giữ truy vấn, field và action |
| [resources/views/admin/vouchers/index.blade.php](../resources/views/admin/vouchers/index.blade.php) | Chuẩn hóa bảng, ảnh hoặc badge; giữ truy vấn, field và action |
| [resources/views/auth/forgot.blade.php](../resources/views/auth/forgot.blade.php) | Kích thước panel dùng class chung thay inline style |
| [resources/views/auth/login.blade.php](../resources/views/auth/login.blade.php) | Kích thước panel dùng class chung thay inline style |
| [resources/views/auth/register.blade.php](../resources/views/auth/register.blade.php) | Kích thước panel dùng class chung thay inline style |
| [resources/views/auth/reset.blade.php](../resources/views/auth/reset.blade.php) | Kích thước panel dùng class chung thay inline style |
| [resources/views/cart/index.blade.php](../resources/views/cart/index.blade.php) | Chuẩn hóa bảng, ảnh hoặc badge; giữ truy vấn, field và action |
| [resources/views/components/delete.blade.php](../resources/views/components/delete.blade.php) | Hộp xác nhận có tên đối tượng; giữ action, CSRF, method |
| [resources/views/components/field.blade.php](../resources/views/components/field.blade.php) | Liên kết lỗi server với ô nhập, giữ giá trị và tên field |
| [resources/views/components/gmail-email.blade.php](../resources/views/components/gmail-email.blade.php) | Liên kết lỗi server với ô nhập, giữ giá trị và tên field |
| [resources/views/components/sales-chart.blade.php](../resources/views/components/sales-chart.blade.php) | Màu biểu đồ từ token, nhãn đọc được và cuộn trong khối |
| [resources/views/layouts/app.blade.php](../resources/views/layouts/app.blade.php) | Layout, landmark, sidebar, điều hướng theo quyền và asset cục bộ |
| [resources/views/menu/home.blade.php](../resources/views/menu/home.blade.php) | Ảnh, trạng thái rỗng dùng lại và giao diện thực đơn |
| [resources/views/menu/results.blade.php](../resources/views/menu/results.blade.php) | Ảnh, trạng thái rỗng dùng lại và giao diện thực đơn |
| [resources/views/menu/show.blade.php](../resources/views/menu/show.blade.php) | Ảnh, trạng thái rỗng dùng lại và giao diện thực đơn |
| [resources/views/orders/index.blade.php](../resources/views/orders/index.blade.php) | Chuẩn hóa bảng, ảnh hoặc badge; giữ truy vấn, field và action |
| [resources/views/orders/show.blade.php](../resources/views/orders/show.blade.php) | Ngữ nghĩa tiến trình, bảng tiền và chi tiết đơn |
| [resources/views/profile/edit.blade.php](../resources/views/profile/edit.blade.php) | Chuẩn hóa bảng, ảnh hoặc badge; giữ truy vấn, field và action |
| [resources/views/reservations/create.blade.php](../resources/views/reservations/create.blade.php) | Chuẩn hóa bảng, ảnh hoặc badge; giữ truy vấn, field và action |
| [resources/views/reservations/index.blade.php](../resources/views/reservations/index.blade.php) | Chuẩn hóa bảng, ảnh hoặc badge; giữ truy vấn, field và action |
| [resources/views/reviews/index.blade.php](../resources/views/reviews/index.blade.php) | Chuẩn hóa bảng, ảnh hoặc badge; giữ truy vấn, field và action |
| [resources/views/shared/dish-card.blade.php](../resources/views/shared/dish-card.blade.php) | Chuẩn hóa bảng, ảnh hoặc badge; giữ truy vấn, field và action |
| [public/assets/app.css](../public/assets/app.css) | Ánh xạ Bootstrap, kiểu riêng theo nghiệp vụ và responsive |
| [public/assets/app.js](../public/assets/app.js) | Gắn modal bất đồng bộ và phản hồi tải vào các hook hiện có |
| [public/assets/ui-components.css](../public/assets/ui-components.css) | Mở phạm vi component từ trang mẫu sang ứng dụng |
| [public/assets/ui-tokens.css](../public/assets/ui-tokens.css) | Bảng token trung tâm dùng cho trang thật và trang mẫu |
| [resources/views/shared/management-nav.blade.php](../resources/views/shared/management-nav.blade.php) | Menu quản trị và mục đang chọn, giữ Gate |
| [resources/views/shared/ui-icons.blade.php](../resources/views/shared/ui-icons.blade.php) | Sprite SVG cục bộ dùng chung |
| [resources/views/shared/confirm-modal.blade.php](../resources/views/shared/confirm-modal.blade.php) | Modal Bootstrap dùng chung, Esc và mô tả truy cập được |
| [resources/views/components/empty-state.blade.php](../resources/views/components/empty-state.blade.php) | Trạng thái rỗng, gợi ý từ khóa và nút đưa focus về tìm kiếm |
| [public/assets/app-ui.js](../public/assets/app-ui.js) | Tiện ích giao diện dùng chung: bảng, modal, focus, loading, retry |
| [tests/Feature/UiPresentationTest.php](../tests/Feature/UiPresentationTest.php) | Kiểm thử lỗi validation thật, ARIA và email Gmail cố định |

README được bổ sung đường dẫn tới báo cáo này. Tài liệu và ảnh mới nằm trong docs; script Chrome nằm tại .runtime/review-ui.cjs, .runtime/check-ui-interactions.cjs, .runtime/review-ui-extra.cjs. Không đọc/sửa vendor, node_modules hoặc build cache thủ công; test/formatter chạy qua công cụ của dự án.

## 8. Tùy biến nhanh

Đổi màu thương hiệu tại ui-tokens.css: chỉnh ui-primary, ui-primary-hover, ui-primary-strong, ui-primary-soft, ui-primary-selected và ui-accent; đo lại tương phản cho cả nền thường/hover/focus/badge. Không đổi màu rải rác trong từng Blade. Bảng token đầy đủ ở [tài liệu giai đoạn B](08-he-thong-thiet-ke-ui.md), trang mẫu tại /ui-style-guide.html.

Tắt animation toàn ứng dụng: thêm data-ui-motion="off" vào thẻ html. Có thể thử trong DevTools bằng lệnh dưới; gỡ thuộc tính để bật lại. Hệ điều hành chọn Giảm chuyển động cũng được tôn trọng.

    document.documentElement.dataset.uiMotion = "off";
    delete document.documentElement.dataset.uiMotion;

Thêm bảng mới: giữ truy vấn/phân trang hiện có, bọc table trong .table-responsive, thêm scope="col" cho th, .ui-table-body cho tbody và .ui-numeric cho cột số. Dùng x-delete với item là tên bản ghi, giữ CSRF và method. Với fragment fetch mới, gọi RestaurantUI.dispose trước khi thay nội dung rồi RestaurantUI.enhance sau khi thay. Không thêm nút sắp xếp nếu server chưa xử lý.

## 9. Chạy lại và khôi phục

Ứng dụng demo hiện tại: http://127.0.0.1:18080/. Có thể Ctrl+F5 nếu trình duyệt còn giữ asset cũ. Không cần Node build hoặc cài dependency mới. Cách chạy Laragon và tài khoản demo giữ nguyên trong README. Chỉ Chủ nhà hàng và Thu ngân được xem báo cáo. Với database cũ, Chủ nhà hàng có thể gán chức vụ Thu ngân trong Nhân viên & chức vụ; nâng cấp UI không tự đổi quyền các tài khoản đang có.

Lệnh test tại thư mục dự án (thay php bằng đường dẫn PHP Laragon nếu PATH chưa có):

    php artisan test --compact
    php artisan test --compact --configuration=phpunit.mysql.xml
    node --check public/assets/app.js
    node --check public/assets/app-ui.js

Database MySQL thử phải tồn tại và cấu hình theo README/.env.testing; guard sẽ chặn tên database không phù hợp. Script Chrome chỉ dùng máy chủ --env=browser cổng 18081 và database nhahang_browser_feature_test. Không trỏ các script CRUD sang database đang dùng.

Bản sao trước C: .runtime/ui-stage-c-backup (251 file ban đầu, kèm README bổ sung trước khi chỉnh). Khôi phục bằng PowerShell sau khi đóng phiên demo đang gửi dữ liệu; các lệnh chỉ chép lại source UI, không đổi database:

    $projectPath = (Resolve-Path "D:\nam4Ki1\LapTrinhMaNguonMo_PHP\NhaHangLaravel").Path
    $restorePath = Join-Path $projectPath ".runtime\ui-stage-c-backup"
    Copy-Item -LiteralPath (Join-Path $restorePath "resources\views") -Destination (Join-Path $projectPath "resources") -Recurse -Force
    Copy-Item -LiteralPath (Join-Path $restorePath "public\assets") -Destination (Join-Path $projectPath "public") -Recurse -Force
    Copy-Item -LiteralPath (Join-Path $restorePath "README.md") -Destination (Join-Path $projectPath "README.md") -Force

Các partial/asset mới còn trên ổ đĩa nhưng layout cũ không sử dụng. Nếu khôi phục giao diện cũ, bỏ test UiPresentationTest mới vì test này yêu cầu liên kết lỗi ARIA của giao diện mới. Xóa view đã biên dịch bằng lệnh chuẩn php artisan view:clear nếu Laravel vẫn hiện template cũ; không sửa file cache bằng tay. Bản sao giai đoạn B và ảnh giai đoạn A cũng được giữ.
