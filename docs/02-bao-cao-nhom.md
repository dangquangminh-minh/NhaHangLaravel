# Báo cáo đồ án: Website quản lý nhà hàng và đặt món trực tuyến

## Thông tin nhóm và phê duyệt

- Môn học / lớp: **[Nhóm điền]**.
- Giảng viên: **[Nhóm điền]**.
- Nhóm / số thành viên: **[Nhóm xác nhận]**.
- Việc giảng viên phê duyệt đề tài: **CẦN NHÓM XÁC NHẬN**. Tài liệu không khẳng định đã được duyệt.
- Phiên bản triển khai: Laravel 13.37.0, PHP 8.3.30, MySQL 8.4.3; xem composer.lock và báo cáo kiểm thử.

## Lý do chọn, mục tiêu và phạm vi

Nhà hàng cần quản lý thực đơn, đơn món, đặt bàn và ưu đãi trong một hệ thống nhất quán. Đề tài xuất phát trực tiếp từ source ASP.NET MVC được cung cấp, thuận tiện đối chiếu khi học chuyển đổi công nghệ.

Mục tiêu: chuyển nghiệp vụ có trong source sang MVC Laravel; Eloquent/MySQL quản lý dữ liệu; Blade/Bootstrap cung cấp giao diện tiếng Việt; server bảo vệ quyền và dữ liệu cá nhân; migration/seeder/test giúp nhóm chạy và thuyết trình lại được.

Trong phạm vi: thực đơn, danh mục, nhà cung cấp, món và ảnh, đặt món/đơn/chi tiết, đặt bàn, khách/nhân viên, hồ sơ/địa chỉ/đánh giá/liên hệ, điểm thưởng/voucher và thống kê. Ngoài phạm vi: kế toán thuế, lương/ca làm, tồn kho nguyên liệu, đồng bộ Flutter cũ, cổng thanh toán và chuyển dữ liệu thật tự động.

## Đối tượng và bảng phân quyền

Admin là nhóm nhân viên vận hành, gồm Quản lý vận hành, Thu ngân, Phục vụ, Đầu bếp, Lễ tân, Nhân viên giao hàng và Quản lý kho; các chức vụ dùng nhóm quyền vận hành, riêng Thu ngân được xem báo cáo doanh thu/thống kê cùng Chủ. Chủ kế thừa nhóm vận hành, thêm quản lý nhân viên/voucher. User là khách hàng. Cập nhật theo phiên bản 18/10/2026.

| Nghiệp vụ | Khách chưa đăng nhập | User | Admin | Chủ nhà hàng |
|---|---|---|---|---|
| Xem/tìm/lọc thực đơn, chi tiết và đánh giá | Có | Có | Có | Có |
| Đăng ký | Tạo User | — | — | — |
| Gửi đặt bàn/liên hệ | Có | Có; đặt bàn gắn tài khoản | Có | Có |
| Giỏ hàng/đặt món/đổi voucher | Phải đăng nhập | Có | Không | Không |
| Xem đơn và lịch đặt bàn | Không | Chỉ của mình | Toàn bộ | Toàn bộ |
| Hủy đơn | Không | Của mình, trạng thái 1 | Mọi bước chưa kết thúc (1/2/3) | Như Admin |
| Chuyển trạng thái/xác nhận thanh toán | Không | Không | Có, theo luồng | Có |
| CRUD loại món/món/nhà cung cấp và ảnh | Không | Không | Có | Có |
| Thống kê doanh thu/doanh số và biểu đồ | Không | Không | Chỉ chức vụ Thu ngân | Có |
| Đọc/đánh dấu liên hệ | Không | Không | Có | Có |
| Thêm/sửa đánh giá | Không | Của mình; một/món | Không | Không |
| Xóa đánh giá | Không | Của mình | Tất cả | Tất cả |
| Hồ sơ/đổi mật khẩu | Không | Của mình | Của mình | Của mình |
| CRUD địa chỉ | Không | Chỉ của mình | Không | Không |
| Thêm/xóa khách, không sửa hồ sơ khách | Không | Không | Có | Có |
| CRUD nhân viên/voucher | Không | Không | Không | Có; nhân viên không được chọn owner |
| Tự xóa tài khoản hoặc tự đổi vai trò | Không | Không có chức năng | Không có chức năng | Bị chặn |

Chốt quyền: nhóm route `auth`, `RequireRole`, Gate `admin`/`owner`/`manage-accounts`/`view-reports`; OrderPolicy/AddressPolicy/ReviewPolicy; truy vấn lọc chủ sở hữu và kiểm tra đặt bàn. Các kiểm tra đều thực thi tại server, kể cả khi người dùng tự gửi request hoặc nhập URL. Blade chỉ hỗ trợ hiển thị phù hợp.

## Tổ chức MVC và luồng chính

- Route → middleware → Form Request → controller → Eloquent → Blade/redirect.
- CRUD danh mục đơn giản nằm trong controller, có whitelist trường và validation unique/FK.
- OrderService thực hiện đặt món, lưu nhiều bảng và chuyển trạng thái trong transaction.
- LoyaltyService tính điểm/đổi voucher; khóa user trước khi tính điểm, khóa offer trước khi trừ số lượng.
- Session Auth của Laravel dùng mật khẩu hash, regenerate sau đăng nhập; logout invalidate toàn bộ session và giỏ hàng.
- Giao diện dùng layout chung, component nhập liệu/xóa, partial tìm kiếm và thẻ món. Thực đơn/quản lý món dùng fetch cập nhật danh sách và phân trang; Blade vẫn dựng HTML phía server, không SPA. Asset tĩnh cục bộ; không có SPA.

### Đặt món

User chọn món → giỏ chỉ lưu dish_id/số lượng → nhập thông tin giao/bàn, hình thức thanh toán và mã voucher → server kiểm tra token đặt hàng, khóa khách/món/mã, đọc giá hiện tại → tạo orders và order_items → đánh dấu mã đã dùng → commit → xóa giỏ → xem chi tiết đơn. Tổng không âm; mã không thuộc khách hoặc hết hạn làm hủy toàn bộ thao tác.

### Duyệt và tích điểm

COD: Chờ xác nhận (1) → Đang làm (2) → Đang giao (3) → Đã thanh toán (4). Online: khách quét QR và báo chuyển khoản; nhân viên kiểm tra rồi đánh dấu đã nhận tiền, đơn vẫn (1); tiếp tục bấm (2) → (3) → Đã giao thành công (6). Không bỏ bước/quay ngược. Nhân viên hủy từ 1/2/3 sang 5; khách chỉ hủy đơn của mình từ 1. Các trạng thái 4/5/6 kết thúc. Đơn hủy sau khi nhận tiền giữ sự kiện nhận tiền và báo cần hoàn tiền thủ công. Thống kê/điểm chỉ tính đơn đã trả tiền và hoàn tất 4/6.

### Đổi và dùng voucher

Điểm khả dụng = floor(tổng tiền đơn đã thanh toán/10.000)×10 − tổng points_spent. Đổi voucher kiểm tra điểm/tồn/hạn → trừ tồn → tạo mã và snapshot quyền lợi. Điểm đã trừ không mất khỏi lịch sử khi mã được dùng. Checkout dùng mã một lần; hủy đơn hợp lệ mở lại mã, không hoàn điểm đổi hoặc tăng tồn kho.

## Thiết kế dữ liệu và sơ đồ

Từ điển đầy đủ: [03-thiet-ke-database.md](03-thiet-ke-database.md). ERD bên dưới phản ánh migration/model thực tế, không phải bản schema SQL cũ.

- [ERD Mermaid có thể chỉnh sửa](erd.mmd).
- [UML Use Case PlantUML có thể chỉnh sửa](use-case.puml). Sơ đồ có actor, ranh giới hệ thống, kế thừa actor, include/extend; không dùng lưu đồ thay Use Case.

Mở file `.mmd` trong công cụ hỗ trợ Mermaid; mở `.puml` bằng PlantUML. Không cần các công cụ sơ đồ để chạy ứng dụng.

## Đặc tả use case

### UC01 — Đăng nhập / đăng xuất

- Actor: User, Admin, Chủ nhà hàng.
- Tiền điều kiện: tài khoản tồn tại và người dùng chưa đăng nhập khi mở login.
- Luồng chính: nhập email/mật khẩu → kiểm tra định dạng và giới hạn lượt → Auth xác thực hash → regenerate session → chuyển về trang dự định hoặc thực đơn/dashboard. Logout qua POST có CSRF → invalidate session, xóa giỏ → trang đăng nhập.
- Thay thế/lỗi: sai thông tin hiện lỗi tiếng Việt, không đăng nhập; quá nhiều lượt trả 429; CSRF sai trả 419.
- Kết quả: phiên thuộc đúng tài khoản; không nhận role từ client.

### UC02 — CRUD loại món và món ăn

- Actor: Admin hoặc Chủ.
- Tiền điều kiện: đăng nhập, có quyền quản trị; loại/nhà cung cấp tồn tại khi tạo món.
- Luồng chính: mở danh sách → tìm/lọc/phân trang → tạo mới → chọn tên loại/nhà cung cấp, nhập giá/mô tả/ảnh → validate → lưu Eloquent → thông báo thành công. Chi tiết và sửa dùng bản ghi route; unique bỏ qua bản ghi hiện tại. Xóa qua DELETE sau xác nhận.
- Thay thế/lỗi: trùng tên/giá sai/FK không tồn tại/ảnh không hợp lệ → lỗi dưới trường và old input; loại có món hoặc món có chi tiết đơn/đánh giá → từ chối xóa; User gọi URL/request quản trị → 403; không có bản ghi → 404.
- Kết quả: dữ liệu đúng quan hệ; xóa nhiều rollback toàn bộ nếu một món không hợp lệ.

### UC03 — Đặt món

- Actor: User.
- Tiền điều kiện: đăng nhập User, giỏ có 1–50 món; mỗi món 1–99 phần và đang phục vụ.
- Luồng chính: cập nhật giỏ bằng nút ± hoặc gõ số lượng 1–99 (Enter/rời ô để lưu) → chọn thanh toán tiền mặt/chuyển khoản, bàn hoặc địa chỉ → gửi form CSRF và checkout token → server đọc giá/sở hữu địa chỉ, kiểm mã voucher nếu có → transaction lưu đơn và chi tiết/snapshot → tiêu mã → commit; COD vào chi tiết, online vào màn hình QR trước. Khách bấm Tôi đã chuyển khoản mới mở thông tin đơn, chưa tự đánh dấu đã nhận tiền.
- Thay thế/lỗi: giỏ rỗng/món ngừng bán/địa chỉ người khác/mã đã dùng/hết hạn → không tạo đơn, giữ giỏ để sửa; lỗi lưu chi tiết → rollback cả đơn và mã; token gửi lặp không tạo đơn trùng; tổng/đơn giá/user_id client không được tin cậy.
- Kết quả: đơn chờ xác nhận, chưa thanh toán, tổng = max(0, subtotal − discount).

### UC04 — Xem/hủy đơn

- Actor: User chủ đơn; Admin/Chủ xem toàn bộ.
- Tiền điều kiện: đăng nhập, đơn tồn tại, quyền xem hợp lệ.
- Luồng chính: mở lịch sử → chi tiết snapshot → User hủy khi trạng thái 1 → transaction chuyển 5, giữ dòng đơn, mở lại voucher nếu có.
- Thay thế/lỗi: đơn người khác → 403; không có → 404; không còn ở trạng thái chờ → báo không được hủy, không đổi dữ liệu.
- Kết quả: lịch sử còn nguyên; đơn hủy không tính doanh thu/điểm.

### UC05 — Duyệt đơn / xác nhận thanh toán

- Actor: Admin/Chủ.
- Tiền điều kiện: đơn chưa kết thúc và nhân viên có quyền.
- Luồng chính: mở trang quản lý trạng thái đơn → xem chi tiết. Với online, khách đã báo chuyển khoản thì nhân viên kiểm tra tiền và xác minh; chỉ cập nhật thông tin thanh toán, không đổi trạng thái. Nhân viên gọi xác nhận và bấm từng bước 1→2→3→4 (COD) hoặc 1→2→3→6 (online đã xác minh), lưu transaction khóa khách/đơn.
- Thay thế/lỗi: trạng thái không tồn tại hoặc bước ngược → từ chối; User gửi request → 403; đơn đã kết thúc (4/5/6) không sửa lại.
- Kết quả: thanh toán và thực hiện được theo dõi riêng, không tự xác minh tiền từ QR. Hủy sau khi nhận tiền gắn refund_required để xử lý hoàn tiền thủ công.

### UC06 — Đổi điểm lấy voucher

- Actor: User.
- Tiền điều kiện: đăng nhập, offer còn hạn/còn số lượng, điểm đủ.
- Luồng chính: xem điểm và offer → chọn đổi → khóa user/offer → tính điểm từ hóa đơn/lịch sử → trừ tồn → tạo mã unique và snapshot → commit → hiển thị trong hồ sơ.
- Thay thế/lỗi: thiếu điểm/hết kho/hết hạn → không trừ tồn, không tạo lịch sử; mã của người khác không sử dụng được.
- Kết quả: điểm giảm đúng points_spent; mã chưa dùng; điểm đã dùng tiếp tục được lưu khi checkout.

### UC07 — Quản lý hồ sơ/địa chỉ/đánh giá

- Actor: User; mọi tài khoản được sửa hồ sơ chính mình.
- Tiền điều kiện: đăng nhập; dữ liệu sửa/xóa phải thuộc chủ hiện tại.
- Luồng chính: sửa hồ sơ từ Auth user; email unique bỏ qua chính mình; đổi mật khẩu cần mật khẩu hiện tại. Địa chỉ tạo qua relation của Auth user, đầu tiên mặc định; chọn mặc định khác trong transaction. Đánh giá gắn Auth user và món, một đánh giá/món, được sửa/xóa.
- Thay thế/lỗi: gửi user_id/role giả bị bỏ qua; truy cập địa chỉ/đánh giá người khác bị chặn; định dạng/ảnh sai hiện lỗi; xóa địa chỉ mặc định chọn lại địa chỉ còn lại, đơn cũ giữ snapshot địa chỉ.
- Kết quả: không thay dữ liệu người khác, không lộ password; nội dung đánh giá/mô tả được escape khi hiển thị.

### UC08 — Đặt bàn và liên hệ

- Actor: Người truy cập; Admin xử lý.
- Tiền điều kiện: dữ liệu hợp lệ; giờ đặt bàn trong tương lai; 1–50 khách.
- Luồng chính: gửi form → lưu pending hoặc tin chưa đọc → thông báo; tài khoản đăng nhập có user_id cho đặt bàn. Admin xác nhận/hủy đặt bàn, đánh dấu tin đã đọc. User theo dõi yêu cầu mình tạo khi đăng nhập, chỉ tự hủy lúc chờ.
- Thay thế/lỗi: ngày quá khứ/số khách sai/email sai → lỗi tiếng Việt; quá nhiều lượt → 429; User không thay yêu cầu người khác. Đặt bàn chưa đăng nhập được nhà hàng xác nhận qua thông tin liên hệ, không tự gắn lại chỉ bằng email.
- Kết quả: yêu cầu có số bàn cụ thể 1–200, giữ 120 phút; khóa bảng bàn để chặn đặt trùng khung giờ. Tài khoản đặt món phải trùng email người giữ bàn trong khung hiệu lực; sai email báo bàn đã được đặt. Chưa có sơ đồ bố trí/sức chứa từng bàn.

### UC09 — Quản lý tài khoản và voucher

- Actor: Chủ nhà hàng.
- Tiền điều kiện: chủ quản lý nhân viên/voucher; Admin/chủ thêm/xóa khách. Nhân viên chỉ được chọn 7 mã trong Role::EMPLOYEE_NAMES; không có owner. Khách cố định user.
- Luồng chính: tìm tài khoản/offer → tạo khách hoặc tạo/sửa nhân viên/offer theo quyền; nhập tên Gmail, server ghép @gmail.com và kiểm tra email unique; mật khẩu hash → xem/xóa nếu chưa được tham chiếu. Khách tự sửa hồ sơ của mình; quản trị không có nút sửa và bị chặn GET/PUT trực tiếp.
- Thay thế/lỗi: User gọi route quản trị hoặc Admin gọi module chỉ chủ bị chặn; chỉnh khách qua URL/request bị 403; gửi quyền owner trong form nhân viên bị validation từ chối; tài khoản/voucher có liên kết không xóa; xóa nhiều có bản ghi bị chặn rollback toàn bộ.
- Kết quả: quyền không bị nâng bằng đăng ký/sửa hồ sơ; không làm mất lịch sử.

### UC10 — Thống kê

- Actor: Thu ngân hoặc Chủ nhà hàng.
- Tiền điều kiện: đăng nhập với mã vai trò cashier hoặc owner; ngày đúng định dạng, đến ngày không trước từ ngày, khoảng tối đa 366 ngày.
- Luồng chính: chọn khoảng ngày → lấy đơn trạng thái 4 hoặc 6 và is_paid → tính doanh thu sau giảm, nhóm theo ngày; chi tiết nhóm theo loại/top số phần cho doanh số trước giảm.
- Thay thế/lỗi: ngày sai hiện lỗi; kỳ không có đơn hiển thị trạng thái trống; Các chức vụ khác (kể cả Quản lý vận hành/admin) và User không xem được.
- Kết quả: hai biểu đồ số đơn hoàn tất và doanh thu theo ngày, cùng bảng chi tiết; số liệu ghi rõ ngày tạo đơn, trước/sau giảm; không nhận là doanh thu thực từ ngân hàng.

## Phân công nhóm — mẫu để điền thực tế

Bảng này là gợi ý chia module, **chưa phải xác nhận đóng góp**. Nhóm có thể gộp/tách dòng theo số người thực tế.

| Thành viên thực tế | Nhiệm vụ | File/module phụ trách | Sản phẩm bàn giao | Xác nhận / tiến độ thực tế |
|---|---|---|---|---|
| [Điền] | Phân tích C# / nghiệp vụ | docs/01, docs/04, source C# tham chiếu | Bảng đối chiếu và phạm vi | [Điền] |
| [Điền] | Database / dữ liệu demo | database/migrations, seeders, models | Schema, seeder, ERD | [Điền] |
| [Điền] | Xác thực / phân quyền | AuthController, middleware, policies, Requests | Đăng nhập và quyền server | [Điền] |
| [Điền] | Quản lý thực đơn | Category/Dish/SupplierController, views/admin | CRUD và tìm/phân trang | [Điền] |
| [Điền] | Đặt món / voucher | OrderService, LoyaltyService, Cart/Order/ProfileController | Giao dịch nhất quán | [Điền] |
| [Điền] | Hồ sơ / địa chỉ / tương tác | Profile/Address/Reservation/Contact/ReviewController | Màn hình khách và xử lý admin | [Điền] |
| [Điền] | Giao diện / triển khai | resources/views, public/assets, README | UI tiếng Việt, hướng dẫn Laragon | [Điền] |
| [Điền] | Test / tài liệu / thuyết trình | tests/Feature, docs/05, Use Case | Kết quả kiểm thử, slide/demo | [Điền] |

## Kịch bản demo gợi ý 12–15 phút

1. Mở trang chủ/thực đơn; tìm `Cơm`, lọc loại; thử từ khóa không có và chuyển trang giữ từ khóa.
2. Đăng nhập User mẫu; mở giỏ, thêm hai món, sửa số lượng; xem tên/giá và chi tiết món.
3. Dùng mã `DEMO20K` nếu chưa dùng; chọn tiền mặt hoặc chuyển khoản; tạo đơn và xem tổng. Không tuyên bố đã thu tiền thật.
4. Mở URL `/admin/categories` bằng User để thấy chặn quyền. Đăng ký User thứ hai và thử URL đơn/địa chỉ tài khoản mẫu để thấy chặn sở hữu.
5. Đăng nhập Admin; tạo loại mới, tạo món có loại và nhà cung cấp; sửa giá; cố xóa loại có món để thấy không xóa; xóa món demo mới rồi xóa loại đó.
6. Gửi form giá âm/tên trùng để thấy lỗi và giữ dữ liệu nhập; mở tài khoản khách để kiểm tra có giới hạn quyền.
7. Admin mở Quản lý trạng thái đơn: online báo chuyển khoản → nhân viên xác minh tiền → vẫn Chờ xác nhận → Đang làm → Đang giao → Đã giao thành công; COD kết thúc Đã thanh toán. Thử request quay ngược và hủy đơn kết thúc để thấy bị chặn. Quản lý vận hành (admin) mở /admin thấy 403; Thu ngân mở được báo cáo.
8. Đăng nhập lại User, xem điểm, đổi voucher và mã trong hồ sơ; thử mã đã dùng hoặc hết điểm. Thử hủy đơn mới, kiểm tra lịch sử giữ lại.
9. Tạo hai địa chỉ, chọn mặc định, sửa/xóa; gửi đặt bàn và đánh giá, rồi Admin xác nhận bàn/đọc tin.
10. Đăng nhập Chủ: xem hai biểu đồ, thêm/sửa nhân viên với nhiều chức vụ, xem quản lý khách/voucher. Admin/chủ chỉ thêm/xóa khách; thử URL sửa khách để thấy 403.
11. Mở migration/model, ERD, Use Case, bảng phân công đã nhóm điền và báo cáo test. Trình bày phần đã thực chạy so với phần SMTP/ngân hàng/mobile chưa triển khai.

Dùng database demo riêng; không làm demo xóa dữ liệu thật. Mỗi lần demo có thể tạo tên món/loại mới để tránh trùng. Kết quả tự động xem [05-kiem-thu-va-doi-chieu.md](05-kiem-thu-va-doi-chieu.md); kiểm tra trình bày trên màn hình/điện thoại là phần nhóm cần thực hiện tại máy thuyết trình.
