# Giai đoạn A — Khảo sát giao diện hiện tại

Ngày khảo sát: 18/10/2026. Chỉ khảo sát và lưu chứng cứ; chưa triển khai Giai đoạn B/C.

## Dự án và phạm vi đã xác định

- Root: D:\nam4Ki1\LapTrinhMaNguonMo_PHP\NhaHangLaravel.
- Frontend: Laravel Blade, Bootstrap **5.2.3** cục bộ, vanilla JavaScript; 55 view, 12 phần tử table.
- CSS/JS đang dùng: public/assets/app.css (3.771 byte), app.js (6.119 byte). Bootstrap CSS 194.901 byte; Bootstrap bundle JS 80.420 byte; QRCode.js 19.927 byte. Đây là kích thước file, không phải kích thước truyền nén hay điểm Lighthouse.
- Layout chung: resources/views/layouts/app.blade.php. Thành phần dùng chung: components/field, gmail-email, delete, sales-chart; shared/search, dish-card, cart-link, order-actions, payment-qr.
- Đang có menu phân vai trò, bảng có khung cuộn, phân trang Bootstrap, thông báo lỗi/thành công, tìm kiếm fetch giữ focus, nhập số lượng trực tiếp và QR trước thông tin đơn.
- Workspace không có Git. Khi được duyệt triển khai sẽ sao lưu tập tin trước khi sửa; bản khảo sát này chưa sửa file nghiệp vụ/giao diện.

Các mục trống trong mẫu đầu vào được đề xuất để nhóm duyệt: nâng cấp cả quản trị và khách hàng, giao diện sáng tối giản, giữ nhận diện xanh lá đậm/điểm nhấn ấm, font hệ thống hỗ trợ tiếng Việt, không thêm thư viện, chưa làm dark mode. Trình duyệt mục tiêu đề xuất Chrome/Edge/Firefox/Safari bản mới; hiện **chỉ Chrome headless đã được chạy thực tế**, chưa kiểm chứng các trình duyệt còn lại.

## Chứng cứ thực chạy

Lưu tại .runtime/ui-audit-before/ (không công bố hay upload ra ngoài):

| Ảnh | Nội dung |
|---|---|
| login-1440.png | Đăng nhập |
| menu-1440.png | Thực đơn |
| dashboard-1440.png | Báo cáo/biểu đồ |
| orders-1440.png | Bảng trạng thái đơn |
| dishes-1440.png | Bảng món trên desktop |
| dishes-1024.png | Bảng món 1024px |
| dishes-768.png | Bảng món 768px |
| dishes-360.png | Bảng món 360px |
| dish-form-1440.png | Form tạo món |
| dish-form-errors-1440.png | Validation phía server của form tạo món |

observations.json lưu kích thước, nút bấm, thuộc tính trường lỗi, header bảng, console và quan sát reduced-motion. Chỉ đăng nhập, GET các trang và gửi form thiếu trường bắt buộc để xem validation; không tạo/sửa/xóa bản ghi nghiệp vụ. Các ảnh là hiện trạng, không phải bản thiết kế mới.

Bảng món ở bốn độ rộng đều không làm tràn ngang document; tại 360px chữ/cột bị ép và thao tác nằm ngoài phần khung đang thấy, cần cải thiện bố cục cuộn. Chưa kết luận toàn bộ 55 view đều đạt responsive.

## Vấn đề theo mức ưu tiên

| Mức | Phát hiện | Dẫn chứng / hướng xử lý đề xuất |
|---|---|---|
| Ưu tiên cao | Vùng bấm nhỏ | Nút Chi tiết/Sửa/Xóa trong bảng đo được cao **31px**; nút ± giỏ hàng định nghĩa 33px. Chuẩn hóa ít nhất 36px, ưu tiên 44px trên cảm ứng. |
| Ưu tiên cao | Lỗi form chưa được liên kết đầy đủ | Bốn trường invalid của form tạo món thiếu aria-invalid và aria-describedby tới lỗi. Component field có lỗi tiếng Việt và is-invalid nhưng chưa có mã liên kết lỗi. Bổ sung thuộc tính hỗ trợ truy cập, giữ nguyên tên/id trường hiện có. |
| Ưu tiên cao | Chữ phụ trên nền kem thiếu tương phản so với mục tiêu | Tính từ màu hiện tại: #6c757d trên #f7f6f1 khoảng **4,33:1**, thấp hơn mức 4,5:1 người dùng đặt. Chữ chính 13,01:1, nút chính 8,27:1 và giá trên trắng 5,39:1 đạt mục tiêu này. Chưa kiểm tra toàn bộ trạng thái/mọi cặp màu. |
| Ưu tiên cao | Bảng mobile khó quét thông tin | Tên/loại/nhà cung cấp xuống quá nhiều dòng ở 360px. Cần quy định độ rộng cột, khung cuộn rõ, tên dễ đọc và thao tác dễ chạm. |
| Nên sửa | Token chưa đủ | app.css mới có ba token --ink/--accent/--paper nhưng có 28 mã màu riêng. Chưa có token typography, spacing, radius, shadow, duration/easing. |
| Nên sửa | Style rải rác | 5 style inline trong auth và bảng đơn; SVG biểu đồ có màu trực tiếp. Chuyển phần trình bày sang token/class dùng chung. |
| Nên sửa | Bảng thiếu trạng thái tương tác | Chưa có hover/focus-within/selected nhất quán, sticky header, số liệu căn phải/tabular-nums; 55 th chưa khai báo scope. Phần lớn action chỉ có chữ và kích thước nhỏ. |
| Nên sửa | Điều hướng chưa rõ vị trí | Navbar hiện position relative, chưa có breadcrumb và chỉ báo trang đang chọn nhất quán. Quản trị dùng dropdown; khách dùng navbar có giỏ hàng cạnh menu. |
| Nên sửa | Trạng thái tải/rỗng/lỗi còn cơ bản | Fetch có aria-busy, báo đang tải và lỗi; chưa có skeleton/khối retry riêng. Empty state phần lớn chỉ có chữ. Hộp xác nhận xóa hiện là confirm trình duyệt với thông điệp khá chung. |
| Nên sửa | Motion/focus của CSS riêng chưa thành hệ thống | app.css chưa có :focus-visible riêng hay override prefers-reduced-motion; transition opacity .12s của vùng kết quả vẫn còn khi mô phỏng reduce. Bootstrap có hỗ trợ riêng của nó; không suy ra mọi hiệu ứng của ứng dụng đều đã được xử lý. |
| Tùy chọn | Toast/modal, hiệu ứng card và số KPI | Chỉ bổ sung khi giúp phản hồi thao tác, có fallback/reduced-motion. Số tiền giữ đúng định dạng hiện có. |
| Tùy chọn | Dark mode, sidebar thu gọn | Chưa đề xuất làm mặc định để giữ phạm vi rõ. Có thể chọn tại bước duyệt phong cách. |

Bảng hiện chưa có chức năng sắp xếp cột. Không gắn icon/aria-sort tạo cảm giác có thể sắp xếp nếu chưa có hành vi thực. Thêm sorting là thay đổi chức năng riêng cần xác định phạm vi; không tự đổi truy vấn/route trong đợt nâng giao diện.

## Hướng triển khai đề xuất để duyệt

1. **Giai đoạn B:** một file tokens trung tâm, một file component styles; màu chủ đạo xanh lá đậm, nền sáng trung tính, điểm nhấn ấm, font hệ thống. Chốt bảng màu tương phản, cỡ chữ, spacing 4/8/12/16/24/32/48, bo góc/bóng và motion 120/200/320ms. Tạo trang/ảnh mẫu gồm bảng, form, badge, card, loading/rỗng/lỗi để duyệt; chưa áp đại trà trước khi được xác nhận phong cách.
2. **Giai đoạn C:** layout/nav trước, rồi bảng, form, button/badge/card, phản hồi, chi tiết và responsive. Ưu tiên thành phần dùng chung để đồng bộ 55 view; tận dụng Bootstrap hiện có, vanilla JS và SVG, không thêm dependency. Mỗi nhóm kiểm tra trước khi đi tiếp.
3. **Giai đoạn D:** kiểm theo checklist người dùng trên các màn hình thuộc phạm vi; tối đa 3 vòng kiểm tra/sửa. Ghi rõ đạt/chưa đạt/chưa kiểm chứng; chụp trước/sau và bàn giao danh sách file/cách chỉnh token/khôi phục.

Phạm vi code dự kiến: public/assets (tokens/styles/UI JS), resources/views/layouts, components, shared và các view cần class/thuộc tính ngữ nghĩa. Giữ các name/id/action/data hooks hiện có. Không đổi controller, route, tên biến server, database hay phân quyền. Giữ tìm kiếm/lọc/phân trang không reload, nhập số lượng trực tiếp, QR trước chi tiết đơn và quyền báo cáo chỉ Thu ngân/Chủ. Không đọc/sửa vendor, node_modules hay build cache trong đợt khảo sát này.

## Giới hạn khảo sát

- Đã thực chạy Chrome headless và xem ảnh của bảng desktop/mobile, form lỗi. Có 10 ảnh baseline, không có Runtime.exceptionThrown trong các màn hình khảo sát.
- Chưa kiểm tra Edge/Firefox/Safari hoặc điện thoại vật lý; kích thước 360/768/1024/1440 là viewport mô phỏng.
- Chưa chạy Lighthouse, đo FPS hay kết luận hiệu năng không giảm. Giá trị navigation trong JSON chỉ là quan sát tại máy này, không phải benchmark.
- Chưa audit toàn bộ đường tab/trình đọc màn hình, mọi màu, mọi trạng thái hay toàn bộ 55 view. Không dùng kết quả khảo sát để thay thế nghiệm thu giai đoạn D.
- Chưa có thay đổi UI nên chưa có ảnh sau, tokens mới hoặc kết quả kiểm thử bản nâng cấp.

## Điểm dừng và xác nhận

Theo yêu cầu Giai đoạn A: **“DỪNG và chờ xác nhận.”**

Chờ xác nhận đề xuất: **nâng cấp toàn bộ giao diện quản trị và khách hàng, phong cách sáng tối giản với xanh lá đậm/điểm nhấn ấm, font hệ thống, không thư viện mới, chưa dark mode**. Có thể điều chỉnh phạm vi/màu/phong cách trong cùng một phản hồi. Sau xác nhận mới tạo mẫu Giai đoạn B; tiếp tục chờ duyệt mẫu trước khi triển khai Giai đoạn C.
