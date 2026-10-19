# Thay đổi, dữ liệu cũ và giới hạn

## Chức năng đã chuyển

Thực đơn/tìm kiếm/lọc/top món, quản lý loại/món/nhà cung cấp, ảnh đại diện/album, đăng ký/đăng nhập/đăng xuất/quên mật khẩu, hồ sơ/ảnh, giỏ hàng/đặt món, chi tiết/lịch sử/hủy/duyệt đơn, đặt bàn, liên hệ, nhân viên/khách hàng/voucher, tích điểm/đổi/dùng voucher, địa chỉ và đánh giá món, thống kê ngày/loại/top món. Bổ sung màn hình web cho địa chỉ/đánh giá vốn có ở controller mobile, quản lý liên hệ và đặt bàn cho Admin.

## Thay đổi có chủ đích

| Nội dung | Bản Laravel và lý do |
|---|---|
| Tài khoản chia hai bảng | Gộp users + roles để dùng Auth Laravel, email unique toàn hệ thống; giữ phân biệt khách/nhân viên/chủ. |
| 11 loại chức vụ trong SQL | Giữ ba nhóm quyền khách/nhân viên/chủ, bổ sung 7 chức vụ nhân viên hợp lý theo yêu cầu mới (Role::EMPLOYEE_NAMES). Các chức vụ dùng quyền vận hành; riêng Thu ngân được xem báo cáo doanh thu/thống kê cùng Chủ. Không triển khai module lương/ca làm. |
| Khách hủy xóa hóa đơn vĩnh viễn | Chuyển trạng thái 5, giữ chi tiết và lịch sử; không mất dữ liệu đã liên kết. |
| Áp dụng voucher | Nhập mã ở checkout; chưa lưu đơn thành công thì không tiêu voucher. Không xóa lịch sử, không tự hoàn lại số lượng khi dùng. |
| Hủy đơn có voucher | Mở lại mã đã dùng và bỏ FK voucher ở đơn bị hủy; giữ số giảm/tổng tiền lịch sử. Mã chỉ dùng tiếp nếu còn hạn. Điểm đổi vẫn bị trừ, không tăng tồn kho voucher. |
| Chỉnh voucher sau khi đổi | Lịch sử giữ ảnh chụp giá trị giảm, điểm đã trừ, hạn dùng; thay đổi offer không sửa quyền lợi đã đổi. |
| Khách tự xác nhận chuyển khoản | Bỏ quyền này. Chỉ Admin/chủ xác nhận đã nhận tiền; tránh tạo doanh thu và điểm giả. |
| VietQR/ngân hàng gắn cứng | Theo yêu cầu mới dùng MB BANK / QUAN AN VUI VE / 0333820793, tạo VietQR cục bộ bằng QRCode.js (MIT). Khách báo chuyển khoản, nhân viên xác minh thủ công; không tích hợp tự nhận giao dịch. Thông tin người nhận là do người dùng cung cấp, chưa đối soát ngân hàng. |
| Chuyển trạng thái tùy ý | Chỉ tiến đúng một bước: COD 1→2→3→4, online đã xác minh tiền 1→2→3→6; trạng thái 4/5/6 kết thúc. Nhân viên hủy từ 1/2/3; chủ đơn hủy từ 1. Xác minh tiền online tách riêng, không tự đổi trạng thái. |
| Bình luận nằm trong liên hệ | Tách reviews với FK dish_id/user_id; mỗi khách có một đánh giá/món, có sửa/xóa của mình; Admin xóa khi cần. Không tách món từ chuỗi chủ đề thiếu chính xác. |
| Password rõ / quên mật khẩu qua SMTP gắn cứng | Hash Laravel; reset token có hạn, không đổi mật khẩu trước khi xác thực token. Mặc định mail log, không chứa bí mật SMTP cũ. |
| Mô tả HTML tùy ý | Blade escape văn bản; giữ xuống dòng, không thực thi HTML để tránh XSS. |
| Xóa nhiều | Giữ cho món và tài khoản; transaction toàn bộ, có bản ghi bị chặn thì rollback. |
| API mobile cũ | Không xuất lại endpoint nhận ID/giá không xác thực. Nghiệp vụ đã đưa lên web; Flutter/client cũ cần viết lại kết nối API có token nếu muốn dùng tiếp. |
| Yêu thích | Không chuyển endpoint giả thành chức năng thật; gốc không có bảng/logic lưu yêu thích. |

## Chưa chuyển dữ liệu thật

Migration và Seeder tạo cấu trúc mới cùng dữ liệu **demo**. Một số tên món lấy cảm hứng từ INSERT mẫu trong SQL; không có quá trình import dữ liệu đang vận hành, không sao chép tài khoản khách thật. Tất cả email demo dùng miền `example.test`; mật khẩu demo đã hash.

Nếu cần chuyển dữ liệu thật, thực hiện một đợt riêng:

1. Nhóm xác định đúng database SQL Server đang dùng, sao lưu và xuất từng bảng ra CSV UTF-8; không chạy trực tiếp toàn bộ SQL mẫu vì có lệnh tạo bảng lặp và chênh lệch EDMX.
2. Xuất bảng ánh xạ mã cũ → mã mới cho loại món, nhà cung cấp, khách, nhân viên, món, trạng thái, voucher. Không giả định ID của hai bảng tài khoản không trùng nhau.
3. Dùng một database MySQL staging mới, chạy migration; nạp các bảng cha trước, con sau qua một Artisan import riêng sử dụng Eloquent, validation và transaction theo lô. Không import vào DB demo/thật trước khi được yêu cầu.
4. Chuẩn hóa email trùng/rỗng giữa khách và nhân viên trước khi áp unique. Mật khẩu rõ cũ không dùng để đăng nhập trực tiếp: yêu cầu reset, hoặc hash bằng Laravel trong đợt import được phê duyệt; không lưu bản rõ.
5. Đối chiếu số dòng, tổng tiền, số lượng chi tiết, FK mồ côi, trạng thái thanh toán, điểm và voucher với bản xuất. Với lịch sử voucher đã bị code C# xóa, không thể tự phục hồi nếu không có backup/audit.
6. Giữ unit_price và total lịch sử; xác định subtotal/discount thiếu từ dữ liệu gốc thay vì tự bịa. Bình luận có chủ đề không xác định món cần nhóm đối soát.
7. Sao chép ảnh hợp lệ vào nơi lưu mới; không tải/chạy file không phải ảnh. Chạy test trên staging, nhóm đối soát rồi mới lập kế hoạch đổi kết nối và hoàn tác.

## Giới hạn cần biết

- Đã có QR online và ghi nhận xác minh thủ công, chưa tự kết nối/xác minh giao dịch ngân hàng hoặc hoàn tiền tự động; chưa có email SMTP thực, đồng bộ app mobile hay nhập dữ liệu thật.
- Chưa quản lý tồn kho nguyên liệu, ca làm, hóa đơn thuế hay sơ đồ bàn. Source không có quy tắc đầy đủ cho các module này; không tự bổ sung đề tài khác.
- Đặt bàn có số 1–200, giữ 120 phút và kiểm tra email với bàn khi checkout. Chưa quản lý sức chứa/bố trí thực tế của từng bàn; dữ liệu đặt bàn cũ chưa có số vẫn NULL để nhóm đối soát.
- Danh mục thống kê dùng loại món hiện tại; tên/giá dòng đơn có snapshot. Bộ lọc thống kê dùng ngày tạo đơn, có payment_verified_at nhưng bộ lọc hiện vẫn theo ngày tạo đơn để giữ định nghĩa so sánh cũ.
- Transaction và khóa bản ghi bảo vệ thao tác cùng khách/voucher/đơn; chưa chạy kiểm thử tải hoặc nhiều tiến trình đồng thời.
- Quy tắc một địa chỉ mặc định được giữ qua transaction khóa user ở controller, không có unique index có điều kiện trong MySQL.
- Admin/chủ là người được tin cậy để duyệt thanh toán. Không tự cấp quyền khi đăng ký. Nhóm phải quản lý việc chia sẻ tài khoản demo.
- Ảnh demo được dùng lại từ source cung cấp; nhóm cần xác nhận quyền sử dụng ảnh khi công bố ngoài phạm vi đồ án.

## Phần nhóm cần hoàn tất

Điền thành viên và phân công thực tế, xác nhận với giảng viên việc duyệt đề tài, chạy demo trực quan trên máy thuyết trình/điện thoại, cấu hình Laragon theo README. Nếu cần mail thật hoặc chuyển khoản thật, tự cấu hình thông tin riêng và kiểm tra quy trình vận hành trước khi dùng.
